import React, { useState, useMemo, useCallback, useEffect } from 'react';
import { usePagination } from '../hooks/usePagination';
import {
  createColumnHelper,
  flexRender,
  getCoreRowModel,
  getPaginationRowModel,
  useReactTable,
} from '@tanstack/react-table';
import {
  Plus,
  Search,
  MessageSquare,
  Clock,
  AlertCircle,
  CheckCircle2,
  User,
  Pencil,
  Trash2,
  Filter,
  SlidersHorizontal,
} from 'lucide-react';
import { motion } from 'motion/react';
import { useQueryClient } from '@tanstack/react-query';
import { useApiQuery, useApiMutation } from '../hooks/useApi';
import { useDebounce } from '../hooks/useDebounce';
import { PageHeader } from '../components/PageHeader';
import { LoadingSkeleton } from '../components/LoadingSkeleton';
import { ApiErrorBoundary } from '../components/ApiErrorBoundary';
import { EntityFormModal, type FormField } from '../components/EntityFormModal';
import { tiketService } from '../lib/api-services';
import type { Tiket as ApiTiket } from '../types/api';

type TiketRow = {
  id: number;
  deskripsi: string;
  pelapor: string;
  jenis_keluhan: string;
  status: string;
  tanggal: string;
  keterangan: string | null;
  user_id: number;
};

const statusStyles: Record<string, string> = {
  Pending: 'bg-purple-100 text-purple-700 border-purple-200 dark:bg-purple-500/10 dark:text-purple-400',
  'In Progress': 'bg-blue-100 text-blue-700 border-blue-200 dark:bg-blue-500/10 dark:text-blue-400',
  Resolved: 'bg-emerald-100 text-emerald-700 border-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-400',
  'Selesai Ditangani': 'bg-emerald-100 text-emerald-700 border-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-400',
  closed: 'bg-gray-100 text-gray-600 border-gray-200 dark:bg-white/5 dark:text-gray-400',
};

const KELUHAN_OPTIONS = [
  { value: 'Sistem', label: 'Sistem' },
  { value: 'Software', label: 'Software' },
  { value: 'Printer', label: 'Printer' },
  { value: 'Hardware PC/Laptop', label: 'Hardware PC/Laptop' },
  { value: 'Jaringan', label: 'Jaringan' },
  { value: 'Akun BPS', label: 'Akun BPS' },
  { value: 'Lainnya', label: 'Lainnya' },
];

const CREATE_FIELDS: FormField[] = [
  { name: 'jenis_keluhan', label: 'Jenis Keluhan', type: 'select', required: true, options: KELUHAN_OPTIONS },
  { name: 'deskripsi', label: 'Deskripsi', type: 'textarea', required: true, placeholder: 'Tulis detail keluhan...' },
];

const EDIT_FIELDS: FormField[] = [
  { name: 'status', label: 'Status', type: 'select', required: true, options: [
    { value: 'selesai', label: 'Selesai' },
    { value: 'perlu perbaikan', label: 'Perlu Perbaikan' },
    { value: 'pending', label: 'Pending' },
  ]},
  { name: 'keterangan', label: 'Keterangan', type: 'textarea', placeholder: 'Tambahkan keterangan...' },
];

