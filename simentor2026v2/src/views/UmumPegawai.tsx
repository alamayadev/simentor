import React, { useState, useMemo, useEffect } from 'react';
import { usePagination } from '../hooks/usePagination';
import {
  createColumnHelper,
  flexRender,
  getCoreRowModel,
  getPaginationRowModel,
  useReactTable,
} from '@tanstack/react-table';
import {
  Search,
  Plus,
  User,
  Mail,
  Phone,
  Building2,
  Ellipsis,
  Pencil,
  Trash2,
} from 'lucide-react';
import { motion } from 'motion/react';
import { useApiQuery, useApiMutation } from '../hooks/useApi';
import { useDebounce } from '../hooks/useDebounce';
import { PageHeader } from '../components/PageHeader';
import { LoadingSkeleton } from '../components/LoadingSkeleton';
import { ApiErrorBoundary } from '../components/ApiErrorBoundary';
import { EntityFormModal } from '../components/EntityFormModal';
import { ConfirmDialog } from '../components/ConfirmDialog';
import { pegawaiService } from '../lib/api-services';
import type { Pegawai as ApiPegawai } from '../types/api';

type PegawaiRow = {
  id: number;
  nip: string | null;
  nama: string;
  jabatan: string | null;
  gol: string | null;
  pangkat: string | null;
  no_hp: string | null;
  status: string | null;
  kelas: number | null;
  user_id: number | null;
};

const statusStyles: Record<string, string> = {
  aktif: 'bg-emerald-100 text-emerald-700 border-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-400',
  cuti: 'bg-amber-100 text-amber-700 border-amber-200 dark:bg-amber-500/10 dark:text-amber-400',
  tugas: 'bg-blue-100 text-blue-700 border-blue-200 dark:bg-blue-500/10 dark:text-blue-400',
  nonaktif: 'bg-gray-100 text-gray-600 border-gray-200 dark:bg-white/5 dark:text-gray-400',
};

const columnHelper = createColumnHelper<PegawaiRow>();

const columns = [
  columnHelper.accessor('nama', {
    header: 'Nama',
    cell: (info) => (
      <span className="font-medium text-slate-900 dark:text-white">
        {info.getValue()}
      </span>
    ),
  }),
  columnHelper.accessor('nip', {
    header: 'NIP',
    cell: (info) => <span>{info.getValue() || '-'}</span>,
  }),
  columnHelper.accessor('pangkat', {
    header: 'Pangkat/Gol',
    cell: (info) => (
      <span>
        {info.row.original.pangkat} / {info.row.original.gol}
      </span>
    ),
  }),
  columnHelper.accessor('jabatan', {
    header: 'Jabatan',
    cell: (info) => <span>{info.getValue() || '-'}</span>,
  }),
  columnHelper.accessor('kelas', {
    header: 'Kelas',
    cell: (info) => <span>{info.getValue() || '-'}</span>,
  }),
  columnHelper.display({
    id: 'actions',
    header: () => <div className="text-right">Aksi</div>,
    cell: (info) => {
      const row = info.row.original;
      const meta = info.table.options.meta as any;
      return (
        <div className="text-right">
          <ActionMenu
            id={row.id}
            onEdit={() => meta?.onEdit?.(row)}
            onDelete={() => meta?.onDelete?.(row)}
            isOpen={meta?.openMenuId === row.id}
            onToggle={() => meta?.setOpenMenuId?.(meta?.openMenuId === row.id ? null : row.id)}
          />
        </div>
      );
    },
  }),
];

