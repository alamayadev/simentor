import React, { useMemo } from 'react';
import {
  createColumnHelper,
  flexRender,
  getCoreRowModel,
  getPaginationRowModel,
  useReactTable,
} from '@tanstack/react-table';
import {
  TrendingUp,
  Download,
  ChevronLeft,
  ChevronRight,
} from 'lucide-react';
import { motion } from 'motion/react';
import { useApiQuery } from '../hooks/useApi';
import { PageHeader } from '../components/PageHeader';
import { LoadingSkeleton } from '../components/LoadingSkeleton';
import { ApiErrorBoundary } from '../components/ApiErrorBoundary';
import { kegiatanService } from '../lib/api-services';

type AnggaranRow = {
  tahun: number;
  fungsi: string;
  total_anggaran: number;
  total_penyerapan: number;
  persen: number;
};

type MitraRow = {
  nama_lengkap: string;
  keca: string;
  tahun: number;
  total_nilai: number;
};

type PenyerapanRow = {
  kegiatan: string;
  fungsi: string;
  tahun: string;
  total_nilai: number;
};

const fmt = (n: number) => new Intl.NumberFormat('id-ID').format(n);
const fmtCurrency = (n: number) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(n);

const anggaranCol = createColumnHelper<AnggaranRow>();
const anggaranColumns = [
  anggaranCol.accessor('tahun', { header: 'Tahun', cell: i => <span className="text-[13px]">{i.getValue()}</span> }),
  anggaranCol.accessor('fungsi', { header: 'Fungsi', cell: i => <span className="text-[13px] font-medium text-gray-900 dark:text-white">{i.getValue()}</span> }),
  anggaranCol.accessor('total_anggaran', { header: 'Total Anggaran', cell: i => <span className="text-[13px] font-bold text-gray-700 dark:text-gray-300">{fmtCurrency(i.getValue())}</span> }),
  anggaranCol.accessor('total_penyerapan', { header: 'Total Penyerapan', cell: i => <span className="text-[13px] font-bold text-blue-600 dark:text-blue-400">{fmtCurrency(i.getValue())}</span> }),
  anggaranCol.accessor('persen', {
    header: 'Persentase',
    cell: i => {
      const pct = i.getValue();
      return (
        <div className="flex items-center gap-2">
          <div className="w-16 h-1.5 bg-gray-200/80 dark:bg-white/10 rounded-full overflow-hidden">
            <div
              className={`h-full rounded-full ${pct >= 30 ? 'bg-emerald-500' : pct >= 10 ? 'bg-amber-500' : 'bg-red-400'}`}
              style={{ width: `${Math.min(pct, 100)}%` }}
            />
          </div>
          <span className="text-[13px] font-bold">{pct.toFixed(2)}%</span>
        </div>
      );
    },
  }),
];

const mitraCol = createColumnHelper<MitraRow>();
const mitraColumns = [
  mitraCol.accessor('nama_lengkap', { header: 'Nama', cell: i => <span className="text-[13px] font-medium text-gray-900 dark:text-white">{i.getValue()}</span> }),
  mitraCol.accessor('keca', { header: 'Kecamatan', cell: i => <span className="text-[13px] text-gray-600 dark:text-gray-400">{i.getValue()}</span> }),
  mitraCol.accessor('tahun', { header: 'Tahun', cell: i => <span className="text-[13px]">{i.getValue()}</span> }),
  mitraCol.accessor('total_nilai', { header: 'Total', cell: i => <span className="text-[13px] font-bold text-gray-700 dark:text-gray-300">{fmtCurrency(i.getValue())}</span> }),
];

const penyerapanCol = createColumnHelper<PenyerapanRow>();
const penyerapanColumns = [
  penyerapanCol.accessor('kegiatan', { header: 'Kegiatan', cell: i => <span className="text-[13px] font-medium text-gray-900 dark:text-white">{i.getValue()}</span> }),
  penyerapanCol.accessor('fungsi', { header: 'Fungsi', cell: i => <span className="text-[13px] text-gray-600 dark:text-gray-400">{i.getValue()}</span> }),
  penyerapanCol.accessor('tahun', { header: 'Tahun', cell: i => <span className="text-[13px]">{i.getValue()}</span> }),
  penyerapanCol.accessor('total_nilai', { header: 'Total Nilai', cell: i => <span className="text-[13px] font-bold text-gray-700 dark:text-gray-300">{fmtCurrency(i.getValue())}</span> }),
];

