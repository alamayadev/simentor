import React, { useState, useMemo, useEffect } from 'react';
import { usePagination } from '../hooks/usePagination';
import { 
  createColumnHelper, 
  flexRender, 
  getCoreRowModel, 
  useReactTable,
  getPaginationRowModel
} from '@tanstack/react-table';
import { 
  Search,
  Filter,
  Activity,
  Plus,
} from 'lucide-react';
import { motion } from 'motion/react';
import { useApiQuery, useApiMutation } from '../hooks/useApi';
import { EntityFormModal } from '../components/EntityFormModal';
import { PageHeader } from '../components/PageHeader';
import { LoadingSkeleton } from '../components/LoadingSkeleton';
import { ApiErrorBoundary } from '../components/ApiErrorBoundary';
import { mitraService, penugasanService } from '../lib/api-services';
import type { PenugasanCreatePayload } from '../types/api';

type PetugasRow = {
  id: number;
  nama: string;
  nik: string | null;
  posisi: string | null;
  kecamatan: string | null;
  kontak: string | null;
  umur: number | null;
  totalTugas: number;
};

export function PetugasTable() {
  const [showFilters, setShowFilters] = useState(false);
  const [search, setSearch] = useState('');
  const [filterKecamatan, setFilterKecamatan] = useState('');
  const [filterDesa, setFilterDesa] = useState('');
  const [filterPosisi, setFilterPosisi] = useState('');
  const [showPenugasanModal, setShowPenugasanModal] = useState(false);
  const [selectedMitra, setSelectedMitra] = useState<PetugasRow | null>(null);

  const pagination = usePagination(10);

  const { data: mitraResp, isLoading: loadingMitra, error: errorMitra, refetch } = useApiQuery(
    ['mitra-petugas', search, filterKecamatan, filterDesa, filterPosisi, pagination.params],
    () => mitraService.list({
      ...pagination.params,
      search: search || undefined,
      'filter[keca]': filterKecamatan || undefined,
      'filter[desa]': filterDesa || undefined,
      'filter[posisi]': filterPosisi || undefined,
    }),
  );

  useEffect(() => {
    pagination.sync(mitraResp);
  }, [mitraResp]);

  useEffect(() => {
    pagination.reset();
  }, [search, filterKecamatan, filterDesa, filterPosisi]);

  const { data: mitraFiltersResp } = useApiQuery(
    ['mitra-filter-options', filterKecamatan],
    () => mitraService.filters({
      selected_keca: filterKecamatan || undefined,
    }),
  );

  const { data: mitraStatsResp } = useApiQuery(
    ['mitra-statistics'],
    () => mitraService.statistics(),
  );

  const { data: penugasanOptionsResp } = useApiQuery(
    ['mitra-penugasan-options'],
    () => mitraService.penugasanOptions(),
  );

  const { data: penugasanResp } = useApiQuery(
    ['penugasan-stats'],
    () => penugasanService.list({ per_page: 10 }),
  );

  const createPenugasanMutation = useApiMutation(
    (payload: Record<string, unknown>) =>
      mitraService.createPenugasan(
        payload as Pick<PenugasanCreatePayload, 'kegiatan_id' | 'jabatan_tugas' | 'mitra_id' | 'volume' | 'bln_bayar'>
      ),
    {
      invalidateKeys: [['mitra-petugas'], ['mitra-statistics'], ['penugasan-stats'], ['penugasan']],
      onSuccess: () => {
        setShowPenugasanModal(false);
        setSelectedMitra(null);
      },
    }
  );

  // Count tugas per mitra
  const tugasCount = useMemo(() => {
    const raw = penugasanResp?.data;
    if (!Array.isArray(raw)) return {} as Record<number, number>;
    const counts: Record<number, number> = {};
    raw.forEach((p: any) => {
      if (p.mitra_id) {
        counts[p.mitra_id] = (counts[p.mitra_id] || 0) + 1;
      }
    });
    return counts;
  }, [penugasanResp]);

  const data: PetugasRow[] = useMemo(() => {
    if (!mitraResp?.data) return [];
    const raw = mitraResp.data;
    if (!Array.isArray(raw)) return [];
    return raw.map((item: any) => ({
      id: item.id,
      nama: item.nama_lengkap || item.nama || '-',
      nik: item.nik || item.nip || null,
      posisi: item.posisi || item.jabatan || 'Mitra Pendataan',
      kecamatan: item.keca || item.kecamatan || '-',
      kontak: item.no_telp || item.no_hp || null,
      umur: typeof item.umur === 'number' ? item.umur : null,
      totalTugas: item.penugasan_count ?? (tugasCount[item.id] || 0),
    }));
  }, [mitraResp, tugasCount]);

  const filteredData = data;

  const kecamatanOptions = useMemo(
    () => mitraFiltersResp?.data?.kecList || [],
    [mitraFiltersResp]
  );

  const desaOptions = useMemo(
    () => mitraFiltersResp?.data?.desaList || [],
    [mitraFiltersResp]
  );

  const posisiOptions = useMemo(
    () => mitraFiltersResp?.data?.posisiList || [],
    [mitraFiltersResp]
  );

  const penugasanFormFields = useMemo(
    () => [
      {
        name: 'jabatan_tugas',
        label: 'Jabatan Tugas',
        type: 'select' as const,
        required: true,
        options: (penugasanOptionsResp?.data?.jabatanTugasOptions || []).map((option) => ({
          value: option,
          label: option,
        })),
        placeholder: 'Pilih jabatan',
      },
      {
        name: 'kegiatan_id',
        label: 'Kegiatan',
        type: 'select' as const,
        required: true,
        options: (penugasanOptionsResp?.data?.kegiatanOptions || []).map((item) => ({
          value: String(item.id),
          label: item.nama,
        })),
        searchable: true,
        wrapLabel: true,
        placeholder: 'Pilih kegiatan',
        searchPlaceholder: 'Cari kegiatan...',
        emptyLabel: 'Kegiatan tidak ditemukan.',
      },
      {
        name: 'volume',
        label: 'Volume',
        type: 'number' as const,
        required: true,
      },
      {
        name: 'bln_bayar',
        label: 'Bulan Bayar',
        type: 'date' as const,
        required: true,
        dateMode: 'month' as const,
        placeholder: 'Pilih bulan bayar',
      },
    ],
    [penugasanOptionsResp]
  );

  const stats = useMemo(() => ({
    total: mitraStatsResp?.data?.total_mitra ?? 0,
    aktif: mitraStatsResp?.data?.mitra_aktif ?? 0,
    avgTugas: mitraStatsResp?.data?.rata_tugas ?? 0,
    topTugas: mitraStatsResp?.data?.tugas_terbanyak ?? 0,
  }), [mitraStatsResp]);

  const columnHelper = createColumnHelper<PetugasRow>();

  const columns = useMemo(() => [
    columnHelper.accessor('nama', {
      header: 'Nama',
      cell: info => (
        <div className="whitespace-nowrap font-medium text-slate-900 dark:text-white">
          <div>{info.getValue()}</div>
          <div className="text-xs text-slate-500 dark:text-slate-400">{info.row.original.nik || '-'}</div>
        </div>
      ),
    }),
    columnHelper.accessor('posisi', {
      header: 'Posisi',
      cell: info => <span className="whitespace-nowrap">{info.getValue() || '-'}</span>,
    }),
    columnHelper.accessor('kecamatan', {
      header: 'Kecamatan',
      cell: info => <span className="whitespace-nowrap">{info.getValue() || '-'}</span>,
    }),
    columnHelper.accessor('kontak', {
      header: 'Kontak',
      cell: info => (
        <div className="whitespace-nowrap">
          <div>{info.getValue() || '-'}</div>
          <div className="text-xs text-slate-500 dark:text-slate-400">
            Usia {info.row.original.umur != null ? `${info.row.original.umur} tahun` : '-'}
          </div>
        </div>
      ),
    }),
    columnHelper.accessor('totalTugas', {
      header: 'Penugasan',
      cell: info => (
        <div className="flex items-center gap-2 whitespace-nowrap">
          <Activity className="h-3.5 w-3.5 text-blue-500 opacity-60" />
          <span>{info.getValue().toLocaleString('id-ID')}</span>
        </div>
      ),
    }),
    columnHelper.display({
      id: 'actions',
      header: () => <div className="text-center">Aksi</div>,
      cell: info => (
        <div className="text-center whitespace-nowrap">
          <button
            data-scan="tombol tambah penugasan"
            type="button"
            onClick={() => openPenugasanModal(info.row.original)}
            className="flex items-center justify-center gap-1.5 px-4 py-2 rounded-full bg-amber-300 hover:bg-amber-400 text-gray-900 text-sm font-semibold shadow-lg shadow-amber-500/20 transition"
          >
            <Plus className="h-4 w-4" />
            Tambah Penugasan
          </button>
        </div>
      ),
    }),
  ], []);

  const table = useReactTable({
    data: filteredData,
    columns,
    getCoreRowModel: getCoreRowModel(),
    manualPagination: true,

  });

  const isLoading = loadingMitra;
  const hasActiveFilters = Boolean(search || filterKecamatan || filterDesa || filterPosisi);

  const resetFilters = () => {
    setSearch('');
    setFilterKecamatan('');
    setFilterDesa('');
    setFilterPosisi('');
  };

  const openPenugasanModal = (mitra: PetugasRow) => {
    setSelectedMitra(mitra);
    setShowPenugasanModal(true);
  };

  const handlePenugasanSubmit = (formData: Record<string, unknown>) => {
    if (!selectedMitra) return;

    const payload: Pick<PenugasanCreatePayload, 'kegiatan_id' | 'jabatan_tugas' | 'mitra_id' | 'volume' | 'bln_bayar'> = {
      kegiatan_id: Number(formData.kegiatan_id),
      jabatan_tugas: String(formData.jabatan_tugas) as PenugasanCreatePayload['jabatan_tugas'],
      mitra_id: selectedMitra.id,
      volume: Number(formData.volume),
      bln_bayar: String(formData.bln_bayar),
    };

    createPenugasanMutation.mutate(payload as unknown as Record<string, unknown>);
  };

  return (
    <div className="space-y-6">
      <PageHeader 
        title="Daftar Petugas" 
        description="Kelola data mitra, beban kerja, dan ringkasan performa."
      />

      {/* Stats Summary */}
      <div 
        data-scan="ringkasan statistik"
        className="grid grid-cols-2 lg:grid-cols-4 gap-4"
      >
        {[
          { label: 'Total Petugas', value: stats.total, color: 'text-gray-900 dark:text-white' },
          { label: 'Petugas Aktif', value: stats.aktif, color: 'text-emerald-600 dark:text-emerald-400' },
          { label: 'Rata-rata Tugas', value: stats.avgTugas, color: 'text-amber-600 dark:text-amber-400', suffix: '/bulan' },
          { label: 'Tugas Terbanyak', value: stats.topTugas, color: 'text-purple-600 dark:text-purple-400', suffix: '/bulan' },
        ].map((s, i) => (
          <motion.div key={i} initial={{ opacity: 0, y: 12 }} animate={{ opacity: 1, y: 0 }} transition={{ delay: i * 0.08 }}
            className="bg-white/40 dark:bg-white/5 glass p-5 rounded-2xl border border-white/40 dark:border-white/10"
          >
            <p className="text-[11px] font-bold uppercase tracking-widest text-gray-400 mb-0.5">{s.label}</p>
            <p className={`text-2xl font-bold ${s.color}`}>
              {isLoading ? '...' : s.value}
              {s.suffix ? <span className="ml-1 text-xs font-medium text-gray-400 dark:text-gray-500">{s.suffix}</span> : null}
            </p>
          </motion.div>
        ))}
      </div>

      {/* Filters */}
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
              placeholder="Cari petugas..."
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
              <span className={`w-2 h-2 rounded-full ${showFilters ? 'bg-white' : 'bg-orange-500'}`} />
            )}
          </button>
        </div>

        {showFilters && (
          <div 
            data-scan="rincian filter"
            className="flex flex-wrap items-end gap-3 px-4 pb-3 pt-1 border-t border-white/30 dark:border-white/10"
          >
            <div className="flex flex-col gap-1.5">
              <label className="text-[10px] uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Kecamatan</label>
              <select
                value={filterKecamatan}
                onChange={(e) => {
                  setFilterKecamatan(e.target.value);
                  setFilterDesa('');
                }}
                className="h-9 rounded-xl border border-white/50 dark:border-white/10 bg-white/70 dark:bg-white/5 px-3 text-xs text-gray-700 dark:text-gray-200 outline-none cursor-pointer"
              >
                <option value="">Semua</option>
                {kecamatanOptions.map((item) => (
                  <option key={item} value={item}>{item}</option>
                ))}
              </select>
            </div>
            <div className="flex flex-col gap-1.5">
              <label className="text-[10px] uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Desa</label>
              <select
                value={filterDesa}
                onChange={(e) => setFilterDesa(e.target.value)}
                disabled={!filterKecamatan}
                className="h-9 rounded-xl border border-white/50 dark:border-white/10 bg-white/70 dark:bg-white/5 px-3 text-xs text-gray-700 dark:text-gray-200 outline-none cursor-pointer disabled:opacity-60"
              >
                <option value="">Semua</option>
                {desaOptions.map((item) => (
                  <option key={item} value={item}>{item}</option>
                ))}
              </select>
            </div>
            <div className="flex flex-col gap-1.5">
              <label className="text-[10px] uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Posisi</label>
              <select
                value={filterPosisi}
                onChange={(e) => setFilterPosisi(e.target.value)}
                className="h-9 rounded-xl border border-white/50 dark:border-white/10 bg-white/70 dark:bg-white/5 px-3 text-xs text-gray-700 dark:text-gray-200 outline-none cursor-pointer"
              >
                <option value="">Semua</option>
                {posisiOptions.map((item) => (
                  <option key={item} value={item}>{item}</option>
                ))}
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

      {/* Table */}
      <div 
        data-scan="tabel petugas"
        className="bg-white/65 dark:bg-white/[0.04] glass-strong rounded-[24px] border border-white/50 dark:border-white/10 shadow-[0_10px_40px_-15px_rgba(0,0,0,0.08)] overflow-hidden"
      >
        {isLoading ? (
          <LoadingSkeleton variant="table" message="Memuat data petugas..." />
        ) : errorMitra ? (
          <ApiErrorBoundary error={errorMitra} onRetry={refetch} title="Gagal Memuat Petugas" />
        ) : (
          <>
            <div className="overflow-x-auto">
              <table className="w-full caption-bottom text-sm">
                <thead>
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
                      <td colSpan={columns.length} className="p-2 align-middle text-center text-gray-400 text-sm">
                        Tidak ada data petugas.
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
                  Halaman <span className="text-gray-900 dark:text-white font-bold">{pagination.page}</span> dari <span className="text-gray-900 dark:text-white font-bold">{pagination.totalPages}</span>
                </p>
                <div className="h-4 w-px bg-gray-200 dark:bg-white/10" />
                <p className="text-[12px] text-gray-500 dark:text-gray-400 font-medium">
                  Total: <span className="font-bold text-gray-900 dark:text-white">{pagination.totalRecords}</span> petugas
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

      <EntityFormModal
        data-scan="modal form penugasan"
        open={showPenugasanModal}
        onClose={() => {
          setShowPenugasanModal(false);
          setSelectedMitra(null);
        }}
        onSubmit={handlePenugasanSubmit}
        title="Tambah Penugasan Mitra"
        description={selectedMitra ? `Tentukan jabatan tugas dan kegiatan untuk ${selectedMitra.nama}.` : 'Tentukan jabatan tugas dan kegiatan untuk mitra terpilih.'}
        fields={penugasanFormFields}
        initialData={null}
        isLoading={createPenugasanMutation.isPending}
        mode="create"
      />
    </div>
  );
}
