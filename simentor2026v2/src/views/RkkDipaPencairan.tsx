import React, { useMemo, useState, useCallback, useEffect } from 'react';
import {
  Calendar,
  Download,
  Info,
  Save,
  Search,
  X,
  CheckCircle2,
  Loader2,
  Database,
  RefreshCw,
  Coins,
  LayoutList,
  MousePointer2,
} from 'lucide-react';
import { motion, AnimatePresence } from 'motion/react';
import { useQueryClient } from '@tanstack/react-query';
import { DatePicker } from '../components/DatePicker';
import { PageHeader } from '../components/PageHeader';
import { LoadingSkeleton } from '../components/LoadingSkeleton';
import { ApiErrorBoundary } from '../components/ApiErrorBoundary';
import { useApiQuery, useApiMutation } from '../hooks/useApi';
import { useDebounce } from '../hooks/useDebounce';
import { dipaBudgetService } from '../lib/api-services';
import type { DipaBudgetItem, DipaUsageHistory } from '../types/api';

// ── Formatters ───────────────────────────────────────────────
const fmtCurrency = (value: number) =>
  new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(value);

const fmtDate = (datestr: string) =>
  new Intl.DateTimeFormat('id-ID', { dateStyle: 'long' }).format(
    new Date(`${datestr}T00:00:00`),
  );