export function IpdsTiket() {
  const [search, setSearch] = useState('');
  const debouncedSearch = useDebounce(search, 300);
  const queryClient = useQueryClient();
  const pagination = usePagination(10);

  // Filter state
  const [filterKeluhan, setFilterKeluhan] = useState('');
  const [filterSort, setFilterSort] = useState('-created_at');
  const [showFilters, setShowFilters] = useState(false);

  // Modal state
  const [modalOpen, setModalOpen] = useState(false);
  const [modalMode, setModalMode] = useState<'create' | 'edit'>('create');
  const [selectedTiket, setSelectedTiket] = useState<TiketRow | null>(null);
  const [deleteConfirm, setDeleteConfirm] = useState<number | null>(null);

  const { data: response, isLoading, error, refetch } = useApiQuery(
    ['ipds-tikets', filterKeluhan, filterSort, pagination.params],
    () => tiketService.list({
      ...pagination.params,
      'filter[jenis_keluhan]': filterKeluhan || undefined,
      sort: filterSort || undefined,
    }),
  );

  useEffect(() => {
    pagination.sync(response);
  }, [response]);

  useEffect(() => {
    pagination.reset();
  }, [filterKeluhan, filterSort, debouncedSearch]);

  const { data: statsResponse, isLoading: statsLoading } = useApiQuery(
    ['ipds-tikets-statistics'],
    () => tiketService.statistics(),
  );

  // Mutations
  const createMutation = useApiMutation(
    (data: Record<string, unknown>) => tiketService.create(data),
    {
      onSuccess: () => {
        queryClient.invalidateQueries({ queryKey: ['ipds-tikets'] });
        queryClient.invalidateQueries({ queryKey: ['ipds-tikets-statistics'] });
        setModalOpen(false);
      },
    },
  );

  const updateMutation = useApiMutation(
    (data: Record<string, unknown> & { _id: number }) => tiketService.update(data._id, data),
    {
      onSuccess: () => {
        queryClient.invalidateQueries({ queryKey: ['ipds-tikets'] });
        queryClient.invalidateQueries({ queryKey: ['ipds-tikets-statistics'] });
        setModalOpen(false);
      },
    },
  );

  const deleteMutation = useApiMutation(
    (id: number) => tiketService.delete(id),
    {
      onSuccess: () => {
        queryClient.invalidateQueries({ queryKey: ['ipds-tikets'] });
        queryClient.invalidateQueries({ queryKey: ['ipds-tikets-statistics'] });
        setDeleteConfirm(null);
      },
    },
  );

  const tiketData: TiketRow[] = useMemo(() => {
    if (!response?.data) return [];
    return (response.data as ApiTiket[]).map((t: ApiTiket) => ({
      id: t.id,
      deskripsi: t.deskripsi,
      pelapor: t.user?.name || `User #${t.user_id}`,
      jenis_keluhan: t.jenis_keluhan,
      status: t.status,
      tanggal: new Date(t.created_at).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }),
      keterangan: t.keterangan,
      user_id: t.user_id,
    }));
  }, [response]);

  const filteredData = useMemo(() => {
    let result = tiketData;

    // Text search only (server handles jenis_keluhan filter)
    if (debouncedSearch) {
      const q = debouncedSearch.toLowerCase();
      result = result.filter((t) =>
        t.deskripsi.toLowerCase().includes(q) ||
        t.pelapor.toLowerCase().includes(q) ||
        t.jenis_keluhan.toLowerCase().includes(q) ||
        t.status.toLowerCase().includes(q)
      );
    }

    // Note: Sorting is now handled by server via 'sort' param

    return result;
  }, [tiketData, debouncedSearch]);

  const hasActiveFilters = filterKeluhan || filterSort !== '-created_at';

  const keluhanOptions = [
    'Hardware PC/Laptop',
    'Software',
    'Printer',
    'Jaringan',
    'Sistem',
    'Akun BPS',
    'Lainnya',
  ];

  const resetFilters = () => {
    setSearch('');
    setFilterKeluhan('');
    setFilterSort('-created_at');
  };

  const stats = useMemo(() => {
    const sd = statsResponse?.data;
    if (!sd) return { total: 0, pending: 0, inProgress: 0, closed: 0 };
    return {
      total: sd.total_tiket,
      pending: sd.status_counts['Pending'] || 0,
      inProgress: sd.status_counts['In Progress'] || 0,
      closed: (sd.status_counts['closed'] || 0) + (sd.status_counts['Resolved'] || 0) + (sd.status_counts['Selesai Ditangani'] || 0),
    };
  }, [statsResponse]);

  // Handlers
  const handleCreate = useCallback(() => {
    setModalMode('create');
    setSelectedTiket(null);
    setModalOpen(true);
  }, []);

  const handleEdit = useCallback((row: TiketRow) => {
    setModalMode('edit');
    setSelectedTiket(row);
    setModalOpen(true);
  }, []);

  const handleSubmit = useCallback((data: Record<string, unknown>) => {
    if (modalMode === 'create') {
      createMutation.mutate(data);
    } else {
      updateMutation.mutate({ ...data, _id: selectedTiket!.id });
    }
  }, [modalMode, selectedTiket, createMutation, updateMutation]);

  const handleDelete = useCallback((id: number) => {
    setDeleteConfirm(id);
  }, []);

  const confirmDelete = useCallback(() => {
    if (deleteConfirm) deleteMutation.mutate(deleteConfirm);
  }, [deleteConfirm, deleteMutation]);

  // Column helper with actions
  const columnHelper = createColumnHelper<TiketRow>();

  const columns = useMemo(() => [
    columnHelper.accessor('id', {
      header: 'ID',
      cell: (info) => (
        <span className="font-bold text-gray-900 dark:text-white text-[13px]">#{info.getValue()}</span>
      ),
    }),
    columnHelper.accessor('deskripsi', {
      header: 'Masalah',
      cell: (info) => (
        <div className="flex flex-col gap-0.5 max-w-[280px]">
          <span className="font-medium text-gray-900 dark:text-white text-[13px] leading-tight truncate">{info.getValue()}</span>
          <span className="text-[11px] text-gray-500">{info.row.original.jenis_keluhan}</span>
        </div>
      ),
    }),
    columnHelper.accessor('pelapor', {
      header: 'Pelapor',
      cell: (info) => (
        <div className="flex items-center gap-2">
          <div className="h-6 w-6 rounded-full bg-gray-100 dark:bg-white/10 flex items-center justify-center">
            <User className="h-3 w-3 text-gray-500 dark:text-gray-400" />
          </div>
          <span className="text-[13px] text-gray-700 dark:text-gray-300">{info.getValue()}</span>
        </div>
      ),
    }),
    columnHelper.accessor('status', {
      header: 'Status',
      cell: (info) => (
        <span className={`px-2 py-0.5 rounded-md border text-[10px] font-bold uppercase tracking-wider ${statusStyles[info.getValue()] || 'bg-gray-100 text-gray-600 border-gray-200 dark:bg-white/5 dark:text-gray-400'}`}>
          {info.getValue()}
        </span>
      ),
    }),
    columnHelper.accessor('tanggal', {
      header: 'Tanggal',
      cell: (info) => <span className="text-[13px] text-gray-600 dark:text-gray-400">{info.getValue()}</span>,
    }),
    columnHelper.display({
      id: 'actions',
      header: '',
      cell: (info) => (
        <div className="flex items-center gap-1">
          <button
            data-scan="tombol edit tiket"
            onClick={() => handleEdit(info.row.original)}
            className="p-1.5 rounded-lg hover:bg-blue-50 dark:hover:bg-blue-500/10 text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition"
            title="Edit tiket"
          >
            <Pencil className="h-3.5 w-3.5" />
          </button>
          <button
            data-scan="tombol hapus tiket"
            onClick={() => handleDelete(info.row.original.id)}
            className="p-1.5 rounded-lg hover:bg-red-50 dark:hover:bg-red-500/10 text-gray-400 hover:text-red-600 dark:hover:text-red-400 transition"
            title="Hapus tiket"
          >
            <Trash2 className="h-3.5 w-3.5" />
          </button>
        </div>
      ),
    }),
  ], [handleEdit, handleDelete]);

  const table = useReactTable({
    data: filteredData,
    columns,
    getCoreRowModel: getCoreRowModel(),
    manualPagination: true,
  });

  const isMutating = createMutation.isPending || updateMutation.isPending;

  return (
    <div className="space-y-6">
      <PageHeader
        title="Tiket Bantuan IT"
        description="Kelola permintaan bantuan dan gangguan teknologi informasi."
        actions={
          <button
            data-scan="tombol tiket baru"
            onClick={handleCreate}
            className="flex items-center gap-1.5 px-4 py-2 rounded-full bg-amber-300 hover:bg-amber-400 text-gray-900 text-sm font-semibold shadow-lg shadow-amber-500/20 transition"
          >
            <Plus className="h-4 w-4" />
            Tiket Baru
          </button>
        }
      />

      {/* Stats */}
      <div 
        data-scan="ringkasan statistik"
        className="grid grid-cols-2 lg:grid-cols-4 gap-4"
      >
        {[
          { label: 'Total Tiket', value: stats.total, color: 'text-orange-600 dark:text-orange-400', icon: <MessageSquare className="h-5 w-5" /> },
          { label: 'Pending', value: stats.pending, color: 'text-purple-600 dark:text-purple-400', icon: <AlertCircle className="h-5 w-5" /> },
          { label: 'Diproses', value: stats.inProgress, color: 'text-blue-600 dark:text-blue-400', icon: <Clock className="h-5 w-5" /> },
          { label: 'Selesai / Ditutup', value: stats.closed, color: 'text-emerald-600 dark:text-emerald-400', icon: <CheckCircle2 className="h-5 w-5" /> },
        ].map((s, i) => (
          <motion.div
            key={i}
            initial={{ opacity: 0, y: 12 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ delay: i * 0.08 }}
            className="bg-white/65 dark:bg-white/[0.04] glass-strong rounded-[20px] border border-white/50 dark:border-white/10 p-5 space-y-2"
          >
            <div className="flex items-center justify-between">
              <p className="text-[11px] font-bold uppercase tracking-widest text-gray-500 dark:text-gray-400">{s.label}</p>
              <span className="opacity-30">{s.icon}</span>
            </div>
            <p className={`text-2xl font-black ${s.color}`}>{statsLoading ? '...' : s.value}</p>
          </motion.div>
        ))}
      </div>

      {/* Search + Filters */}
      <div 
        data-scan="pencarian dan filter"
        className="bg-white/40 dark:bg-white/5 glass rounded-2xl border border-white/40 dark:border-white/10 overflow-hidden"
      >
        <div className="flex items-center gap-3 p-2">
          <div className="relative flex-1 group">
            <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400 group-focus-within:text-orange-500 transition-colors" />
            <input
              data-scan="input pencarian"
              type="text"
              value={search}
              onChange={(e) => setSearch(e.target.value)}
              placeholder="Cari tiket..."
              className="w-full bg-transparent border-none focus:ring-0 focus:outline-none pl-10 pr-4 py-2 text-sm text-gray-700 dark:text-gray-200 placeholder:text-gray-400"
            />
          </div>
          <button
            onClick={() => setShowFilters(!showFilters)}
            className={`flex items-center gap-2 px-4 py-2 rounded-xl transition text-[13px] font-medium shrink-0 ${
              showFilters
                ? 'bg-orange-500 text-white shadow-sm'
                : 'bg-white/70 dark:bg-white/10 border border-white/50 dark:border-white/20 text-gray-700 dark:text-gray-300 hover:bg-white dark:hover:bg-white/10 shadow-sm'
            }`}
          >
            <Filter className="h-4 w-4" />
            Filter
            {hasActiveFilters && (
              <span className="w-2 h-2 rounded-full bg-orange-500" />
            )}
          </button>
        </div>
        {showFilters && (
          <div className="px-4 pb-3 pt-1 border-t border-white/30 dark:border-white/10 space-y-3">
            <div className="flex flex-wrap items-end gap-3">
              <div className="flex w-full max-w-xs flex-col gap-2">
                <label className="text-[10px] uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Jenis Keluhan</label>
                <select
                  data-scan="filter jenis keluhan"
                  value={filterKeluhan}
                  onChange={(e) => setFilterKeluhan(e.target.value)}
                  className="h-9 rounded-xl border border-white/50 dark:border-white/10 bg-white/70 dark:bg-white/5 px-3 text-xs text-gray-700 dark:text-gray-200 outline-none cursor-pointer"
                >
                  <option value="">Semua</option>
                  {keluhanOptions.map((k) => <option key={k} value={k}>{k}</option>)}
                </select>
              </div>
              <div className="flex flex-col gap-2">
                <label className="text-[10px] uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Urutkan</label>
                <select
                  data-scan="filter urutkan"
                  value={filterSort}
                  onChange={(e) => setFilterSort(e.target.value)}
                  className="h-9 rounded-xl border border-white/50 dark:border-white/10 bg-white/70 dark:bg-white/5 px-3 text-xs text-gray-700 dark:text-gray-200 outline-none cursor-pointer"
                >
                  <option value="-created_at">Terbaru</option>
                  <option value="created_at">Terlama</option>
                  <option value="status">Status</option>
                </select>
              </div>
              <div className="flex items-end">
                <button
                  data-scan="tombol reset"
                  onClick={resetFilters}
                  className="h-9 px-4 rounded-xl border border-white/50 dark:border-white/10 bg-white/70 dark:bg-white/5 text-xs font-medium text-gray-700 dark:text-gray-200 hover:bg-white dark:hover:bg-white/10 transition shadow-sm"
                >
                  Reset
                </button>
              </div>
            </div>
            <div>
              <p className="text-[12px] text-gray-500 dark:text-gray-400 font-medium">
                Menampilkan {filteredData.length} dari {tiketData.length} data
              </p>
            </div>
          </div>
        )}
      </div>

      {/* Table */}
      <div 
        data-scan="tabel tiket"
        className="bg-white/65 dark:bg-white/[0.04] glass-strong rounded-[24px] border border-white/50 dark:border-white/10 shadow-[0_10px_40px_-15px_rgba(0,0,0,0.08)] overflow-hidden"
      >
        {isLoading ? (
          <LoadingSkeleton variant="table" message="Memuat data tiket..." />
        ) : error ? (
          <ApiErrorBoundary error={error} onRetry={() => refetch()} title="Gagal Memuat Tiket" />
        ) : (
          <>
            <div className="overflow-x-auto">
              <table className="w-full text-[13px]">
                <thead>
                  <tr className="text-left text-[11.5px] text-gray-500 dark:text-gray-400 border-b border-gray-200/60 dark:border-white/10">
                    {table.getHeaderGroups().map((hg) => (
                      <React.Fragment key={hg.id}>
                        {hg.headers.map((h) => (
                          <th key={h.id} className="px-6 py-5 font-bold uppercase tracking-widest bg-gray-50/30 dark:bg-white/5 whitespace-nowrap">
                            {flexRender(h.column.columnDef.header, h.getContext())}
                          </th>
                        ))}
                      </React.Fragment>
                    ))}
                  </tr>
                </thead>
                <tbody className="divide-y divide-gray-100/70 dark:divide-white/5">
                  {table.getRowModel().rows.map((row) => (
                    <tr key={row.id} className="hover:bg-white/40 dark:hover:bg-white/[0.02] transition-colors">
                      {row.getVisibleCells().map((cell) => (
                        <td key={cell.id} className="px-6 py-4">
                          {flexRender(cell.column.columnDef.cell, cell.getContext())}
                        </td>
                      ))}
                    </tr>
                  ))}
                  {table.getRowModel().rows.length === 0 && (
                    <tr>
                      <td colSpan={columns.length} className="px-6 py-12 text-center text-gray-400 text-sm">
                        Tidak ada data tiket.
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
              <p className="text-[12px] text-gray-500 dark:text-gray-400 font-medium">
                Halaman <span className="text-gray-900 dark:text-white font-bold">{pagination.page}</span> dari{' '}
                <span className="text-gray-900 dark:text-white font-bold">{pagination.totalPages}</span>
              </p>
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

      {/* Create/Edit Modal */}
      <EntityFormModal
        data-scan="modal form tiket"
        open={modalOpen}
        onClose={() => setModalOpen(false)}
        onSubmit={handleSubmit}
        title={modalMode === 'create' ? 'Buat Tiket' : 'Perbarui Tiket'}
        fields={modalMode === 'create' ? CREATE_FIELDS : EDIT_FIELDS}
        initialData={selectedTiket}
        isLoading={isMutating}
        mode={modalMode}
      />

      {/* Delete Confirmation */}
      {deleteConfirm !== null && (
        <div className="fixed inset-0 z-50 flex items-center justify-center">
          <div className="absolute inset-0 bg-black/40 backdrop-blur-sm" onClick={() => setDeleteConfirm(null)} />
          <div className="relative bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-white/10 shadow-2xl w-full max-w-sm mx-4 p-6 space-y-4">
            <div className="flex items-center gap-3">
              <div className="h-10 w-10 rounded-full bg-red-100 dark:bg-red-500/10 flex items-center justify-center">
                <AlertCircle className="h-5 w-5 text-red-600 dark:text-red-400" />
              </div>
              <div>
                <h3 className="text-sm font-bold text-gray-900 dark:text-white">Hapus Tiket?</h3>
                <p className="text-[12px] text-gray-500 dark:text-gray-400">Tiket #{deleteConfirm} akan dihapus permanen.</p>
              </div>
            </div>
            <div className="flex justify-end gap-3">
              <button
                onClick={() => setDeleteConfirm(null)}
                className="px-4 py-2 text-[12px] font-bold rounded-full bg-white/70 dark:bg-white/10 border border-white/50 dark:border-white/20 text-gray-700 dark:text-gray-300 hover:bg-white dark:hover:bg-white/10 transition-all shadow-sm"
              >
                Batal
              </button>
              <button
                onClick={confirmDelete}
                disabled={deleteMutation.isPending}
                className="px-4 py-2 text-[12px] font-bold rounded-full bg-red-600 hover:bg-red-700 text-white shadow-sm transition-all disabled:opacity-50"
              >
                {deleteMutation.isPending ? 'Menghapus...' : 'Hapus'}
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}