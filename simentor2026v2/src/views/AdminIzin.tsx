import React, { useMemo, useState, useEffect } from 'react';
import { usePagination } from '../hooks/usePagination';
import {
  Plus,
  Key,
  Edit3,
  Search,
  ToggleRight,
  X,
  Loader2,
  Trash2,
} from 'lucide-react';
import { motion } from 'motion/react';
import { useApiMutation, useApiQuery } from '../hooks/useApi';
import { PageHeader } from '../components/PageHeader';
import { LoadingSkeleton } from '../components/LoadingSkeleton';
import { ApiErrorBoundary } from '../components/ApiErrorBoundary';
import { ConfirmDialog } from '../components/ConfirmDialog';
import { adminPermissionsService } from '../lib/api-services';
import type { AdminPermissionGroup, Permission } from '../types/api';

type PermissionFormValues = {
  name: string;
  prefix: string;
};

type NoticeState = {
  tone: 'success' | 'error';
  message: string;
} | null;



const emptyForm: PermissionFormValues = {
  name: '',
  prefix: '',
};

const modulColors: Record<string, string> = {
  SKP: 'bg-orange-100 text-orange-700 dark:bg-orange-500/10 dark:text-orange-400',
  Kegiatan: 'bg-blue-100 text-blue-700 dark:bg-blue-500/10 dark:text-blue-400',
  'SPK & BAST': 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400',
  Umum: 'bg-amber-100 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400',
  IPDS: 'bg-purple-100 text-purple-700 dark:bg-purple-500/10 dark:text-purple-400',
  Admin: 'bg-red-100 text-red-700 dark:bg-red-500/10 dark:text-red-400',
  System: 'bg-gray-100 text-gray-700 dark:bg-white/5 dark:text-gray-400',
};

function inferModule(code: string) {
  const value = code.toLowerCase();
  if (value === '*' || value.startsWith('system')) return 'System';
  if (value.startsWith('skp')) return 'SKP';
  if (value.startsWith('kegiatan')) return 'Kegiatan';
  if (value.startsWith('kontraktual') || value.startsWith('spk') || value.startsWith('bast')) return 'SPK & BAST';
  if (value.startsWith('umum')) return 'Umum';
  if (value.startsWith('ipds') || value.startsWith('raw-data') || value.startsWith('asset') || value.startsWith('tiket')) return 'IPDS';
  if (value.startsWith('admin')) return 'Admin';
  return 'System';
}

function inferDescription(code: string, guard: string) {
  if (code === '*') return `Akses penuh seluruh sistem pada guard ${guard}.`;
  if (code.endsWith('.read') || code.endsWith('-view')) return `Izin untuk melihat resource ${code} pada guard ${guard}.`;
  if (code.endsWith('.write') || code.endsWith('-create') || code.endsWith('-update')) {
    return `Izin untuk menambah atau memperbarui resource ${code} pada guard ${guard}.`;
  }
  if (code.endsWith('.*') || code.endsWith('-delete')) return `Izin lanjutan untuk resource ${code} pada guard ${guard}.`;
  return `Izin akses ${code} pada guard ${guard}.`;
}

function inferGroupDescription(prefix: string, count: number, guard: string) {
  if (prefix === '*') return `Prefix ini memberi akses penuh lintas modul pada guard ${guard}.`;
  return `${count} izin akses tersedia pada modul ${prefix} untuk guard ${guard}.`;
}

