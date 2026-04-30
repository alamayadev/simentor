import React, { useEffect, useMemo, useState } from 'react';
import { Download, Layers, TriangleAlert, CircleCheck, TrendingUp } from 'lucide-react';
import { PageHeader } from '../components/PageHeader';
import { apiGet, type ApiError } from '../lib/api';
import { usePagination } from '../hooks/usePagination';

const monitoringTabs = [
  { id: 'tanpa_spk', label: 'Tanpa SPK' },
  { id: 'tanpa_bast', label: 'Tanpa BAST' },
  { id: 'diatas_4jt', label: 'Diatas 4JT' },
] as const;

type MonitoringTabId = (typeof monitoringTabs)[number]['id'];

type MonitoringItem = {
  id?: number;
  mitra_id?: number;
  bln_bayar?: string | null;
  no_sk?: string | null;
  tgl_sk?: string | null;
  no_bast?: string | null;
  tgl_bast?: string | null;
  total?: string | number | null;
  mitra?: {
    nama_lengkap?: string | null;
    posisi?: string | null;
  } | null;
  nama_lengkap?: string | null;
  nama?: string | null;
  posisi?: string | null;
  posisi_daftar?: string | null;
  bulan?: string | null;
  nilai?: string | number | null;
  nominal?: string | number | null;
};

type MonitoringResponse = {
  success: boolean;
  message: string;
  data: MonitoringItem[];
  listbln?: string[];
  links?: {
    next_cursor?: string | null;
    prev_cursor?: string | null;
    next_page_url?: string | null;
    prev_page_url?: string | null;
    next?: string | null;
    path?: string | null;
  };
  pagination_info?: {
    total_page?: number;
    total_records?: number;
  };
};

function getNestedValue(item: MonitoringItem, path: string) {
  const parts = path.split('.');
  let current: unknown = item;
  for (const part of parts) {
    if (!current || typeof current !== 'object') {
      return undefined;
    }
    current = (current as Record<string, unknown>)[part];
  }
  return current;
}

function getValue(item: MonitoringItem, keys: string[]) {
  for (const key of keys) {
    const value = key.includes('.') ? getNestedValue(item, key) : item[key as keyof MonitoringItem];
    if (value !== undefined && value !== null && value !== '') {
      return value as string | number;
    }
  }
  return '-';
}

function formatCurrency(value: string | number) {
  const numeric = typeof value === 'number' ? value : Number(String(value).replace(/[^\d.-]/g, ''));
  if (Number.isNaN(numeric)) {
    return String(value);
  }

  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
  }).format(numeric);
}

