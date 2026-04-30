import React, { useMemo, useState, useCallback, useEffect } from 'react';
import {
  Calendar,
  FileSpreadsheet,
  List,
  Save,
  Search,
  Plus,
  Pencil,
  Info,
  Loader2,
  CheckCircle2,
  Trash2,
  X,
  Download,
} from 'lucide-react';
import { motion, AnimatePresence } from 'motion/react';
import { PageHeader } from '../components/PageHeader';
import { LoadingSkeleton } from '../components/LoadingSkeleton';
import { ApiErrorBoundary } from '../components/ApiErrorBoundary';
import { ConfirmDialog } from '../components/ConfirmDialog';
import { useApiQuery, useApiMutation } from '../hooks/useApi';
import { useDebounce } from '../hooks/useDebounce';
import { dipaBudgetService } from '../lib/api-services';
import type { DipaBudgetItem, DipaBudgetPlan } from '../types/api';

// ── Formatters ───────────────────────────────────────────────
const fmtCurrency = (value: number) =>
  new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(value);

const fmtMonth = (value: string) => {
  if (!value) return '—';

  const normalizedValue = value.trim();
  let dateValue: Date | null = null;

  if (/^\d{4}-\d{2}$/.test(normalizedValue)) {
    dateValue = new Date(`${normalizedValue}-01T00:00:00`);
  } else if (/^\d{4}-\d{2}-\d{2}$/.test(normalizedValue)) {
    dateValue = new Date(`${normalizedValue}T00:00:00`);
  } else {
    const parsed = new Date(normalizedValue);
    if (!Number.isNaN(parsed.getTime())) {
      dateValue = parsed;
    }
  }

  if (!dateValue || Number.isNaN(dateValue.getTime())) {
    return normalizedValue;
  }

  return new Intl.DateTimeFormat('id-ID', { month: 'long', year: 'numeric' }).format(dateValue);
};

const toMonthInput = (value: string) => {
  if (!value) return '';
  const normalizedValue = value.trim();
  return /^\d{4}-\d{2}/.test(normalizedValue) ? normalizedValue.slice(0, 7) : normalizedValue;
};

const MONTH_COLUMNS = ['JAN', 'FEB', 'MAR', 'APR', 'MEI', 'JUN', 'JUL', 'AGU', 'SEP', 'OKT', 'NOV', 'DES'] as const;

const getDateParts = (value: string) => {
  const normalizedValue = value.trim();

  if (/^\d{4}-\d{2}$/.test(normalizedValue)) {
    const [year, month] = normalizedValue.split('-');
    return { year: Number(year), monthIndex: Number(month) - 1 };
  }

  if (/^\d{4}-\d{2}-\d{2}$/.test(normalizedValue)) {
    const [year, month] = normalizedValue.split('-');
    return { year: Number(year), monthIndex: Number(month) - 1 };
  }

  const parsed = new Date(normalizedValue);
  if (Number.isNaN(parsed.getTime())) {
    return null;
  }

  return { year: parsed.getFullYear(), monthIndex: parsed.getMonth() };
};

type ViewMode = 'input' | 'list';
type ListDisplayMode = 'monthly' | 'yearly';
type PlanEditForm = {
  id: number;
  planned_amount: string;
  target_month: string;
  description: string;
};

