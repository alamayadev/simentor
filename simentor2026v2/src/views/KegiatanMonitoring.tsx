import React, { useState, useMemo, useEffect, useRef } from 'react';
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
  Filter,
  Download,
  Pencil,
  Trash2,
  Settings,
  ChevronDown,
  Table2,
} from 'lucide-react';
import { useApiQuery, useApiMutation } from '../hooks/useApi';
import { PageHeader } from '../components/PageHeader';
import { LoadingSkeleton } from '../components/LoadingSkeleton';
import { useNavigate } from '@tanstack/react-router';
import { ApiErrorBoundary } from '../components/ApiErrorBoundary';
import { MonitoringCreateModal } from '../components/MonitoringCreateModal';
import { ConfirmDialog } from '../components/ConfirmDialog';
import { SearchableSelect } from '@/components/ui/searchable-select';
import { monitoringKegiatanService, monitoringKegiatanConfigService } from '../lib/api-services';

type MonitoringRow = {
  id: number;
  kode_sampel: string;
  kegiatan: string;
  nmkec: string;
  nmdesa: string;
  mitra: string | null;
  fungsi: string;
  status_pelaksanaan: string | null;
};

const statusStyles: Record<string, string> = {
  selesai: 'bg-emerald-100 text-emerald-700 border-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-400',
  'sedang berjalan': 'bg-blue-100 text-blue-700 border-blue-200 dark:bg-blue-500/10 dark:text-blue-400',
  'belum dimulai': 'bg-gray-100 text-gray-600 border-gray-200 dark:bg-white/5 dark:text-gray-400',
  terlambat: 'bg-red-100 text-red-700 border-red-200 dark:bg-red-500/10 dark:text-red-400',
};

