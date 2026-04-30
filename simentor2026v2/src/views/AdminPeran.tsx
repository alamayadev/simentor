import React, { useMemo, useState, useEffect } from 'react';
import {
  Plus,
  Shield,
  Users,
  Key,
  Edit3,
  Trash2,
  X,
  Loader2,
} from 'lucide-react';
import { motion } from 'motion/react';
import { useApiMutation, useApiQuery } from '../hooks/useApi';
import { usePagination } from '../hooks/usePagination';
import { PageHeader } from '../components/PageHeader';
import { LoadingSkeleton } from '../components/LoadingSkeleton';
import { ApiErrorBoundary } from '../components/ApiErrorBoundary';
import { ConfirmDialog } from '../components/ConfirmDialog';
import { adminRolesService, adminUsersService } from '../lib/api-services';
import type { Permission, Role, User } from '../types/api';

type RoleCardItem = {
  id: number;
  nama: string;
  deskripsi: string;
  guard: string;
  jumlahPengguna: number;
  izin: string[];
  color: string;
};

type RoleFormValues = {
  name: string;
};

type NoticeState = {
  tone: 'success' | 'error';
  message: string;
} | null;

const emptyForm: RoleFormValues = {
  name: '',
};

const roleGradients = [
  'from-red-200 to-red-300 dark:from-red-500/20 dark:to-red-500/20',
  'from-purple-200 to-purple-300 dark:from-purple-500/20 dark:to-purple-500/20',
  'from-blue-200 to-blue-300 dark:from-blue-500/20 dark:to-blue-500/20',
  'from-emerald-200 to-emerald-300 dark:from-emerald-500/20 dark:to-emerald-500/20',
  'from-amber-200 to-amber-300 dark:from-amber-500/20 dark:to-amber-500/20',
  'from-gray-200 to-gray-300 dark:from-gray-500/20 dark:to-gray-500/20',
];

