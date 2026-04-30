import React, { useState, useMemo, useEffect } from 'react';
import { usePagination } from '../hooks/usePagination';
import { 
  createColumnHelper, 
  flexRender, 
  getCoreRowModel, 
  useReactTable,
  getPaginationRowModel
} from '@tanstack/react-table';
import { useNavigate } from '@tanstack/react-router';
import { 
  Plus, 
  Search, 
  Filter,
  Download,
  Pencil,
  Trash2,
  FileInput,
} from 'lucide-react';
import { motion } from 'motion/react';
import { useApiQuery, useApiMutation } from '../hooks/useApi';
import { useDebounce } from '../hooks/useDebounce';
import { useAuth } from '../hooks/useAuth';
import { PageHeader } from '../components/PageHeader';
import { LoadingSkeleton } from '../components/LoadingSkeleton';
import { ApiErrorBoundary } from '../components/ApiErrorBoundary';
import { EntityFormModal } from '../components/EntityFormModal';
import { ConfirmDialog } from '../components/ConfirmDialog';
import { SearchableSelect } from '@/components/ui/searchable-select';
import { API_BASE_URL, getToken } from '../lib/api';
import { penugasanService } from '../lib/api-services';
import type { Penugasan as ApiPenugasan, PenugasanCreatePayload } from '../types/api';

const BULAN = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

const fmtMonthLabel = (value: string) => {
  if (!value) return '-';
  const dt = new Date(value);
  if (Number.isNaN(dt.getTime())) return value;
  return `${dt.getDate()} ${BULAN[dt.getMonth()]} ${dt.getFullYear()}`;
};

const fmtMonthYearLabel = (value: string) => {
  if (!value) return '-';
  const dt = new Date(value);
  if (Number.isNaN(dt.getTime())) return value;
  return `${BULAN[dt.getMonth()]} ${dt.getFullYear()}`;
};

type PenugasanRow = {
  id: number;
  created_by: number;
  kegiatan_id: number;
  kegiatan_nama: string;
  kegiatan_fungsi: string;
  kegiatan_tahun: string;
  petugas_nama: string;
  mitra_nama: string;
  mitra_id: number | null;
  mitra_nik: string;
  jabatan_tugas: string;
  volume: number;
  nilai: number;
  bln_bayar: string;
};