function TableCard<T>({ title, icon, data, columns, pageSize, dataScan }: {
  title: string;
  icon?: React.ReactNode;
  data: T[];
  columns: any[];
  pageSize?: number;
  dataScan?: string;
}) {
  const table = useReactTable({
    data,
    columns,
    getCoreRowModel: getCoreRowModel(),
    getPaginationRowModel: getPaginationRowModel(),
    initialState: { pagination: { pageSize: pageSize || 10 } },
  });

  return (
    <div 
      data-scan={dataScan}
      className="bg-white/65 dark:bg-white/[0.04] glass-strong rounded-[24px] border border-white/50 dark:border-white/10 shadow-[0_10px_40px_-15px_rgba(0,0,0,0.08)] overflow-hidden"
    >
      <div className="flex items-center justify-between px-6 py-5 border-b border-gray-200/60 dark:border-white/10">
        <h3 className="font-semibold text-[15px] text-gray-900 dark:text-white">{title}</h3>
        {icon}
      </div>
      <div className="overflow-x-auto">
        <table className="w-full text-[13px]">
          <thead>
            <tr className="text-left text-[11.5px] text-gray-500 dark:text-gray-400 border-b border-gray-200/60 dark:border-white/10">
              {table.getHeaderGroups().map(hg => (
                <React.Fragment key={hg.id}>
                  {hg.headers.map(h => (
                    <th key={h.id} className="px-4 py-3 font-medium whitespace-nowrap bg-gray-50/30 dark:bg-white/5">
                      {flexRender(h.column.columnDef.header, h.getContext())}
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
                  <td key={cell.id} className="px-4 py-3 align-middle whitespace-nowrap">
                    {flexRender(cell.column.columnDef.cell, cell.getContext())}
                  </td>
                ))}
              </tr>
            ))}
          </tbody>
        </table>
      </div>
      <div className="flex flex-wrap items-center justify-between gap-3 px-6 py-4 border-t border-gray-200/60 dark:border-white/10 text-[12px] text-gray-500 dark:text-gray-400">
        <span>Menampilkan {table.getRowModel().rows.length > 0 ? table.getState().pagination.pageIndex * (pageSize || 10) + 1 : 0} - {Math.min((table.getState().pagination.pageIndex + 1) * (pageSize || 10), data.length)} dari {data.length} data</span>
        <div className="flex items-center gap-2">
          <button 
            data-scan="tombol halaman sebelumnya"
            onClick={() => table.previousPage()} 
            disabled={!table.getCanPreviousPage()}
            className="p-2 rounded-lg border border-white/50 dark:border-white/10 bg-white/70 dark:bg-white/5 hover:bg-white transition disabled:opacity-30 disabled:cursor-not-allowed">
            <ChevronLeft className="h-4 w-4" />
          </button>
          <span className="text-[11px]">Halaman {table.getState().pagination.pageIndex + 1} dari {table.getPageCount() || 1}</span>
          <button 
            data-scan="tombol halaman berikutnya"
            onClick={() => table.nextPage()} 
            disabled={!table.getCanNextPage()}
            className="p-2 rounded-lg border border-white/50 dark:border-white/10 bg-white/70 dark:bg-white/5 hover:bg-white transition disabled:opacity-30 disabled:cursor-not-allowed">
            <ChevronRight className="h-4 w-4" />
          </button>
        </div>
      </div>
    </div>
  );
}