function PermissionFormModal({
  open,
  mode,
  values,
  isLoading,
  onClose,
  onChange,
  onSubmit,
}: {
  open: boolean;
  mode: 'create' | 'edit';
  values: PermissionFormValues;
  isLoading: boolean;
  onClose: () => void;
  onChange: (field: keyof PermissionFormValues, value: string) => void;
  onSubmit: () => void;
}) {
  if (!open) return null;

  return (
    <div 
      data-scan="modal form izin"
      className="fixed inset-0 z-50 flex items-center justify-center"
    >
      <div className="absolute inset-0 bg-black/40 backdrop-blur-sm" onClick={onClose} />
      <div className="relative bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-white/10 shadow-2xl w-full max-w-lg mx-4 flex flex-col">
        <div className="flex items-center justify-between px-6 py-4 border-b border-gray-200/60 dark:border-white/10">
          <div>
            <h2 className="text-base font-bold text-gray-900 dark:text-white">
              {mode === 'create' ? 'Tambah Izin (Bulk Create)' : 'Edit Izin'}
            </h2>
            <p className="mt-1 text-sm text-gray-500 dark:text-gray-400">
              {mode === 'create'
                ? 'Masukkan prefix untuk membuat banyak izin sekaligus.'
                : 'Perbarui nama izin akses yang sudah ada.'}
            </p>
          </div>
          <button
            data-scan="tombol tutup modal"
            onClick={onClose}
            className="p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-white/10 text-gray-400 transition"
            type="button"
          >
            <X className="h-4 w-4" />
          </button>
        </div>

        <div className="px-6 py-5">
          {mode === 'create' ? (
            <>
              <label className="block text-[11px] font-bold uppercase tracking-widest text-gray-500 dark:text-gray-400 mb-1.5">
                Prefix
              </label>
              <input
                data-scan="input prefix izin"
                type="text"
                value={values.prefix}
                onChange={(event) => onChange('prefix', event.target.value)}
                placeholder="Contoh: raw-data"
                className="w-full rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50/50 dark:bg-white/5 px-3 py-2 text-sm text-gray-900 dark:text-white placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 transition"
              />
            </>
          ) : (
            <>
              <label className="block text-[11px] font-bold uppercase tracking-widest text-gray-500 dark:text-gray-400 mb-1.5">
                Nama Izin
              </label>
              <input
                data-scan="input nama izin"
                type="text"
                value={values.name}
                onChange={(event) => onChange('name', event.target.value)}
                placeholder="Contoh: raw-data-view"
                className="w-full rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50/50 dark:bg-white/5 px-3 py-2 text-sm text-gray-900 dark:text-white placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 transition"
              />
            </>
          )}
        </div>

        <div className="flex items-center justify-end gap-3 px-6 py-4 border-t border-gray-200/60 dark:border-white/10">
          <button
            type="button"
            onClick={onClose}
            disabled={isLoading}
            className="px-4 py-2 text-[12px] font-bold rounded-full bg-white/70 dark:bg-white/10 border border-white/50 dark:border-white/20 text-gray-700 dark:text-gray-300 hover:bg-white dark:hover:bg-white/10 transition-all shadow-sm disabled:opacity-50"
          >
            Batal
          </button>
          <button
            data-scan="tombol simpan izin"
            type="button"
            onClick={onSubmit}
            disabled={isLoading}
            className="flex items-center gap-1.5 px-5 py-2 text-[12px] font-bold rounded-full bg-amber-400 hover:bg-amber-500 text-gray-900 shadow-sm transition-all disabled:opacity-50"
          >
            {isLoading && <Loader2 className="h-3.5 w-3.5 animate-spin" />}
            {mode === 'create' ? 'Simpan' : 'Perbarui'}
          </button>
        </div>
      </div>
    </div>
  );
}