export function PenugasanTable() {
  const { user } = useAuth();
  const navigate = useNavigate();
  const [search, setSearch] = useState('');
  const [filterKegiatan, setFilterKegiatan] = useState('');
  const [filterJabatan, setFilterJabatan] = useState('');
  const [filterMitra, setFilterMitra] = useState('');
  const [filterBulanBayar, setFilterBulanBayar] = useState('');
  const [showFilters, setShowFilters] = useState(false);
  const [isExporting, setIsExporting] = useState(false);
  const [showFormModal, setShowFormModal] = useState(false);
  const [editingRow, setEditingRow] = useState<PenugasanRow | null>(null);
  const [deleteTarget, setDeleteTarget] = useState<PenugasanRow | null>(null);

  const pagination = usePagination(10);
  const debouncedSearch = useDebounce(search, 300);

  const { data: response, isLoading, error, refetch } = useApiQuery(
    ['penugasan', filterKegiatan, filterJabatan, filterMitra, filterBulanBayar, pagination.params],
    () => penugasanService.list({
      ...pagination.params,
      'filter[kegiatan_id]': filterKegiatan || undefined,
      'filter[jabatan_tugas]': filterJabatan || undefined,
      'filter[mitra_id]': filterMitra || undefined,
      'filter[bln_bayar]': filterBulanBayar || undefined,
    }),
  );

  useEffect(() => {
    pagination.sync(response);
  }, [response]);

  useEffect(() => {
    pagination.reset();
  }, [filterKegiatan, filterJabatan, filterMitra, filterBulanBayar, debouncedSearch]);

  const { data: filterResp } = useApiQuery(
    ['penugasan-filters'],
    () => penugasanService.filters(),
  );

  const { data: formOptionsResp } = useApiQuery(
    ['penugasan-form-options'],
    () => penugasanService.formOptions(),
  );

  const { data: mitraOptionsResp } = useApiQuery(
    ['penugasan-mitra-options'],
    () => penugasanService.mitraOptions(),
  );

  const createMutation = useApiMutation(
    (data: Record<string, unknown>) => penugasanService.create(data as any),
    { invalidateKeys: [['penugasan']], onSuccess: () => { setShowFormModal(false); setEditingRow(null); } },
  );

  const updateMutation = useApiMutation(
    (data: Record<string, unknown>) => penugasanService.update(data.id as number, data as any),
    { invalidateKeys: [['penugasan']], onSuccess: () => { setShowFormModal(false); setEditingRow(null); } },
  );

  const deleteMutation = useApiMutation(
    (data: Record<string, unknown>) => penugasanService.delete(data.id as number),
    { invalidateKeys: [['penugasan']], onSuccess: () => setDeleteTarget(null) },
  );

  const data: PenugasanRow[] = useMemo(() => {
    if (!response?.data) return [];
    const raw = response.data;
    if (!Array.isArray(raw)) return [];
    return raw.map((p: ApiPenugasan) => ({
      id: p.id,
      created_by: p.created_by,
      kegiatan_id: p.kegiatan_id,
      kegiatan_nama: p.kegiatan?.nama || `Kegiatan #${p.kegiatan_id}`,
      kegiatan_fungsi: p.kegiatan?.fungsi || '-',
      kegiatan_tahun: p.kegiatan?.tahun || '-',
      petugas_nama: p.mitra?.nama_lengkap || p.pegawai?.nama || '-',
      mitra_nama: p.mitra?.nama_lengkap || '',
      mitra_id: p.mitra_id,
      mitra_nik: p.mitra?.nik || '-',
      jabatan_tugas: p.jabatan_tugas,
      volume: p.volume,
      nilai: p.nilai,
      bln_bayar: p.bln_bayar || '',
    }));
  }, [response]);

  const kegiatanFilterOptions = useMemo(() => {
    return (filterResp?.data?.kegiatanList || [])
      .map((item) => ({ value: String(item.id), label: item.nama }))
      .sort((a, b) => a.label.localeCompare(b.label, 'id-ID'));
  }, [filterResp]);

  const mitraOptions = useMemo(() => {
    return (mitraOptionsResp?.data?.mitraOptions || [])
      .map((item) => ({ value: String(item.id), label: item.nama_lengkap }))
      .sort((a, b) => a.label.localeCompare(b.label, 'id-ID'));
  }, [mitraOptionsResp]);

  const bulanBayarOptions = useMemo(() => {
    return [...(filterResp?.data?.blnBayarList || [])].sort((a, b) => b.localeCompare(a));
  }, [filterResp]);

  const jabatanOptions = useMemo(() => {
    return formOptionsResp?.data?.jabatanTugasOptions || ['PCL', 'PML', 'OPERATOR', 'SUPERVISOR'];
  }, [formOptionsResp]);

  const filteredData = useMemo(() => {
    return data.filter((row) => {
      const q = debouncedSearch.toLowerCase();
      const matchSearch =
        !debouncedSearch ||
        row.kegiatan_nama.toLowerCase().includes(q) ||
        row.petugas_nama.toLowerCase().includes(q) ||
        row.jabatan_tugas.toLowerCase().includes(q);

      return matchSearch;
    });
  }, [data, debouncedSearch]);

  const stats = useMemo(() => ({
    total: data.length,
    proses: data.filter(d => d.mitra_id == null).length,
    selesai: data.filter(d => d.mitra_id != null).length,
    totalNilai: data.reduce((sum, d) => sum + d.nilai, 0),
  }), [data]);

  const filteredTotalNilai = useMemo(
    () => filteredData.reduce((sum, row) => sum + row.nilai, 0),
    [filteredData]
  );

  const showTotalFooter = Boolean(filterKegiatan && filterBulanBayar);

  const hasActiveFilters = Boolean(filterKegiatan || filterJabatan || filterMitra || filterBulanBayar);

  const columnHelper = createColumnHelper<PenugasanRow>();

  const columns = useMemo(() => [
    columnHelper.accessor('kegiatan_nama', {
      header: 'Kegiatan',
      cell: info => (
        <div className="whitespace-nowrap font-medium text-slate-900 dark:text-white">
          <div>{info.getValue()}</div>
          <div className="text-xs text-slate-500 dark:text-slate-400">
            {info.row.original.kegiatan_fungsi} · {info.row.original.kegiatan_tahun}
          </div>
        </div>
      ),
    }),
    columnHelper.accessor('petugas_nama', {
      header: 'Mitra',
      cell: info => (
        <div className="whitespace-nowrap">
          <div>{info.getValue()}</div>
          <div className="text-xs text-slate-500 dark:text-slate-400">
            {info.row.original.mitra_nama ? info.row.original.mitra_nik : '-'}
          </div>
        </div>
      ),
    }),
    columnHelper.accessor('jabatan_tugas', {
      header: 'Jabatan',
      cell: info => <span className="whitespace-nowrap">{info.getValue()}</span>,
    }),
    columnHelper.accessor('volume', {
      header: 'Volume',
      cell: info => <span className="whitespace-nowrap">{info.getValue().toLocaleString('id-ID')}</span>,
    }),
    columnHelper.accessor('nilai', {
      header: () => <div className="text-right">Nilai</div>,
      cell: info => (
        <span className="block whitespace-nowrap text-right">
          Rp {info.getValue().toLocaleString('id-ID')}
        </span>
      ),
    }),
    columnHelper.accessor('bln_bayar', {
      header: 'Bulan Bayar',
      cell: info => (
        <span className="whitespace-nowrap">{info.getValue() ? fmtMonthYearLabel(info.getValue()) : '-'}</span>
      ),
    }),
    columnHelper.display({
      id: 'actions',
      header: () => <div className="text-right">Aksi</div>,
      cell: (info) => (
        info.row.original.created_by === user?.id ? (
          <div className="flex justify-end gap-1 whitespace-nowrap">
            <button
              data-scan="tombol edit penugasan"
              onClick={() => { setEditingRow(info.row.original); setShowFormModal(true); }}
              className="p-1.5 px-2.5 rounded-lg hover:bg-gray-100 dark:hover:bg-white/10 text-gray-500 dark:text-gray-400 text-[12px] font-medium flex items-center gap-1.5 transition"
            >
              <Pencil className="h-3.5 w-3.5" />
              Edit
            </button>
            <button
              data-scan="tombol hapus penugasan"
              onClick={() => setDeleteTarget(info.row.original)}
              className="p-1.5 px-2.5 rounded-lg hover:bg-red-50 dark:hover:bg-red-500/10 text-red-600 dark:text-red-400 text-[12px] font-medium flex items-center gap-1.5 transition"
            >
              <Trash2 className="h-3.5 w-3.5" />
              Hapus
            </button>
          </div>
        ) : null
      ),
    }),
  ], [user?.id]);

  const table = useReactTable({
    data: filteredData,
    columns,
    getCoreRowModel: getCoreRowModel(),
    manualPagination: true,

  });

  const formFields = useMemo(() => [
    {
      name: 'kegiatan_id',
      label: 'Kegiatan',
      type: 'select' as const,
      required: true,
      options: kegiatanFilterOptions,
      colSpan: 2 as const,
      searchable: true,
      wrapLabel: true,
      placeholder: 'Pilih kegiatan',
      searchPlaceholder: 'Cari kegiatan...',
      emptyLabel: 'Kegiatan tidak ditemukan.',
    },
    {
      name: 'mitra_id',
      label: 'Mitra',
      type: 'select' as const,
      options: [{ value: '', label: 'Pilih Mitra' }, ...mitraOptions],
      searchable: true,
      placeholder: 'Pilih mitra',
      searchPlaceholder: 'Cari mitra...',
      emptyLabel: 'Mitra tidak ditemukan.',
    },
    { name: 'jabatan_tugas', label: 'Jabatan Tugas', type: 'select' as const, required: true, options: jabatanOptions.map((option) => ({ value: option, label: option }))},
    { name: 'volume', label: 'Volume', type: 'number' as const, required: true },
    { name: 'nilai', label: 'Nilai (Rp)', type: 'number' as const, required: true },
    { name: 'bln_bayar', label: 'Bulan Bayar', type: 'date' as const, dateMode: 'month' as const, placeholder: 'Pilih bulan bayar' },
  ], [jabatanOptions, kegiatanFilterOptions, mitraOptions]);

  const handleFormSubmit = (formData: Record<string, unknown>) => {
    const payload: Partial<PenugasanCreatePayload> = {
      ...formData,
      kegiatan_id: Number(formData.kegiatan_id),
      jabatan_tugas: String(formData.jabatan_tugas) as PenugasanCreatePayload['jabatan_tugas'],
      mitra_id: formData.mitra_id ? Number(formData.mitra_id) : null,
      volume: Number(formData.volume),
      nilai: Number(formData.nilai),
      bln_bayar: formData.bln_bayar ? String(formData.bln_bayar) : '',
    };
    if (editingRow) {
      updateMutation.mutate({ ...payload, id: editingRow.id } as Record<string, unknown>);
    } else {
      createMutation.mutate(payload as Record<string, unknown>);
    }
  };

  const fmtCurrency = (n: number) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(n);

  const resetFilters = () => {
    setSearch('');
    setFilterKegiatan('');
    setFilterJabatan('');
    setFilterMitra('');
    setFilterBulanBayar('');
  };

  const handleCatatRealisasi = () => {
    const selectedKegiatan = kegiatanFilterOptions.find((k) => k.value === filterKegiatan);
    if (!selectedKegiatan) return;

    // Normalize activity name into search keywords
    const searchWords = selectedKegiatan.label
      .toLowerCase()
      .replace(/[^a-z0-9\s]/g, '')
      .split(/\s+/)
      .filter(Boolean)
      .join(', ');

    sessionStorage.setItem(
      'pencatatan_prefilled',
      JSON.stringify({
        searchQuery: searchWords,
        amount: filteredTotalNilai,
      }),
    );

    navigate({ to: '/umum/rkk-dipa/pencairan' as any });
  };

  const handleExport = async () => {
    try {
      setIsExporting(true);
      const token = getToken();
      const params = new URLSearchParams();

      if (filterBulanBayar) {
        params.append('filter[bln_bayar]', filterBulanBayar);
      }

      const response = await fetch(`${API_BASE_URL}/kantor/penugasan/export${params.toString() ? `?${params.toString()}` : ''}`, {
        headers: {
          Accept: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/octet-stream, */*',
          ...(token ? { Authorization: `Bearer ${token}` } : {}),
        },
      });

      if (!response.ok) {
        throw new Error('Export gagal');
      }

      const blob = await response.blob();
      const url = window.URL.createObjectURL(blob);
      const link = document.createElement('a');
      link.href = url;
      link.download = 'penugasan.xlsx';
      document.body.appendChild(link);
      link.click();
      link.remove();
      window.URL.revokeObjectURL(url);
    } finally {
      setIsExporting(false);
    }
  };

  return (
    <div className="space-y-6">
      <PageHeader 
        title="Daftar Penugasan" 
        description="Monitoring distribusi tugas dan tenggat waktu petugas lapangan."
      />

      {/* Stats Summary */}
      <div 
        data-scan="ringkasan statistik"
        className="grid grid-cols-2 md:grid-cols-4 gap-4"
      >
        {[
          { label: 'Total Tugas', value: stats.total, color: 'text-gray-900 dark:text-white' },
          { label: 'Dalam Proses', value: stats.proses, color: 'text-blue-600 dark:text-blue-400' },
          { label: 'Selesai', value: stats.selesai, color: 'text-emerald-600 dark:text-emerald-400' },
          { label: 'Total Nilai', value: fmtCurrency(stats.totalNilai), color: 'text-amber-600 dark:text-amber-400' },
        ].map((s, i) => (
          <motion.div key={i} initial={{ opacity: 0, y: 12 }} animate={{ opacity: 1, y: 0 }} transition={{ delay: i * 0.08 }}
            className="bg-white/40 dark:bg-white/5 glass p-4 rounded-2xl border border-white/40 dark:border-white/10"
          >
            <p className="text-[11px] font-bold uppercase tracking-widest text-gray-400 mb-1">{s.label}</p>
            <p className={`text-2xl font-bold ${s.color}`}>{isLoading ? '...' : s.value}</p>
          </motion.div>
        ))}
      </div>

      {/* Search + Filter */}
      <div 
        data-scan="pencarian dan filter"
        className="bg-white/40 dark:bg-white/5 glass rounded-2xl border border-white/40 dark:border-white/10 overflow-hidden"
      >
        <div className="flex flex-col gap-3 p-2 sm:flex-row sm:flex-wrap sm:items-center">
          <div className="relative flex-1 group">
            <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400 group-focus-within:text-orange-500 transition-colors" />
            <input
              data-scan="input pencarian"
              type="text"
              value={search}
              onChange={(e) => setSearch(e.target.value)}
              placeholder="Cari penugasan..."
              className="w-full bg-transparent border-none focus:ring-0 focus:outline-none pl-10 pr-4 py-2 text-sm text-gray-700 dark:text-gray-200 placeholder:text-gray-400"
            />
          </div>
          <button
            onClick={() => setShowFilters(!showFilters)}
            className={`flex w-full items-center justify-center gap-2 px-4 py-2 rounded-xl transition text-[13px] font-medium sm:w-auto ${
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
            data-scan="tombol tambah penugasan"
            onClick={() => { setEditingRow(null); setShowFormModal(true); }}
            className="flex w-full items-center justify-center gap-1.5 px-4 py-2 rounded-full bg-amber-300 hover:bg-amber-400 text-gray-900 text-sm font-semibold shadow-lg shadow-amber-500/20 transition sm:w-auto"
          >
            <Plus className="h-4 w-4" />
            Tambah Penugasan
          </button>
          <button
            data-scan="tombol export"
            onClick={handleExport}
            disabled={isExporting}
            className="flex w-full items-center justify-center gap-1.5 px-4 py-2 rounded-xl border border-white/50 dark:border-white/10 bg-white/70 dark:bg-white/5 text-gray-700 dark:text-gray-200 text-sm font-medium hover:bg-white dark:hover:bg-white/10 transition disabled:opacity-50 disabled:cursor-not-allowed sm:w-auto"
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
              <label className="text-[10px] uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Kegiatan</label>
              <SearchableSelect
                value={filterKegiatan}
                onChange={setFilterKegiatan}
                options={kegiatanFilterOptions}
                placeholder="Semua Kegiatan"
                searchPlaceholder="Cari kegiatan..."
                emptyLabel="Kegiatan tidak ditemukan."
                wrapLabel
                className="sm:w-[250px]"
              />
            </div>
            <div className="flex flex-col gap-1.5">
              <label className="text-[10px] uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Jabatan</label>
              <select
                value={filterJabatan}
                onChange={(e) => setFilterJabatan(e.target.value)}
                className="h-9 w-full rounded-xl border border-white/50 dark:border-white/10 bg-white/70 dark:bg-white/5 px-3 text-xs text-gray-700 dark:text-gray-200 outline-none cursor-pointer sm:w-[150px]"
              >
                <option value="">Semua</option>
                {jabatanOptions.map((jabatan) => (
                  <option key={jabatan} value={jabatan}>{jabatan}</option>
                ))}
              </select>
            </div>
            <div className="flex flex-col gap-1.5">
              <label className="text-[10px] uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Mitra</label>
              <SearchableSelect
                value={filterMitra}
                onChange={setFilterMitra}
                options={mitraOptions}
                placeholder="Semua Mitra"
                searchPlaceholder="Cari mitra..."
                emptyLabel="Mitra tidak ditemukan."
                className="sm:w-[200px]"
              />
            </div>
            <div className="flex flex-col gap-1.5">
              <label className="text-[10px] uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Bulan Bayar</label>
              <select
                value={filterBulanBayar}
                onChange={(e) => setFilterBulanBayar(e.target.value)}
                className="h-9 w-full rounded-xl border border-white/50 dark:border-white/10 bg-white/70 dark:bg-white/5 px-3 text-xs text-gray-700 dark:text-gray-200 outline-none cursor-pointer sm:w-[150px]"
              >
                <option value="">Semua</option>
                {bulanBayarOptions.map((bulan) => (
                  <option key={bulan} value={bulan}>{fmtMonthLabel(bulan)}</option>
                ))}
              </select>
            </div>
            {hasActiveFilters && (
              <button
                onClick={resetFilters}
                className="h-9 px-4 rounded-xl border border-white/50 dark:border-white/10 bg-white/70 dark:bg-white/5 text-xs font-medium text-gray-700 dark:text-gray-200 hover:bg-white dark:hover:bg-white/10 transition shadow-sm"
              >
                Reset
              </button>
            )}
          </div>
        )}
      </div>

      {/* Table */}
      <div 
        data-scan="tabel penugasan"
        className="bg-white/65 dark:bg-white/[0.04] glass-strong rounded-[24px] border border-white/50 dark:border-white/10 shadow-[0_10px_40px_-15px_rgba(0,0,0,0.08)] overflow-hidden"
      >
        {isLoading ? (
          <LoadingSkeleton variant="table" message="Memuat data penugasan..." />
        ) : error ? (
          <ApiErrorBoundary error={error} onRetry={refetch} title="Gagal Memuat Penugasan" />
        ) : (
          <>
            <div className="overflow-x-auto">
              <table className="w-full caption-bottom text-sm">
                <thead className="[&_tr]:border-b">
                  <tr className="border-b transition-colors hover:bg-muted/50">
                    {table.getHeaderGroups().map(headerGroup => (
                      <React.Fragment key={headerGroup.id}>
                        {headerGroup.headers.map(header => (
                          <th key={header.id} className="h-10 px-2 text-start align-middle font-medium whitespace-nowrap text-foreground">
                            {flexRender(header.column.columnDef.header, header.getContext())}
                          </th>
                        ))}
                      </React.Fragment>
                    ))}
                  </tr>
                </thead>
                <tbody className="[&_tr:last-child]:border-0">
                  {table.getRowModel().rows.map(row => (
                    <tr key={row.id} className="border-b transition-colors hover:bg-muted/50">
                      {row.getVisibleCells().map(cell => (
                        <td key={cell.id} className="p-2 align-middle whitespace-nowrap">
                          {flexRender(cell.column.columnDef.cell, cell.getContext())}
                        </td>
                      ))}
                    </tr>
                  ))}
                  {table.getRowModel().rows.length === 0 && (
                    <tr>
                      <td colSpan={columns.length} className="px-6 py-12 text-center text-gray-400 text-sm">
                        Tidak ada data penugasan.
                      </td>
                    </tr>
                  )}
                </tbody>
                {showTotalFooter && (
                  <tfoot className="[&_tr]:border-t">
                    <tr className="border-b transition-colors hover:bg-muted/50">
                      <td className="p-2 align-middle whitespace-nowrap font-semibold" colSpan={4}>Total</td>
                      <td className="p-2 align-middle whitespace-nowrap text-right font-semibold">
                        Rp {filteredTotalNilai.toLocaleString('id-ID')}
                      </td>
                      <td className="p-2 px-6 align-middle whitespace-nowrap" colSpan={2}>
                        <button
                          data-scan="tombol catat realisasi"
                          onClick={handleCatatRealisasi}
                          className="inline-flex items-center gap-1.5 h-8 px-3 text-[11px] font-bold rounded-full bg-blue-500/10 text-blue-600 border border-blue-500/20 hover:bg-blue-500 hover:text-white transition-all shadow-sm"
                        >
                          <FileInput className="w-3.5 h-3.5" />
                          Catat Realisasi
                        </button>
                      </td>
                    </tr>
                  </tfoot>
                )}
              </table>
            </div>
            <div 
              data-scan="navigasi halaman"
              className="flex items-center justify-between px-6 py-5 border-t border-gray-200/60 dark:border-white/10 dark:bg-white/5"
            >
              <div className="flex items-center gap-4">
                <p className="text-[12px] text-gray-500 dark:text-gray-400 font-medium">
                  Halaman <span className="text-gray-900 dark:text-white font-bold">{pagination.page}</span> dari <span className="text-gray-900 dark:text-white font-bold">{pagination.totalPages}</span>
                </p>
                <div className="h-4 w-px bg-gray-200 dark:bg-white/10" />
                <p className="text-[12px] text-gray-500 dark:text-gray-400 font-medium">
                  Menampilkan <span className="font-bold text-gray-900 dark:text-white">{data.length}</span> dari <span className="font-bold text-gray-900 dark:text-white">{pagination.totalRecords}</span> data
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

      {/* Create/Edit Modal */}
      <EntityFormModal
        data-scan="modal form penugasan"
        open={showFormModal}
        onClose={() => { setShowFormModal(false); setEditingRow(null); }}
        onSubmit={handleFormSubmit}
        title={editingRow ? 'Edit Penugasan' : 'Tambah Penugasan'}
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
        title="Hapus Penugasan"
        message={`Yakin ingin menghapus penugasan "${deleteTarget?.petugas_nama}" pada kegiatan "${deleteTarget?.kegiatan_nama}"?`}
        variant="danger"
        isLoading={deleteMutation.isPending}
      />
    </div>
  );
}