function RoleFormModal({
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
  values: RoleFormValues;
  isLoading: boolean;
  onClose: () => void;
  onChange: (value: string) => void;
  onSubmit: () => void;
}) {
  if (!open) return null;

  return (
    <div 
      data-scan="modal form peran"
      className="fixed inset-0 z-50 flex items-center justify-center"
    >
      <div className="absolute inset-0 bg-black/40 backdrop-blur-sm" onClick={onClose} />
      <div 
        data-scan="modal form peran"
        className="relative bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-white/10 shadow-2xl w-full max-w-lg mx-4 flex flex-col"
      >
        <div className="flex items-center justify-between px-6 py-4 border-b border-gray-200/60 dark:border-white/10">
          <div>
            <h2 className="text-base font-bold text-gray-900 dark:text-white">
              {mode === 'create' ? 'Tambah Peran' : 'Edit Peran'}
            </h2>
            <p className="mt-1 text-sm text-gray-500 dark:text-gray-400">
              Definisikan nama peran untuk pengelompokan akses sistem.
            </p>
          </div>
          <button
            onClick={onClose}
            className="p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-white/10 text-gray-400 transition"
            type="button"
          >
            <X className="h-4 w-4" />
          </button>
        </div>

        <div className="px-6 py-5">
          <label className="block text-[11px] font-bold uppercase tracking-widest text-gray-500 dark:text-gray-400 mb-1.5">
            Nama Peran
          </label>
          <input
            type="text"
            value={values.name}
            onChange={(event) => onChange(event.target.value)}
            placeholder="Contoh: admin"
            className="w-full rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50/50 dark:bg-white/5 px-3 py-2 text-sm text-gray-900 dark:text-white placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 transition"
          />
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

export function AdminPeran() {
  const [showFormModal, setShowFormModal] = useState(false);
  const [editingRole, setEditingRole] = useState<Role | null>(null);
  const [deleteTarget, setDeleteTarget] = useState<Role | null>(null);
  const [notice, setNotice] = useState<NoticeState>(null);
  const [formValues, setFormValues] = useState<RoleFormValues>(emptyForm);
  const pagination = usePagination(6);

  const { data: rolesResponse, isLoading, error, refetch } = useApiQuery(
    ['admin-roles', pagination.params],
    () => adminRolesService.list(pagination.params),
  );

  useEffect(() => {
    pagination.sync(rolesResponse);
  }, [rolesResponse]);

  const { data: usersResponse } = useApiQuery(
    ['admin-users-role-counts'],
    () => adminUsersService.list({ per_page: 200 }),
  );

  const roles = useMemo(() => {
    const payload = rolesResponse?.data;
    if (Array.isArray(payload)) return payload as Role[];
    if (payload && typeof payload === 'object' && Array.isArray((payload as { data?: Role[] }).data)) {
      return (payload as { data: Role[] }).data;
    }
    return [];
  }, [rolesResponse]);

  const roleDetailsQuery = useApiQuery(
    ['admin-role-details', roles.map((role) => role.id).join(',')],
    async () => {
      if (roles.length === 0) {
        return { success: true, message: 'No roles', data: [] as Array<Role & { permissions?: Permission[] }> };
      }
      const detailResponses = await Promise.all(roles.map((role) => adminRolesService.get(role.id)));
      return {
        success: true,
        message: 'Role details loaded',
        data: detailResponses.map((response) => response.data as Role & { permissions?: Permission[] }),
      };
    },
    {
      enabled: roles.length > 0,
    },
  );

  const createMutation = useApiMutation(
    (payload: { name: string }) => adminRolesService.create(payload),
    {
      invalidateKeys: [['admin-roles']],
      onSuccess: () => {
        setNotice({ tone: 'success', message: 'Peran berhasil dibuat.' });
        setShowFormModal(false);
      },
      onError: (mutationError) => {
        setNotice({ tone: 'error', message: mutationError.message || 'Gagal membuat peran.' });
      },
    },
  );

  const updateMutation = useApiMutation(
    ({ id, data }: { id: number; data: { name: string } }) => adminRolesService.update(id, data),
    {
      invalidateKeys: [['admin-roles']],
      onSuccess: () => {
        setNotice({ tone: 'success', message: 'Peran berhasil diperbarui.' });
        setShowFormModal(false);
        setEditingRole(null);
      },
      onError: (mutationError) => {
        setNotice({ tone: 'error', message: mutationError.message || 'Gagal memperbarui peran.' });
      },
    },
  );

  const deleteMutation = useApiMutation(
    (id: number) => adminRolesService.delete(id),
    {
      invalidateKeys: [['admin-roles']],
      onSuccess: () => {
        setNotice({ tone: 'success', message: 'Peran berhasil dihapus.' });
        setDeleteTarget(null);
      },
      onError: (mutationError) => {
        setNotice({ tone: 'error', message: mutationError.message || 'Gagal menghapus peran.' });
      },
    },
  );

  const allUsers = useMemo(() => {
    const payload = usersResponse?.data;
    if (!payload || typeof payload !== 'object') return [] as User[];
    const organik = Array.isArray((payload as { organik_data?: User[] }).organik_data)
      ? (payload as { organik_data: User[] }).organik_data
      : [];
    const mitra = Array.isArray((payload as { mitra_data?: User[] }).mitra_data)
      ? (payload as { mitra_data: User[] }).mitra_data
      : [];
    return [...organik, ...mitra];
  }, [usersResponse]);

  const userCountByRole = useMemo(() => {
    const counts = new Map<string, number>();
    allUsers.forEach((user) => {
      user.roles?.forEach((role) => {
        counts.set(role.name, (counts.get(role.name) || 0) + 1);
      });
    });
    return counts;
  }, [allUsers]);

  const roleDetailsById = useMemo(() => {
    const details = (roleDetailsQuery.data?.data || []) as Array<Role & { permissions?: Permission[] }>;
    return new Map(details.map((detail) => [detail.id, detail]));
  }, [roleDetailsQuery.data]);

  const data = useMemo<RoleCardItem[]>(() => {
    return roles.map((role, index) => {
      const detail = roleDetailsById.get(role.id);
      const permissions = detail?.permissions?.map((permission) => permission.name) || [];
      const count = userCountByRole.get(role.name) || 0;

      return {
        id: role.id,
        nama: role.name,
        deskripsi:
          permissions.length > 0
            ? `Peran ini memiliki ${permissions.length} izin akses untuk guard ${role.guard_name || 'web'}.`
            : `Peran ini belum memiliki izin granular untuk guard ${role.guard_name || 'web'}.`,
        guard: role.guard_name || 'web',
        jumlahPengguna: count,
        izin: permissions.length > 0 ? permissions.slice(0, 5) : [role.guard_name || 'web'],
        color: roleGradients[index % roleGradients.length],
      };
    });
  }, [roleDetailsById, roles, userCountByRole]);

  const currentPage = pagination.page;

  const openCreateModal = () => {
    setEditingRole(null);
    setFormValues(emptyForm);
    setNotice(null);
    setShowFormModal(true);
  };

  const openEditModal = (role: Role) => {
    setEditingRole(role);
    setFormValues({ name: role.name });
    setNotice(null);
    setShowFormModal(true);
  };

  const handleSubmit = async () => {
    const payload = { name: formValues.name.trim() };

    if (!payload.name) {
      setNotice({ tone: 'error', message: 'Nama peran wajib diisi.' });
      return;
    }

    setNotice(null);

    if (editingRole) {
      await updateMutation.mutateAsync({ id: editingRole.id, data: payload });
      return;
    }

    await createMutation.mutateAsync(payload);
  };

  const handleRefresh = async () => {
    setNotice(null);
    await refetch();
  };

  return (
    <div className="space-y-6">
      <PageHeader
        title="Manajemen Peran"
        description="Kelola peran pengguna dan hak akses sistem."
        actions={
          <button
            data-scan="tombol tambah peran"
            onClick={openCreateModal}

            className="flex items-center gap-1.5 px-4 py-2 rounded-full bg-amber-300 hover:bg-amber-400 text-gray-900 text-sm font-semibold shadow-lg shadow-amber-500/20 transition"
          >
            <Plus className="h-4 w-4" />
            Tambah Peran
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

      {error ? (
        <ApiErrorBoundary error={error} onRetry={() => refetch()} title="Gagal Memuat Peran" />
      ) : (
        <>
          <div 
            data-scan="daftar peran"
            className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4"
          >

            {isLoading ? (
              Array.from({ length: 6 }).map((_, index) => (
                <div
                  key={index}
                  className="bg-white/65 dark:bg-white/[0.04] glass-strong rounded-[20px] border border-white/50 dark:border-white/10 p-6"
                >
                  <LoadingSkeleton variant="cards" />
                </div>
              ))
            ) : data.length === 0 ? (
              <div className="col-span-full bg-white/65 dark:bg-white/[0.04] glass-strong rounded-[20px] border border-white/50 dark:border-white/10 p-8 text-center text-sm text-gray-500 dark:text-gray-400">
                Belum ada data peran.
              </div>
            ) : (
              data.map((peran, index) => (
                <motion.div
                  key={peran.id}
                  initial={{ opacity: 0, y: 12 }}
                  animate={{ opacity: 1, y: 0 }}
                  transition={{ delay: index * 0.08 }}
                  className="bg-white/65 dark:bg-white/[0.04] glass-strong rounded-[20px] border border-white/50 dark:border-white/10 p-6 space-y-4"
                >
                  <div className="flex items-start justify-between">
                    <div className="flex items-center gap-3">
                      <div className={`h-10 w-10 rounded-xl bg-gradient-to-br ${peran.color} flex items-center justify-center`}>
                        <Shield className="h-5 w-5 text-gray-700 dark:text-gray-300" />
                      </div>
                      <div>
                        <h3 className="font-bold text-gray-900 dark:text-white text-[14px]">{peran.nama}</h3>
                        <p className="text-[11px] text-gray-500">ID {peran.id}</p>
                      </div>
                    </div>
                    <div className="flex gap-1">
                      <button
                        data-scan="tombol edit peran"
                        onClick={() => openEditModal(roles.find((role) => role.id === peran.id) || { id: peran.id, name: peran.nama, guard_name: peran.guard })}
                        className="p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-white/10 text-gray-400 transition"
                        type="button"
                      >
                        <Edit3 className="h-3.5 w-3.5" />
                      </button>
                      <button
                        data-scan="tombol hapus peran"
                        onClick={() => setDeleteTarget(roles.find((role) => role.id === peran.id) || { id: peran.id, name: peran.nama, guard_name: peran.guard })}
                        className="p-1.5 rounded-lg hover:bg-red-50 dark:hover:bg-red-500/10 text-red-400 transition"
                        type="button"
                      >
                        <Trash2 className="h-3.5 w-3.5" />
                      </button>
                    </div>
                  </div>

                  <p className="text-[12px] text-gray-500 dark:text-gray-400">{peran.deskripsi}</p>

                  <div className="flex items-center justify-between gap-3 text-[12px] text-gray-600 dark:text-gray-400">
                    <div className="flex items-center gap-2">
                      <Users className="h-3.5 w-3.5 opacity-50" />
                      <span>{peran.jumlahPengguna} pengguna</span>
                    </div>
                    <div className="flex items-center gap-2">
                      <Key className="h-3.5 w-3.5 opacity-50" />
                      <span>{roleDetailsById.get(peran.id)?.permissions?.length ?? 0} izin</span>
                    </div>
                  </div>

                  <div className="flex flex-wrap gap-1">
                    {peran.izin.map((izin) => (
                      <span key={izin} className="px-2 py-0.5 rounded-md bg-gray-100 dark:bg-white/5 text-[10px] font-mono font-bold text-gray-600 dark:text-gray-400">
                        {izin}
                      </span>
                    ))}
                  </div>
                </motion.div>
              ))
            )}
          </div>

          {!isLoading && data.length > 0 && (
            <div 
              data-scan="navigasi halaman"
              className="flex items-center justify-between px-1 py-1"
            >

              <p className="text-[12px] text-gray-500 dark:text-gray-400 font-medium">
                Halaman <span className="text-gray-900 dark:text-white font-bold">{currentPage}</span> dari{' '}
                <span className="text-gray-900 dark:text-white font-bold">{pagination.meta?.lastPage || 1}</span>
                {' '}• Menampilkan {data.length} dari {pagination.meta?.total || 0} peran
              </p>
              <div className="flex gap-2">
                <button
                  onClick={() => pagination.handlePrev()}
                  disabled={!pagination.canGoPrev || isLoading}
                  className="px-4 py-2 text-[11px] font-bold rounded-full bg-white/70 dark:bg-white/10 border border-white/50 dark:border-white/20 text-gray-700 dark:text-gray-300 hover:bg-white transition-all disabled:opacity-30 disabled:cursor-not-allowed shadow-sm"
                >
                  Sebelumnya
                </button>
                <button
                  onClick={() => {
                    const nextToken = (rolesResponse as any)?.links?.next_cursor || (rolesResponse as any)?.links?.next || (rolesResponse as any)?.links?.next_page_url;
                    pagination.handleNext(nextToken);
                  }}
                  disabled={!pagination.canGoNext || isLoading}
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
          )}
        </>
      )}

      <RoleFormModal
        open={showFormModal}
        mode={editingRole ? 'edit' : 'create'}
        values={formValues}
        isLoading={createMutation.isPending || updateMutation.isPending}
        onClose={() => {
          setShowFormModal(false);
          setEditingRole(null);
          setFormValues(emptyForm);
        }}
        onChange={(value) => setFormValues({ name: value })}
        onSubmit={() => void handleSubmit()}
      />

      <ConfirmDialog
        open={!!deleteTarget}
        onClose={() => setDeleteTarget(null)}
        onConfirm={() => deleteTarget && deleteMutation.mutate(deleteTarget.id)}
        title="Hapus Peran"
        message={`Yakin ingin menghapus peran "${deleteTarget?.name}"? Tindakan ini tidak dapat dibatalkan.`}
        confirmLabel="Hapus"
        variant="danger"
        isLoading={deleteMutation.isPending}
      />
    </div>
  );
}
