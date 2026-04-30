import React, { useMemo, useState, useCallback, useEffect } from 'react';
import {
  AlertCircle,
  Calendar,
  Check,
  Download,
  Pencil,
  Trash2,
  X,
  Loader2,
} from 'lucide-react';
import { motion, AnimatePresence } from 'motion/react';
import { useApiQuery, useApiMutation } from '../hooks/useApi';
import { dipaBudgetService } from '../lib/api-services';
import { PageHeader } from '../components/PageHeader';
import { LoadingSkeleton } from '../components/LoadingSkeleton';
import { ApiErrorBoundary } from '../components/ApiErrorBoundary';
import { useAuth } from '../hooks/useAuth';
import { hasRequiredRole } from '../lib/authz';
import type { DipaUsageHistory } from '../types/api';

// ── Formatters ───────────────────────────────────────────────
const fmtCurrency = (n: number) =>
  new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
  }).format(n);

const fmtDate = (datestr: string) => {
  if (!datestr) return '—';
  const d = new Date(`${datestr}T00:00:00`);
  return isNaN(d.getTime())
    ? datestr
    : new Intl.DateTimeFormat('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
      }).format(d);
};

// ── Component ─────────────────────────────────────────────────
export function RkkDipaRiwayat() {
  const { user } = useAuth();
  const canManageHistory = useMemo(
    () => hasRequiredRole(user, 'katim'),
    [user],
  );

  // ── Tab state ───────────────────────────────────────────────
  const [activeTab, setActiveTab] = useState<'history' | 'reconciliation'>('history');
  const [editingId, setEditingId] = useState<number | null>(null);
  const [editValue, setEditValue] = useState('');
  const [deleteId, setDeleteId] = useState<number | null>(null);
  const [filterStart, setFilterStart] = useState('');
  const [filterEnd, setFilterEnd] = useState('');
  const [filterProgram, setFilterProgram] = useState('');
  const [filterOutput, setFilterOutput] = useState('');
  const [page, setPage] = useState(1);
  const perPage = 20;

  // ── Query: History ───────────────────────────────────────────
  const {
    data: historyResp,
    isLoading: historyLoading,
    error: historyError,
    refetch: historyRefetch,
  } = useApiQuery(
    ['dipa-history', page, perPage, filterStart, filterEnd, filterProgram, filterOutput],
    () =>
      dipaBudgetService.getHistory({
        page,
        per_page: perPage,
        start_date: filterStart || undefined,
        end_date: filterEnd || undefined,
        program_name: filterProgram || undefined,
        output_name: filterProgram && filterOutput ? filterOutput : undefined,
      }),
    {
      enabled: activeTab === 'history',
    },
  );

  // ── Query: Reconciliation ────────────────────────────────────
  const {
    data: reconResp,
    isLoading: reconLoading,
    error: reconError,
    refetch: reconRefetch,
  } = useApiQuery(['sakti-reconciliation'], () => dipaBudgetService.getSaktiReconciliation(), {
    enabled: activeTab === 'reconciliation',
  });

  // ── Mutations ────────────────────────────────────────────────
  const updateMutation = useApiMutation(
    (vars: { id: number; amount_spent: number }) =>
      dipaBudgetService.updateUsageAmount(vars.id, vars.amount_spent),
    {
      invalidateKeys: [['dipa-history'], ['dipa-monitoring'], ['sakti-reconciliation']],
      onSuccess: () => setEditingId(null),
    },
  );

  const deleteMutation = useApiMutation(
    (id: number) => dipaBudgetService.deleteUsage(id),
    {
      invalidateKeys: [['dipa-history'], ['dipa-monitoring'], ['sakti-reconciliation']],
      onSuccess: () => setDeleteId(null),
    },
  );

  // ── Derived data ─────────────────────────────────────────────
  const pageHistory: DipaUsageHistory[] = useMemo(
    () => (historyResp?.data as DipaUsageHistory[]) ?? [],
    [historyResp],
  );

  const reconData = useMemo(
    () => (reconResp?.data as any[]) ?? [],
    [reconResp],
  );

  const programOptions = useMemo(
    () => (((historyResp as any)?.filter_options?.programs as string[] | undefined) ?? []),
    [historyResp],
  );

  const outputOptions = useMemo(
    () => (((historyResp as any)?.filter_options?.outputs as string[] | undefined) ?? []),
    [historyResp],
  );

  const totalFiltered = (((historyResp as any)?.summary?.total_amount_filtered as number | undefined) ?? 0);
  const totalAll = (((historyResp as any)?.summary?.total_amount_all as number | undefined) ?? 0);
  const totalFilteredRecords = (((historyResp as any)?.summary?.total_records_filtered as number | undefined) ?? 0);
  const totalAllRecords = (((historyResp as any)?.summary?.total_records_all as number | undefined) ?? 0);
  const currentPage = historyResp?.meta?.current_page ?? page;
  const lastPage = historyResp?.meta?.last_page ?? 1;
  const fromRecord = historyResp?.meta?.from ?? (pageHistory.length > 0 ? 1 : 0);
  const toRecord = historyResp?.meta?.to ?? pageHistory.length;

  const isFiltered =
    filterStart !== '' ||
    filterEnd !== '' ||
    filterProgram !== '' ||
    filterOutput !== '';

  useEffect(() => {
    setPage(1);
  }, [filterStart, filterEnd, filterProgram, filterOutput]);

  useEffect(() => {
    if (currentPage > lastPage) {
      setPage(lastPage || 1);
    }
  }, [currentPage, lastPage]);

  // ── Handlers ─────────────────────────────────────────────────
  const startEdit = useCallback((item: DipaUsageHistory) => {
    if (!canManageHistory) return;
    setEditingId(item.id);
    setEditValue(String(item.amount_spent));
  }, [canManageHistory]);

  const cancelEdit = useCallback(() => {
    setEditingId(null);
    setEditValue('');
  }, []);

  const confirmEdit = useCallback(
    (id: number) => {
      if (!canManageHistory) return;
      const parsed = Number(editValue);
      if (!Number.isFinite(parsed) || parsed <= 0) return;
      updateMutation.mutate({ id, amount_spent: parsed });
    },
    [canManageHistory, editValue, updateMutation],
  );

  const resetFilters = useCallback(() => {
    setFilterStart('');
    setFilterEnd('');
    setFilterProgram('');
    setFilterOutput('');
  }, []);

  // ── Render ───────────────────────────────────────────────────
  return (
    <div className="space-y-6">
      <PageHeader
        title="Pencairan & Rekonsiliasi"
        description="Pantau riwayat pencairan internal dan bandingkan dengan data resmi SAKTI."
      />


      {/* Tabs */}
      <div className="flex gap-2 border-b border-gray-200/60 dark:border-white/10">
        {[
          { id: 'history', label: 'Riwayat Internal', icon: Calendar },
          { id: 'reconciliation', label: 'Rekonsiliasi SAKTI', icon: Check },
        ].map((tab) => (
          <button
            key={tab.id}
            data-scan={`tab ${tab.label.toLowerCase()}`}
            onClick={() => setActiveTab(tab.id as any)}
            className={`flex items-center gap-2 px-4 py-3 text-sm font-medium transition-all ${
              activeTab === tab.id
                ? 'border-b-2 border-sky-500 text-sky-600 dark:text-sky-400'
                : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200'
            }`}
          >
            <tab.icon className="h-4 w-4" />
            {tab.label}
          </button>
        ))}
      </div>

      {activeTab === 'history' ? (
        <div className="space-y-6">
          {/* Summary cards */}
          <div className="grid grid-cols-2 gap-4 lg:grid-cols-4">
            {[
              {
                label: 'Total Transaksi',
                value: historyLoading ? '...' : String(totalAllRecords),
                color: 'text-orange-600 dark:text-orange-400',
              },
              {
                label: 'Total Nilai (Semua)',
                value: historyLoading ? '...' : fmtCurrency(totalAll),
                color: 'text-emerald-600 dark:text-emerald-400',
              },
              {
                label: isFiltered ? 'Transaksi (Filter)' : 'Transaksi Ditampilkan',
                value: historyLoading ? '...' : String(totalFilteredRecords),
                color: 'text-sky-600 dark:text-sky-400',
              },
              {
                label: isFiltered ? 'Nilai (Filter)' : 'Nilai Ditampilkan',
                value: historyLoading ? '...' : fmtCurrency(totalFiltered),
                color: 'text-blue-600 dark:text-blue-400',
              },
            ].map((s, i) => (
              <motion.div
                key={s.label}
                initial={{ opacity: 0, y: 12 }}
                animate={{ opacity: 1, y: 0 }}
                transition={{ delay: i * 0.06 }}
                className="space-y-2 rounded-[20px] border border-white/50 bg-white/65 p-5 shadow-[0_10px_40px_-15px_rgba(0,0,0,0.08)] dark:border-white/10 dark:bg-white/[0.04]"
              >
                <p className="text-[11px] font-bold uppercase tracking-widest text-gray-500 dark:text-gray-400">
                  {s.label}
                </p>
                <p className={`text-lg font-black ${s.color}`}>{s.value}</p>
              </motion.div>
            ))}
          </div>

          {/* Filters */}
          <div className="flex flex-wrap items-end gap-3 rounded-[20px] border border-white/50 bg-white/65 px-5 py-4 shadow-[0_10px_40px_-15px_rgba(0,0,0,0.08)] dark:border-white/10 dark:bg-white/[0.04]">
            <div className="flex flex-col gap-1.5">
              <label className="text-[10px] uppercase tracking-widest text-gray-500 dark:text-gray-400">
                Program
              </label>
              <select
                data-scan="filter program"
                value={filterProgram}
                onChange={(e) => {
                  const nextProgram = e.target.value;
                  setFilterProgram(nextProgram);
                  setFilterOutput('');
                }}
                className="h-9 rounded-xl border border-white/50 bg-white/70 px-3 text-xs text-gray-900 outline-none transition focus:border-sky-500 dark:border-white/10 dark:bg-white/[0.04] dark:text-white"
              >
                <option value="">Semua Program</option>
                {programOptions.map((program) => (
                  <option key={program} value={program}>
                    {program}
                  </option>
                ))}
              </select>
            </div>
            <div className="flex flex-col gap-1.5">
              <label className="text-[10px] uppercase tracking-widest text-gray-500 dark:text-gray-400">
                Output
              </label>
              <select
                data-scan="filter output"
                value={filterOutput}
                onChange={(e) => setFilterOutput(e.target.value)}
                disabled={!filterProgram}
                className="h-9 rounded-xl border border-white/50 bg-white/70 px-3 text-xs text-gray-900 outline-none transition focus:border-sky-500 disabled:cursor-not-allowed disabled:opacity-50 dark:border-white/10 dark:bg-white/[0.04] dark:text-white"
              >
                <option value="">{filterProgram ? 'Semua Output' : 'Pilih Program Dulu'}</option>
                {outputOptions.map((output) => (
                  <option key={output} value={output}>
                    {output}
                  </option>
                ))}
              </select>
            </div>
            <div className="flex flex-col gap-1.5">
              <label className="flex items-center gap-1 text-[10px] uppercase tracking-widest text-gray-500 dark:text-gray-400">
                <Calendar className="h-3 w-3" />
                Dari Tanggal
              </label>
              <input
                data-scan="filter tanggal mulai"
                type="date"
                value={filterStart}
                onChange={(e) => setFilterStart(e.target.value)}
                className="h-9 rounded-xl border border-white/50 bg-white/70 px-3 text-xs text-gray-900 outline-none transition focus:border-sky-500 dark:border-white/10 dark:bg-white/[0.04] dark:text-white"
              />
            </div>
            <div className="flex flex-col gap-1.5">
              <label className="flex items-center gap-1 text-[10px] uppercase tracking-widest text-gray-500 dark:text-gray-400">
                <Calendar className="h-3 w-3" />
                Sampai Tanggal
              </label>
              <input
                data-scan="filter tanggal selesai"
                type="date"
                value={filterEnd}
                onChange={(e) => setFilterEnd(e.target.value)}
                className="h-9 rounded-xl border border-white/50 bg-white/70 px-3 text-xs text-gray-900 outline-none transition focus:border-sky-500 dark:border-white/10 dark:bg-white/[0.04] dark:text-white"
              />
            </div>
            {isFiltered && (
              <button
                data-scan="tombol reset filter"
                onClick={resetFilters}
                className="flex h-9 items-center gap-1.5 rounded-xl border border-white/50 bg-white/70 px-4 text-xs font-medium text-gray-600 transition hover:bg-white dark:border-white/10 dark:bg-white/[0.04] dark:text-gray-300 dark:hover:bg-white/[0.08]"
              >
                <X className="h-3.5 w-3.5" />
                Reset Filter
              </button>
            )}
          </div>

          {/* Table */}
          <div className="overflow-hidden rounded-[24px] border border-white/50 bg-white/65 shadow-[0_10px_40px_-15px_rgba(0,0,0,0.08)] dark:border-white/10 dark:bg-white/[0.04]">
            {historyLoading ? (
              <LoadingSkeleton variant="table" message="Memuat riwayat pencairan..." />
            ) : historyError ? (
              <ApiErrorBoundary
                error={historyError}
                onRetry={() => historyRefetch()}
                title="Gagal Memuat Riwayat"
              />
            ) : (
              <>
                <div className="overflow-x-auto">
                  <table className="w-full text-[13px]">
                    <thead>
                      <tr className="border-b border-gray-200/60 text-left text-[11.5px] text-gray-500 dark:border-white/10 dark:text-gray-400">
                        {['Tanggal', 'Budget Key', 'Deskripsi', 'Jumlah Pencairan', ''].map(
                          (h) => (
                            <th
                              key={h}
                              className={`whitespace-nowrap bg-gray-50/30 px-6 py-5 font-bold uppercase tracking-widest dark:bg-white/5 ${
                                h === 'Jumlah Pencairan' ? 'text-right' : ''
                              }`}
                            >
                              {h}
                            </th>
                          ),
                        )}
                      </tr>
                    </thead>
                    <tbody className="divide-y divide-gray-100/70 dark:divide-white/5">
                      {pageHistory.length === 0 ? (
                        <tr>
                          <td
                            colSpan={5}
                            className="px-6 py-12 text-center text-sm text-gray-400"
                          >
                            {isFiltered
                              ? 'Tidak ada transaksi pada rentang tanggal yang dipilih.'
                              : 'Belum ada riwayat pencairan.'}
                          </td>
                        </tr>
                      ) : (
                        pageHistory.map((item, index) => (
                          <motion.tr
                            key={item.id}
                            initial={{ opacity: 0, y: 6 }}
                            animate={{ opacity: 1, y: 0 }}
                            transition={{ delay: index * 0.02 }}
                            className="transition-colors hover:bg-white/40 dark:hover:bg-white/[0.02]"
                          >
                            <td className="whitespace-nowrap px-6 py-4">
                              <div className="flex items-start gap-1.5 text-gray-600 dark:text-gray-400">
                                <Calendar className="mt-0.5 h-3.5 w-3.5 opacity-50" />
                                <div className="flex flex-col">
                                  <span>{fmtDate(item.usage_date)}</span>
                                  <span className="text-[10px] font-semibold uppercase tracking-wide text-sky-600 dark:text-sky-400">
                                    {item.data_source}
                                  </span>
                                </div>
                              </div>
                            </td>
                            <td className="px-6 py-4">
                              <span className="font-mono text-[11px] text-gray-500 dark:text-gray-400">
                                {item.budget_item_key ?? '—'}
                              </span>
                            </td>
                            <td className="max-w-[260px] px-6 py-4">
                              <span
                                className="block truncate font-medium text-gray-900 dark:text-white"
                                dangerouslySetInnerHTML={{ __html: item.usage_description ?? '—' }}
                              />
                            </td>
                            <td className="px-6 py-4 text-right">
                              <span className="font-bold text-gray-700 dark:text-gray-300">
                                {fmtCurrency(item.amount_spent)}
                              </span>
                            </td>
                            <td className="px-6 py-4 text-right">
                              {canManageHistory ? (
                                <div className="flex items-center justify-end gap-1">
                                  <button
                                    onClick={() => startEdit(item)}
                                    className="rounded-lg p-1.5 text-gray-400 transition hover:bg-blue-50 hover:text-blue-600 dark:hover:bg-blue-500/10"
                                  >
                                    <Pencil className="h-3.5 w-3.5" />
                                  </button>
                                  <button
                                    onClick={() => setDeleteId(item.id)}
                                    className="rounded-lg p-1.5 text-gray-400 transition hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-500/10"
                                  >
                                    <Trash2 className="h-3.5 w-3.5" />
                                  </button>
                                </div>
                              ) : (
                                <span className="text-[11px] font-medium text-gray-400">Read only</span>
                              )}
                            </td>
                          </motion.tr>
                        ))
                      )}
                    </tbody>
                  </table>
                </div>
                <div className="flex items-center justify-between border-t border-gray-200/60 px-6 py-5 dark:border-white/10 dark:bg-white/5">
                  <p className="text-[12px] font-medium text-gray-500 dark:text-gray-400">
                    Menampilkan{' '}
                    <span className="font-bold text-gray-900 dark:text-white">{fromRecord}</span>
                    {' - '}
                    <span className="font-bold text-gray-900 dark:text-white">{toRecord}</span>
                    {' '}dari{' '}
                    <span className="font-bold text-gray-900 dark:text-white">{totalFilteredRecords}</span>
                    {' '}data
                  </p>
                  <div className="flex items-center gap-2">
                    <button
                      data-scan="tombol halaman sebelumnya"
                      onClick={() => setPage((prev) => Math.max(1, prev - 1))}
                      disabled={currentPage <= 1}
                      className="rounded-xl border border-white/50 bg-white/70 px-3 py-2 text-xs font-medium text-gray-600 transition hover:bg-white disabled:cursor-not-allowed disabled:opacity-50 dark:border-white/10 dark:bg-white/[0.04] dark:text-gray-300 dark:hover:bg-white/[0.08]"
                    >
                      Sebelumnya
                    </button>
                    <span className="text-[12px] font-medium text-gray-500 dark:text-gray-400">
                      Halaman{' '}
                      <span className="font-bold text-gray-900 dark:text-white">{currentPage}</span>
                      {' '}dari{' '}
                      <span className="font-bold text-gray-900 dark:text-white">{lastPage}</span>
                    </span>
                    <button
                      data-scan="tombol halaman berikutnya"
                      onClick={() => setPage((prev) => Math.min(lastPage, prev + 1))}
                      disabled={currentPage >= lastPage}
                      className="rounded-xl border border-white/50 bg-white/70 px-3 py-2 text-xs font-medium text-gray-600 transition hover:bg-white disabled:cursor-not-allowed disabled:opacity-50 dark:border-white/10 dark:bg-white/[0.04] dark:text-gray-300 dark:hover:bg-white/[0.08]"
                    >
                      Berikutnya
                    </button>
                  </div>
                </div>
              </>
            )}
          </div>
        </div>
      ) : (
        <div className="space-y-6">
          <div className="overflow-hidden rounded-[24px] border border-white/50 bg-white/65 shadow-[0_10px_40px_-15px_rgba(0,0,0,0.08)] dark:border-white/10 dark:bg-white/[0.04]">
            {reconLoading ? (
              <LoadingSkeleton variant="table" message="Memuat data rekonsiliasi..." />
            ) : reconError ? (
              <ApiErrorBoundary
                error={reconError}
                onRetry={() => reconRefetch()}
                title="Gagal Memuat Rekonsiliasi"
              />
            ) : (
              <div className="overflow-x-auto">
                <table className="w-full text-[13px]">
                  <thead>
                    <tr className="border-b border-gray-200/60 text-left text-[11.5px] text-gray-500 dark:border-white/10 dark:text-gray-400">
                      {['Segmen / Key', 'Deskripsi', 'Internal (Simentor)', 'Official (SAKTI)', 'Selisih'].map(
                        (h) => (
                          <th
                            key={h}
                            className={`whitespace-nowrap bg-gray-50/30 px-6 py-5 font-bold uppercase tracking-widest dark:bg-white/5 ${
                              ['Internal (Simentor)', 'Official (SAKTI)', 'Selisih'].includes(h) ? 'text-right' : ''
                            }`}
                          >
                            {h}
                          </th>
                        ),
                      )}
                    </tr>
                  </thead>
                  <tbody className="divide-y divide-gray-100/70 dark:divide-white/5">
                    {reconData.length === 0 ? (
                      <tr>
                        <td
                          colSpan={5}
                          className="px-6 py-12 text-center text-sm text-gray-400"
                        >
                          Data SAKTI belum diimport untuk tahun anggaran ini.
                        </td>
                      </tr>
                    ) : (
                      reconData.map((item, index) => (
                        <motion.tr
                          key={item.budget_item_key}
                          initial={{ opacity: 0, y: 6 }}
                          animate={{ opacity: 1, y: 0 }}
                          transition={{ delay: index * 0.02 }}
                          className="transition-colors hover:bg-white/40 dark:hover:bg-white/[0.02]"
                        >
                          <td className="px-6 py-4">
                            <span className="font-mono text-[11px] text-gray-500 dark:text-gray-400">
                              {item.budget_item_key}
                            </span>
                          </td>
                          <td className="max-w-[300px] px-6 py-4">
                            <span className="block truncate font-medium text-gray-900 dark:text-white">
                              {item.uraian_kegiatan}
                            </span>
                          </td>
                          <td className="px-6 py-4 text-right">
                            <span className="text-gray-600 dark:text-gray-400">
                              {fmtCurrency(item.total_internal)}
                            </span>
                          </td>
                          <td className="px-6 py-4 text-right">
                            <span className="font-bold text-gray-900 dark:text-white">
                              {fmtCurrency(item.total_sakti)}
                            </span>
                          </td>
                          <td className="px-6 py-4 text-right">
                            <span
                              className={`font-black ${
                                item.selisih !== 0
                                  ? 'text-red-500'
                                  : 'text-emerald-500'
                              }`}
                            >
                              {fmtCurrency(item.selisih)}
                            </span>
                          </td>
                        </motion.tr>
                      ))
                    )}
                  </tbody>
                </table>
              </div>
            )}
          </div>
        </div>
      )}

      {/* Delete Modal omitted for brevity, keeping existing logic */}
      <AnimatePresence>
        {canManageHistory && deleteId !== null && (
          <motion.div
            initial={{ opacity: 0 }}
            animate={{ opacity: 1 }}
            exit={{ opacity: 0 }}
            className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm"
          >
            <motion.div
              initial={{ scale: 0.95, opacity: 0 }}
              animate={{ scale: 1, opacity: 1 }}
              exit={{ scale: 0.95, opacity: 0 }}
              className="relative w-full max-w-sm rounded-2xl border border-gray-200 bg-white p-6 shadow-2xl dark:border-white/10 dark:bg-gray-900"
            >
              <h3 className="text-lg font-bold text-gray-900 dark:text-white">Hapus Transaksi?</h3>
              <p className="mt-2 text-sm text-gray-500">Aksi ini tidak dapat dibatalkan.</p>
              <div className="mt-6 flex justify-end gap-3">
                <button
                  onClick={() => setDeleteId(null)}
                  className="rounded-xl px-4 py-2 text-sm font-medium text-gray-500 hover:bg-gray-100 dark:hover:bg-white/5"
                >
                  Batal
                </button>
                <button
                  onClick={() => deleteId && deleteMutation.mutate(deleteId)}
                  className="rounded-xl bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700"
                >
                  {deleteMutation.isPending ? 'Menghapus...' : 'Hapus'}
                </button>
              </div>
            </motion.div>
          </motion.div>
        )}
      </AnimatePresence>
    </div>
  );
}
