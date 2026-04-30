import React, { useState, useMemo, useEffect } from 'react';
import {
  createColumnHelper,
  flexRender,
  getCoreRowModel,
  getPaginationRowModel,
  useReactTable
} from '@tanstack/react-table';
import {
  Plus,
  Search,
  Filter,
  MoreVertical,
  Calendar as CalendarIcon,
  Pencil,
  Trash2,
  Ellipsis,
} from 'lucide-react';
import { motion } from 'motion/react';
import { useApiQuery, useApiMutation } from '../hooks/useApi';
import { usePagination } from '../hooks/usePagination';
import { useDebounce } from '../hooks/useDebounce';
import { PageHeader } from '../components/PageHeader';
import { LoadingSkeleton } from '../components/LoadingSkeleton';
import { ApiErrorBoundary } from '../components/ApiErrorBoundary';
import { EntityFormModal } from '../components/EntityFormModal';
import { ConfirmDialog } from '../components/ConfirmDialog';
import { kegiatanService } from '../lib/api-services';
import type { Kegiatan as ApiKegiatan } from '../types/api';

type KegiatanRow = {
  id: number;
  nama: string;
  tgl_mulai: string;
  tgl_selesai: string;
  fungsi: string;
  jenis_kegiatan: string;
  tahun: string;
  status: string;
  volume: number;
  satuan: string;
  jml_penugasan: number;
  rate_pcl: number | null;
  rate_pml: number | null;
  rate_entri: number | null;
};

const BULAN = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

const fmtDate = (d: string): string => {
  if (!d) return '-';
  const dt = new Date(d);
  return `${dt.getDate()} ${BULAN[dt.getMonth()]} ${dt.getFullYear()}`;
};

const fmtRate = (n: number | null): string => {
  if (n == null) return '-';
  return n.toLocaleString('id-ID');
};

const statusMap: Record<string, { label: string; style: string }> = {
  'aktif': { label: 'Berjalan', style: 'bg-blue-100 text-blue-700 border-blue-200 dark:bg-blue-500/10 dark:text-blue-400' },
  'tidak dicairkan': { label: 'Tidak Dicairkan', style: 'bg-gray-100 text-gray-600 border-gray-200 dark:bg-white/5 dark:text-gray-400' },
  'dibatalkan': { label: 'Dibatalkan', style: 'bg-red-100 text-red-700 border-red-200 dark:bg-red-500/10 dark:text-red-400' },
};