// ── Component ─────────────────────────────────────────────────
export function RkkDipaPerencanaan() {
  const [activeTab, setActiveTab] = useState<ViewMode>('input');
  const currentYear = new Date().getFullYear();
  const currentMonth = new Date().toISOString().slice(0, 7);

  // Input tab state
  const [search, setSearch] = useState('');
  const [selectedItem, setSelectedItem] = useState<DipaBudgetItem | null>(null);
  const [amount, setAmount] = useState('');
  const [description, setDescription] = useState('');
  const [targetMonth, setTargetMonth] = useState(
    new Date().toISOString().slice(0, 7),
  ); // YYYY-MM
  const [showSuccess, setShowSuccess] = useState(false);
  const [editingPlan, setEditingPlan] = useState<PlanEditForm | null>(null);
  const [deleteTarget, setDeleteTarget] = useState<DipaBudgetPlan | null>(null);

  // List tab filter
  const [listDisplayMode, setListDisplayMode] = useState<ListDisplayMode>('yearly');
  const [filterMonth, setFilterMonth] = useState(currentMonth);
  const [filterYear, setFilterYear] = useState(String(currentYear));
  const [monthlyPage, setMonthlyPage] = useState(1);
  const [isExporting, setIsExporting] = useState(false);
  const monthlyPerPage = 20;

  const debouncedQ = useDebounce(search, 400);
  const canSearch = debouncedQ.trim().length >= 3;

  // ── Queries ──────────────────────────────────────────────────
  const {
    data: pickerResp,
    isLoading: pickerLoading,
    error: pickerError,
  } = useApiQuery(
    ['dipa-picker-perencanaan', debouncedQ],
    () => dipaBudgetService.getPicker(debouncedQ),
    { enabled: canSearch },
  );

  const {
    data: plansResp,
    isLoading: plansLoading,
    error: plansError,
    refetch: plansRefetch,
  } = useApiQuery(
    ['dipa-plans', listDisplayMode, filterMonth, monthlyPage, monthlyPerPage],
    () =>
      dipaBudgetService.getPlans(
        listDisplayMode === 'monthly'
          ? { month: filterMonth || undefined, page: monthlyPage, per_page: monthlyPerPage }
          : undefined,
      ),
  );

  // ── Mutation ─────────────────────────────────────────────────
  const saveMutation = useApiMutation(
    (payload: {
      budget_item_key: string;
      planned_amount: number;
      target_month: string;
      description?: string;
    }) =>
      dipaBudgetService.recordPlan(payload),
    {
      invalidateKeys: [['dipa-plans'], ['dipa-monitoring']],
      onSuccess: () => {
        setShowSuccess(true);
        setSelectedItem(null);
        setAmount('');
        setDescription('');
        setSearch('');
        setTimeout(() => {
          setShowSuccess(false);
          setActiveTab('list');
        }, 1200);
      },
    },
  );

  const updateMutation = useApiMutation(
    (payload: { id: number; data: { planned_amount: number; target_month: string; description: string } }) =>
      dipaBudgetService.updatePlan(payload.id, payload.data),
    {
      invalidateKeys: [['dipa-plans'], ['dipa-monitoring']],
      onSuccess: () => setEditingPlan(null),
    },
  );

  const deleteMutation = useApiMutation(
    (id: number) => dipaBudgetService.deletePlan(id),
    {
      invalidateKeys: [['dipa-plans'], ['dipa-monitoring']],
      onSuccess: () => setDeleteTarget(null),
    },
  );

  // ── Derived ──────────────────────────────────────────────────
  const pickerItems: DipaBudgetItem[] = useMemo(
    () => (pickerResp?.data as DipaBudgetItem[]) ?? [],
    [pickerResp],
  );

  const plans: DipaBudgetPlan[] = useMemo(
    () => (plansResp?.data as DipaBudgetPlan[]) ?? [],
    [plansResp],
  );

  const monthlyCurrentPage = plansResp?.meta?.current_page ?? monthlyPage;
  const monthlyLastPage = plansResp?.meta?.last_page ?? 1;
  const monthlyTotalRecords = plansResp?.meta?.total ?? plans.length;
  const monthlyFromRecord = plansResp?.meta?.from ?? (plans.length > 0 ? 1 : 0);
  const monthlyToRecord = plansResp?.meta?.to ?? plans.length;

  const yearlyPlans = useMemo(() => {
    return plans.filter((plan) => {
      const parts = getDateParts(plan.target_month);
      return parts?.year === Number(filterYear);
    });
  }, [filterYear, plans]);

  const totalPlanned = useMemo(
    () => plans.reduce((sum, p) => sum + p.planned_amount, 0),
    [plans],
  );

  const totalPlannedYearly = useMemo(
    () => yearlyPlans.reduce((sum, p) => sum + p.planned_amount, 0),
    [yearlyPlans],
  );

  const availableYears = useMemo(() => {
    const years = new Set<number>([currentYear]);

    plans.forEach((plan) => {
      const parts = getDateParts(plan.target_month);
      if (parts) {
        years.add(parts.year);
      }
    });

    return Array.from(years).sort((a, b) => b - a);
  }, [currentYear, plans]);

  const yearlyRows = useMemo(() => {
    const grouped = new Map<
      string,
      {
        rowKey: string;
        accountLabel: string;
        budget_item_key: string;
        descriptionHtml: string;
        months: number[];
        total: number;
      }
    >();

    yearlyPlans.forEach((plan) => {
      const parts = getDateParts(plan.target_month);
      if (!parts || parts.monthIndex < 0 || parts.monthIndex > 11) return;

      const groupKey = `${plan.account_code ?? plan.budget_item_key}__${plan.budget_item_key}__${plan.description ?? plan.budget_name ?? ''}`;
      const existing = grouped.get(groupKey) ?? {
        rowKey: groupKey,
        accountLabel: plan.account_code ?? plan.budget_item_key,
        budget_item_key: plan.budget_item_key,
        descriptionHtml: plan.description ?? plan.budget_name ?? '—',
        months: Array.from({ length: 12 }, () => 0),
        total: 0,
      };

      existing.months[parts.monthIndex] += plan.planned_amount;
      existing.total += plan.planned_amount;

      grouped.set(groupKey, existing);
    });

    return Array.from(grouped.values()).sort((a, b) =>
      a.accountLabel.localeCompare(b.accountLabel, 'id-ID'),
    );
  }, [yearlyPlans]);

  const yearlyMonthTotals = useMemo(() => {
    const totals = Array.from({ length: 12 }, () => 0);

    yearlyRows.forEach((row) => {
      row.months.forEach((value, index) => {
        totals[index] += value;
      });
    });

    return totals;
  }, [yearlyRows]);

  const parsedAmount = Number(amount);
  const canSave =
    selectedItem !== null && parsedAmount > 0 && targetMonth !== '' && !saveMutation.isPending;

  // ── Handlers ─────────────────────────────────────────────────
  const handleSave = useCallback(() => {
    if (!selectedItem || parsedAmount <= 0 || !targetMonth) return;
    const normalizedDescription = description.trim() || selectedItem.formatted_description;
    saveMutation.mutate({
      budget_item_key: selectedItem.budget_item_key,
      planned_amount: parsedAmount,
      target_month: targetMonth,
      description: normalizedDescription,
    });
  }, [description, selectedItem, parsedAmount, targetMonth, saveMutation]);

  const handleStartEdit = useCallback((plan: DipaBudgetPlan) => {
    setEditingPlan({
      id: plan.id,
      planned_amount: String(plan.planned_amount),
      target_month: toMonthInput(plan.target_month),
      description: plan.description ?? plan.budget_name ?? '',
    });
  }, []);

  const handleSubmitEdit = useCallback(() => {
    if (!editingPlan) return;
    const parsedPlannedAmount = Number(editingPlan.planned_amount);
    if (!Number.isFinite(parsedPlannedAmount) || parsedPlannedAmount <= 0 || !editingPlan.target_month) {
      return;
    }

    updateMutation.mutate({
      id: editingPlan.id,
      data: {
        planned_amount: parsedPlannedAmount,
        target_month: editingPlan.target_month,
        description: editingPlan.description.trim(),
      },
    });
  }, [editingPlan, updateMutation]);

  const handleExport = useCallback(async () => {
    try {
      setIsExporting(true);
      const exportYear =
        listDisplayMode === 'monthly' && filterMonth
          ? new Date(`${filterMonth}-01T00:00:00`).getFullYear()
          : Number(filterYear);
      await dipaBudgetService.exportPlansToExcel(exportYear);
    } catch (error) {
      console.error('Export failed:', error);
      alert('Gagal export data. Silakan coba lagi.');
    } finally {
      setIsExporting(false);
    }
  }, [filterMonth, filterYear, listDisplayMode]);

  useEffect(() => {
    if (listDisplayMode !== 'monthly') return;
    setMonthlyPage(1);
  }, [filterMonth, listDisplayMode]);

  useEffect(() => {
    if (listDisplayMode !== 'monthly') return;
    if (monthlyCurrentPage > monthlyLastPage) {
      setMonthlyPage(monthlyLastPage || 1);
    }
  }, [listDisplayMode, monthlyCurrentPage, monthlyLastPage]);

  // ── Render ───────────────────────────────────────────────────
  return (
    <div className="space-y-6">
      <PageHeader
        title="Perencanaan RKK DIPA"
        description="Kelola input rencana pencairan dari sumber budget RKK DIPA yang tersedia."
        actions={
          <button
            data-scan="tombol tambah rencana"
            onClick={() => setActiveTab('input')}
            className="flex items-center gap-1.5 rounded-full bg-amber-300 px-4 py-2 text-sm font-semibold text-gray-900 shadow-lg shadow-amber-500/20 transition hover:bg-amber-400"
          >
            <Plus className="h-4 w-4" />
            Tambah Rencana
          </button>
        }
      />

      <div className="overflow-hidden rounded-[24px] border border-white/50 bg-white/65 shadow-[0_10px_40px_-15px_rgba(0,0,0,0.08)] dark:border-white/10 dark:bg-white/[0.04]">
        {/* Tabs */}
        <div 
          data-scan="tab kontrol"
          className="flex border-b border-gray-200/70 dark:border-white/10"
        >
          {[
            { key: 'input', label: 'Input Rencana', icon: FileSpreadsheet },
            { key: 'list', label: 'Daftar Rencana', icon: List },
          ].map((tab) => {
            const active = activeTab === tab.key;
            const Icon = tab.icon;
            return (
              <button
                key={tab.key}
                type="button"
                onClick={() => setActiveTab(tab.key as ViewMode)}
                className={`flex items-center gap-2 border-b-2 px-6 py-3 text-xs font-bold transition-all ${
                  active
                    ? 'border-sky-500 bg-sky-500/5 text-sky-600 dark:text-sky-400'
                    : 'border-transparent text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white'
                }`}
              >
                <Icon className="h-4 w-4" />
                {tab.label}
              </button>
            );
          })}
        </div>

        {/* ── Tab: Input ── */}
        {activeTab === 'input' && (
          <div 
            data-scan="form input rencana"
            className="grid grid-cols-1 gap-8 p-5 pt-6 lg:grid-cols-2"
          >
            {/* Picker */}
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
                  value={search}
                  onChange={(e) => setSearch(e.target.value)}
                  className="h-10 w-full rounded-xl border border-white/50 bg-gray-50/80 pl-10 pr-4 text-sm text-gray-900 outline-none transition focus:border-sky-500 dark:border-white/10 dark:bg-white/[0.04] dark:text-white"
                  placeholder="Cari kode atau uraian... (min. 3 karakter)"
                />
              </div>

              <div 
                data-scan="daftar sumber budget"
                className="max-h-[320px] space-y-2 overflow-y-auto pr-1"
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
                    const active =
                      item.budget_item_key === selectedItem?.budget_item_key;
                    return (
                      <motion.button
                        key={item.budget_item_key}
                        type="button"
                        initial={{ opacity: 0, y: 8 }}
                        animate={{ opacity: 1, y: 0 }}
                        transition={{ delay: index * 0.03 }}
                        onClick={() => {
                          setSelectedItem(item);
                          setDescription(item.formatted_description);
                        }}
                        className={`w-full rounded-[18px] border p-4 text-left transition ${
                          active
                            ? 'border-sky-500 bg-sky-500/5 shadow-sm'
                            : 'border-white/50 bg-gray-50/60 hover:bg-white dark:border-white/10 dark:bg-white/[0.03] dark:hover:bg-white/[0.06]'
                        }`}
                      >
                        <div className="flex items-start gap-3">
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

                {!pickerLoading && !canSearch && (
                  <p className="flex items-center gap-1 pt-2 text-[10px] italic text-gray-500 dark:text-gray-400">
                    <Info className="h-3 w-3" />
                    Masukkan minimal 3 karakter untuk mencari
                  </p>
                )}

                {!pickerLoading && canSearch && pickerItems.length === 0 && (
                  <div className="rounded-[18px] border border-dashed border-gray-200 bg-gray-50/60 p-4 text-[13px] text-gray-500 dark:border-white/10 dark:bg-white/[0.03] dark:text-gray-400">
                    Tidak ada sumber budget yang cocok dengan pencarian.
                  </div>
                )}
              </div>
            </div>

            {/* Rincian Rencana */}
            <div className="space-y-6">
              <h3 className="border-b border-gray-200/70 pb-2 text-sm font-bold text-gray-900 dark:border-white/10 dark:text-white">
                2. Rincian Rencana
              </h3>

              {/* Selected item display */}
              <div className="space-y-2">
                <label className="text-[11px] uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">
                  Item Terpilih
                </label>
                <div 
                  data-scan="detail budget terpilih"
                  className="rounded-[18px] border border-dashed border-gray-200 bg-gray-50/60 p-4 dark:border-white/10 dark:bg-white/[0.03]"
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
                    <span className="text-xs italic text-gray-500 dark:text-gray-400">
                      Pilih item dari daftar di samping
                    </span>
                  )}
                </div>
              </div>

              <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div className="space-y-2">
                  <label className="text-[11px] uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">
                    Jumlah Rencana Pencairan (Rp)
                  </label>
                  <input
                    value={amount}
                    onChange={(e) => setAmount(e.target.value)}
                    className="h-11 w-full rounded-xl border border-white/50 bg-white/70 px-4 font-mono text-sm text-gray-900 outline-none transition focus:border-sky-500 dark:border-white/10 dark:bg-white/[0.04] dark:text-white"
                    placeholder="Contoh: 5000000"
                    type="number"
                  />
                </div>

                <div className="space-y-2">
                  <label className="flex items-center gap-1 text-[11px] uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">
                    <Calendar className="h-3.5 w-3.5" />
                    Bulan Rencana
                  </label>
                  {/* Month-mode DatePicker if available, otherwise plain month input */}
                  <input
                    type="month"
                    value={targetMonth}
                    onChange={(e) => setTargetMonth(e.target.value)}
                    className="h-11 w-full rounded-xl border border-white/50 bg-white/70 px-4 text-sm text-gray-900 outline-none transition focus:border-sky-500 dark:border-white/10 dark:bg-white/[0.04] dark:text-white"
                  />
                </div>
              </div>

              <div className="space-y-2">
                <label className="text-[11px] uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">
                  Deskripsi Rencana
                </label>
                <textarea
                  value={description}
                  onChange={(e) => setDescription(e.target.value)}
                  rows={4}
                  className="w-full rounded-xl border border-white/50 bg-white/70 px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-sky-500 dark:border-white/10 dark:bg-white/[0.04] dark:text-white"
                  placeholder="Deskripsi akan terisi dari item budget terpilih, lalu bisa Anda sesuaikan."
                />
              </div>

              {/* Error */}
              {saveMutation.error && (
                <p className="text-[11px] text-red-500">
                  {saveMutation.error.message ?? 'Gagal menyimpan rencana.'}
                </p>
              )}

              {/* Success */}
              <AnimatePresence>
                {showSuccess && (
                  <motion.div
                    initial={{ opacity: 0, y: 6 }}
                    animate={{ opacity: 1, y: 0 }}
                    exit={{ opacity: 0 }}
                    className="flex items-center gap-2 rounded-xl border border-emerald-400/30 bg-emerald-500/5 px-4 py-3 text-[12px] font-medium text-emerald-600 dark:text-emerald-400"
                  >
                    <CheckCircle2 className="h-4 w-4" />
                    Rencana berhasil disimpan.
                  </motion.div>
                )}
              </AnimatePresence>

               <button
                type="button"
                data-scan="tombol simpan rencana"
                onClick={handleSave}
                disabled={!canSave}
                className="flex h-12 w-full items-center justify-center gap-2 rounded-xl bg-sky-600 text-sm font-bold text-white transition hover:bg-sky-700 disabled:cursor-not-allowed disabled:opacity-50"
              >
                {saveMutation.isPending ? (
                  <Loader2 className="h-5 w-5 animate-spin" />
                ) : (
                  <Save className="h-5 w-5" />
                )}
                {saveMutation.isPending ? 'Menyimpan...' : 'Simpan Rencana'}
              </button>
            </div>
          </div>
        )}

        {/* ── Tab: Daftar ── */}
        {activeTab === 'list' && (
          <div 
            data-scan="daftar rencana"
            className="p-5"
          >
            <div className="mb-4 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
              <div className="inline-flex w-full max-w-[280px] items-center rounded-2xl border border-gray-200/60 bg-gray-50/60 p-1 dark:border-white/10 dark:bg-white/[0.03]">
                {[
                  { key: 'monthly', label: 'Bulanan' },
                  { key: 'yearly', label: 'Tahunan' },
                ].map((mode) => {
                  const active = listDisplayMode === mode.key;
                  return (
                    <button
                      key={mode.key}
                      type="button"
                      onClick={() => setListDisplayMode(mode.key as ListDisplayMode)}
                      className={`flex-1 rounded-xl px-4 py-2 text-xs font-bold uppercase tracking-wider transition ${
                        active
                          ? 'bg-white text-sky-600 shadow-sm dark:bg-white/10 dark:text-sky-300'
                          : 'text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white'
                      }`}
                    >
                      {mode.label}
                    </button>
                  );
                })}
              </div>

              <div className="flex flex-wrap items-center gap-3">
                {listDisplayMode === 'monthly' ? (
                  <>
                    <label className="text-[11px] uppercase tracking-widest text-gray-500 dark:text-gray-400">
                      Filter Bulan
                    </label>
                    <input
                      type="month"
                      value={filterMonth}
                      onChange={(e) => setFilterMonth(e.target.value)}
                      className="h-9 rounded-xl border border-white/50 bg-white/70 px-3 text-sm text-gray-900 outline-none transition focus:border-sky-500 dark:border-white/10 dark:bg-white/[0.04] dark:text-white"
                    />
                    {filterMonth && (
                      <button
                        onClick={() => {
                          setFilterMonth(currentMonth);
                          setMonthlyPage(1);
                        }}
                        className="text-[11px] text-gray-400 underline hover:text-gray-700 dark:hover:text-gray-200"
                      >
                        Reset
                      </button>
                    )}
                  </>
                ) : (
                  <>
                    <label className="text-[11px] uppercase tracking-widest text-gray-500 dark:text-gray-400">
                      Filter Tahun
                    </label>
                    <select
                      value={filterYear}
                      onChange={(e) => setFilterYear(e.target.value)}
                      className="h-9 rounded-xl border border-white/50 bg-white/70 px-3 text-sm text-gray-900 outline-none transition focus:border-sky-500 dark:border-white/10 dark:bg-white/[0.04] dark:text-white"
                    >
                      {availableYears.map((year) => (
                        <option key={year} value={year}>
                          {year}
                        </option>
                      ))}
                    </select>
                  </>
                )}
              </div>

              <button
                data-scan="tombol export excel"
                onClick={handleExport}
                disabled={isExporting}
                className="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2 text-xs font-bold text-white transition hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-50"
              >
                {isExporting ? (
                  <>
                    <Loader2 className="h-4 w-4 animate-spin" />
                    Mengexport...
                  </>
                ) : (
                  <>
                    <Download className="h-4 w-4" />
                    Export Excel
                  </>
                )}
              </button>
            </div>

            {plansLoading ? (
              <LoadingSkeleton variant="table" message="Memuat daftar rencana..." />
            ) : plansError ? (
              <ApiErrorBoundary
                error={plansError}
                onRetry={() => plansRefetch()}
                title="Gagal Memuat Rencana"
              />
            ) : (
              <div 
                data-scan="tabel rencana"
                className="overflow-hidden rounded-[20px] border border-white/50 bg-white/40 dark:border-white/10 dark:bg-white/[0.02]"
              >
                  {listDisplayMode === 'monthly' ? (
                    <>
                      <div className="overflow-x-auto">
                        <table className="w-full text-[13px]">
                          <thead>
                            <tr className="border-b border-gray-200/60 text-left text-[11.5px] text-gray-500 dark:border-white/10 dark:text-gray-400">
                              {['Akun / Key', 'Uraian', 'Bulan Rencana', 'Jumlah Rencana', 'Aksi'].map(
                                (h) => (
                                  <th
                                    key={h}
                                    className={`whitespace-nowrap bg-gray-50/30 px-6 py-5 font-bold uppercase tracking-widest dark:bg-white/5 ${
                                      h === 'Jumlah Rencana' || h === 'Aksi' ? 'text-right' : ''
                                    }`}
                                  >
                                    {h}
                                  </th>
                                ),
                              )}
                            </tr>
                          </thead>
                          <tbody className="divide-y divide-gray-100/70 dark:divide-white/5">
                            {plans.length === 0 ? (
                              <tr>
                                <td
                                  colSpan={5}
                                  className="px-6 py-12 text-center text-sm text-gray-400"
                                >
                                  {filterMonth
                                    ? `Tidak ada rencana untuk ${fmtMonth(filterMonth)}.`
                                    : 'Belum ada rencana yang dibuat.'}
                                </td>
                              </tr>
                            ) : (
                              plans.map((item) => (
                                <motion.tr
                                  key={item.id}
                                  initial={{ opacity: 0 }}
                                  animate={{ opacity: 1 }}
                                  className="transition-colors hover:bg-white/40 dark:hover:bg-white/[0.02]"
                                >
                                  <td className="px-6 py-4">
                                    <div className="flex flex-col gap-0.5">
                                      <span className="font-mono text-[12px] font-bold text-gray-900 dark:text-white">
                                        {item.account_code ?? item.budget_item_key}
                                      </span>
                                      <span className="text-[10px] text-gray-400">
                                        {item.budget_item_key}
                                      </span>
                                    </div>
                                  </td>
                                  <td
                                    className="max-w-[260px] px-6 py-4 text-[13px] font-medium text-gray-900 dark:text-white"
                                    dangerouslySetInnerHTML={{ __html: item.description ?? item.budget_name ?? '—' }}
                                  />
                                  <td className="px-6 py-4 text-[13px] text-gray-600 dark:text-gray-400">
                                    {fmtMonth(item.target_month)}
                                  </td>
                                  <td className="px-6 py-4 text-right text-[13px] font-bold text-sky-600 dark:text-sky-400">
                                    {fmtCurrency(item.planned_amount)}
                                  </td>
                                  <td className="px-6 py-4">
                                    <div className="flex items-center justify-end gap-2">
                                      <button
                                        type="button"
                                        onClick={() => handleStartEdit(item)}
                                        className="inline-flex h-9 w-9 items-center justify-center rounded-full border border-sky-200 bg-sky-50 text-sky-600 transition hover:bg-sky-100 dark:border-sky-500/30 dark:bg-sky-500/10 dark:text-sky-300"
                                        title="Edit rencana"
                                      >
                                        <Pencil className="h-4 w-4" />
                                      </button>
                                      <button
                                        type="button"
                                        onClick={() => setDeleteTarget(item)}
                                        className="inline-flex h-9 w-9 items-center justify-center rounded-full border border-red-200 bg-red-50 text-red-600 transition hover:bg-red-100 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-300"
                                        title="Hapus rencana"
                                      >
                                        <Trash2 className="h-4 w-4" />
                                      </button>
                                    </div>
                                  </td>
                                </motion.tr>
                              ))
                            )}
                          </tbody>
                          {plans.length > 0 && (
                            <tfoot>
                              <tr className="border-t border-gray-200/60 bg-gray-50/30 dark:border-white/10 dark:bg-white/5">
                                <td
                                  colSpan={4}
                                  className="px-6 py-4 text-right text-[12px] font-bold uppercase tracking-widest text-gray-500 dark:text-gray-400"
                                >
                                  Total Rencana
                                </td>
                                <td className="px-6 py-4 text-right text-[14px] font-black text-emerald-600 dark:text-emerald-400">
                                  {fmtCurrency(totalPlanned)}
                                </td>
                              </tr>
                            </tfoot>
                          )}
                        </table>
                      </div>
                      {plans.length > 0 && (
                        <div className="flex flex-col gap-3 border-t border-gray-200/60 px-6 py-4 text-sm text-gray-500 dark:border-white/10 dark:text-gray-400 lg:flex-row lg:items-center lg:justify-between">
                          <p>
                            {`Menampilkan ${monthlyFromRecord}-${monthlyToRecord} dari ${monthlyTotalRecords} rencana`}
                          </p>
                          <div className="flex items-center gap-2 self-end lg:self-auto">
                            <button
                              type="button"
                              onClick={() => setMonthlyPage((current) => Math.max(current - 1, 1))}
                              disabled={monthlyCurrentPage <= 1 || plansLoading}
                              className="rounded-full border border-white/50 bg-white/70 px-4 py-2 text-xs font-bold text-gray-700 transition hover:bg-white disabled:cursor-not-allowed disabled:opacity-50 dark:border-white/10 dark:bg-white/[0.06] dark:text-gray-200 dark:hover:bg-white/[0.1]"
                            >
                              Sebelumnya
                            </button>
                            <span className="min-w-[88px] text-center text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                              {`Hal ${monthlyCurrentPage}/${monthlyLastPage}`}
                            </span>
                            <button
                              type="button"
                              onClick={() =>
                                setMonthlyPage((current) => Math.min(current + 1, monthlyLastPage))
                              }
                              disabled={monthlyCurrentPage >= monthlyLastPage || plansLoading}
                              className="rounded-full border border-white/50 bg-white/70 px-4 py-2 text-xs font-bold text-gray-700 transition hover:bg-white disabled:cursor-not-allowed disabled:opacity-50 dark:border-white/10 dark:bg-white/[0.06] dark:text-gray-200 dark:hover:bg-white/[0.1]"
                            >
                              Berikutnya
                            </button>
                          </div>
                        </div>
                      )}
                    </>
                  ) : (
                    <div className="show-horizontal-scrollbar w-full overflow-x-auto overscroll-x-contain pb-2">
                    <table className="min-w-[1500px] text-[13px]">
                      <thead>
                        <tr className="border-b border-gray-200/60 text-left text-[11.5px] text-gray-500 dark:border-white/10 dark:text-gray-400">
                          <th className="whitespace-nowrap bg-gray-50/30 px-6 py-5 font-bold uppercase tracking-widest dark:bg-white/5">
                            Akun / Key
                          </th>
                          <th className="whitespace-nowrap bg-gray-50/30 px-6 py-5 font-bold uppercase tracking-widest dark:bg-white/5">
                            Uraian
                          </th>
                          {MONTH_COLUMNS.map((month) => (
                            <th
                              key={month}
                              className="whitespace-nowrap bg-gray-50/30 px-4 py-5 text-right font-bold uppercase tracking-widest dark:bg-white/5"
                            >
                              {month}
                            </th>
                          ))}
                          <th className="whitespace-nowrap bg-gray-50/30 px-6 py-5 text-right font-bold uppercase tracking-widest dark:bg-white/5">
                            Total
                          </th>
                        </tr>
                      </thead>
                      <tbody className="divide-y divide-gray-100/70 dark:divide-white/5">
                        {yearlyRows.length === 0 ? (
                          <tr>
                            <td
                              colSpan={15}
                              className="px-6 py-12 text-center text-sm text-gray-400"
                            >
                              {`Tidak ada rencana untuk tahun ${filterYear}.`}
                            </td>
                          </tr>
                        ) : (
                          yearlyRows.map((row) => (
                            <motion.tr
                              key={row.rowKey}
                              initial={{ opacity: 0 }}
                              animate={{ opacity: 1 }}
                              className="transition-colors hover:bg-white/40 dark:hover:bg-white/[0.02]"
                            >
                              <td className="px-6 py-4 align-top">
                                <div className="flex flex-col gap-0.5">
                                  <span className="font-mono text-[12px] font-bold text-gray-900 dark:text-white">
                                    {row.accountLabel}
                                  </span>
                                  <span className="text-[10px] text-gray-400">
                                    {row.budget_item_key}
                                  </span>
                                </div>
                              </td>
                              <td
                                className="min-w-[280px] max-w-[380px] px-6 py-4 align-top text-[13px] font-medium text-gray-900 dark:text-white"
                                dangerouslySetInnerHTML={{ __html: row.descriptionHtml }}
                              />
                              {row.months.map((value, index) => (
                                <td
                                  key={`${row.rowKey}-${MONTH_COLUMNS[index]}`}
                                  className="whitespace-nowrap px-4 py-4 text-right text-[12px] font-semibold text-gray-700 dark:text-gray-300"
                                >
                                  {value > 0 ? fmtCurrency(value) : '—'}
                                </td>
                              ))}
                              <td className="px-6 py-4 text-right text-[13px] font-bold text-sky-600 dark:text-sky-400">
                                {fmtCurrency(row.total)}
                              </td>
                            </motion.tr>
                          ))
                        )}
                      </tbody>
                      {yearlyRows.length > 0 && (
                        <tfoot>
                          <tr className="border-t border-gray-200/60 bg-gray-50/30 dark:border-white/10 dark:bg-white/5">
                            <td
                              colSpan={2}
                              className="px-6 py-4 text-right text-[12px] font-bold uppercase tracking-widest text-gray-500 dark:text-gray-400"
                            >
                              Total Rencana {filterYear}
                            </td>
                            {yearlyMonthTotals.map((value, index) => (
                              <td
                                key={`total-${MONTH_COLUMNS[index]}`}
                                className="whitespace-nowrap px-4 py-4 text-right text-[12px] font-bold text-gray-700 dark:text-gray-300"
                              >
                                {value > 0 ? fmtCurrency(value) : '—'}
                              </td>
                            ))}
                            <td className="px-6 py-4 text-right text-[14px] font-black text-emerald-600 dark:text-emerald-400">
                              {fmtCurrency(totalPlannedYearly)}
                            </td>
                          </tr>
                        </tfoot>
                      )}
                    </table>
                    </div>
                  )}
              </div>
            )}
          </div>
        )}
      </div>

      {editingPlan && (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4">
          <div
            className="absolute inset-0 bg-black/40 backdrop-blur-sm"
            onClick={() => !updateMutation.isPending && setEditingPlan(null)}
          />
          <div className="relative z-10 w-full max-w-2xl rounded-[24px] border border-white/50 bg-white p-6 shadow-2xl dark:border-white/10 dark:bg-gray-900">
            <button
              type="button"
              onClick={() => setEditingPlan(null)}
              disabled={updateMutation.isPending}
              className="absolute right-4 top-4 rounded-lg p-2 text-gray-400 transition hover:bg-gray-100 hover:text-gray-700 disabled:opacity-50 dark:hover:bg-white/10 dark:hover:text-white"
            >
              <X className="h-4 w-4" />
            </button>

            <div className="space-y-6">
              <div>
                <h3 className="text-lg font-bold text-gray-900 dark:text-white">Edit Rencana</h3>
                <p className="mt-1 text-sm text-gray-500 dark:text-gray-400">
                  Perbarui bulan target, jumlah, dan deskripsi rencana pencairan.
                </p>
              </div>

              <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div className="space-y-2">
                  <label className="text-[11px] uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">
                    Jumlah Rencana Pencairan (Rp)
                  </label>
                  <input
                    type="number"
                    value={editingPlan?.planned_amount ?? ''}
                    onChange={(e) =>
                      setEditingPlan((current) =>
                        current ? { ...current, planned_amount: e.target.value } : current,
                      )
                    }
                    className="h-11 w-full rounded-xl border border-white/50 bg-white/70 px-4 font-mono text-sm text-gray-900 outline-none transition focus:border-sky-500 dark:border-white/10 dark:bg-white/[0.04] dark:text-white"
                  />
                </div>

                <div className="space-y-2">
                  <label className="flex items-center gap-1 text-[11px] uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">
                    <Calendar className="h-3.5 w-3.5" />
                    Bulan Rencana
                  </label>
                  <input
                    type="month"
                    value={editingPlan?.target_month ?? ''}
                    onChange={(e) =>
                      setEditingPlan((current) =>
                        current ? { ...current, target_month: e.target.value } : current,
                      )
                    }
                    className="h-11 w-full rounded-xl border border-white/50 bg-white/70 px-4 text-sm text-gray-900 outline-none transition focus:border-sky-500 dark:border-white/10 dark:bg-white/[0.04] dark:text-white"
                  />
                </div>
              </div>

              <div className="space-y-2">
                <label className="text-[11px] uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">
                  Deskripsi Rencana
                </label>
                <textarea
                  value={editingPlan?.description ?? ''}
                  onChange={(e) =>
                    setEditingPlan((current) =>
                      current ? { ...current, description: e.target.value } : current,
                    )
                  }
                  rows={6}
                  className="w-full rounded-xl border border-white/50 bg-white/70 px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-sky-500 dark:border-white/10 dark:bg-white/[0.04] dark:text-white"
                />
              </div>

              {updateMutation.error && (
                <p className="text-[11px] text-red-500">
                  {(updateMutation.error as any)?.message ?? 'Gagal memperbarui rencana.'}
                </p>
              )}

              <div className="flex items-center justify-end gap-3">
                <button
                  type="button"
                  onClick={() => setEditingPlan(null)}
                  disabled={updateMutation.isPending}
                  className="rounded-full border border-white/50 bg-white/70 px-4 py-2 text-xs font-bold text-gray-700 transition hover:bg-white disabled:opacity-50 dark:border-white/10 dark:bg-white/[0.06] dark:text-gray-200 dark:hover:bg-white/[0.1]"
                >
                  Batal
                </button>
                <button
                  type="button"
                  onClick={handleSubmitEdit}
                  disabled={updateMutation.isPending}
                  className="inline-flex items-center gap-2 rounded-full bg-sky-600 px-4 py-2 text-xs font-bold text-white transition hover:bg-sky-700 disabled:opacity-50"
                >
                  {updateMutation.isPending ? (
                    <Loader2 className="h-4 w-4 animate-spin" />
                  ) : (
                    <Save className="h-4 w-4" />
                  )}
                  {updateMutation.isPending ? 'Menyimpan...' : 'Simpan Perubahan'}
                </button>
              </div>
            </div>
          </div>
        </div>
      )}

      <ConfirmDialog
        open={deleteTarget !== null}
        onClose={() => setDeleteTarget(null)}
        onConfirm={() => {
          if (!deleteTarget) return;
          deleteMutation.mutate(deleteTarget.id);
        }}
        title="Hapus Rencana"
        message={`Rencana untuk ${deleteTarget ? fmtMonth(deleteTarget.target_month) : ''} akan dihapus permanen.`}
        confirmLabel="Hapus"
        cancelLabel="Batal"
        variant="danger"
        isLoading={deleteMutation.isPending}
      />
    </div>
  );
}