// Helper sub-component for the action menu to keep things clean
function ActionMenu({ id, onEdit, onDelete, isOpen, onToggle }: { id: number, onEdit: () => void, onDelete: () => void, isOpen: boolean, onToggle: () => void }) {
  return (
    <div className="relative flex justify-end">
      <button
        onClick={(e) => {
          e.stopPropagation();
          onToggle();
        }}
        className="inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-none text-sm font-medium transition-all hover:bg-accent hover:text-accent-foreground dark:hover:bg-accent/50 size-9 outline-none"
        type="button"
      >
        <Ellipsis className="h-4 w-4" />
      </button>
      
      {isOpen && (
        <div className="absolute right-0 top-full mt-1 z-50 min-w-[140px] bg-white dark:bg-gray-950 border border-gray-200 dark:border-white/10 rounded-xl shadow-xl py-1 transform origin-top-right transition-all overflow-hidden scale-in-95">
          <button
            onClick={(e) => { e.stopPropagation(); onEdit(); }}
            className="w-full flex items-center gap-2 px-3 py-2 text-xs font-bold text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-white/5 transition-colors"
          >
            <Pencil className="h-3.5 w-3.5 text-amber-500" />
            Edit Pegawai
          </button>
          <div className="h-px bg-gray-100 dark:bg-white/5 mx-2" />
          <button
            onClick={(e) => { e.stopPropagation(); onDelete(); }}
            className="w-full flex items-center gap-2 px-3 py-2 text-xs font-bold text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-500/10 transition-colors"
          >
            <Trash2 className="h-3.5 w-3.5" />
            Hapus Data
          </button>
        </div>
      )}
    </div>
  );
}