export function DaftarKegiatan() {
  const [search, setSearch] = useState('');
  const [filterFungsi, setFilterFungsi] = useState('');
  const [filterTahun, setFilterTahun] = useState('');
  const [filterJenis, setFilterJenis] = useState('');
  const [filterStatus, setFilterStatus] = useState('');
  const [showFilters, setShowFilters] = useState(false);
  const pagination = usePagination(10);

  // Modal state
  const [showFormModal, setShowFormModal] = useState(false);
  const [editingRow, setEditingRow] = useState<KegiatanRow | null>(null);
  const [deleteTarget, setDeleteTarget] = useState<KegiatanRow | null>(null);

  const debouncedSearch = useDebounce(search, 300);

  const { data: filtersResp } = useApiQuery(['kegiatan-filters'], () => kegiatanService.filterList());

  const { data: response, isLoading, error, refetch } = useApiQuery(
    ['kegiatan', filterFungsi, filterTahun, filterJenis, filterStatus, pagination.params],
    () => kegiatanService.list({
      ...pagination.params,
      'filter[fungsi]': filterFungsi || undefined,
      'filter[tahun]': filterTahun || undefined,
      'filter[jenis_kegiatan]': filterJenis || undefined,
      'filter[status]': filterStatus || undefined,
    }),
  );

  useEffect(() => {
    pagination.sync(response);
  }, [response]);

  const createMutation = useApiMutation(
    (data: Record<string, unknown>) => kegiatanService.create(data as any),
    {
      invalidateKeys: [['kegiatan']],
      onSuccess: () => { setShowFormModal(false); setEditingRow(null); },
    },
  );

  const updateMutation = useApiMutation(
    (data: Record<string, unknown>) => kegiatanService.update(data.id as number, data as any),
    {
      invalidateKeys: [['kegiatan']],
      onSuccess: () => { setShowFormModal(false); setEditingRow(null); },
    },
  );

  const deleteMutation = useApiMutation(
    (data: Record<string, unknown>) => kegiatanService.delete(data.id as number),
    {
      invalidateKeys: [['kegiatan']],
      onSuccess: () => setDeleteTarget(null),
    },
  );

  const kegiatanData: KegiatanRow[] = useMemo(() => {
    if (!response?.data) return [];
    const raw = response.data;
    if (!Array.isArray(raw)) return [];
    return raw.map((k: any) => ({
      id: k.id,
      nama: k.nama,
      tgl_mulai: k.tgl_mulai,
      tgl_selesai: k.tgl_selesai,
      fungsi: k.fungsi,
      jenis_kegiatan: k.jenis_kegiatan,
      tahun: k.tahun,
      status: k.status || 'aktif',
      volume: k.volume,
      satuan: k.satuan,
      jml_penugasan: k.jml_penugasan ?? k.penugasan_sum_volume ?? 0,
      rate_pcl: k.rate_pcl ?? null,
      rate_pml: k.rate_pml ?? null,
      rate_entri: k.rate_entri ?? null,
    }));
  }, [response]);

  const filteredData = useMemo(() => {
    return kegiatanData.filter((row) => {
      const matchSearch =
        !debouncedSearch ||
        row.nama.toLowerCase().includes(debouncedSearch.toLowerCase()) ||
        String(row.id).includes(debouncedSearch);
      return matchSearch;
    });
  }, [kegiatanData, debouncedSearch]);

  const filterData = filtersResp?.data as Record<string, string[]> | undefined;
  const fungsiOptions: string[] = (filterData?.fungsiList || []).filter(Boolean) as string[];
  const jenisOptions: string[] = (filterData?.jenisList || []).filter(Boolean) as string[];
  const statusOptions: string[] = (filterData?.statusList || []).filter(Boolean) as string[];
  const tahunOptions: string[] = (filterData?.tahunList || []).filter(Boolean) as string[];
  const satuanOptions: string[] = ['Dok', 'Resp', 'BS', 'Desa', 'SLS', 'Ruta', 'Paket', 'Set', 'Lembar'];
  const hasActiveFilters = filterFungsi || filterTahun || filterJenis || filterStatus;

  const formFields = useMemo(() => [
    { name: 'tahun', label: 'Tahun', type: 'text' as const, placeholder: '2026', required: true },
    { name: 'fungsi', label: 'Fungsi', type: 'select' as const, required: true, options: fungsiOptions.map(f => ({ value: f, label: f })) },
    { name: 'kode_kegiatan', label: 'Kode Kegiatan', type: 'text' as const, placeholder: '2904.521213', required: true },
    { name: 'nama', label: 'Nama Kegiatan', type: 'text' as const, placeholder: 'Nama kegiatan...', required: true, colSpan: 2 as const },
    { name: 'tgl_mulai', label: 'Tgl Mulai', type: 'date' as const, required: true },
    { name: 'tgl_selesai', label: 'Tgl Selesai', type: 'date' as const, required: true },
    { name: 'jenis_kegiatan', label: 'Jenis', type: 'select' as const, required: true, options: jenisOptions.map(j => ({ value: j, label: j })) },
    { name: 'jml_ptgs', label: 'Jml Petugas', type: 'number' as const, required: true },
    { name: 'volume', label: 'Volume', type: 'number' as const, required: true },
    { name: 'satuan', label: 'Satuan', type: 'select' as const, required: true, options: satuanOptions.map(s => ({ value: s, label: s })) },
    { name: 'rate_pcl', label: 'Rate PCL', type: 'number' as const },
    { name: 'rate_pml', label: 'Rate PML', type: 'number' as const },
    { name: 'rate_entri', label: 'Rate Entri', type: 'number' as const },
    { name: 'status', label: 'Status', type: 'select' as const, options: [
      { value: 'aktif', label: 'Aktif' },
      { value: 'tidak dicairkan', label: 'Tidak Dicairkan' },
      { value: 'dibatalkan', label: 'Dibatalkan' },
    ]},
  ], [fungsiOptions, jenisOptions, satuanOptions]);

  const columnHelper = createColumnHelper<KegiatanRow>();

  const [openMenuId, setOpenMenuId] = useState<number | null>(null);

  const columns = useMemo(() => [
    columnHelper.accessor('nama', {
      header: 'Nama',
      cell: info => (
        <div className="whitespace-normal font-medium text-slate-900 dark:text-white">
          <div>{info.getValue()}</div>
          <div className="mt-1 text-xs text-slate-500 dark:text-slate-400">
            {info.row.original.tahun} • {info.row.original.fungsi} • {info.row.original.jenis_kegiatan}
          </div>
        </div>
      ),
    }),
    columnHelper.accessor('tgl_mulai', {
      header: 'Tgl Mulai',
      cell: info => (
        <span className="text-[13px] text-gray-600 dark:text-gray-400 whitespace-nowrap">{fmtDate(info.getValue())}</span>
      ),
    }),
    columnHelper.accessor('tgl_selesai', {
      header: 'Tgl Selesai',
      cell: info => (
        <span className="text-[13px] text-gray-600 dark:text-gray-400 whitespace-nowrap">{fmtDate(info.getValue())}</span>
      ),
    }),
    columnHelper.accessor('volume', {
      header: 'Volume',
      cell: info => (
        <span className="text-[13px] text-gray-600 dark:text-gray-400 whitespace-nowrap">
          {info.getValue()} {info.row.original.satuan}
        </span>
      ),
    }),
    columnHelper.accessor('jml_penugasan', {
      header: 'Penugasan',
      cell: info => (
        <span className="text-[13px] text-gray-600 dark:text-gray-400 whitespace-nowrap">{info.getValue()}</span>
      ),
    }),
    columnHelper.display({
      id: 'rate',
      header: 'Rate',
      cell: (info) => {
        const row = info.row.original;
        const rate = row.rate_pcl ?? row.rate_pml ?? row.rate_entri;
        return (
          <span className="text-[13px] text-gray-600 dark:text-gray-400 whitespace-nowrap">{fmtRate(rate)}</span>
        );
      },
    }),
    columnHelper.display({
      id: 'actions',
      header: '',
      cell: (info) => (
        <div className="relative flex justify-end">
          <button
            onClick={() => setOpenMenuId(openMenuId === info.row.original.id ? null : info.row.original.id)}
            className="inline-flex items-center justify-center size-9 rounded-none hover:bg-accent hover:text-accent-foreground dark:hover:bg-accent/50 transition"
          >
            <Ellipsis className="size-4" />
          </button>
          {openMenuId === info.row.original.id && (
            <div className="absolute right-0 top-full mt-1 z-50 min-w-[140px] bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg py-1">
              <button
                data-scan="tombol edit kegiatan"
                onClick={() => { setEditingRow(info.row.original); setShowFormModal(true); setOpenMenuId(null); }}
                className="w-full flex items-center gap-2 px-3 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700"
              >
                <Pencil className="h-3.5 w-3.5" />
                Edit
              </button>
              <button
                data-scan="tombol hapus kegiatan"
                onClick={() => { setDeleteTarget(info.row.original); setOpenMenuId(null); }}
                className="w-full flex items-center gap-2 px-3 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-500/10"
              >
                <Trash2 className="h-3.5 w-3.5" />
                Hapus
              </button>
            </div>
          )}
        </div>
      ),
    }),
  ], [openMenuId]);

  const table = useReactTable({
    data: filteredData,
    columns,
    getCoreRowModel: getCoreRowModel(),
    manualPagination: true,
    pageCount: pagination.meta?.lastPage ?? 1,
    state: {
      pagination: {
        pageIndex: pagination.page - 1,
        pageSize: pagination.perPage,
      },
    },
    onPaginationChange: (updater) => {
      if (typeof updater === 'function') {
        const newState = updater({
          pageIndex: pagination.page - 1,
          pageSize: pagination.perPage,
        });
        pagination.setPage(newState.pageIndex + 1);
        pagination.setPerPage(newState.pageSize);
      }
    },
  });

  const resetFilters = () => {
    setSearch('');
    setFilterFungsi('');
    setFilterTahun('');
    setFilterJenis('');
    setFilterStatus('');
    pagination.reset();
  };

  const handleFormSubmit = (formData: Record<string, unknown>) => {
    if (editingRow) {
      updateMutation.mutate({ ...formData, id: editingRow.id });
    } else {
      createMutation.mutate(formData);
    }
  };

  return (
    <div className="space-y-6">
      <PageHeader 
        title="Daftar Kegiatan" 
        description="Kelola jadwal, penugasan, dan monitoring kegiatan kantor."
      />

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
              placeholder="Cari kegiatan..."
              className="w-full bg-transparent border-none focus:ring-0 focus:outline-none pl-10 pr-4 py-2 text-sm text-gray-700 dark:text-gray-200 placeholder:text-gray-400"
            />
          </div>
          <button
            onClick={() => setShowFilters(!showFilters)}
            className={`flex items-center gap-2 px-4 py-2 rounded-xl transition text-[13px] font-medium ${
              showFilters
                ? 'bg-orange-500 text-white shadow-sm'
                : 'hover:bg-white/60 dark:hover:bg-white/10 text-gray-600 dark:text-gray-300'
            }`}
          >
            <Filter className="h-4 w-4" />
            Filter
            {hasActiveFilters && (
              <span className="w-2 h-2 rounded-full bg-orange-500" />
            )}
          </button>
          <button
            data-scan="tombol tambah kegiatan"
            onClick={() => { setEditingRow(null); setShowFormModal(true); }}
            className="flex items-center gap-1.5 px-4 py-2 rounded-full bg-amber-300 hover:bg-amber-400 text-gray-900 text-sm font-semibold shadow-lg shadow-amber-500/20 transition"
          >

            <Plus className="h-4 w-4" />
            Tambah Kegiatan
          </button>
        </div>

        {showFilters && (
          <div 
            data-scan="rincian filter"
            className="flex flex-wrap items-center gap-3 px-4 pb-3 pt-1 border-t border-white/30 dark:border-white/10"
          >

            <div className="flex flex-col gap-1.5">
              <label className="text-[10px] uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Fungsi</label>
              <select
                value={filterFungsi}
                onChange={(e) => {
                  setFilterFungsi(e.target.value);
                  pagination.setPage(1);
                }}
                className="h-9 rounded-xl border border-white/50 dark:border-white/10 bg-white/70 dark:bg-white/5 px-3 text-xs text-gray-700 dark:text-gray-200 outline-none cursor-pointer"
              >
                <option value="">Semua</option>
                {fungsiOptions.map(f => <option key={f} value={f}>{f}</option>)}
              </select>
            </div>
            <div className="flex flex-col gap-1.5">
              <label className="text-[10px] uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Tahun</label>
              <select
                value={filterTahun}
                onChange={(e) => {
                  setFilterTahun(e.target.value);
                  pagination.setPage(1);
                }}
                className="h-9 rounded-xl border border-white/50 dark:border-white/10 bg-white/70 dark:bg-white/5 px-3 text-xs text-gray-700 dark:text-gray-200 outline-none cursor-pointer"
              >
                <option value="">Semua</option>
                {tahunOptions.map(t => <option key={t} value={t}>{t}</option>)}
              </select>
            </div>
            <div className="flex flex-col gap-1.5">
              <label className="text-[10px] uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Jenis</label>
              <select
                value={filterJenis}
                onChange={(e) => {
                  setFilterJenis(e.target.value);
                  pagination.setPage(1);
                }}
                className="h-9 rounded-xl border border-white/50 dark:border-white/10 bg-white/70 dark:bg-white/5 px-3 text-xs text-gray-700 dark:text-gray-200 outline-none cursor-pointer"
              >
                <option value="">Semua</option>
                {jenisOptions.map(j => <option key={j} value={j}>{j}</option>)}
              </select>
            </div>
            <div className="flex flex-col gap-1.5">
              <label className="text-[10px] uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Status</label>
              <select
                value={filterStatus}
                onChange={(e) => {
                  setFilterStatus(e.target.value);
                  pagination.setPage(1);
                }}
                className="h-9 rounded-xl border border-white/50 dark:border-white/10 bg-white/70 dark:bg-white/5 px-3 text-xs text-gray-700 dark:text-gray-200 outline-none cursor-pointer"
              >
                <option value="">Semua</option>
                {statusOptions.map(s => <option key={s} value={s}>{s}</option>)}
              </select>
            </div>
            <div className="flex items-end gap-2 pt-4">
              {hasActiveFilters && (
                <button
                  onClick={resetFilters}
                  className="h-9 px-4 rounded-xl border border-white/50 dark:border-white/10 bg-white/70 dark:bg-white/5 text-xs font-medium text-gray-700 dark:text-gray-200 hover:bg-white dark:hover:bg-white/10 transition shadow-sm"
                >
                  Reset
                </button>
              )}
            </div>
          </div>
        )}
      </div>

      <div 
        data-scan="tabel kegiatan"
        className="bg-white/65 dark:bg-white/[0.04] glass-strong rounded-[24px] border border-white/50 dark:border-white/10 shadow-[0_10px_40px_-15px_rgba(0,0,0,0.08)] overflow-hidden"
      >

        {isLoading ? (
          <LoadingSkeleton variant="table" message="Memuat data kegiatan..." />
        ) : error ? (
          <ApiErrorBoundary error={error} onRetry={refetch} title="Gagal Memuat Kegiatan" />
        ) : (
          <>
            <div className="overflow-x-auto">
              <table className="w-full text-[13px]">
                <thead>
                  <tr className="text-left text-[11.5px] text-gray-500 dark:text-gray-400 border-b border-gray-200/60 dark:border-white/10">
                    {table.getHeaderGroups().map(headerGroup => (
                      <React.Fragment key={headerGroup.id}>
                        {headerGroup.headers.map(header => (
                          <th key={header.id} className="px-6 py-5 font-bold uppercase tracking-widest bg-gray-50/30 dark:bg-white/5">
                            {flexRender(header.column.columnDef.header, header.getContext())}
                          </th>
                        ))}
                      </React.Fragment>
                    ))}
                  </tr>
                </thead>
                <tbody className="divide-y divide-gray-100/70 dark:divide-white/5">
                  {table.getRowModel().rows.map(row => (
                    <tr key={row.id} className="hover:bg-white/40 dark:hover:bg-white/[0.02] transition-colors">
                      {row.getVisibleCells().map(cell => (
                        <td key={cell.id} className="px-6 py-5">
                          {flexRender(cell.column.columnDef.cell, cell.getContext())}
                        </td>
                      ))}
                    </tr>
                  ))}
                  {table.getRowModel().rows.length === 0 && (
                    <tr>
                      <td colSpan={columns.length} className="px-6 py-12 text-center text-gray-400 text-sm">
                        Tidak ada data yang cocok dengan pencarian.
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
                  <span className="text-gray-900 dark:text-white font-bold">{pagination.meta?.lastPage || 1}</span>
                </p>
                <div className="h-4 w-px bg-gray-200 dark:bg-white/10" />
                <p className="text-[12px] text-gray-500 dark:text-gray-400 font-medium">
                  Total <span className="text-gray-900 dark:text-white font-bold">{pagination.meta?.total || 0}</span> data
                </p>
              </div>
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
                    const nextToken = (response as any)?.links?.next_cursor || (response as any)?.links?.next || (response as any)?.links?.next_page_url;
                    pagination.handleNext(nextToken);
                  }}
                  disabled={!pagination.canGoNext || isLoading}
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
        data-scan="modal form kegiatan"
        open={showFormModal}
        onClose={() => { setShowFormModal(false); setEditingRow(null); }}
        onSubmit={handleFormSubmit}
        title={editingRow ? 'Edit Kegiatan' : 'Tambah Kegiatan'}
        fields={formFields}
        initialData={editingRow}
        isLoading={createMutation.isPending || updateMutation.isPending}
        mode={editingRow ? 'edit' : 'create'}
      />

      {/* Delete Confirmation */}
      <ConfirmDialog
        open={!!deleteTarget}
        onClose={() => setDeleteTarget(null)}
        onConfirm={() => deleteTarget && deleteMutation.mutate({ id: deleteTarget.id })}
        title="Hapus Kegiatan"
        message={`Yakin ingin menghapus kegiatan "${deleteTarget?.nama}"? Tindakan ini tidak dapat dibatalkan.`}
        variant="danger"
        isLoading={deleteMutation.isPending}
      />
    </div>
  );
}