export function KegiatanMonitoring() {
  const navigate = useNavigate();
  const [showFormModal, setShowFormModal] = useState(false);
  const [editingRow, setEditingRow] = useState<MonitoringRow | null>(null);
  const [deleteTarget, setDeleteTarget] = useState<MonitoringRow | null>(null);
  const [search, setSearch] = useState('');
  const [filterFungsi, setFilterFungsi] = useState('');
  const [filterKegiatan, setFilterKegiatan] = useState('');
  const [showFilters, setShowFilters] = useState(false);
  const [isExporting, setIsExporting] = useState(false);
  const [showConfigMenu, setShowConfigMenu] = useState(false);
  const configMenuRef = useRef<HTMLDivElement>(null);

  useEffect(() => {
    const handleClick = (e: MouseEvent) => {
      if (configMenuRef.current && !configMenuRef.current.contains(e.target as Node)) {
        setShowConfigMenu(false);
      }
    };
    if (showConfigMenu) document.addEventListener('mousedown', handleClick);
    return () => document.removeEventListener('mousedown', handleClick);
  }, [showConfigMenu]);

  const { data: response, isLoading, error, refetch } = useApiQuery(
    ['monitoring-kegiatan', filterFungsi, filterKegiatan],
    () => monitoringKegiatanService.list({
      per_page: 100,
      'filter[kegiatan_id]': filterKegiatan || undefined,
    }),
  );

  const { data: filtersResp } = useApiQuery(
    ['monitoring-filters'],
    () => monitoringKegiatanService.filters(),
  );

  const { data: kegiatanOptsResp } = useApiQuery(
    ['monitoring-kegiatan-options', filterFungsi],
    () => monitoringKegiatanService.kegiatanOptions(filterFungsi),
  );

  const { data: configOptsResp } = useApiQuery(
    ['monitoring-config-options'],
    () => monitoringKegiatanConfigService.list({ per_page: 100 }),
  );

  const fungsiFilterOptions = useMemo(() => {
    return (filtersResp?.data?.fungsi || []).map((f: string) => ({ value: f, label: f }));
  }, [filtersResp]);

  const kegiatanFilterOptions = useMemo(() => {
    if (!kegiatanOptsResp?.data) return [];
    const raw = kegiatanOptsResp.data;
    if (!Array.isArray(raw)) return [];
    return raw.map((k: any) => ({ value: String(k.id), label: k.nama }));
  }, [kegiatanOptsResp]);

  const kegiatanFormOptions = useMemo(() => {
    if (!kegiatanOptsResp?.data) return [];
    const raw = kegiatanOptsResp.data;
    if (!Array.isArray(raw)) return [];
    return raw.map((k: any) => ({ value: String(k.id), label: k.nama }));
  }, [kegiatanOptsResp]);

  const configOptions = useMemo(() => {
    if (!configOptsResp?.data) return [];
    const raw = configOptsResp.data;
    if (!Array.isArray(raw)) return [];
    return raw.map((c: any) => ({
      value: String(c.id),
      label: `Config #${c.id} — ${c.fungsi} (Keg. ${c.kegiatan_id})`,
    }));
  }, [configOptsResp]);

  const createMutation = useApiMutation(
    (data: Record<string, unknown>) => monitoringKegiatanService.create(data),
    { invalidateKeys: [['monitoring-kegiatan']], onSuccess: () => { setShowFormModal(false); setEditingRow(null); } },
  );

  const updateMutation = useApiMutation(
    (data: Record<string, unknown>) => monitoringKegiatanService.update(data.id as number, data),
    { invalidateKeys: [['monitoring-kegiatan']], onSuccess: () => { setShowFormModal(false); setEditingRow(null); } },
  );

  const deleteMutation = useApiMutation(
    (data: Record<string, unknown>) => monitoringKegiatanService.delete(data.id as number),
    { invalidateKeys: [['monitoring-kegiatan']], onSuccess: () => setDeleteTarget(null) },
  );

  const data: MonitoringRow[] = useMemo(() => {
    if (!response?.data) return [];
    const raw = response.data;
    if (!Array.isArray(raw)) return [];
    return raw.map((m: any) => ({
      id: m.id,
      kode_sampel: m.kode_sampel || '-',
      kegiatan: m.kegiatan || '-',
      nmkec: m.nmkec || '-',
      nmdesa: m.nmdesa || '-',
      mitra: m.mitra || null,
      fungsi: m.fungsi || '-',
      status_pelaksanaan: m.status_pelaksanaan || null,
    }));
  }, [response]);

  const filteredData = useMemo(() => {
    const q = search.toLowerCase();
    return data.filter((row) => {
      if (!search) return true;
      return (
        row.kode_sampel.toLowerCase().includes(q) ||
        row.kegiatan.toLowerCase().includes(q) ||
        row.nmkec.toLowerCase().includes(q) ||
        row.nmdesa.toLowerCase().includes(q) ||
        (row.mitra || '').toLowerCase().includes(q) ||
        row.fungsi.toLowerCase().includes(q)
      );
    });
  }, [data, search]);

  const hasActiveFilters = Boolean(filterFungsi || filterKegiatan);

  const formFields = useMemo(() => [
    { name: 'kode_sampel', label: 'Kode Sampel', type: 'text' as const, placeholder: 'SMP-001', required: true },
    {
      name: 'fungsi', label: 'Fungsi', type: 'select' as const, required: true,
      options: ['Sosial', 'Produksi', 'Distribusi', 'Nerwilis', 'IPDS'].map(f => ({ value: f, label: f })),
    },
    {
      name: 'kegiatan_id', label: 'Kegiatan', type: 'select' as const, required: true,
      options: kegiatanFormOptions,
      searchable: true,
      wrapLabel: true,
      placeholder: 'Pilih kegiatan',
      searchPlaceholder: 'Cari kegiatan...',
      emptyLabel: 'Kegiatan tidak ditemukan.',
    },
    { name: 'kec_id', label: 'Kode Kecamatan', type: 'text' as const, placeholder: '3215112', required: true },
    { name: 'desa_id', label: 'Kode Desa', type: 'text' as const, placeholder: '3215112001', required: true },
    {
      name: 'monitoring_kegiatan_config_id', label: 'Config Monitoring', type: 'select' as const, required: true,
      options: configOptions,
    },
  ], [kegiatanFormOptions, configOptions]);

  const handleFormSubmit = (formData: Record<string, unknown>) => {
    if (editingRow) {
      updateMutation.mutate({ ...formData, id: editingRow.id });
    } else {
      createMutation.mutate(formData);
    }
  };

  const resetFilters = () => {
    setSearch('');
    setFilterFungsi('');
    setFilterKegiatan('');
  };

  const handleExport = async () => {
    try {
      setIsExporting(true);
      const params = new URLSearchParams();
      if (filterKegiatan) params.append('kegiatan_id', filterKegiatan);

      const token = localStorage.getItem('token');
      const res = await fetch(`/api/kantor/kegiatan/monitoring/download${params.toString() ? `?${params.toString()}` : ''}`, {
        headers: {
          Accept: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/octet-stream, */*',
          ...(token ? { Authorization: `Bearer ${token}` } : {}),
        },
      });
      if (!res.ok) throw new Error('Export gagal');
      const blob = await res.blob();
      const url = window.URL.createObjectURL(blob);
      const link = document.createElement('a');
      link.href = url;
      link.download = 'monitoring-kegiatan.xlsx';
      document.body.appendChild(link);
      link.click();
      link.remove();
      window.URL.revokeObjectURL(url);
    } finally {
      setIsExporting(false);
    }
  };

  const columnHelper = createColumnHelper<MonitoringRow>();

  const columns = useMemo(() => [
    columnHelper.accessor('kode_sampel', {
      header: 'Kode Sampel',
      cell: (info) => (
        <div className="whitespace-normal font-medium text-slate-900 dark:text-white">
          <div>{info.getValue()}</div>
          <div className="mt-1 text-xs text-slate-500 dark:text-slate-400">
            {info.row.original.nmkec}, {info.row.original.nmdesa}
          </div>
        </div>
      ),
    }),
    columnHelper.accessor('fungsi', {
      header: 'Fungsi',
      cell: info => <span className="text-[13px] text-gray-600 dark:text-gray-400 whitespace-nowrap">{info.getValue()}</span>,
    }),
    columnHelper.accessor('status_pelaksanaan', {
      header: 'Status',
      cell: (info) => {
        const status = info.getValue() || 'belum dimulai';
        return (
          <span className={`px-2 py-0.5 rounded-md border text-[10px] font-bold uppercase tracking-wider whitespace-nowrap ${statusStyles[status] || statusStyles['belum dimulai']}`}>
            {status}
          </span>
        );
      },
    }),
    columnHelper.display({
      id: 'actions',
      header: () => <div className="text-right">Aksi</div>,
      cell: (info) => (
        <div className="flex justify-end gap-1 whitespace-nowrap">
          <button
            data-scan="tombol edit monitoring"
            onClick={() => { setEditingRow(info.row.original); setShowFormModal(true); }}
            className="p-1.5 px-2.5 rounded-lg hover:bg-gray-100 dark:hover:bg-white/10 text-gray-500 dark:text-gray-400 text-[12px] font-medium flex items-center gap-1.5 transition"
          >
            <Pencil className="h-3.5 w-3.5" />
            Edit
          </button>
          <button
            data-scan="tombol hapus monitoring"
            onClick={() => setDeleteTarget(info.row.original)}
            className="p-1.5 px-2.5 rounded-lg hover:bg-red-50 dark:hover:bg-red-500/10 text-red-600 dark:text-red-400 text-[12px] font-medium flex items-center gap-1.5 transition"
          >
            <Trash2 className="h-3.5 w-3.5" />
            Hapus
          </button>
        </div>
      ),
    }),
  ], []);

  const table = useReactTable({
    data: filteredData,
    columns,
    getCoreRowModel: getCoreRowModel(),
    getPaginationRowModel: getPaginationRowModel(),
    initialState: { pagination: { pageSize: 7 } },
  });

  return (
    <div className="space-y-6">
      <PageHeader
        title="Monitoring Kegiatan"
        description="Pantau progres dan kelengkapan data lapangan."
      />

      {/* Action Header */}
      <div className="flex items-center justify-between gap-3">
        <div ref={configMenuRef} className="relative">
          <button
            data-scan="tombol konfigurasi"
            onClick={() => setShowConfigMenu(!showConfigMenu)}
            className="flex items-center gap-2 px-4 py-2 rounded-full bg-white/70 dark:bg-white/5 glass border border-white/50 dark:border-white/10 text-sm font-medium hover:bg-white/90 dark:hover:bg-white/10 transition"
          >
            <Settings className="h-4 w-4" />
            Konfigurasi
            <ChevronDown className="h-3.5 w-3.5" />
          </button>
          {showConfigMenu && (
            <div className="absolute left-0 top-full mt-2 z-50 min-w-[200px] bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-lg py-1">
              <button
                data-scan="menu config monitoring"
                onClick={() => { setShowConfigMenu(false); navigate({ to: '/kegiatan/monitoring/config' }); }}
                className="w-full flex items-center gap-2.5 px-4 py-2.5 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition"
              >
                <Settings className="h-4 w-4 text-gray-400" />
                Config Monitoring
              </button>
              <button
                data-scan="menu config detil"
                onClick={() => { setShowConfigMenu(false); navigate({ to: '/kegiatan/monitoring/detil-configs' }); }}
                className="w-full flex items-center gap-2.5 px-4 py-2.5 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition"
              >
                <Settings className="h-4 w-4 text-gray-400" />
                Config Detil
              </button>
              <div className="my-1 border-t border-gray-100 dark:border-gray-700" />
              <button
                onClick={() => { setShowConfigMenu(false); }}
                className="w-full flex items-center gap-2.5 px-4 py-2.5 text-sm text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700 transition"
              >
                <Table2 className="h-4 w-4 text-gray-400" />
                Buat Table (Legacy)
              </button>
            </div>
          )}
        </div>
        <button
          data-scan="tombol tambah monitoring"
          onClick={() => { setEditingRow(null); setShowFormModal(true); }}
          className="flex items-center gap-1.5 px-4 py-2 rounded-full bg-amber-300 hover:bg-amber-400 text-gray-900 text-sm font-semibold shadow-lg shadow-amber-500/20 transition"
        >
          <Plus className="h-4 w-4" />
          Tambah Monitoring
        </button>
      </div>

      {/* Search + Filter */}
      <div 
        data-scan="pencarian dan filter"
        className="bg-white/40 dark:bg-white/5 glass rounded-2xl border border-white/40 dark:border-white/10 overflow-hidden"
      >
        <div className="flex items-center gap-3 p-2">
          <div className="relative flex-1 group">
            <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400 group-focus-within:text-orange-500 transition-colors" />
            <input
              data-scan="input cari monitoring"
              type="text"
              value={search}
              onChange={(e) => setSearch(e.target.value)}
              placeholder="Cari monitoring..."
              className="w-full bg-transparent border-none focus:ring-0 focus:outline-none pl-10 pr-4 py-2 text-sm text-gray-700 dark:text-gray-200 placeholder:text-gray-400"
            />
          </div>
          <button
            data-scan="tombol toggle filter"
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
              <span className={`w-2 h-2 rounded-full ${showFilters ? 'bg-white' : 'bg-orange-500'}`} />
            )}
          </button>
          <button
            data-scan="tombol export"
            onClick={handleExport}
            disabled={isExporting}
            className="flex items-center gap-1.5 px-4 py-2 rounded-xl border border-white/50 dark:border-white/10 bg-white/70 dark:bg-white/5 text-gray-700 dark:text-gray-200 text-sm font-medium hover:bg-white dark:hover:bg-white/10 transition disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <Download className="h-4 w-4" />
            {isExporting ? 'Mengekspor...' : 'Export XLSX'}
          </button>
        </div>

        {showFilters && (
          <div 
            data-scan="rincian filter"
            className="flex flex-wrap items-end gap-3 px-4 pb-3 pt-1 border-t border-white/30 dark:border-white/10"
          >
            <div className="flex flex-col gap-1.5">
              <label className="text-[10px] uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Fungsi</label>
              <select
                data-scan="filter fungsi"
                value={filterFungsi}
                onChange={(e) => setFilterFungsi(e.target.value)}
                className="h-9 rounded-xl border border-white/50 dark:border-white/10 bg-white/70 dark:bg-white/5 px-3 text-xs text-gray-700 dark:text-gray-200 outline-none cursor-pointer"
              >
                <option value="">Semua</option>
                {fungsiFilterOptions.map((opt) => (
                  <option key={opt.value} value={opt.value}>{opt.label}</option>
                ))}
              </select>
            </div>
            <div className="flex flex-col gap-1.5">
              <label className="text-[10px] uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Kegiatan</label>
              <SearchableSelect
                value={filterKegiatan}
                onChange={setFilterKegiatan}
                options={kegiatanFilterOptions}
                placeholder="Semua Kegiatan"
                searchPlaceholder="Cari kegiatan..."
                emptyLabel="Kegiatan tidak ditemukan."
                wrapLabel
                className="sm:w-[300px]"
              />
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

      {/* Table */}
      <div 
        data-scan="tabel monitoring"
        className="bg-white/65 dark:bg-white/[0.04] glass-strong rounded-[24px] border border-white/50 dark:border-white/10 shadow-[0_10px_40px_-15px_rgba(0,0,0,0.08)] overflow-hidden"
      >
        {isLoading ? (
          <LoadingSkeleton variant="table" message="Memuat data monitoring..." />
        ) : error ? (
          <ApiErrorBoundary error={error} onRetry={refetch} title="Gagal Memuat Monitoring" />
        ) : (
          <>
            <div className="overflow-x-auto">
              <table className="w-full text-[13px]">
                <thead>
                  <tr className="text-left text-[11.5px] text-gray-500 dark:text-gray-400 border-b border-gray-200/60 dark:border-white/10">
                    {table.getHeaderGroups().map((headerGroup) => (
                      <React.Fragment key={headerGroup.id}>
                        {headerGroup.headers.map((header) => (
                          <th key={header.id} className="px-6 py-5 font-bold uppercase tracking-widest bg-gray-50/30 dark:bg-white/5 whitespace-nowrap">
                            {flexRender(header.column.columnDef.header, header.getContext())}
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
                        <td key={cell.id} className="px-6 py-5">
                          {flexRender(cell.column.columnDef.cell, cell.getContext())}
                        </td>
                      ))}
                    </tr>
                  ))}
                  {table.getRowModel().rows.length === 0 && (
                    <tr>
                      <td colSpan={columns.length} className="px-6 py-12 text-center text-gray-400 text-sm">
                        Tidak ada data monitoring.
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
                  Halaman <span className="text-gray-900 dark:text-white font-bold">{table.getState().pagination.pageIndex + 1}</span> dari{' '}
                  <span className="text-gray-900 dark:text-white font-bold">{response?.pagination_info?.total_page || response?.meta?.last_page || table.getPageCount() || 1}</span>
                </p>
                <div className="h-4 w-px bg-gray-200 dark:bg-white/10" />
                <p className="text-[12px] text-gray-500 dark:text-gray-400 font-medium">
                  Menampilkan {filteredData.length} dari {response?.pagination_info?.total_records || response?.meta?.total || data.length} data
                </p>
              </div>
              <div className="flex gap-2">
                <button
                  data-scan="tombol halaman sebelumnya"
                  onClick={() => table.previousPage()}
                  disabled={!table.getCanPreviousPage()}
                  className="px-4 py-2 text-[11px] font-bold rounded-full bg-white/70 dark:bg-white/10 border border-white/50 dark:border-white/20 text-gray-700 dark:text-gray-300 hover:bg-white transition-all disabled:opacity-30 disabled:cursor-not-allowed shadow-sm"
                >
                  Sebelumnya
                </button>
                <button
                  data-scan="tombol halaman berikutnya"
                  onClick={() => table.nextPage()}
                  disabled={!table.getCanNextPage()}
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
      <MonitoringCreateModal
        data-scan="modal form monitoring"
        open={showFormModal}
        onClose={() => { setShowFormModal(false); setEditingRow(null); }}
        onSubmit={handleFormSubmit}
        isEdit={!!editingRow}
        initialData={editingRow}
        isPending={createMutation.isPending || updateMutation.isPending}
      />

      {/* Delete Confirmation */}
      <ConfirmDialog
        open={!!deleteTarget}
        onClose={() => setDeleteTarget(null)}
        onConfirm={() => deleteTarget && deleteMutation.mutate({ id: deleteTarget.id })}
        title="Hapus Monitoring"
        message={`Yakin ingin menghapus monitoring sampel "${deleteTarget?.kode_sampel}"?`}
        variant="danger"
        isLoading={deleteMutation.isPending}
      />
    </div>
  );
}