export function AdminIzin() {
  const [showFormModal, setShowFormModal] = useState(false);
  const [formMode, setFormMode] = useState<'create' | 'edit'>('create');
  const [editingPermission, setEditingPermission] = useState<Permission | null>(null);
  const [deleteTarget, setDeleteTarget] = useState<Permission | null>(null);
  const [notice, setNotice] = useState<NoticeState>(null);
  const [formValues, setFormValues] = useState<PermissionFormValues>(emptyForm);
  const [search, setSearch] = useState('');
  const [searchInput, setSearchInput] = useState('');
  const pagination = usePagination(6);

  const { data: response, isLoading, error, refetch } = useApiQuery(
    ['admin-permission-groups', search, pagination.params],
    () =>
      adminPermissionsService.groupedList({
        ...pagination.params,
        sort: 'prefix',
        'filter[search]': search || undefined,
      }),
  );

  useEffect(() => {
    pagination.sync(response);
  }, [response]);

  useEffect(() => {
    pagination.reset();
  }, [search]);

  const createMutation = useApiMutation(
    (payload: { prefix: string }) => adminPermissionsService.bulkCreate(payload),
    {
      invalidateKeys: [['admin-permissions'], ['admin-permission-groups']],
      onSuccess: () => {
        setNotice({ tone: 'success', message: 'Izin akses berhasil dibuat.' });
        setShowFormModal(false);
        pagination.reset();
      },
      onError: (mutationError) => {
        setNotice({ tone: 'error', message: mutationError.message || 'Gagal membuat izin akses.' });
      },
    },
  );

  const updateMutation = useApiMutation(
    ({ id, data }: { id: number; data: { name: string } }) => adminPermissionsService.update(id, data),
    {
      invalidateKeys: [['admin-permissions'], ['admin-permission-groups']],
      onSuccess: () => {
        setNotice({ tone: 'success', message: 'Izin akses berhasil diperbarui.' });
        setShowFormModal(false);
        setEditingPermission(null);
      },
      onError: (mutationError) => {
        setNotice({ tone: 'error', message: mutationError.message || 'Gagal memperbarui izin akses.' });
      },
    },
  );

  const deleteMutation = useApiMutation(
    (id: number) => adminPermissionsService.delete(id),
    {
      invalidateKeys: [['admin-permissions'], ['admin-permission-groups']],
      onSuccess: () => {
        setNotice({ tone: 'success', message: 'Izin akses berhasil dihapus.' });
        setDeleteTarget(null);
      },
      onError: (mutationError) => {
        setNotice({ tone: 'error', message: mutationError.message || 'Gagal menghapus izin akses.' });
      },
    },
  );

  const permissionGroups = useMemo(() => {
    return Array.isArray(response?.data) ? (response.data as AdminPermissionGroup[]) : [];
  }, [response]);

  // paginationMeta removed in favor of pagination hook

  const openCreateModal = () => {
    setFormMode('create');
    setEditingPermission(null);
    setFormValues(emptyForm);
    setNotice(null);
    setShowFormModal(true);
  };

  const openEditModal = (permission: Permission) => {
    setFormMode('edit');
    setEditingPermission(permission);
    setFormValues({ name: permission.name, prefix: '' });
    setNotice(null);
    setShowFormModal(true);
  };

  const handleSubmit = async () => {
    if (formMode === 'create') {
      const prefix = formValues.prefix.trim();
      if (!prefix) {
        setNotice({ tone: 'error', message: 'Prefix wajib diisi.' });
        return;
      }
      setNotice(null);
      await createMutation.mutateAsync({ prefix });
      return;
    }

    const name = formValues.name.trim();
    if (!editingPermission?.id) return;
    if (!name) {
      setNotice({ tone: 'error', message: 'Nama izin wajib diisi.' });
      return;
    }
    setNotice(null);
    await updateMutation.mutateAsync({ id: editingPermission.id, data: { name } });
  };

  const handleRefresh = async () => {
    setNotice(null);
    await refetch();
  };

  const handleSearchSubmit = (event: React.FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    pagination.reset();
    setSearch(searchInput.trim());
  };

  const clearSearch = () => {
    setSearchInput('');
    setSearch('');
    pagination.reset();
  };

  return (
    <div className="space-y-6">
      <PageHeader
        title="Izin Akses"
        description="Kelola daftar izin dan hak akses pada setiap modul."
        actions={
          <button
            data-scan="tombol tambah izin"
            onClick={openCreateModal}
            className="flex items-center gap-1.5 px-4 py-2 rounded-full bg-amber-300 hover:bg-amber-400 text-gray-900 text-sm font-semibold shadow-lg shadow-amber-500/20 transition"
          >
            <Plus className="h-4 w-4" />
            Tambah Izin
          </button>
        }
      />

      {notice && (
        <div
          className={`rounded-[20px] border px-4 py-3 text-sm ${
            notice.tone === 'success'
              ? 'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-300'
              : 'border-red-200 bg-red-50 text-red-700 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-300'
          }`}
        >
          {notice.message}
        </div>
      )}

      <div 
        data-scan="pencarian dan filter"
        className="bg-white/65 dark:bg-white/[0.04] glass-strong rounded-[24px] border border-white/50 dark:border-white/10 shadow-[0_10px_40px_-15px_rgba(0,0,0,0.08)] p-5"
      >
        <form onSubmit={handleSearchSubmit} className="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
          <div>
            <p className="text-[11px] font-bold uppercase tracking-[0.24em] text-gray-500 dark:text-gray-400">
              Filter Prefix
            </p>
            <p className="mt-1 text-sm text-gray-500 dark:text-gray-400">
              Cari grup izin berdasarkan prefix untuk menampilkan 1 kartu per modul.
            </p>
          </div>
          <div className="flex w-full flex-col gap-2 sm:flex-row md:w-auto">
            <div className="relative min-w-0 sm:min-w-[280px]">
              <Search className="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
              <input
                data-scan="input pencarian"
                type="text"
                value={searchInput}
                onChange={(event) => setSearchInput(event.target.value)}
                placeholder="Cari prefix, contoh: raw-data"
                className="w-full rounded-2xl border border-gray-200 dark:border-white/10 bg-white/80 dark:bg-white/5 py-2.5 pl-10 pr-4 text-sm text-gray-900 dark:text-white placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 transition"
              />
            </div>
            <div className="flex gap-2">
              <button
                type="submit"
                data-scan="tombol terapkan"
                className="px-4 py-2.5 text-[12px] font-bold rounded-full bg-slate-900 dark:bg-white text-white dark:text-slate-900 transition-all shadow-sm"
              >
                Terapkan
              </button>
              <button
                type="button"
                data-scan="tombol reset"
                onClick={clearSearch}
                className="px-4 py-2.5 text-[12px] font-bold rounded-full bg-white/70 dark:bg-white/10 border border-white/50 dark:border-white/20 text-gray-700 dark:text-gray-300 hover:bg-white transition-all shadow-sm"
              >
                Reset
              </button>
            </div>
          </div>
        </form>
      </div>

      <div 
        data-scan="daftar grup izin"
        className="bg-white/65 dark:bg-white/[0.04] glass-strong rounded-[24px] border border-white/50 dark:border-white/10 shadow-[0_10px_40px_-15px_rgba(0,0,0,0.08)] overflow-hidden"
      >
        {isLoading ? (
          <LoadingSkeleton variant="table" message="Memuat data izin akses..." />
        ) : error ? (
          <ApiErrorBoundary error={error} onRetry={() => refetch()} title="Gagal Memuat Izin Akses" />
        ) : permissionGroups.length === 0 ? (
          <div className="px-6 py-12 text-center text-gray-400 text-sm">
            Belum ada data izin akses.
          </div>
        ) : (
          <>
            <div className="grid gap-5 p-6 md:grid-cols-2">
              {permissionGroups.map((group, index) => (
                <motion.div
                  key={group.prefix}
                  initial={{ opacity: 0 }}
                  animate={{ opacity: 1 }}
                  transition={{ delay: index * 0.03 }}
                  className="rounded-[24px] border border-gray-200/70 dark:border-white/10 bg-[linear-gradient(145deg,rgba(255,255,255,0.95),rgba(245,247,250,0.92))] dark:bg-[linear-gradient(145deg,rgba(26,32,44,0.95),rgba(15,23,42,0.92))] shadow-[0_16px_40px_-24px_rgba(15,23,42,0.35)] overflow-hidden"
                >
                  <div className="flex items-start justify-between gap-3 border-b border-gray-200/70 dark:border-white/10 px-5 py-4">
                    <div className="min-w-0">
                      <div className="flex items-center gap-2">
                        <div className="h-10 w-10 rounded-2xl bg-gray-100 dark:bg-white/10 flex items-center justify-center flex-shrink-0">
                          <Key className="h-4 w-4 text-gray-500 dark:text-gray-400" />
                        </div>
                        <div>
                          <div className="flex items-center gap-2 flex-wrap">
                            <h3 className="font-bold text-gray-900 dark:text-white text-sm">
                              {group.prefix}
                            </h3>
                            <span
                              className={`px-1.5 py-0.5 rounded text-[9px] font-bold uppercase tracking-wider ${modulColors[inferModule(group.prefix)]}`}
                            >
                              {inferModule(group.prefix)}
                            </span>
                          </div>
                          <p className="mt-1 text-[12px] text-gray-500 dark:text-gray-400">
                            {inferGroupDescription(group.prefix, group.permissions_count, group.guard_name || 'web')}
                          </p>
                        </div>
                      </div>
                    </div>

                    <div className="text-right">
                      <p className="text-[10px] font-bold uppercase tracking-[0.24em] text-gray-400">Total Izin</p>
                      <p className="mt-1 text-lg font-black text-gray-900 dark:text-white">{group.permissions_count}</p>
                    </div>
                  </div>

                  <div className="divide-y divide-gray-100/70 dark:divide-white/5">
                    {group.permissions.map((permission) => (
                      <div
                        key={permission.id}
                        className="flex items-center gap-3 px-5 py-3 hover:bg-white/50 dark:hover:bg-white/[0.03] transition-colors"
                      >
                        <div className="min-w-0 flex-1">
                          <div className="flex items-center gap-2 flex-wrap">
                            <span className="font-semibold text-gray-900 dark:text-white text-[13px]">
                              {permission.name}
                            </span>
                            <code className="px-2 py-1 rounded-md bg-gray-100 dark:bg-white/5 text-[10px] font-mono font-bold text-gray-600 dark:text-gray-400">
                              {permission.guard_name || 'web'}
                            </code>
                          </div>
                          <p className="mt-1 text-[12px] text-gray-500 dark:text-gray-400">
                            {inferDescription(permission.name, permission.guard_name || 'web')}
                          </p>
                        </div>

                        <div className="flex items-center gap-1 flex-shrink-0">
                          <button
                            data-scan="tombol edit izin"
                            onClick={() => openEditModal(permission)}
                            className="p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-white/10 text-gray-400 transition"
                            type="button"
                            aria-label={`Edit ${permission.name}`}
                          >
                            <Edit3 className="h-3.5 w-3.5" />
                          </button>
                          <button
                            data-scan="tombol hapus izin"
                            onClick={() => setDeleteTarget(permission)}
                            className="p-1.5 rounded-lg hover:bg-red-50 dark:hover:bg-red-500/10 text-red-400 transition"
                            type="button"
                            aria-label={`Hapus ${permission.name}`}
                          >
                            <Trash2 className="h-3.5 w-3.5" />
                          </button>
                          <ToggleRight className="h-6 w-6 text-emerald-500" />
                        </div>
                      </div>
                    ))}
                  </div>
                </motion.div>
              ))}
            </div>

            <div 
              data-scan="navigasi halaman"
              className="flex items-center justify-between px-6 py-5 border-t border-gray-200/60 dark:border-white/10 dark:bg-white/5"
            >
              <div className="flex items-center gap-4">
                <p className="text-[12px] text-gray-500 dark:text-gray-400 font-medium">
                  Halaman <span className="text-gray-900 dark:text-white font-bold">{pagination.page}</span> dari{' '}
                  <span className="text-gray-900 dark:text-white font-bold">{pagination.totalPages}</span>
                </p>
                <div className="h-4 w-px bg-gray-200 dark:bg-white/10" />
                <p className="text-[12px] text-gray-500 dark:text-gray-400 font-medium">
                  Menampilkan <span className="font-bold text-gray-900 dark:text-white">{permissionGroups.length}</span> dari{' '}
                  <span className="font-bold text-gray-900 dark:text-white">{pagination.totalRecords}</span> grup izin
                </p>
              </div>
              <div className="flex gap-2">
                <button
                  onClick={() => pagination.handlePrev()}
                  disabled={!pagination.canPrev || isLoading}
                  className="px-4 py-2 text-[11px] font-bold rounded-full bg-white/70 dark:bg-white/10 border border-white/50 dark:border-white/20 text-gray-700 dark:text-gray-300 hover:bg-white transition-all disabled:opacity-30 disabled:cursor-not-allowed shadow-sm"
                >
                  Sebelumnya
                </button>
                <button
                  onClick={() => pagination.handleNext()}
                  disabled={!pagination.canNext || isLoading}
                  className="px-4 py-2 text-[11px] font-bold rounded-full bg-white/70 dark:bg-white/10 border border-white/50 dark:border-white/20 text-gray-700 dark:text-gray-300 hover:bg-white transition-all disabled:opacity-30 disabled:cursor-not-allowed shadow-sm"
                >
                  Berikutnya
                </button>
                <button
                  data-scan="tombol muat ulang"
                  onClick={() => void handleRefresh()}
                  className="px-4 py-2 text-[11px] font-bold rounded-full bg-slate-900 dark:bg-white text-white dark:text-slate-900 transition-all shadow-sm"
                >
                  Muat Ulang
                </button>
              </div>
            </div>
          </>
        )}
      </div>

      <PermissionFormModal
        open={showFormModal}
        mode={formMode}
        values={formValues}
        isLoading={createMutation.isPending || updateMutation.isPending}
        onClose={() => {
          setShowFormModal(false);
          setEditingPermission(null);
          setFormValues(emptyForm);
        }}
        onChange={(field, value) => setFormValues((prev) => ({ ...prev, [field]: value }))}
        onSubmit={() => void handleSubmit()}
      />

      <ConfirmDialog
        open={!!deleteTarget}
        onClose={() => setDeleteTarget(null)}
        onConfirm={() => deleteTarget && deleteMutation.mutate(deleteTarget.id)}
        title="Hapus Izin"
        message={`Yakin ingin menghapus izin "${deleteTarget?.name}"? Tindakan ini tidak dapat dibatalkan.`}
        confirmLabel="Hapus"
        variant="danger"
        isLoading={deleteMutation.isPending}
      />
    </div>
  );
}