export function KontraktualMonitoring() {
  const [activeTab, setActiveTab] = useState<MonitoringTabId>('tanpa_spk');
  const [items, setItems] = useState<MonitoringItem[]>([]);
  const [bulanOptions, setBulanOptions] = useState<string[]>([]);
  const [selectedBulan, setSelectedBulan] = useState('');
  const pagination = usePagination(10);
  const [nextCursor, setNextCursor] = useState<string | null>(null);
  const [isLoading, setIsLoading] = useState(false);
  const [errorMessage, setErrorMessage] = useState<string | null>(null);

  useEffect(() => {
    let ignore = false;

    const fetchMonitoring = async () => {
      setIsLoading(true);
      setErrorMessage(null);
      try {
        const params: Record<string, string | number> = {
          type: activeTab,
          ...pagination.params,
        };
        if (selectedBulan) {
          params.selectedbln = selectedBulan;
        }

        const response = await apiGet<MonitoringItem[]>('/kantor/spk/monitoring', params);
        const typedResponse = response as unknown as MonitoringResponse;

        if (ignore) return;

        setItems(typedResponse.data ?? []);
        setBulanOptions(typedResponse.listbln ?? []);
        pagination.sync(typedResponse);
        const nc = typedResponse.links?.next_cursor || typedResponse.links?.next || typedResponse.links?.next_page_url || null;
        setNextCursor(nc);
      } catch (error) {
        if (ignore) return;
        const apiError = error as ApiError;
        setErrorMessage(apiError.message ?? 'Gagal memuat data monitoring SPK.');
      } finally {
        if (!ignore) {
          setIsLoading(false);
        }
      }
    };

    void fetchMonitoring();

    return () => {
      ignore = true;
    };
  }, [activeTab, pagination.params, selectedBulan]);

  useEffect(() => {
    pagination.reset();
  }, [activeTab, selectedBulan]);

  const stats = useMemo(
    () => ({
      total: pagination.meta?.total || 0,
      tampil: items.length,
      tanpaDokumen: items.filter((item) => !item.no_sk || !item.no_bast).length,
      bernilaiBesar: items.filter((item) => {
        const raw = getValue(item, ['total', 'nilai', 'nominal']);
        return raw !== '-' && Number(raw) > 4000000;
      }).length,
    }),
    [items, pagination.totalRecords]
  );

  return (
    <div className="space-y-6">
      <PageHeader
        title="Monitoring Kontrak"
        description="Pantau penugasan yang belum memiliki SPK atau BAST."
        actions={
          <button 
            data-scan="tombol ekspor"
            className="flex items-center gap-2 px-3.5 py-2 rounded-full bg-white/70 dark:bg-white/5 glass border border-white/50 dark:border-white/10 text-sm font-medium hover:bg-white/90 dark:hover:bg-white/10 transition"
          >
            <Download className="h-4 w-4" />
            Ekspor XLSX
          </button>
        }
      />
      <div 
        data-scan="ringkasan statistik"
        className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4"
      >
        {[
          { label: 'Total Data', value: stats.total, color: 'text-sky-500', icon: Layers },
          { label: 'Tampil Saat Ini', value: stats.tampil, color: 'text-blue-500', icon: TrendingUp },
          { label: 'Tanpa Dokumen', value: stats.tanpaDokumen, color: 'text-amber-500', icon: TriangleAlert },
          { label: 'Di Atas 4JT', value: stats.bernilaiBesar, color: 'text-emerald-500', icon: CircleCheck },
        ].map((item, index) => {
          const Icon = item.icon;
          return (
            <div
              key={item.label}
              className="rounded-[20px] border border-white/50 bg-white/60 p-5 shadow-sm shadow-black/5 dark:border-white/10 dark:bg-white/[0.03] glass-strong"
            >
              <div className="mb-2 flex items-center justify-between">
                <span className="text-[11px] font-bold uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">
                  {item.label}
                </span>
                <Icon className={`h-4 w-4 ${item.color}`} />
              </div>
              <div className="text-2xl font-black text-gray-900 dark:text-white">
                {item.value}
              </div>
            </div>
          );
        })}
      </div>

      {errorMessage && (
        <div className="rounded-2xl border border-rose-200 bg-rose-50/50 px-4 py-3 text-sm text-rose-600 dark:border-rose-500/40 dark:bg-rose-500/10 dark:text-rose-300 glass">
          {errorMessage}
        </div>
      )}

      <div className="bg-white/65 dark:bg-white/[0.04] glass-strong rounded-[24px] border border-white/50 dark:border-white/10 shadow-[0_10px_40_px_-15px_rgba(0,0,0,0.08)] overflow-hidden">
        {/* Tabs and Filter Header */}
        <div 
          data-scan="tab tipe monitoring"
          className="flex flex-wrap items-center justify-between border-b border-gray-200/70 p-1 dark:border-white/10 bg-white/30 dark:bg-black/10"
        >
          <div className="flex">
            {monitoringTabs.map((tab) => {
              const active = activeTab === tab.id;
              return (
                <button
                  key={tab.id}
                  type="button"
                  data-scan={`tab ${tab.label.toLowerCase()}`}
                  onClick={() => {
                    setActiveTab(tab.id);
                    setSelectedBulan('');
                    pagination.reset();
                  }}
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

          <div 
            data-scan="filter bulan"
            className="flex items-center gap-3 px-4 py-2"
          >
            <span className="text-[10px] font-bold uppercase tracking-widest text-gray-400">
              Filter Bulan
            </span>
            <select
              value={selectedBulan}
              onChange={(event) => {
                setSelectedBulan(event.target.value);
                pagination.reset();
              }}
              className="h-8 min-w-[120px] rounded-lg border border-white/50 bg-white/70 px-3 text-[11px] font-bold text-gray-700 outline-none transition focus:border-sky-500 dark:border-white/10 dark:bg-white/[0.04] dark:text-gray-300 dark:[color-scheme:dark]"
            >
              <option value="">Semua Bulan</option>
              {bulanOptions.map((bulan) => (
                <option key={bulan} value={bulan}>
                  {bulan}
                </option>
              ))}
            </select>
          </div>
        </div>
        <div 
          data-scan="tabel monitoring kontrak"
          className="overflow-x-auto"
        >
          <table className="w-full text-[13px]">
            <thead>
              <tr className="text-left text-[11.5px] text-gray-500 dark:text-gray-400 border-b border-gray-200/60 dark:border-white/10">
                {['Nama', 'Posisi', 'Bulan Bayar', 'Total', 'SPK/BAST'].map((header) => (
                  <th
                    key={header}
                    className="px-6 py-5 font-bold uppercase tracking-widest bg-gray-50/30 dark:bg-white/5 whitespace-nowrap"
                  >
                    {header}
                  </th>
                ))}
              </tr>
            </thead>
            <tbody className="divide-y divide-gray-100/70 dark:divide-white/5">
              {isLoading ? (
                <tr>
                  <td colSpan={5} className="px-6 py-12 text-center text-sm text-gray-500 dark:text-gray-400">
                    Memuat data monitoring...
                  </td>
                </tr>
              ) : items.length === 0 ? (
                <tr>
                  <td colSpan={5} className="px-6 py-12 text-center text-sm text-gray-500 dark:text-gray-400">
                    Belum ada data monitoring.
                  </td>
                </tr>
              ) : (
                items.map((item, index) => {
                  const key = item.mitra_id ?? item.id ?? index;
                  const totalValue = getValue(item, ['total', 'nilai', 'nominal']);
                  const spkBastValue =
                    activeTab === 'tanpa_bast'
                      ? getValue(item, ['no_sk'])
                      : getValue(item, ['no_sk', 'no_bast']);

                  return (
                    <tr key={String(key)} className="hover:bg-white/40 dark:hover:bg-white/[0.02] transition-colors">
                      <td className="px-6 py-4 font-medium text-gray-900 dark:text-white">
                        {getValue(item, ['mitra.nama_lengkap', 'nama_lengkap', 'nama'])}
                      </td>
                      <td className="px-6 py-4 text-gray-600 dark:text-gray-300">
                        {getValue(item, ['mitra.posisi', 'posisi', 'posisi_daftar'])}
                      </td>
                      <td className="px-6 py-4 text-gray-600 dark:text-gray-300">
                        {getValue(item, ['bln_bayar', 'bulan'])}
                      </td>
                      <td className="px-6 py-4 font-semibold text-gray-900 dark:text-white">
                        {totalValue === '-' ? '-' : formatCurrency(totalValue)}
                      </td>
                      <td className="px-6 py-4 text-gray-600 dark:text-gray-300">
                        {spkBastValue}
                      </td>
                    </tr>
                  );
                })
              )}
            </tbody>
          </table>
        </div>

        <div 
          data-scan="navigasi halaman"
          className="flex items-center justify-between px-6 py-5 border-t border-gray-200/60 dark:border-white/10 dark:bg-white/5"
        >
          <p className="text-[12px] text-gray-500 dark:text-gray-400 font-medium">
            Halaman <span className="text-gray-900 dark:text-white font-bold">{pagination.page}</span> dari{' '}
            <span className="text-gray-900 dark:text-white font-bold">{pagination.meta?.lastPage || 1}</span>
            <span className="mx-2 text-gray-300 dark:text-white/10">|</span>
            Total <span className="text-gray-900 dark:text-white font-bold">{pagination.meta?.total || 0}</span> data
          </p>
          <div className="flex gap-2">
            <button
              data-scan="tombol halaman sebelumnya"
              type="button"
              onClick={() => pagination.handlePrev()}
              disabled={!pagination.canGoPrev || isLoading}
              className="px-4 py-2 text-[11px] font-bold rounded-full bg-white/70 dark:bg-white/10 border border-white/50 dark:border-white/20 text-gray-700 dark:text-gray-300 hover:bg-white transition-all disabled:opacity-30 disabled:cursor-not-allowed shadow-sm"
            >
              Sebelumnya
            </button>
            <button
              data-scan="tombol halaman berikutnya"
              type="button"
              onClick={() => pagination.handleNext(nextCursor)}
              disabled={!pagination.canGoNext || isLoading}
              className="px-4 py-2 text-[11px] font-bold rounded-full bg-white/70 dark:bg-white/10 border border-white/50 dark:border-white/20 text-gray-700 dark:text-gray-300 hover:bg-white transition-all disabled:opacity-30 disabled:cursor-not-allowed shadow-sm"
            >
              Berikutnya
            </button>
          </div>
        </div>
      </div>
    </div>
  );
}