export function KegiatanEvaluasi() {
  const { data: statsResp, isLoading, error, refetch } = useApiQuery(
    ['kegiatan-statistics'],
    () => kegiatanService.statistics(),
  );

  const anggaranData: AnggaranRow[] = useMemo(() => {
    const raw = (statsResp?.data as any)?.kegiatan_by_fungsi;
    if (!raw) return [];
    return raw.map((r: any) => ({
      tahun: r.tahun,
      fungsi: r.fungsi,
      total_anggaran: r.total_anggaran,
      total_penyerapan: r.total_penyerapan,
      persen: r.persen,
    }));
  }, [statsResp]);

  const penugasanData: { bulan: string; nilai: number }[] = useMemo(() => {
    const raw = (statsResp?.data as any)?.nilai_penugasan_by_month;
    if (!raw) return [];
    return raw.map((r: any) => ({ bulan: r.month as string, nilai: r.total_nilai as number }));
  }, [statsResp]);

  const mitraData: MitraRow[] = useMemo(() => {
    const raw = (statsResp?.data as any)?.top_mitra_honor;
    if (!raw) return [];
    return raw;
  }, [statsResp]);

  const penyerapanData: PenyerapanRow[] = useMemo(() => {
    const raw = (statsResp?.data as any)?.kegiatan_penyerapan_100;
    if (!raw) return [];
    return raw.map((r: any) => ({
      kegiatan: r.nama,
      fungsi: r.fungsi,
      tahun: r.tahun,
      total_nilai: r.total_nilai,
    }));
  }, [statsResp]);

  const maxPenugasan = Math.max(0, ...penugasanData.map(d => d.nilai));

  if (isLoading) return <div className="space-y-6"><PageHeader title="Evaluasi Kegiatan" description="Ringkasan anggaran, penyerapan, dan performa kegiatan." /><LoadingSkeleton variant="table" message="Memuat statistik..." /></div>;
  if (error) return <ApiErrorBoundary error={error} onRetry={refetch} title="Gagal Memuat Statistik" />;

  return (
    <div className="space-y-6">
      <PageHeader
        title="Evaluasi Kegiatan"
        description="Ringkasan anggaran, penyerapan, dan performa kegiatan."
        actions={
          <button 
            data-scan="tombol ekspor laporan"
            className="flex items-center gap-2 px-3.5 py-2 rounded-full bg-white/70 dark:bg-white/5 glass border border-white/50 dark:border-white/10 text-sm font-medium hover:bg-white/90 dark:hover:bg-white/10 transition"
          >
            <Download className="h-4 w-4" />
            Ekspor Laporan
          </button>
        }
      />

      {/* Anggaran vs Penyerapan */}
      <motion.div initial={{ opacity: 0, y: 12 }} animate={{ opacity: 1, y: 0 }} transition={{ delay: 0.05 }}>
        <TableCard
          title="Anggaran vs Penyerapan"
          icon={<TrendingUp className="h-4 w-4 text-gray-400" />}
          data={anggaranData}
          columns={anggaranColumns}
          pageSize={10}
          dataScan="tabel anggaran vs penyerapan"
        />
      </motion.div>

      {/* Two Column Grid */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {/* Nilai Penugasan per Bulan */}
        <motion.div 
          initial={{ opacity: 0, y: 12 }} 
          animate={{ opacity: 1, y: 0 }} 
          transition={{ delay: 0.1 }}
          data-scan="grafik penugasan bulanan"
          className="bg-white/65 dark:bg-white/[0.04] glass-strong rounded-[24px] border border-white/50 dark:border-white/10 shadow-[0_10px_40px_-15px_rgba(0,0,0,0.08)] overflow-hidden"
        >
          <div className="px-6 py-5 border-b border-gray-200/60 dark:border-white/10">
            <h3 className="font-semibold text-[15px] text-gray-900 dark:text-white">Nilai Penugasan per Bulan</h3>
          </div>
          <div className="px-6 py-5 space-y-4">
            {penugasanData.length === 0 ? (
              <p className="text-[13px] text-gray-400 text-center py-8">Belum ada data penugasan.</p>
            ) : (
              penugasanData.map((item) => {
                const pct = maxPenugasan > 0 ? (item.nilai / maxPenugasan) * 100 : 0;
                return (
                  <div key={item.bulan} className="space-y-1.5">
                    <div className="flex items-center justify-between text-[12px] text-gray-600 dark:text-gray-300">
                      <span>{item.bulan}</span>
                      <span className="font-bold text-gray-900 dark:text-white">{fmt(item.nilai)}</span>
                    </div>
                    <div className="h-2 w-full overflow-hidden rounded-full bg-gray-200/80 dark:bg-white/10">
                      <div
                        className="h-full rounded-full bg-gray-900/80 dark:bg-gray-200 transition-all duration-500"
                        style={{ width: `${pct}%` }}
                      />
                    </div>
                  </div>
                );
              })
            )}
          </div>
        </motion.div>

        {/* Top Mitra Honor */}
        <motion.div initial={{ opacity: 0, y: 12 }} animate={{ opacity: 1, y: 0 }} transition={{ delay: 0.15 }}>
          <TableCard
            title="Top Mitra Honor"
            data={mitraData}
            columns={mitraColumns}
            pageSize={10}
            dataScan="tabel top mitra honor"
          />
        </motion.div>
      </div>

      {/* Kegiatan Penyerapan */}
      <motion.div initial={{ opacity: 0, y: 12 }} animate={{ opacity: 1, y: 0 }} transition={{ delay: 0.2 }}>
        <TableCard
          title="Penyerapan per Kegiatan"
          data={penyerapanData}
          columns={penyerapanColumns}
          pageSize={penyerapanData.length || 10}
          dataScan="tabel penyerapan per kegiatan"
        />
      </motion.div>
    </div>
  );
}
