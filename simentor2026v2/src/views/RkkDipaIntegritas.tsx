import React, { useMemo, useState, useCallback, useEffect } from 'react';
import {
  AlertTriangle,
  CheckCircle2,
  DatabaseZap,
  Info,
  Loader2,
  Merge,
  RefreshCw,
  Search,
  ShieldCheck,
  X,
  ArrowRight,
} from 'lucide-react';
import { motion, AnimatePresence } from 'motion/react';
import { useApiQuery, useApiMutation } from '../hooks/useApi';
import { dipaBudgetService } from '../lib/api-services';
import { PageHeader } from '../components/PageHeader';
import { LoadingSkeleton } from '../components/LoadingSkeleton';
import { ApiErrorBoundary } from '../components/ApiErrorBoundary';
import { useDebounce } from '../hooks/useDebounce';
import type { DipaOrphan, DipaBudgetItem } from '../types/api';

// ── Formatters ───────────────────────────────────────────────
const fmtCurrency = (n: number) =>
  new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
  }).format(n);

// ── Component ─────────────────────────────────────────────────
export function RkkDipaIntegritas() {
  // ── State ────────────────────────────────────────────────────
  const [mappingTarget, setMappingTarget] = useState<DipaOrphan | null>(null);
  const [search, setSearch] = useState('');
  const [selectedNewItem, setSelectedNewItem] = useState<DipaBudgetItem | null>(null);
  const [showSuccess, setShowSuccess] = useState(false);

  const debouncedQ = useDebounce(search, 400);
  const canSearch = debouncedQ.trim().length >= 3;

  // ── Queries ──────────────────────────────────────────────────
  const {
    data: orphansResp,
    isLoading: orphansLoading,
    error: orphansError,
    refetch: orphansRefetch,
  } = useApiQuery(['dipa-orphans'], () => dipaBudgetService.getOrphans());

  const {
    data: pickerResp,
    isLoading: pickerLoading,
    error: pickerError,
  } = useApiQuery(
    ['dipa-picker-integritas', debouncedQ],
    () => dipaBudgetService.getPicker(debouncedQ),
    { enabled: canSearch && !!mappingTarget },
  );

  // ── Mutations ────────────────────────────────────────────────
  const mapMutation = useApiMutation(
    (payload: { old_composite_key: string; new_composite_key: string }) =>
      dipaBudgetService.saveMapping(payload),
    {
      invalidateKeys: [['dipa-orphans'], ['dipa-monitoring']],
      onSuccess: () => {
        setShowSuccess(true);
        setTimeout(() => {
          setShowSuccess(false);
          setMappingTarget(null);
          setSelectedNewItem(null);
          setSearch('');
        }, 2000);
      },
    },
  );

  // ── Derived ──────────────────────────────────────────────────
  const orphans: DipaOrphan[] = useMemo(
    () => (orphansResp?.data as DipaOrphan[]) ?? [],
    [orphansResp],
  );

  const pickerItems: DipaBudgetItem[] = useMemo(
    () => (pickerResp?.data as DipaBudgetItem[]) ?? [],
    [pickerResp],
  );

  // ── Handlers ─────────────────────────────────────────────────
  const handleMap = useCallback(() => {
    if (!mappingTarget || !selectedNewItem) return;
    mapMutation.mutate({
      old_composite_key: mappingTarget.budget_item_key,
      new_composite_key: selectedNewItem.composite_key,
    });
  }, [mappingTarget, selectedNewItem, mapMutation]);

  // ── Render ───────────────────────────────────────────────────
  return (
    <div className="space-y-6">
      <PageHeader
        title="Integritas Data DIPA"
        description="Kelola transaksi yang kehilangan link akibat perubahan nama item atau kode di revisi DIPA terbaru."
        actions={
          <button
            data-scan="tombol periksa integritas"
            onClick={() => orphansRefetch()}
            disabled={orphansLoading}
            className="flex items-center gap-2 rounded-full border border-white/50 bg-white/70 px-4 py-2 text-xs font-bold text-gray-700 shadow-sm transition hover:bg-white disabled:opacity-50 dark:border-white/10 dark:bg-white/[0.04] dark:text-gray-300"
          >
            <RefreshCw className={`h-3.5 w-3.5 ${orphansLoading ? 'animate-spin' : ''}`} />
            Periksa Integritas
          </button>
        }
      />

      {/* Hero / State indicator */}
      {orphansLoading ? (
        <LoadingSkeleton variant="cards" message="Menganalisis konsistensi data..." />
      ) : orphansError ? (
        <ApiErrorBoundary
          error={orphansError}
          onRetry={() => orphansRefetch()}
          title="Gagal Memeriksa Integritas"
        />
      ) : orphans.length === 0 ? (
        <motion.div
          initial={{ opacity: 0, scale: 0.98 }}
          animate={{ opacity: 1, scale: 1 }}
          className="flex flex-col items-center justify-center space-y-4 rounded-[32px] border-2 border-dashed border-emerald-500/20 bg-emerald-500/5 py-16 text-center"
        >
          <div className="flex h-20 w-20 items-center justify-center rounded-full border border-emerald-500/20 bg-emerald-500/10 shadow-lg shadow-emerald-500/10">
            <ShieldCheck className="h-10 w-10 text-emerald-500" />
          </div>
          <div>
            <h3 className="text-xl font-black uppercase tracking-widest text-gray-900 dark:text-white">
              Integritas 100%
            </h3>
            <p className="mt-2 max-w-md text-sm text-gray-500 dark:text-gray-400">
              Seluruh transaksi realisasi terhubung dengan item DIPA yang valid di revisi
              terbaru. Tidak ditemukan data "yatim piatu".
            </p>
          </div>
        </motion.div>
      ) : (
        <motion.div
          initial={{ opacity: 0, y: 12 }}
          animate={{ opacity: 1, y: 0 }}
          className="space-y-6"
        >
          {/* Warning banner */}
          <div className="flex items-start gap-4 rounded-[24px] border border-red-500/20 bg-red-500/5 p-5">
            <AlertTriangle className="mt-1 h-6 w-6 shrink-0 text-red-500" />
            <div className="space-y-1">
              <h4 className="text-sm font-bold text-red-600 dark:text-red-400">
                Data Tidak Sinkron Terdeteksi!
              </h4>
              <p className="text-xs leading-relaxed text-gray-600 dark:text-gray-400">
                Terdapat <b>{orphans.length} kode anggaran</b> yang digunakan dalam
                transaksi namun tidak ditemukan di revisi DIPA terakhir. Hal ini biasanya
                terjadi karena item tersebut <b>ganti nama</b> atau{' '}
                <b>kode uniknya berubah</b>. Anda harus melakukan "Merging" agar realisasi
                tetap terhitung di laporan Monitoring.
              </p>
            </div>
          </div>

          {/* Orphan list */}
          <div className="rounded-[24px] border border-white/50 bg-white/65 shadow-[0_10px_40px_-15px_rgba(0,0,0,0.08)] dark:border-white/10 dark:bg-white/[0.04]">
            <div className="flex items-center gap-2 border-b border-gray-200/70 p-5 dark:border-white/10">
              <DatabaseZap className="h-4 w-4 text-sky-500" />
              <h3 className="text-[11px] font-bold uppercase tracking-widest text-gray-500 dark:text-gray-400">
                Daftar Item Yatim Piatu (Orphans)
              </h3>
            </div>

            <div className="overflow-x-auto">
              <table className="w-full text-[13px]">
                <thead>
                  <tr className="border-b border-gray-200/60 text-left text-[11.5px] text-gray-500 dark:border-white/10 dark:text-gray-400">
                    <th className="whitespace-nowrap bg-gray-50/30 px-6 py-5 font-bold uppercase tracking-widest dark:bg-white/5">
                      Old Budget Key
                    </th>
                    <th className="whitespace-nowrap bg-gray-50/30 px-6 py-5 font-bold uppercase tracking-widest dark:bg-white/5">
                      Deskripsi Terakhir
                    </th>
                    <th className="whitespace-nowrap bg-gray-50/30 px-6 py-5 font-bold uppercase tracking-widest dark:bg-white/5 text-right">
                      Total Lost Amount
                    </th>
                    <th className="whitespace-nowrap bg-gray-50/30 px-6 py-5 font-bold uppercase tracking-widest dark:bg-white/5 text-center">
                      Jml Trx
                    </th>
                    <th className="whitespace-nowrap bg-gray-50/30 px-6 py-5 font-bold uppercase tracking-widest dark:bg-white/5 text-right">
                      Aksi Resolusi
                    </th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-gray-100/70 dark:divide-white/5">
                  {orphans.map((row, index) => (
                    <tr
                      key={row.budget_item_key}
                      className="transition-colors hover:bg-white/40 dark:hover:bg-white/[0.02]"
                    >
                      <td className="px-6 py-4">
                        <span className="font-mono text-[11px] text-gray-500 dark:text-gray-400">
                          {row.budget_item_key}
                        </span>
                      </td>
                      <td className="px-6 py-4">
                        <span
                          className="text-[13px] font-medium text-gray-900 dark:text-white"
                          dangerouslySetInnerHTML={{ __html: row.last_description || '-' }}
                        />
                      </td>
                      <td className="px-6 py-4 text-right">
                        <span className="font-mono text-[13px] font-bold text-red-600 dark:text-red-400">
                          {fmtCurrency(row.total_orphan_amount)}
                        </span>
                      </td>
                      <td className="px-6 py-4 text-center">
                        <span className="text-[11px] font-bold text-gray-400">
                          {row.transaction_count} Trx
                        </span>
                      </td>
                      <td className="px-6 py-4 text-right">
                        <button
                          data-scan="tombol petakan ulang"
                          onClick={() => setMappingTarget(row)}
                          className="inline-flex items-center gap-1.5 rounded-full bg-sky-500/10 px-3 py-1.5 text-[11px] font-bold text-sky-600 transition hover:bg-sky-500 hover:text-white dark:bg-sky-500/20 dark:text-sky-400"
                        >
                          <Merge className="h-3.5 w-3.5" />
                          Petakan Ulang
                        </button>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          </div>
        </motion.div>
      )}

      {/* Info footer */}
      <div className="rounded-[24px] border border-white/50 bg-white/65 p-6 shadow-[0_10px_40px_-15px_rgba(0,0,0,0.08)] dark:border-white/10 dark:bg-white/[0.04]">
        <div className="flex items-start gap-4">
          <Info className="h-5 w-5 shrink-0 text-sky-500" />
          <div className="space-y-1">
            <h5 className="text-xs font-bold text-gray-900 dark:text-white">
              Mengapa ini diperlukan?
            </h5>
            <p className="text-[11px] leading-relaxed text-gray-500 dark:text-gray-400">
              Setiap revisi DIPA mungkin menyertakan pengubahan nama kegiatan (Misal:
              "Honor A" menjadi "Honor Honor A"). Tanpa penggabungan (Merge), sistem akan
              menganggap item baru tersebut belum memiliki realisasi karena kodenya
              berbeda. Dengan melakukan Merge, sistem akan "menjahit" histori pengeluaran
              lama ke dalam pagu (anggaran) yang baru.
            </p>
          </div>
        </div>
      </div>

      {/* ── Mapping Modal ── */}
      <AnimatePresence>
        {mappingTarget && (
          <motion.div
            initial={{ opacity: 0 }}
            animate={{ opacity: 1 }}
            exit={{ opacity: 0 }}
            className="fixed inset-0 z-50 flex items-center justify-center p-4"
          >
            {/* Backdrop */}
            <div
              className="absolute inset-0 bg-black/60 backdrop-blur-md"
              onClick={() => !mapMutation.isPending && setMappingTarget(null)}
            />

            {/* Modal Body */}
            <motion.div
              initial={{ scale: 0.95, y: 20, opacity: 0 }}
              animate={{ scale: 1, y: 0, opacity: 1 }}
              exit={{ scale: 0.95, y: 20, opacity: 0 }}
              className="relative w-full max-w-2xl overflow-hidden rounded-[28px] border border-white/20 bg-white shadow-2xl dark:bg-gray-900"
            >
              {/* Modal Header */}
              <div className="flex items-center justify-between border-b border-gray-100 bg-gray-50/50 p-6 dark:border-white/5 dark:bg-white/[0.02]">
                <div>
                  <h3 className="flex items-center gap-2 text-lg font-black text-gray-900 dark:text-white">
                    <Merge className="h-5 w-5 text-sky-500" />
                    Merge Transaksi
                  </h3>
                  <p className="text-xs text-gray-500 dark:text-gray-400">
                    Petakan transaksi dari item lama ke item baru di revisi terakhir.
                  </p>
                </div>
                <button
                  onClick={() => setMappingTarget(null)}
                  className="rounded-full p-2 hover:bg-gray-200 dark:hover:bg-white/10"
                >
                  <X className="h-5 w-5 text-gray-500" />
                </button>
              </div>

              {/* Modal Content */}
              <div className="max-h-[70vh] space-y-6 overflow-y-auto p-6 scrollbar-hide">
                {/* Source (Old) */}
                <div className="space-y-2 rounded-[20px] border border-red-500/20 bg-red-500/5 p-4">
                  <div className="text-[10px] font-bold uppercase tracking-widest text-red-500">
                    Item Sumber (Lama)
                  </div>
                  <div className="font-mono text-[11px] text-gray-500">
                    {mappingTarget.budget_item_key}
                  </div>
                  <div
                    className="text-sm font-semibold text-gray-900 dark:text-white"
                    dangerouslySetInnerHTML={{ __html: mappingTarget.last_description || '-' }}
                  />
                </div>

                <div className="flex justify-center">
                  <div className="flex h-10 w-10 animate-bounce items-center justify-center rounded-full bg-gray-100 dark:bg-white/10">
                    <ArrowRight className="h-5 w-5 rotate-90 text-gray-400 lg:rotate-0" />
                  </div>
                </div>

                {/* Target Search */}
                <div className="space-y-4">
                  <div className="flex items-center gap-2 text-[10px] font-bold uppercase tracking-widest text-sky-600 dark:text-sky-400">
                    <Search className="h-3.5 w-3.5" />
                    Cari Item Baru (DIPA Terkini)
                  </div>
                  <div className="relative">
                    <Search className="absolute left-3 top-3.5 h-4.5 w-4.5 text-gray-400" />
                    <input
                      data-scan="input pencarian item baru"
                      className="h-12 w-full rounded-2xl border border-gray-200 bg-gray-50 pl-10 pr-4 text-sm outline-none transition focus:border-sky-500 focus:bg-white dark:border-white/10 dark:bg-white/[0.04] dark:text-white"
                      placeholder="Ketik kode atau deskripsi item di revisi baru..."
                      value={search}
                      onChange={(e) => setSearch(e.target.value)}
                    />
                  </div>

                  <div className="max-h-[220px] space-y-2 overflow-y-auto rounded-2xl bg-gray-50/50 p-2 dark:bg-black/20">
                    {pickerLoading && (
                      <div className="flex items-center justify-center p-8">
                        <Loader2 className="h-6 w-6 animate-spin text-sky-500" />
                      </div>
                    )}

                    {!pickerLoading &&
                      pickerItems.map((item) => {
                        const active =
                          selectedNewItem?.composite_key === item.composite_key;
                        return (
                          <button
                            key={item.composite_key}
                            onClick={() => setSelectedNewItem(item)}
                            className={`group w-full rounded-xl border p-3 text-left transition-all ${
                              active
                                ? 'border-sky-500 bg-sky-500/10 shadow-sm'
                                : 'border-transparent bg-white hover:bg-gray-100 dark:bg-white/5 dark:hover:bg-white/10'
                            }`}
                          >
                            <div className="flex items-center justify-between">
                              <div className="font-mono text-[10px] font-bold text-sky-600 dark:text-sky-400">
                                {item.composite_key}
                              </div>
                              <div className="rounded bg-gray-100 px-1.5 py-0.5 text-[9px] font-bold text-gray-500 dark:bg-white/10 dark:text-gray-400">
                                Pagu: {fmtCurrency(item.total_pagu)}
                              </div>
                            </div>
                            <div
                              className="mt-1 text-xs font-medium text-gray-900 dark:text-white"
                              dangerouslySetInnerHTML={{ __html: item.formatted_description }}
                            />
                          </button>
                        );
                      })}

                    {!pickerLoading && search.length >= 3 && pickerItems.length === 0 && (
                      <div className="p-4 text-center text-xs text-gray-400">
                        Tidak ada item cocok dengan pencarian.
                      </div>
                    )}
                    {search.length < 3 && (
                      <div className="p-4 text-center text-[10px] italic text-gray-400">
                        <Info className="mr-1 inline-block h-3 w-3" />
                        Masukkan minimal 3 karakter untuk mencari target merge.
                      </div>
                    )}
                  </div>
                </div>

                {/* Selected preview */}
                <AnimatePresence>
                  {selectedNewItem && (
                    <motion.div
                      initial={{ opacity: 0, height: 0 }}
                      animate={{ opacity: 1, height: 'auto' }}
                      exit={{ opacity: 0, height: 0 }}
                      className="space-y-2 rounded-[20px] border border-emerald-500/20 bg-emerald-500/5 p-4"
                    >
                      <div className="flex items-center gap-2 text-[10px] font-bold uppercase tracking-widest text-emerald-600">
                        <CheckCircle2 className="h-3.5 w-3.5" />
                        Target Terpilih
                      </div>
                      <div className="flex items-start justify-between">
                        <div>
                          <div
                            className="text-sm font-black text-gray-900 dark:text-white"
                            dangerouslySetInnerHTML={{ __html: selectedNewItem.formatted_description }}
                          />
                          <div className="mt-1 font-mono text-[10px] text-gray-500">
                            {selectedNewItem.composite_key}
                          </div>
                        </div>
                        <div className="rounded-lg bg-white/50 px-2.5 py-1.5 text-right dark:bg-black/20">
                          <div className="text-[9px] font-bold uppercase tracking-wider text-gray-400">
                            Pagu Target
                          </div>
                          <div className="text-[13px] font-bold text-emerald-600 dark:text-emerald-400">
                            {fmtCurrency(selectedNewItem.total_pagu)}
                          </div>
                        </div>
                      </div>
                    </motion.div>
                  )}
                </AnimatePresence>

                {/* Status/Error */}
                <AnimatePresence>
                  {showSuccess && (
                    <motion.div
                      initial={{ opacity: 0 }}
                      animate={{ opacity: 1 }}
                      className="rounded-xl bg-emerald-500/10 p-3 text-center text-xs font-bold text-emerald-600"
                    >
                      Pemetaan berhasil disimpan!
                    </motion.div>
                  )}
                  {mapMutation.error && (
                    <motion.div
                      initial={{ opacity: 0 }}
                      animate={{ opacity: 1 }}
                      className="rounded-xl bg-red-500/10 p-3 text-center text-xs font-bold text-red-600"
                    >
                      {mapMutation.error.message ?? 'Gagal menyimpan pemetaan.'}
                    </motion.div>
                  )}
                </AnimatePresence>
              </div>

              {/* Modal Footer */}
              <div className="flex gap-3 bg-gray-50 p-6 dark:bg-white/[0.02]">
                <button
                  data-scan="tombol batal merge"
                  onClick={() => setMappingTarget(null)}
                  disabled={mapMutation.isPending}
                  className="flex-1 rounded-xl border border-gray-200 bg-white py-3 text-sm font-bold text-gray-600 transition hover:bg-gray-100 dark:border-white/10 dark:bg-white/5 dark:text-gray-400"
                >
                  Batal
                </button>
                <button
                  data-scan="tombol konfirmasi merge"
                  onClick={handleMap}
                  disabled={!selectedNewItem || mapMutation.isPending}
                  className="flex-[2] flex items-center justify-center gap-2 rounded-xl bg-sky-600 py-3 text-sm font-bold text-white shadow-lg shadow-sky-500/20 transition hover:bg-sky-700 disabled:opacity-50"
                >
                  {mapMutation.isPending ? (
                    <Loader2 className="h-4 w-4 animate-spin" />
                  ) : (
                    <Merge className="h-4 w-4" />
                  )}
                  {mapMutation.isPending ? 'Menyimpan...' : 'Konfirmasi Merge'}
                </button>
              </div>
            </motion.div>
          </motion.div>
        )}
      </AnimatePresence>
    </div>
  );
}
