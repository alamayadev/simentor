import React, { useMemo, useState } from 'react';
import {
  createColumnHelper,
  flexRender,
  getCoreRowModel,
  getPaginationRowModel,
  useReactTable,
} from '@tanstack/react-table';
import {
  CalendarRange,
  CircleCheck,
  Download,
  Layers,
  TriangleAlert,
  TrendingUp,
} from 'lucide-react';
import { motion } from 'motion/react';
import { useApiQuery } from '../hooks/useApi';
import { dipaBudgetService } from '../lib/api-services';
import { PageHeader } from '../components/PageHeader';
import { LoadingSkeleton } from '../components/LoadingSkeleton';
import { ApiErrorBoundary } from '../components/ApiErrorBoundary';
import type { DipaMonitoringRow } from '../types/api';

// ── Formatters ───────────────────────────────────────────────
const fmtCurrency = (value: number) =>
  new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(value);

const fmtCompactJt = (value: number) => {
  if (value === 0) return '0 jt';
  const inMillion = value / 1_000_000;
  return `${new Intl.NumberFormat('id-ID', {
    minimumFractionDigits: inMillion < 10 ? 1 : 0,
    maximumFractionDigits: 1,
  }).format(inMillion)} jt`;
};

// ── Table columns ─────────────────────────────────────────────
const columnHelper = createColumnHelper<DipaMonitoringRow>();

const columns = [
  columnHelper.accessor('formatted_description', {
    header: 'Item Anggaran',
    cell: (info) => (
      <div className="flex max-w-[320px] flex-col gap-0.5">
        <span
          className="text-[13px] font-medium leading-tight text-gray-900 dark:text-white"
          dangerouslySetInnerHTML={{ __html: info.getValue() }}
        />
        {(info.row.original.output_name || info.row.original.component_name) && (
          <span className="text-[11px] text-gray-500">
            {info.row.original.output_name ?? info.row.original.component_name}
          </span>
        )}
      </div>
    ),
  }),
  columnHelper.accessor('total_pagu', {
    header: 'Pagu',
    cell: (info) => (
      <span className="text-[13px] font-bold text-gray-700 dark:text-gray-300">
        {fmtCurrency(info.getValue())}
      </span>
    ),
  }),
  columnHelper.accessor('total_realisasi', {
    header: 'Realisasi',
    cell: (info) => (
      <span className="text-[13px] font-bold text-emerald-600 dark:text-emerald-400">
        {fmtCurrency(info.getValue())}
      </span>
    ),
  }),
  columnHelper.display({
    id: 'sisa',
    header: 'Sisa',
    cell: (info) => {
      const sisa = info.row.original.total_pagu - info.row.original.total_realisasi;
      return (
        <span className="text-[13px] font-medium text-gray-600 dark:text-gray-400">
          {fmtCurrency(sisa)}
        </span>
      );
    },
  }),
  columnHelper.accessor('persentase_realisasi', {
    header: 'Serapan',
    cell: (info) => {
      const pct = info.getValue() ?? 0;
      return (
        <div className="w-24 space-y-1">
          <div className="flex justify-between text-[10px] font-bold text-gray-400">
            <span>{pct.toFixed(1)}%</span>
          </div>
          <div className="h-1.5 w-full overflow-hidden rounded-full bg-gray-100 dark:bg-white/5">
            <div
              className={`h-full transition-all duration-500 ${
                pct >= 50
                  ? 'bg-emerald-500'
                  : pct > 0
                  ? 'bg-amber-500'
                  : 'bg-gray-300 dark:bg-white/10'
              }`}
              style={{ width: `${Math.min(Math.max(pct, pct === 0 ? 6 : 0), 100)}%` }}
            />
          </div>
        </div>
      );
    },
  }),
];

type ViewMode = 'summary' | 'table';

const CURRENT_YEAR = new Date().getFullYear();
const YEAR_OPTIONS = [CURRENT_YEAR, CURRENT_YEAR - 1, CURRENT_YEAR - 2];