export function UmumPegawai() {
  const [searchInput, setSearchInput] = useState('');
  const [searchQuery, setSearchQuery] = useState('');
  const [filterPangkat, setFilterPangkat] = useState('');
  const [filterJabatan, setFilterJabatan] = useState('');
  const [openMenuId, setOpenMenuId] = useState<number | null>(null);
  const pagination = usePagination(10);

  // Modal states
  const [showFormModal, setShowFormModal] = useState(false);
  const [editingRow, setEditingRow] = useState<PegawaiRow | null>(null);
  const [deleteTarget, setDeleteTarget] = useState<PegawaiRow | null>(null);

  const { data: optionsData } = useApiQuery(
    ['pegawai-filters'],
    () => pegawaiService.filters(),
  );

  const { data: formOptionsData } = useApiQuery(
    ['pegawai-form-options'],
    () => pegawaiService.formOptions(),
  );

  const { data: response, isLoading, error, refetch } = useApiQuery(
    ['pegawai', searchQuery, filterPangkat, filterJabatan, pagination.params],
    () => pegawaiService.list({
      ...pagination.params,
      search: searchQuery || undefined,
      'filter[pangkat]': filterPangkat || undefined,
      'filter[jabatan]': filterJabatan || undefined,
    }),
  );

  useEffect(() => {
    pagination.sync(response);
  }, [response]);

  useEffect(() => {
    pagination.reset();
  }, [searchQuery, filterPangkat, filterJabatan]);

  // Mutations
  const createMutation = useApiMutation(
    (data: any) => pegawaiService.create(data),
    {
      invalidateKeys: [['pegawai']],
      onSuccess: () => setShowFormModal(false),
    }
  );

  const updateMutation = useApiMutation(
    ({ id, data }: { id: number, data: any }) => pegawaiService.update(id, data),
    {
      invalidateKeys: [['pegawai']],
      onSuccess: () => {
        setShowFormModal(false);
        setEditingRow(null);
      }
    }
  );

  const deleteMutation = useApiMutation(
    (id: number) => pegawaiService.delete(id),
    {
      invalidateKeys: [['pegawai']],
      onSuccess: () => setDeleteTarget(null),
    }
  );

  const options = useMemo(() => {
    const data = optionsData?.data as any;
    const raw = optionsData as any;
    const jabatanList = data?.jabatanList ?? raw?.jabatanList ?? [];
    const pegawaiList = data?.pegawaiList ?? raw?.pegawaiList ?? [];

    return {
      jabatan: jabatanList.map((j: string) => (j || '').trim()).filter(Boolean),
      pegawai: pegawaiList.map((p: string) => (p || '').trim()).filter(Boolean),
    };
  }, [optionsData]);

  const formOptions = useMemo(() => {
    const data = formOptionsData?.data as any;
    const raw = formOptionsData as any;
    return {
      pangkat: data?.pangkat ?? raw?.pangkat ?? [],
      golongan: data?.golongan ?? raw?.golongan ?? [],
      jabatan: data?.jabatan ?? raw?.jabatan ?? [],
      userNoPegawai: data?.userNoPegawai ?? raw?.userNoPegawai ?? [],
      userPegawai: data?.userPegawai ?? raw?.userPegawai ?? [],
    };
  }, [formOptionsData]);

  const pegawaiData: PegawaiRow[] = useMemo(() => {
    const data = response?.data;
    const rawItems = Array.isArray(data) ? data : (data as any)?.data;
    if (!Array.isArray(rawItems)) return [];
    
    return rawItems.map((p: ApiPegawai) => ({
      id: p.id,
      nip: p.nip || '-',
      nama: p.nama,
      jabatan: p.jabatan,
      gol: p.gol,
      pangkat: p.pangkat,
      no_hp: p.no_hp,
      status: p.status || 'aktif',
      kelas: p.kelas,
      user_id: p.user_id,
    }));
  }, [response]);

  const userAccountOptions = useMemo(() => {
    const source = editingRow
      ? [...formOptions.userPegawai, ...formOptions.userNoPegawai]
      : formOptions.userNoPegawai;

    const seen = new Set<string>();
    return source
      .map((user: any) => ({ value: String(user.id), label: user.name }))
      .filter((option: { value: string; label: string }) => {
        if (seen.has(option.value)) return false;
        seen.add(option.value);
        return true;
      });
  }, [editingRow, formOptions.userNoPegawai, formOptions.userPegawai]);

  const formFields = useMemo(() => [
    { name: 'nama', label: 'Nama Lengkap', type: 'text' as const, placeholder: 'Nama...', required: true, colSpan: 2 as const },
    { name: 'nip', label: 'NIP', type: 'text' as const, placeholder: 'NIP...', required: true },
    { name: 'pangkat', label: 'Pangkat', type: 'select' as const, options: formOptions.pangkat.map((p: string) => ({ value: p, label: p })) },
    { name: 'gol', label: 'Golongan', type: 'select' as const, options: formOptions.golongan.map((g: string) => ({ value: g, label: g })) },
    { name: 'jabatan', label: 'Jabatan', type: 'select' as const, options: formOptions.jabatan.map((j: string) => ({ value: j, label: j })) },
    { name: 'kelas', label: 'Kelas Jabatan', type: 'number' as const },
    { name: 'no_hp', label: 'No. HP', type: 'text' as const },
    {
      name: 'user_id',
      label: 'User Account',
      type: 'select' as const,
      options: userAccountOptions,
    },
  ], [formOptions, userAccountOptions]);

  const handleFormSubmit = async (data: any) => {
    // Sanitize data
    const payload = { ...data };
    
    // user_id must be a valid existing user ID or omitted
    if (!payload.user_id || parseInt(payload.user_id) <= 0) {
      delete payload.user_id;
    } else {
      payload.user_id = parseInt(payload.user_id);
    }

    // Ensure numeric fields are correctly typed if needed, 
    // but Laravel 'sometimes|required' rules are usually fine with strings
    if (payload.kelas) payload.kelas = payload.kelas.toString();

    if (editingRow) {
      await updateMutation.mutateAsync({ id: editingRow.id, data: payload });
    } else {
      await createMutation.mutateAsync(payload);
    }
  };

  const stats = {
    total: pegawaiData.length,
    aktif: pegawaiData.filter((p) => !p.status || p.status === 'aktif').length,
    cuti: pegawaiData.filter((p) => p.status === 'cuti').length,
    tugas: pegawaiData.filter((p) => p.status === 'tugas').length,
  };

  const modalInitialData = useMemo(() => {
    if (!editingRow) return null;

    return {
      ...editingRow,
      user_id: editingRow.user_id ? String(editingRow.user_id) : '',
    };
  }, [editingRow]);

  const table = useReactTable({
    data: pegawaiData,
    columns,
    getCoreRowModel: getCoreRowModel(),
    manualPagination: true,
    meta: {
      openMenuId,
      setOpenMenuId,
      onEdit: (row: PegawaiRow) => {
        setEditingRow(row);
        setShowFormModal(true);
        setOpenMenuId(null);
      },
      onDelete: (row: PegawaiRow) => {
        setDeleteTarget(row);
        setOpenMenuId(null);
      }
    }
  });

  const handleApply = () => {
    setSearchQuery(searchInput);
    refetch();
  };

  const handleReset = () => {
    setSearchInput('');
    setSearchQuery('');
    setFilterPangkat('');
    setFilterJabatan('');
    refetch();
  };

  return (
    <div className="space-y-6">
      <PageHeader
        title="Umum Pegawai"
        description="Direktori pegawai dan informasi kepegawaian."
      />

      <div 
        data-scan="ringkasan statistik"
        className="grid grid-cols-2 lg:grid-cols-4 gap-4"
      >
        {[
          { label: 'Total Pegawai', value: stats.total, color: 'text-orange-600 dark:text-orange-400' },
          { label: 'Aktif', value: stats.aktif, color: 'text-emerald-600 dark:text-emerald-400' },
          { label: 'Cuti', value: stats.cuti, color: 'text-amber-600 dark:text-amber-400' },
          { label: 'Tugas Luar', value: stats.tugas, color: 'text-blue-600 dark:text-blue-400' },
        ].map((s, i) => (
          <motion.div
            key={i}
            initial={{ opacity: 0, y: 12 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ delay: i * 0.08 }}
            className="bg-white/65 dark:bg-white/[0.04] glass-strong rounded-[20px] border border-white/50 dark:border-white/10 p-5 space-y-2"
          >
            <p className="text-[11px] font-bold uppercase tracking-widest text-gray-500 dark:text-gray-400">{s.label}</p>
            <p className={`text-2xl font-black ${s.color}`}>{isLoading ? '...' : s.value}</p>
          </motion.div>
        ))}
      </div>

      <div 
        data-scan="pencarian dan aksi"
        className="bg-white/65 dark:bg-white/[0.04] glass-strong rounded-[24px] border border-white/50 dark:border-white/10 p-6 space-y-6 shadow-sm"
      >
        <div className="flex flex-wrap items-end gap-3">
          <div className="flex w-full max-w-xs flex-col gap-2">
            <label className="text-[10px] font-bold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Pencarian</label>
            <div className="relative group">
              <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-3.5 w-3.5 text-gray-400 group-focus-within:text-amber-500 transition-colors" />
              <input
                type="text"
                placeholder="Cari nama, NIP, atau jabatan..."
                value={searchInput}
                onChange={(e) => setSearchInput(e.target.value)}
                onKeyDown={(e) => e.key === 'Enter' && handleApply()}
                className="w-full h-10 rounded-xl border border-gray-200 dark:border-white/10 bg-white/70 dark:bg-white/5 pl-9 pr-4 text-xs text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-amber-500/20 transition-all outline-none"
              />
            </div>
          </div>
          <button
            data-scan="tombol terapkan"
            onClick={handleApply}
            className="h-10 px-6 rounded-full bg-slate-900 dark:bg-white text-white dark:text-slate-900 text-[11px] font-bold shadow-sm hover:scale-[1.02] active:scale-[0.98] transition-all"
          >
            Terapkan
          </button>
          <button
            data-scan="tombol reset"
            onClick={handleReset}
            className="h-10 px-6 rounded-full bg-white/70 dark:bg-white/10 border border-gray-200 dark:border-white/10 text-gray-700 dark:text-gray-300 text-[11px] font-bold shadow-sm hover:bg-white transition-all"
          >
            Reset
          </button>
          <div className="flex-1" />
          <button 
            data-scan="tombol tambah pegawai"
            onClick={() => { setEditingRow(null); setShowFormModal(true); }}
            className="h-10 px-6 rounded-full bg-amber-400 hover:bg-amber-500 text-gray-900 text-[11px] font-bold shadow-sm shadow-amber-500/10 transition-all flex items-center gap-2"
          >
            <Plus className="h-3.5 w-3.5" />
            Tambah Pegawai
          </button>
        </div>

        <div 
          data-scan="filter lanjutan"
          className="flex flex-wrap items-center gap-6 pt-4 border-t border-gray-100/50 dark:border-white/5"
        >
          <div className="flex items-center gap-3">
            <label className="text-[10px] font-bold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Pegawai</label>
            <select
              value={searchQuery}
              onChange={(e) => {
                setSearchInput(e.target.value);
                setSearchQuery(e.target.value);
              }}
              className="h-9 rounded-lg border border-gray-200 dark:border-white/10 bg-white/70 dark:bg-white/5 px-4 text-[11px] font-bold text-gray-700 dark:text-gray-200 outline-none transition focus:border-amber-500 dark:[color-scheme:dark]"
            >
              <option value="">Semua Pegawai</option>
              {options.pegawai.map((p: string) => (
                <option key={p} value={p}>{p}</option>
              ))}
            </select>
          </div>

          <div className="flex items-center gap-3">
            <label className="text-[10px] font-bold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Jabatan</label>
            <select
              value={filterJabatan}
              onChange={(e) => setFilterJabatan(e.target.value)}
              className="h-9 rounded-lg border border-gray-200 dark:border-white/10 bg-white/70 dark:bg-white/5 px-4 text-[11px] font-bold text-gray-700 dark:text-gray-200 outline-none transition focus:border-amber-500 dark:[color-scheme:dark]"
            >
              <option value="">Semua Jabatan</option>
              {options.jabatan.map((j: string) => (
                <option key={j} value={j}>{j}</option>
              ))}
            </select>
          </div>
        </div>
      </div>

      <div 
        data-scan="tabel pegawai"
        className="bg-white/65 dark:bg-white/[0.04] glass-strong rounded-[24px] border border-white/50 dark:border-white/10 shadow-[0_10px_40px_-15px_rgba(0,0,0,0.08)] overflow-hidden"
      >
        {isLoading ? (
          <LoadingSkeleton variant="table" message="Memuat data pegawai..." />
        ) : error ? (
          <ApiErrorBoundary error={error} onRetry={() => refetch()} title="Gagal Memuat Pegawai" />
        ) : (
          <>
            <div className="overflow-x-auto">
              <table data-slot="table" className="w-full caption-bottom text-sm">
                <thead data-slot="table-header" className="[&_tr]:border-b">
                  <tr data-slot="table-row" className="border-b transition-colors hover:bg-muted/50 data-[state=selected]:bg-muted">
                    {table.getHeaderGroups().map((hg) => (
                      <React.Fragment key={hg.id}>
                        {hg.headers.map((h) => (
                          <th
                            key={h.id}
                            data-slot="table-head"
                            className="h-10 px-6 text-start align-middle font-medium whitespace-nowrap text-foreground"
                          >
                            {flexRender(h.column.columnDef.header, h.getContext())}
                          </th>
                        ))}
                      </React.Fragment>
                    ))}
                  </tr>
                </thead>
                <tbody data-slot="table-body" className="[&_tr:last-child]:border-0">
                  {table.getRowModel().rows.map((row) => (
                    <tr
                      key={row.id}
                      data-slot="table-row"
                      className="border-b transition-colors hover:bg-muted/50 data-[state=selected]:bg-muted"
                    >
                      {row.getVisibleCells().map((cell) => (
                        <td
                          key={cell.id}
                          data-slot="table-cell"
                          className="p-2 px-6 align-middle whitespace-nowrap"
                        >
                          {flexRender(cell.column.columnDef.cell, cell.getContext())}
                        </td>
                      ))}
                    </tr>
                  ))}
                  {table.getRowModel().rows.length === 0 && (
                    <tr data-slot="table-row">
                      <td colSpan={columns.length} className="px-6 py-12 text-center text-gray-400 text-sm">
                        Tidak ada data pegawai.
                      </td>
                    </tr>
                  )}
                </tbody>
              </table>
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
                  Menampilkan <span className="font-bold text-gray-900 dark:text-white">{pegawaiData.length}</span> dari <span className="font-bold text-gray-900 dark:text-white">{pagination.totalRecords}</span> pegawai
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
              </div>
            </div>
          </>
        )}
      </div>

      {/* Modals */}
      <EntityFormModal
        open={showFormModal}
        onClose={() => { setShowFormModal(false); setEditingRow(null); }}
        onSubmit={handleFormSubmit}
        title={editingRow ? 'Edit Pegawai' : 'Tambah Pegawai'}
        fields={formFields}
        initialData={modalInitialData}
        isLoading={createMutation.isPending || updateMutation.isPending}
        isSubmitDisabled={!editingRow && formOptions.userNoPegawai.length === 0}
        mode={editingRow ? 'edit' : 'create'}
      />

      <ConfirmDialog
        open={!!deleteTarget}
        onClose={() => setDeleteTarget(null)}
        onConfirm={() => deleteTarget && deleteMutation.mutate(deleteTarget.id)}
        title="Hapus Pegawai"
        message={`Yakin ingin menghapus data pegawai "${deleteTarget?.nama}"? Tindakan ini tidak dapat dibatalkan.`}
        variant="danger"
        isLoading={deleteMutation.isPending}
      />
    </div>
  );
}