// ── Component ─────────────────────────────────────────────────
export function RkkDipaPencairan() {
  const queryClient = useQueryClient();

  // ── Form state ───────────────────────────────────────────────
  const [search, setSearch] = useState('');
  const [selectedItem, setSelectedItem] = useState<DipaBudgetItem | null>(null);
  const [amount, setAmount] = useState('');
  const [date, setDate] = useState(new Date().toISOString().slice(0, 10));
  const [showSuccess, setShowSuccess] = useState(false);

  // ── Tab state ────────────────────────────────────────────────
  const [activeTab, setActiveTab] = useState<'manual' | 'sakti'>('manual');

  // ── SAKTI state ──────────────────────────────────────────────
  const [yearSakti, setYearSakti] = useState(new Date().getFullYear().toString());
  const [dateSakti, setDateSakti] = useState(new Date().toISOString().slice(0, 10));
  const [saktiSummary, setSaktiSummary] = useState<{ usage_date: string; row_count: number; total_amount: number } | null>(null);
  const [isCheckingSakti, setIsCheckingSakti] = useState(false);
  const [saktiError, setSaktiError] = useState<string | null>(null);
  const [isSyncingSakti, setIsSyncingSakti] = useState(false);
  const [saktiSuccess, setSaktiSuccess] = useState(false);
  const [syncResult, setSyncResult] = useState<{ usage_date: string; row_count: number; total_amount: number } | null>(null);

  // ── Pre-filled data ──────────────────────────────────────────
  useEffect(() => {
    const prefilledData = sessionStorage.getItem('pencatatan_prefilled');
    if (prefilledData) {
      try {
        const { searchQuery, amount: prefilledAmount } = JSON.parse(prefilledData);
        if (searchQuery) setSearch(searchQuery);
        if (prefilledAmount) setAmount(String(prefilledAmount));
        sessionStorage.removeItem('pencatatan_prefilled');
      } catch (e) {
        console.error('Failed to parse prefilled data', e);
      }
    }
  }, []);

  const debouncedQ = useDebounce(search, 400);
  const canSearch = debouncedQ.trim().length >= 3;

  // ── Queries ──────────────────────────────────────────────────
  const {
    data: pickerResp,
    isLoading: pickerLoading,
    error: pickerError,
  } = useApiQuery(
    ['dipa-picker-pencairan', debouncedQ],
    () => dipaBudgetService.getPicker(debouncedQ),
    { enabled: canSearch },
  );

  const {
    data: historyResp,
    isLoading: historyLoading,
    error: historyError,
    refetch: historyRefetch,
  } = useApiQuery(['dipa-history'], () => dipaBudgetService.getHistory());

  // ── Mutation ─────────────────────────────────────────────────
  const saveMutation = useApiMutation(
    (payload: { budget_item_key: string; amount_spent: number; usage_date: string }) =>
      dipaBudgetService.recordUsage(payload),
    {
      invalidateKeys: [['dipa-history'], ['dipa-monitoring']],
      onSuccess: () => {
        setShowSuccess(true);
        handleReset();
        setTimeout(() => setShowSuccess(false), 3000);
      },
    },
  );

  // ── Derived ──────────────────────────────────────────────────
  const pickerItems: DipaBudgetItem[] = useMemo(
    () => (pickerResp?.data as DipaBudgetItem[]) ?? [],
    [pickerResp],
  );

  const recentHistory: DipaUsageHistory[] = useMemo(
    () => ((historyResp?.data as DipaUsageHistory[]) ?? []).slice(0, 5),
    [historyResp],
  );

  const totalRealisasi = useMemo(
    () =>
      ((historyResp?.data as DipaUsageHistory[]) ?? []).reduce(
        (sum, item) => sum + item.amount_spent,
        0,
      ),
    [historyResp],
  );

  const parsedAmount = Number(amount);
  const canSave =
    selectedItem !== null && parsedAmount > 0 && !saveMutation.isPending;

  // ── Handlers ─────────────────────────────────────────────────
  const handleReset = useCallback(() => {
    setSelectedItem(null);
    setAmount('');
    setSearch('');
  }, []);

  const handleSave = useCallback(() => {
    if (!selectedItem || parsedAmount <= 0) return;
    saveMutation.mutate({
      budget_item_key: selectedItem.budget_item_key,
      amount_spent: parsedAmount,
      usage_date: date,
    });
  }, [selectedItem, parsedAmount, date, saveMutation]);

  const handleCheckSakti = async () => {
    setIsCheckingSakti(true);
    setSaktiError(null);
    setSaktiSummary(null);
    setSaktiSuccess(false);

    try {
      // Format dateSakti to YYYY-MM as requested
      const d = new Date(dateSakti);
      const y = d.getFullYear();
      const m = d.getMonth() + 1;
      const formattedMonth = `${y}-${String(m).padStart(2, '0')}`;

      const result = await dipaBudgetService.getSaktiSummary(formattedMonth);
      if (result.success && result.data) {
        setSaktiSummary(result.data);
      } else {
        setSaktiError(result.message ?? 'Gagal mengambil data SAKTI.');
      }
    } catch (err: any) {
      setSaktiError(err.message ?? 'Terjadi kesalahan jaringan.');
    } finally {
      setIsCheckingSakti(false);
    }
  };

  const handleSyncSakti = async () => {
    if (!saktiSummary) return;
    setIsSyncingSakti(true);
    setSaktiError(null);
    setSyncResult(null);

    try {
      const result = await dipaBudgetService.syncSaktiFa({ usage_date: saktiSummary.usage_date });
      if (result.success && result.data) {
        setSyncResult(result.data);
        setSaktiSuccess(true);
        setSaktiSummary(null);
        historyRefetch();
        queryClient.invalidateQueries({ queryKey: ['dipa-monitoring'] });
      } else {
        setSaktiError(result.message ?? 'Gagal sinkronisasi data SAKTI.');
      }
    } catch (err: any) {
      setSaktiError(err.message ?? 'Terjadi kesalahan jaringan.');
    } finally {
      setIsSyncingSakti(false);
    }
  };

  // ── Render ───────────────────────────────────────────────────
  return (
    <div className="space-y-6">
      <PageHeader
        title="Pencairan RKK DIPA"
        description="Input realisasi pencairan dari sumber budget RKK DIPA yang tersedia."
      />
      
      {/* ── Tab Switcher ── */}
      <div 
        data-scan="tab kontrol"
        className="relative flex w-full max-w-md items-center gap-1 rounded-2xl border border-gray-200/60 bg-gray-50/50 p-1.5 dark:border-white/10 dark:bg-white/[0.03]"
      >
        <button
          data-scan="tab manual"
          onClick={() => setActiveTab('manual')}
          className={`relative flex flex-1 items-center justify-center gap-2 py-2 text-xs font-bold transition-colors ${
            activeTab === 'manual' ? 'text-sky-600 dark:text-sky-400' : 'text-gray-500 hover:text-gray-900 dark:hover:text-white'
          }`}
        >
          {activeTab === 'manual' && (
            <motion.div
              layoutId="activeTabBg"
              className="absolute inset-0 rounded-xl bg-white shadow-sm ring-1 ring-gray-200/50 dark:bg-white/10 dark:ring-white/10"
            />
          )}
          <MousePointer2 className="relative z-10 h-3.5 w-3.5" />
          <span className="relative z-10 uppercase tracking-wider">Manual</span>
        </button>

        <button
          data-scan="tab sakti fa"
          onClick={() => setActiveTab('sakti')}
          className={`relative flex flex-1 items-center justify-center gap-2 py-2 text-xs font-bold transition-colors ${
            activeTab === 'sakti' ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-500 hover:text-gray-900 dark:hover:text-white'
          }`}
        >
          {activeTab === 'sakti' && (
            <motion.div
              layoutId="activeTabBg"
              className="absolute inset-0 rounded-xl bg-white shadow-sm ring-1 ring-gray-200/50 dark:bg-white/10 dark:ring-white/10"
            />
          )}
          <Database className="relative z-10 h-3.5 w-3.5" />
          <span className="relative z-10 uppercase tracking-wider">Dari Sakti FA</span>
        </button>
      </div>

      <motion.div
        key={activeTab}
        initial={{ opacity: 0, x: 10 }}
        animate={{ opacity: 1, x: 0 }}
        transition={{ duration: 0.3, ease: 'easeOut' }}
      >
        {activeTab === 'manual' ? (
          <div className="space-y-6">
            {/* Summary cards */}
            <div 
              data-scan="ringkasan statistik"
              className="grid grid-cols-1 gap-4 md:grid-cols-3"
            >
              {[
                {
                  label: 'Total Realisasi',
                  value: historyLoading ? '...' : fmtCurrency(totalRealisasi),
                  color: 'text-emerald-600 dark:text-emerald-400',
                },
                {
                  label: 'Transaksi Tercatat',
                  value: historyLoading
                    ? '...'
                    : String((historyResp?.data as DipaUsageHistory[] | undefined)?.length ?? 0),
                  color: 'text-sky-600 dark:text-sky-400',
                },
                {
                  label: 'Tanggal Input',
                  value: fmtDate(date),
                  color: 'text-amber-600 dark:text-amber-400',
                },
              ].map((item, index) => (
                <div
                  key={item.label}
                  className="rounded-[20px] border border-white/50 bg-white/65 p-5 shadow-[0_10px_40px_-15px_rgba(0,0,0,0.08)] dark:border-white/10 dark:bg-white/[0.04]"
                >
                  <p className="text-[11px] font-bold uppercase tracking-widest text-gray-500 dark:text-gray-400">
                    {item.label}
                  </p>
                  <p className={`mt-2 text-lg font-black ${item.color}`}>{item.value}</p>
                </div>
              ))}
            </div>

            {/* Form */}
            <div 
              data-scan="form input manual"
              className="rounded-[24px] border border-white/50 bg-white/65 p-5 shadow-[0_10px_40px_-15px_rgba(0,0,0,0.08)] dark:border-white/10 dark:bg-white/[0.04]"
            >
              <div className="grid grid-cols-1 gap-8 md:grid-cols-2">
                {/* Left: Picker */}
                <div className="space-y-4">
                  <h3 className="border-b border-gray-200/70 pb-2 text-sm font-bold text-gray-900 dark:border-white/10 dark:text-white">
                    1. Pilih Sumber Budget
                  </h3>

                  <div 
                    data-scan="pencarian sumber budget"
                    className="relative"
                  >
                    <Search className="absolute left-3 top-3 h-4 w-4 text-gray-400" />
                    <input
                      data-scan="pencarian sumber budget"
                      value={search}
                      onChange={(e) => setSearch(e.target.value)}
                      className="h-10 w-full rounded-xl border border-white/50 bg-gray-50/80 pl-10 pr-4 text-sm text-gray-900 outline-none transition focus:border-sky-500 dark:border-white/10 dark:bg-white/[0.04] dark:text-white"
                      placeholder="Cari kode atau uraian... (min. 3 karakter)"
                    />
                  </div>

                  <div 
                    data-scan="daftar sumber budget"
                    className="max-h-[300px] space-y-2 overflow-y-auto pr-1"
                  >
                    {pickerLoading && canSearch && (
                      <LoadingSkeleton variant="cards" message="Mencari item..." />
                    )}

                    {pickerError && canSearch && (
                      <ApiErrorBoundary error={pickerError} title="Gagal memuat picker" />
                    )}

                    {!pickerLoading &&
                      canSearch &&
                      pickerItems.map((item, index) => {
                        const active = item.budget_item_key === selectedItem?.budget_item_key;
                        return (
                          <motion.button
                            key={item.budget_item_key}
                            type="button"
                            initial={{ opacity: 0, y: 8 }}
                            animate={{ opacity: 1, y: 0 }}
                            transition={{ delay: index * 0.03 }}
                            onClick={() => setSelectedItem(item)}
                            className={`w-full rounded-[18px] border p-4 text-left transition ${
                              active
                                ? 'border-sky-500 bg-sky-500/5 shadow-sm'
                                : 'border-white/50 bg-gray-50/60 hover:bg-white dark:border-white/10 dark:bg-white/[0.03] dark:hover:bg-white/[0.06]'
                            }`}
                          >
                            <div className="flex items-start justify-between gap-4">
                              <div className="space-y-1">
                                <div className="flex items-center gap-2">
                                  <div className="font-mono text-[12px] font-bold text-gray-900 dark:text-white">
                                    {item.short_code}
                                  </div>
                                  <div className="rounded bg-gray-100 px-1.5 py-0.5 text-[10px] font-bold text-gray-600 dark:bg-white/10 dark:text-gray-400">
                                    Pagu: {fmtCurrency(item.total_pagu)}
                                  </div>
                                </div>
                                <div
                                  className="text-[13px] font-medium leading-tight text-gray-900 dark:text-white"
                                  dangerouslySetInnerHTML={{ __html: item.formatted_description }}
                                />
                                <div className="text-[11px] text-gray-500">
                                  {item.funding_source}
                                </div>
                              </div>
                            </div>
                          </motion.button>
                        );
                      })}

                    {!pickerLoading && canSearch && pickerItems.length === 0 && (
                      <div className="rounded-[18px] border border-dashed border-gray-200 bg-gray-50/60 p-4 text-[13px] text-gray-500 dark:border-white/10 dark:bg-white/[0.03] dark:text-gray-400">
                        Tidak ada sumber budget yang cocok.
                      </div>
                    )}
                  </div>

                  <p className="flex items-center gap-1 text-[10px] italic text-gray-500 dark:text-gray-400">
                    <Info className="h-3 w-3" />
                    Masukkan minimal 3 karakter untuk mencari
                  </p>
                </div>

                {/* Right: Transaction form */}
                <div className="space-y-6">
                  <h3 className="border-b border-gray-200/70 pb-2 text-sm font-bold text-gray-900 dark:border-white/10 dark:text-white">
                    2. Rincian Transaksi
                  </h3>

                  {/* Selected item display */}
                  <div className="space-y-2">
                    <label className="text-[11px] uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">
                      Item Terpilih
                    </label>
                    <div 
                      data-scan="detail budget terpilih"
                      className="min-h-[60px] rounded-[18px] border border-dashed border-gray-200 bg-gray-50/60 p-4 dark:border-white/10 dark:bg-white/[0.03]"
                    >
                      {selectedItem ? (
                        <div className="space-y-1">
                          <div className="flex items-center gap-2">
                            <div className="font-mono text-[12px] font-bold text-gray-900 dark:text-white">
                              {selectedItem.short_code}
                            </div>
                            <div className="rounded bg-gray-100 px-1.5 py-0.5 text-[10px] font-bold text-gray-600 dark:bg-white/10 dark:text-gray-400">
                              Pagu: {fmtCurrency(selectedItem.total_pagu)}
                            </div>
                          </div>
                          <div
                            className="text-[13px] font-medium text-gray-900 dark:text-white"
                            dangerouslySetInnerHTML={{ __html: selectedItem.formatted_description }}
                          />
                          <div className="text-[11px] text-gray-500">
                            {selectedItem.funding_source}
                          </div>
                        </div>
                      ) : (
                        <span className="text-xs text-gray-500 dark:text-gray-400">
                          Belum ada item dipilih
                        </span>
                      )}
                    </div>
                  </div>

                  <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div className="space-y-2">
                      <label
                        htmlFor="dipa-usage-amount"
                        className="text-[11px] uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400"
                      >
                        Jumlah Pencairan (Rp)
                      </label>
                      <input
                        id="dipa-usage-amount"
                        data-scan="input jumlah pencairan"
                        type="number"
                        value={amount}
                        onChange={(e) => setAmount(e.target.value)}
                        className="h-10 w-full rounded-xl border border-white/50 bg-white/70 px-4 font-mono text-sm text-gray-900 outline-none transition focus:border-sky-500 dark:border-white/10 dark:bg-white/[0.04] dark:text-white"
                        placeholder="Contoh: 1500000"
                      />
                    </div>

                    <div className="space-y-2">
                      <label className="flex items-center gap-1 text-[11px] uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">
                        <Calendar className="h-3.5 w-3.5" />
                        Tanggal Realisasi
                      </label>
                      <DatePicker value={date} onChange={setDate} className="w-full" />
                    </div>
                  </div>

                  {/* Error from mutation */}
                  {saveMutation.error && (
                    <p className="text-[11px] text-red-500">
                      {saveMutation.error.message ?? 'Gagal menyimpan realisasi.'}
                    </p>
                  )}

                  {/* Success flash */}
                  <AnimatePresence>
                    {showSuccess && (
                      <motion.div
                        initial={{ opacity: 0, y: 6 }}
                        animate={{ opacity: 1, y: 0 }}
                        exit={{ opacity: 0 }}
                        className="flex items-center gap-2 rounded-xl border border-emerald-400/30 bg-emerald-500/5 px-4 py-3 text-[12px] font-medium text-emerald-600 dark:text-emerald-400"
                      >
                        <CheckCircle2 className="h-4 w-4" />
                        Realisasi berhasil dicatat.
                      </motion.div>
                    )}
                  </AnimatePresence>

                  <div className="flex gap-3 pt-2">
                    <button
                      type="button"
                      data-scan="tombol simpan realisasi"
                      onClick={handleSave}
                      disabled={!canSave}
                      className="flex h-10 flex-1 items-center justify-center gap-2 rounded-xl bg-sky-600 text-sm font-bold text-white transition hover:bg-sky-700 disabled:cursor-not-allowed disabled:opacity-50"
                    >
                      {saveMutation.isPending ? (
                        <Loader2 className="h-4 w-4 animate-spin" />
                      ) : (
                        <Save className="h-4 w-4" />
                      )}
                      {saveMutation.isPending ? 'Menyimpan...' : 'Simpan Realisasi'}
                    </button>
                    <button
                      type="button"
                      data-scan="tombol reset form"
                      onClick={handleReset}
                      className="flex h-10 items-center justify-center gap-2 rounded-xl border border-white/50 px-4 text-sm text-gray-500 transition hover:bg-gray-50 hover:text-gray-900 dark:border-white/10 dark:text-gray-400 dark:hover:bg-white/[0.04] dark:hover:text-white"
                    >
                      <X className="h-4 w-4" />
                      Reset
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        ) : (
          <div className="space-y-6">
            <div 
              data-scan="form sinkronisasi sakti"
              className="rounded-[24px] border border-white/50 bg-white/65 p-6 shadow-[0_10px_40px_-15px_rgba(0,0,0,0.08)] dark:border-white/10 dark:bg-white/[0.04]"
            >
              <div className="max-w-2xl space-y-6">
                <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                  <div className="space-y-2">
                    <label className="text-[11px] uppercase tracking-widest text-gray-500 dark:text-gray-400">
                      Tahun Anggaran
                    </label>
                    <input
                      type="number"
                      value={yearSakti}
                      onChange={(e) => setYearSakti(e.target.value)}
                      className="h-10 w-full rounded-xl border border-white/50 bg-white/70 px-4 text-sm text-gray-900 outline-none transition focus:border-emerald-500 dark:border-white/10 dark:bg-white/[0.04] dark:text-white"
                    />
                  </div>

                  <div className="space-y-2">
                    <label className="text-[11px] uppercase tracking-widest text-gray-500 dark:text-gray-400">
                      Bulan Pencairan
                    </label>
                    <DatePicker 
                      value={dateSakti} 
                      onChange={setDateSakti} 
                      mode="month"
                      className="w-full"
                    />
                  </div>
                </div>

                <button
                  data-scan="tombol cek sakti"
                  onClick={handleCheckSakti}
                  disabled={isCheckingSakti}
                  className="flex h-11 w-full max-w-[200px] items-center justify-center gap-2 rounded-xl bg-emerald-600 font-bold text-white shadow-lg shadow-emerald-500/20 transition hover:bg-emerald-700 disabled:opacity-50"
                >
                  {isCheckingSakti ? (
                    <Loader2 className="h-4 w-4 animate-spin" />
                  ) : (
                    <Search className="h-4 w-4" />
                  )}
                  Cek Data FA SAKTI
                </button>

                {saktiError && (
                  <div className="rounded-xl border border-red-400/30 bg-red-500/5 p-4 text-xs text-red-600">
                    {saktiError}
                  </div>
                )}

                <AnimatePresence>
                  {saktiSummary && (
                    <motion.div
                      initial={{ opacity: 0, y: 10 }}
                      animate={{ opacity: 1, y: 0 }}
                      exit={{ opacity: 0, y: 10 }}
                      className="space-y-6"
                    >
                      <div className="grid grid-cols-1 gap-4 sm:grid-cols-3">
                        <div className="rounded-2xl border border-white/50 bg-white/40 p-4 dark:border-white/10 dark:bg-white/5">
                          <p className="text-[10px] font-bold uppercase tracking-wider text-gray-500">Usage Date</p>
                          <p className="mt-1 font-mono text-sm font-bold text-gray-900 dark:text-white">{saktiSummary.usage_date}</p>
                        </div>
                        <div className="rounded-2xl border border-white/50 bg-white/40 p-4 dark:border-white/10 dark:bg-white/5">
                          <p className="text-[10px] font-bold uppercase tracking-wider text-gray-500">Jumlah Baris</p>
                          <p className="mt-1 text-sm font-bold text-gray-900 dark:text-white">{saktiSummary.row_count} Baris</p>
                        </div>
                        <div className="rounded-2xl border border-white/50 bg-white/40 p-4 dark:border-white/10 dark:bg-white/5">
                          <p className="text-[10px] font-bold uppercase tracking-wider text-gray-500">Total Nilai</p>
                          <p className="mt-1 text-sm font-bold text-emerald-600 dark:text-emerald-400">{fmtCurrency(saktiSummary.total_amount)}</p>
                        </div>
                      </div>

                      <div className="rounded-2xl border border-amber-500/20 bg-amber-500/5 p-4">
                        <div className="flex gap-3">
                          <Info className="h-5 w-5 shrink-0 text-amber-500" />
                          <p className="text-xs leading-relaxed text-amber-800 dark:text-amber-300">
                            Sinkronisasi akan memproses data FA SAKTI untuk bulan yang dipilih dan mencocokkannya dengan item RKK DIPA. Data yang sudah ada untuk bulan ini akan diperbarui.
                          </p>
                        </div>
                      </div>

                      <button
                        data-scan="tombol sinkronisasi sakti"
                        onClick={handleSyncSakti}
                        disabled={isSyncingSakti || saktiSummary.row_count === 0 || !saktiSummary.usage_date}
                        className="flex h-12 w-full items-center justify-center gap-2 rounded-xl bg-emerald-600 font-bold text-white shadow-lg shadow-emerald-500/20 transition hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-40"
                      >
                        {isSyncingSakti ? (
                          <Loader2 className="h-5 w-5 animate-spin" />
                        ) : (
                          <RefreshCw className="h-5 w-5" />
                        )}
                        Sinkronisasi Realisai Sakti
                      </button>

                      {saktiSummary.row_count === 0 && (
                        <p className="text-center text-[11px] font-medium text-amber-600 dark:text-amber-400">
                          Tidak ada data untuk disinkronisasi.
                        </p>
                      )}
                    </motion.div>
                  )}
                </AnimatePresence>

                {saktiSuccess && syncResult && (
                  <motion.div
                    initial={{ opacity: 0, scale: 0.95 }}
                    animate={{ opacity: 1, scale: 1 }}
                    className="space-y-4 rounded-2xl border border-emerald-400/30 bg-emerald-500/5 p-6 text-emerald-600 dark:text-emerald-400"
                  >
                    <div className="flex items-center gap-3">
                      <div className="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-500/10">
                        <CheckCircle2 className="h-6 w-6" />
                      </div>
                      <div>
                        <p className="text-sm font-bold uppercase tracking-wider">Sinkronisasi Berhasil</p>
                        <p className="text-xs opacity-80">Data realisasi SAKTI telah berhasil diintegrasikan.</p>
                      </div>
                    </div>

                    <div className="grid grid-cols-1 gap-3 border-t border-emerald-400/20 pt-4 sm:grid-cols-3">
                      <div>
                        <p className="text-[10px] font-bold uppercase tracking-widest text-emerald-600/60">Usage Date</p>
                        <p className="font-mono text-sm font-bold">{syncResult.usage_date}</p>
                      </div>
                      <div>
                        <p className="text-[10px] font-bold uppercase tracking-widest text-emerald-600/60">Total Baris</p>
                        <p className="text-sm font-bold">{syncResult.row_count} Item</p>
                      </div>
                      <div>
                        <p className="text-[10px] font-bold uppercase tracking-widest text-emerald-600/60">Total Nilai</p>
                        <p className="text-sm font-bold">{fmtCurrency(syncResult.total_amount)}</p>
                      </div>
                    </div>
                  </motion.div>
                )}
              </div>
            </div>
          </div>
        )}
      </motion.div>

      {/* Recent history preview (Always visible or only in manual? User didn't specify, but usually shared) */}
      <div 
        data-scan="transaksi terakhir"
        className="rounded-[24px] border border-white/50 bg-white/65 p-5 shadow-[0_10px_40px_-15px_rgba(0,0,0,0.08)] dark:border-white/10 dark:bg-white/[0.04]"
      >
        <h3 className="mb-4 text-sm font-bold text-gray-900 dark:text-white">
          Transaksi Terakhir
        </h3>
        {historyLoading ? (
          <LoadingSkeleton variant="table" message="Memuat riwayat..." />
        ) : historyError ? (
          <ApiErrorBoundary
            error={historyError}
            onRetry={() => historyRefetch()}
            title="Gagal Memuat Riwayat"
          />
        ) : recentHistory.length === 0 ? (
          <p className="text-center text-sm text-gray-400">
            Belum ada transaksi pencairan.
          </p>
        ) : (
          <div className="space-y-2">
            {recentHistory.map((item, index) => (
              <motion.div
                key={item.id}
                initial={{ opacity: 0, x: -8 }}
                animate={{ opacity: 1, x: 0 }}
                transition={{ delay: index * 0.04 }}
                className="flex items-center justify-between rounded-[16px] border border-white/50 bg-gray-50/60 px-4 py-3 dark:border-white/10 dark:bg-white/[0.03]"
              >
                <div className="space-y-0.5">
                  <p className="font-mono text-[11px] text-gray-500 dark:text-gray-400">
                    {item.budget_item_key}
                  </p>
                  <p
                    className="text-[13px] font-medium text-gray-900 dark:text-white"
                    dangerouslySetInnerHTML={{ __html: item.usage_description ?? item.description ?? '—' }}
                  />
                </div>
                <div className="text-right">
                  <p className="text-[13px] font-bold text-emerald-600 dark:text-emerald-400">
                    {fmtCurrency(item.amount_spent)}
                  </p>
                  <p className="text-[11px] text-gray-500">{item.usage_date}</p>
                </div>
              </motion.div>
            ))}
          </div>
        )}
      </div>
    </div>
  );
}