// ── Component ─────────────────────────────────────────────────
export function RkkDipaMonitoring() {
  const [activeView, setActiveView] = useState<ViewMode>('summary');
  const [year, setYear] = useState<number>(CURRENT_YEAR);

  // ── Queries ──────────────────────────────────────────────────
  const {
    data: monitoringResp,
    isLoading: monLoading,
    error: monError,
    refetch: monRefetch,
  } = useApiQuery(['dipa-monitoring', year], () => dipaBudgetService.getMonitoring(year));

  const {
    data: summaryResp,
    isLoading: sumLoading,
    error: sumError,
    refetch: sumRefetch,
  } = useApiQuery(['dipa-monitoring-summary', year], () =>
    dipaBudgetService.getMonitoringSummary(year),
  );

  // ── Derived values ───────────────────────────────────────────
  const monitoringData: DipaMonitoringRow[] = useMemo(
    () => (monitoringResp?.data as DipaMonitoringRow[]) ?? [],
    [monitoringResp],
  );

  const summary = summaryResp?.data;
  const trendData = summary?.chart ?? [];
  const chartMax = Math.max(
    ...trendData.map((item) => Math.max(item.realisasi, item.rencana)),
    1_000_000,
  );
  const yTicks = [0, 0.25, 0.5, 0.75, 1].map((t) => t * chartMax);

  const totalPagu = summary?.total_budget_ceiling ?? 0;
  const totalRealisasi = summary?.total_budget_realization ?? 0;
  const totalSisa = summary?.remaining_budget ?? 0;
  const realisasiPct = totalPagu === 0 ? 0 : (totalRealisasi / totalPagu) * 100;

  const currentMonthRealisasi = summary?.current_month_realisasi ?? 0;
  const currentMonthRencana = summary?.current_month_rencana ?? 0;
  const currentMonthLabel = summary?.current_month
    ? new Intl.DateTimeFormat('id-ID', { month: 'long', year: 'numeric' }).format(
        new Date(summary.current_month + '-01'),
      )
    : '—';

  // ── Table setup ──────────────────────────────────────────────
  const table = useReactTable({
    data: monitoringData,
    columns,
    getCoreRowModel: getCoreRowModel(),
    getPaginationRowModel: getPaginationRowModel(),
    initialState: { pagination: { pageSize: 8 } },
  });

  const summaryCards = [
    {
      label: 'Total Pagu',
      value: sumLoading ? '...' : fmtCurrency(totalPagu),
      sublabel: null,
      icon: TrendingUp,
      color: 'text-sky-500',
    },
    {
      label: 'Total Realisasi',
      value: sumLoading ? '...' : fmtCurrency(totalRealisasi),
      sublabel: `${realisasiPct.toFixed(1)}% dari Pagu`,
      icon: CircleCheck,
      color: 'text-emerald-500',
    },
    {
      label: 'Sisa Anggaran',
      value: sumLoading ? '...' : fmtCurrency(totalSisa),
      sublabel: null,
      icon: TriangleAlert,
      color: 'text-amber-500',
    },
    {
      label: 'Realisasi Bulan Ini',
      value: sumLoading ? '...' : fmtCurrency(currentMonthRealisasi),
      sublabel: currentMonthLabel,
      icon: CalendarRange,
      color: 'text-emerald-500',
    },
    {
      label: 'Rencana Bulan Ini',
      value: sumLoading ? '...' : fmtCurrency(currentMonthRencana),
      sublabel: currentMonthLabel,
      extra: 'Target Planning',
      icon: Layers,
      color: 'text-sky-500',
    },
  ];

  // ── Render ───────────────────────────────────────────────────
  return (
    <div className="space-y-6">
      <PageHeader
        title="Monitoring RKK DIPA"
        description="Pantau posisi pagu, realisasi, dan rencana bulanan RKK DIPA dalam satu tampilan."
        actions={
          <div className="flex items-center gap-2">
            {/* Year selector */}
            <select
              value={year}
              onChange={(e) => setYear(Number(e.target.value))}
              className="h-9 rounded-xl border border-white/50 bg-white/70 px-3 text-xs font-medium text-gray-700 outline-none transition focus:border-sky-500 dark:border-white/10 dark:bg-white/[0.04] dark:text-gray-300"
            >
              {YEAR_OPTIONS.map((y) => (
                <option key={y} value={y}>
                  {y}
                </option>
              ))}
            </select>
            <button data-scan="tombol ekspor" className="flex items-center gap-2 rounded-full border border-white/50 bg-white/70 px-3.5 py-2 text-sm font-medium transition hover:bg-white/90 dark:border-white/10 dark:bg-white/5 dark:hover:bg-white/10">
              <Download className="h-4 w-4" />
              Ekspor
            </button>
          </div>
        }
      />

      <div className="overflow-hidden rounded-[24px] border border-white/50 bg-white/65 shadow-[0_10px_40px_-15px_rgba(0,0,0,0.08)] dark:border-white/10 dark:bg-white/[0.04]">
        {/* Tabs */}
        <div data-scan="tab kontrol" className="flex border-b border-gray-200/70 dark:border-white/10">
          {[
            { key: 'summary', label: 'Ringkasan & Chart' },
            { key: 'table', label: 'Tabel Detail' },
          ].map((tab) => {
            const active = activeView === tab.key;
            return (
              <button
                key={tab.key}
                data-scan={`tab ${tab.label.toLowerCase()}`}
                type="button"
                onClick={() => setActiveView(tab.key as ViewMode)}
                className={`border-b-2 px-5 py-3 text-xs font-bold transition-all ${
                  active
                    ? 'border-sky-500 bg-sky-500/5 text-sky-600 dark:text-sky-400'
                    : 'border-transparent text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white'
                }`}
              >
                {tab.label}
              </button>
            );
          })}
        </div>

        <div className="space-y-6 p-4 md:p-5">
          {activeView === 'summary' ? (
            <>
              {/* Summary cards */}
              <div data-scan="ringkasan statistik" className="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-5">
                {summaryCards.map((card, index) => {
                  const Icon = card.icon;
                  return (
                    <motion.div
                      key={card.label}
                      initial={{ opacity: 0, y: 12 }}
                      animate={{ opacity: 1, y: 0 }}
                      transition={{ delay: index * 0.05 }}
                      className="rounded-[20px] border border-white/50 bg-gray-50/60 p-4 shadow-sm shadow-black/5 dark:border-white/10 dark:bg-white/[0.03]"
                    >
                      <div className="mb-2 flex items-center justify-between">
                        <span className="text-[11px] uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">
                          {card.label}
                        </span>
                        <Icon className={`h-4 w-4 ${card.color}`} />
                      </div>
                      <div className="text-lg font-black text-gray-900 dark:text-white xl:text-xl">
                        {card.value}
                      </div>
                      {card.sublabel && (
                        <div className="mt-1 flex items-center justify-between">
                          <div
                            className={`text-[10px] font-medium ${
                              card.extra
                                ? 'text-gray-500 dark:text-gray-400'
                                : 'text-emerald-600 dark:text-emerald-400'
                            }`}
                          >
                            {card.sublabel}
                          </div>
                          {card.extra && (
                            <div className="text-[10px] font-medium text-sky-600 dark:text-sky-400">
                              {card.extra}
                            </div>
                          )}
                        </div>
                      )}
                    </motion.div>
                  );
                })}
              </div>

              {/* Chart */}
              <div data-scan="chart monitoring" className="rounded-[20px] border border-white/50 bg-gray-50/60 p-4 dark:border-white/10 dark:bg-white/[0.03]">
                <div className="mb-5">
                  <div className="text-[10px] uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">
                    Perbandingan Tahunan
                  </div>
                  <h3 className="text-lg font-semibold text-gray-900 dark:text-white">
                    Realisasi vs Rencana per Bulan ({year})
                  </h3>
                </div>

                {sumLoading ? (
                  <LoadingSkeleton variant="cards" message="Memuat data chart..." />
                ) : sumError ? (
                  <ApiErrorBoundary
                    error={sumError}
                    onRetry={() => sumRefetch()}
                    title="Gagal Memuat Chart"
                  />
                ) : (
                  <div className="grid h-[360px] grid-cols-[56px_minmax(0,1fr)] gap-4">
                    {/* Y-axis labels */}
                    <div className="relative">
                      {yTicks.map((tick, index) => {
                        const bottom = `${(index / (yTicks.length - 1)) * 100}%`;
                        return (
                          <div
                            key={tick}
                            className="absolute left-0 right-0"
                            style={{ bottom }}
                          >
                            <div className="-translate-y-1/2 text-right text-[11px] text-gray-400">
                              {fmtCompactJt(tick)}
                            </div>
                          </div>
                        );
                      })}
                    </div>

                    {/* Chart area */}
                    <div className="relative">
                      {/* Grid lines */}
                      {yTicks.map((tick, index) => {
                        const bottom = `${(index / (yTicks.length - 1)) * 100}%`;
                        return (
                          <div
                            key={tick}
                            className="absolute inset-x-0 border-t border-dashed border-gray-200 dark:border-white/10"
                            style={{ bottom }}
                          >
                            <div className="sr-only">{index}</div>
                          </div>
                        );
                      })}

                      {/* Bars */}
                      <div className="relative flex h-full items-end justify-between gap-1 pb-8">
                        {trendData.map((item) => {
                          const realisasiH = `${(item.realisasi / chartMax) * 100}%`;
                          const rencanaH = `${(item.rencana / chartMax) * 100}%`;
                          return (
                            <div
                              key={item.month_number}
                              className="flex h-full flex-1 flex-col items-center justify-end gap-2"
                            >
                              <div className="flex h-full w-full items-end justify-center gap-1">
                                <div className="flex h-full w-4 items-end sm:w-5">
                                  <div
                                    title={`Realisasi ${item.month_label}: ${fmtCurrency(item.realisasi)}`}
                                    className="w-full rounded-t-md bg-emerald-500/95 transition-all"
                                    style={{
                                      height:
                                        item.realisasi === 0 ? '0%' : realisasiH,
                                      minHeight:
                                        item.realisasi > 0 ? '8px' : undefined,
                                    }}
                                  />
                                </div>
                                <div className="flex h-full w-4 items-end sm:w-5">
                                  <div
                                    title={`Rencana ${item.month_label}: ${fmtCurrency(item.rencana)}`}
                                    className="w-full rounded-t-md bg-sky-400/95 transition-all"
                                    style={{
                                      height:
                                        item.rencana === 0 ? '0%' : rencanaH,
                                      minHeight:
                                        item.rencana > 0 ? '8px' : undefined,
                                    }}
                                  />
                                </div>
                              </div>
                              <span className="text-[11px] font-medium text-gray-500 dark:text-gray-400">
                                {item.month_label}
                              </span>
                            </div>
                          );
                        })}
                      </div>

                      {/* Legend */}
                      <div className="mt-3 flex items-center justify-center gap-6 text-[12px]">
                        <div className="flex items-center gap-2 text-emerald-600 dark:text-emerald-400">
                          <span className="h-3 w-3 rounded-[3px] bg-emerald-500" />
                          Realisasi
                        </div>
                        <div className="flex items-center gap-2 text-sky-500 dark:text-sky-400">
                          <span className="h-3 w-3 rounded-[3px] bg-sky-400" />
                          Rencana
                        </div>
                      </div>
                    </div>
                  </div>
                )}
              </div>
            </>
          ) : (
            /* Tabel Detail */
            <>
              {monLoading ? (
                <LoadingSkeleton variant="table" message="Memuat data monitoring..." />
              ) : monError ? (
                <ApiErrorBoundary
                  error={monError}
                  onRetry={() => monRefetch()}
                  title="Gagal Memuat Data Monitoring"
                />
              ) : (
                <div data-scan="tabel monitoring" className="overflow-hidden rounded-[20px] border border-white/50 bg-white/40 dark:border-white/10 dark:bg-white/[0.02]">
                  <div className="overflow-x-auto">
                    <table className="w-full text-[13px]">
                      <thead>
                        <tr className="border-b border-gray-200/60 text-left text-[11.5px] text-gray-500 dark:border-white/10 dark:text-gray-400">
                          {table.getHeaderGroups().map((headerGroup) => (
                            <React.Fragment key={headerGroup.id}>
                              {headerGroup.headers.map((header) => (
                                <th
                                  key={header.id}
                                  className="whitespace-nowrap bg-gray-50/30 px-6 py-5 font-bold uppercase tracking-widest dark:bg-white/5"
                                >
                                  {flexRender(
                                    header.column.columnDef.header,
                                    header.getContext(),
                                  )}
                                </th>
                              ))}
                            </React.Fragment>
                          ))}
                        </tr>
                      </thead>
                      <tbody className="divide-y divide-gray-100/70 dark:divide-white/5">
                        {table.getRowModel().rows.length === 0 ? (
                          <tr>
                            <td
                              colSpan={columns.length}
                              className="px-6 py-12 text-center text-sm text-gray-400"
                            >
                              Belum ada data monitoring DIPA.
                            </td>
                          </tr>
                        ) : (
                          table.getRowModel().rows.map((row) => (
                            <tr
                              key={row.id}
                              className="transition-colors hover:bg-white/40 dark:hover:bg-white/[0.02]"
                            >
                              {row.getVisibleCells().map((cell) => (
                                <td key={cell.id} className="px-6 py-4">
                                  {flexRender(
                                    cell.column.columnDef.cell,
                                    cell.getContext(),
                                  )}
                                </td>
                              ))}
                            </tr>
                          ))
                        )}
                      </tbody>
                    </table>
                  </div>

                  {/* Pagination */}
                  <div data-scan="navigasi halaman" className="flex items-center justify-between border-t border-gray-200/60 px-6 py-5 dark:border-white/10 dark:bg-white/5">
                    <p className="text-[12px] font-medium text-gray-500 dark:text-gray-400">
                      Halaman{' '}
                      <span className="font-bold text-gray-900 dark:text-white">
                        {table.getState().pagination.pageIndex + 1}
                      </span>{' '}
                      dari{' '}
                      <span className="font-bold text-gray-900 dark:text-white">
                        {monitoringResp?.pagination_info?.total_page || monitoringResp?.meta?.last_page || table.getPageCount() || 1}
                      </span>
                    </p>
                    <div className="h-px w-4 bg-gray-200 dark:bg-white/10" />
                    <p className="text-[12px] font-medium text-gray-500 dark:text-gray-400">
                      Total: {monitoringResp?.pagination_info?.total_records || monitoringResp?.meta?.total || monitoringData.length} data
                    </p>
                    <div className="flex gap-2">
                      <button
                        onClick={() => table.previousPage()}
                        disabled={!table.getCanPreviousPage()}
                        className="rounded-full border border-white/50 bg-white/70 px-4 py-2 text-[11px] font-bold text-gray-700 shadow-sm transition-all hover:bg-white disabled:cursor-not-allowed disabled:opacity-30 dark:border-white/20 dark:bg-white/10 dark:text-gray-300"
                      >
                        Sebelumnya
                      </button>
                      <button
                        onClick={() => table.nextPage()}
                        disabled={!table.getCanNextPage()}
                        className="rounded-full border border-white/50 bg-white/70 px-4 py-2 text-[11px] font-bold text-gray-700 shadow-sm transition-all hover:bg-white disabled:cursor-not-allowed disabled:opacity-30 dark:border-white/20 dark:bg-white/10 dark:text-gray-300"
                      >
                        Berikutnya
                      </button>
                    </div>
                  </div>
                </div>
              )}
            </>
          )}
        </div>
      </div>
    </div>
  );
}
