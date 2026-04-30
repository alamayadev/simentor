import React, { useState, useMemo } from 'react';
import {
  Calendar as CalendarIcon,
  ChevronLeft,
  ChevronRight,
  Clock,
  BarChart3,
  Users,
} from 'lucide-react';
import { motion, AnimatePresence } from 'motion/react';
import { useApiQuery } from '../hooks/useApi';
import { PageHeader } from '../components/PageHeader';
import { LoadingSkeleton } from '../components/LoadingSkeleton';
import { ApiErrorBoundary } from '../components/ApiErrorBoundary';
import { kegiatanService } from '../lib/api-services';
import type { KegiatanCalendarItem } from '../types/api';

type TabKey = 'berjalan' | 'akan_datang' | 'selesai';

const TABS: { key: TabKey; label: string }[] = [
  { key: 'berjalan', label: 'Berjalan' },
  { key: 'akan_datang', label: 'Akan Datang' },
  { key: 'selesai', label: 'Selesai' },
];

const BULAN = [
  'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
  'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember',
];

const fmtDate = (d: string): string => {
  const date = new Date(d);
  return date.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
};

const fungsiColors: Record<string, string> = {
  'Umum': 'bg-gray-500',
  'Distribusi': 'bg-amber-500',
  'Produksi': 'bg-blue-500',
  'Sosial': 'bg-emerald-500',
  'Nerwilis': 'bg-purple-500',
  'IPDS': 'bg-orange-500',
};

export function KegiatanKalender() {
  const today = new Date();
  const [currentMonth, setCurrentMonth] = useState(today.getMonth());
  const [currentYear, setCurrentYear] = useState(today.getFullYear());
  const [activeTab, setActiveTab] = useState<TabKey>('berjalan');
  const [page, setPage] = useState(1);
  const [perPage, setPerPage] = useState(10);

  // Fetch calendar data
  const { data: response, isLoading, error, refetch } = useApiQuery(
    ['kegiatan-calendar', currentYear, currentMonth],
    () => kegiatanService.calendar({
      year: String(currentYear),
      month: String(currentMonth + 1).padStart(2, '0'),
    }),
  );

  // Extract the three arrays from response
  const calendarData = useMemo(() => {
    if (!response?.data) return { berjalan: [], akan_datang: [], selesai: [] };
    const d = response.data as any;
    return {
      berjalan: (d.kegiatan_berjalan || []) as KegiatanCalendarItem[],
      akan_datang: (d.kegiatan_akan_datang || []) as KegiatanCalendarItem[],
      selesai: (d.kegiatan_sudah_selesai || []) as KegiatanCalendarItem[],
    };
  }, [response]);

  // Current tab items
  const currentItems = useMemo(() => {
    return calendarData[activeTab] || [];
  }, [calendarData, activeTab]);

  // Pagination
  const totalItems = currentItems.length;
  const totalPages = Math.max(1, Math.ceil(totalItems / perPage));
  const safePage = Math.min(page, totalPages);
  const from = totalItems === 0 ? 0 : (safePage - 1) * perPage + 1;
  const to = Math.min(safePage * perPage, totalItems);
  const paginatedItems = currentItems.slice((safePage - 1) * perPage, safePage * perPage);

  // Reset page when tab changes
  const handleTabChange = (tab: TabKey) => {
    setActiveTab(tab);
    setPage(1);
  };

  const handlePerPageChange = (newPerPage: number) => {
    setPerPage(newPerPage);
    setPage(1);
  };

  const prevMonth = () => {
    if (currentMonth === 0) {
      setCurrentMonth(11);
      setCurrentYear((y) => y - 1);
    } else {
      setCurrentMonth((m) => m - 1);
    }
    setPage(1);
  };

  const nextMonth = () => {
    if (currentMonth === 11) {
      setCurrentMonth(0);
      setCurrentYear((y) => y + 1);
    } else {
      setCurrentMonth((m) => m + 1);
    }
    setPage(1);
  };

  const tabCounts = useMemo(() => ({
    berjalan: calendarData.berjalan.length,
    akan_datang: calendarData.akan_datang.length,
    selesai: calendarData.selesai.length,
  }), [calendarData]);

  return (
    <div className="space-y-6">
      <PageHeader
        title="Kalender Kegiatan"
        description="Visualisasi jadwal kegiatan berdasarkan status pelaksanaan."
      />

      {/* Month Navigation */}
      <div 
        data-scan="navigasi bulan"
        className="bg-white/65 dark:bg-white/[0.04] glass-strong rounded-[24px] border border-white/50 dark:border-white/10 shadow-[0_10px_40px_-15px_rgba(0,0,0,0.08)] overflow-hidden"
      >
        <div className="flex items-center justify-between px-6 py-4 border-b border-gray-200/60 dark:border-white/10">
          <button
            data-scan="tombol bulan sebelumnya"
            onClick={prevMonth}
            className="p-2 rounded-xl hover:bg-gray-100 dark:hover:bg-white/10 transition"
          >
            <ChevronLeft className="h-5 w-5 text-gray-600 dark:text-gray-400" />
          </button>
          <h2 className="text-lg font-bold text-gray-900 dark:text-white">
            {BULAN[currentMonth]} {currentYear}
          </h2>
          <button
            data-scan="tombol bulan berikutnya"
            onClick={nextMonth}
            className="p-2 rounded-xl hover:bg-gray-100 dark:hover:bg-white/10 transition"
          >
            <ChevronRight className="h-5 w-5 text-gray-600 dark:text-gray-400" />
          </button>
        </div>

        {/* Tabs */}
        <div 
          data-scan="tab status kegiatan"
          className="flex gap-1 px-4 pt-4 pb-2"
        >
          {TABS.map((tab) => (
            <button
              key={tab.key}
              data-scan={`tab status ${tab.label.toLowerCase()}`}
              onClick={() => handleTabChange(tab.key)}
              className={`px-4 py-2 rounded-xl text-[13px] font-semibold transition-all ${
                activeTab === tab.key
                  ? 'bg-orange-500 text-white shadow-md shadow-orange-500/25'
                  : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-white/10'
              }`}
            >
              {tab.label}
              <span className={`ml-2 text-[11px] px-1.5 py-0.5 rounded-md ${
                activeTab === tab.key
                  ? 'bg-white/20 text-white'
                  : 'bg-gray-100 dark:bg-white/10 text-gray-500 dark:text-gray-400'
              }`}>
                {tabCounts[tab.key]}
              </span>
            </button>
          ))}
        </div>

        {/* Content */}
        <div className="p-4">
          {isLoading ? (
            <LoadingSkeleton variant="table" message="Memuat data kalender..." />
          ) : error ? (
            <ApiErrorBoundary error={error} onRetry={refetch} title="Gagal Memuat Kalender" />
          ) : (
            <>
              {/* Card Header */}
              <div className="flex items-center gap-2 mb-4 px-2">
                <CalendarIcon className="h-4.5 w-4.5 text-orange-500" />
                <h3 className="text-sm font-bold text-gray-900 dark:text-white">
                  {TABS.find((t) => t.key === activeTab)?.label}
                </h3>
                <span className="text-[11px] text-gray-400 dark:text-gray-500">
                  — {BULAN[currentMonth]} {currentYear}
                </span>
              </div>

              {/* Card Grid */}
              {paginatedItems.length === 0 ? (
                <div className="flex flex-col items-center justify-center py-16 text-center">
                  <CalendarIcon className="h-10 w-10 text-gray-300 dark:text-gray-600 mb-3" />
                  <p className="text-[13px] text-gray-400 dark:text-gray-500">
                    Tidak ada kegiatan {TABS.find((t) => t.key === activeTab)?.label.toLowerCase()} pada periode ini
                  </p>
                </div>
              ) : (
                <AnimatePresence mode="wait">
                  <motion.div
                    key={activeTab}
                    initial={{ opacity: 0, y: 8 }}
                    animate={{ opacity: 1, y: 0 }}
                    exit={{ opacity: 0, y: -8 }}
                    transition={{ duration: 0.2 }}
                    className="grid grid-cols-1 sm:grid-cols-2 gap-3"
                  >
                    {paginatedItems.map((item) => (
                      <div
                        key={item.id}
                        className="rounded-2xl border border-gray-100 dark:border-white/10 bg-gray-50/50 dark:bg-white/5 p-4 space-y-2.5 hover:bg-white/80 dark:hover:bg-white/[0.07] transition-colors"
                      >
                        {/* Nama */}
                        <h4 className="font-bold text-gray-900 dark:text-white text-[13px] leading-tight line-clamp-2">
                          {item.nama}
                        </h4>

                        {/* Fungsi badge */}
                        <div className="flex items-center gap-1.5">
                          <span className={`h-2 w-2 rounded-full ${fungsiColors[item.fungsi] || 'bg-gray-400'}`} />
                          <span className="text-[10px] font-bold uppercase tracking-widest text-gray-500 dark:text-gray-400">
                            {item.fungsi}
                          </span>
                        </div>

                        {/* Date range */}
                        <div className="flex items-center gap-2 text-[12px] text-gray-500 dark:text-gray-400">
                          <Clock className="h-3.5 w-3.5 opacity-50" />
                          <span>{fmtDate(item.tgl_mulai)} — {fmtDate(item.tgl_selesai)}</span>
                        </div>

                        {/* Volume & Penugasan */}
                        <div className="flex items-center gap-4 pt-1">
                          <div className="flex items-center gap-1.5 text-[12px]">
                            <BarChart3 className="h-3.5 w-3.5 text-blue-500 opacity-70" />
                            <span className="text-gray-600 dark:text-gray-300 font-semibold">{item.volume}</span>
                            <span className="text-gray-400 dark:text-gray-500">{item.satuan}</span>
                          </div>
                          <div className="flex items-center gap-1.5 text-[12px]">
                            <Users className="h-3.5 w-3.5 text-emerald-500 opacity-70" />
                            <span className="text-gray-600 dark:text-gray-300 font-semibold">
                              {item.penugasan_sum_volume ?? 0}
                            </span>
                            <span className="text-gray-400 dark:text-gray-500">penugasan</span>
                          </div>
                        </div>
                      </div>
                    ))}
                  </motion.div>
                </AnimatePresence>
              )}

              {/* Pagination */}
              {totalItems > 0 && (
                <div 
                  data-scan="navigasi halaman"
                  className="flex flex-wrap items-center justify-between gap-3 mt-5 pt-4 border-t border-gray-200/60 dark:border-white/10"
                >
                  <p className="text-[12px] text-gray-500 dark:text-gray-400">
                    Menampilkan {from} - {to} dari {totalItems} data
                  </p>
                  <div className="flex items-center gap-3">
                    <div className="flex items-center gap-1.5">
                      <span className="text-[11px] text-gray-500 dark:text-gray-400">Per halaman:</span>
                      <select
                        value={perPage}
                        onChange={(e) => handlePerPageChange(Number(e.target.value))}
                        className="text-[12px] rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-white/5 px-2 py-1 text-gray-700 dark:text-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-500/30"
                      >
                        {[10, 20, 50].map((n) => (
                          <option key={n} value={n}>{n}</option>
                        ))}
                      </select>
                    </div>
                    <div className="flex items-center gap-1">
                      <button
                        onClick={() => setPage((p) => Math.max(1, p - 1))}
                        disabled={safePage <= 1}
                        className="p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-white/10 transition disabled:opacity-30 disabled:cursor-not-allowed"
                      >
                        <ChevronLeft className="h-4 w-4 text-gray-600 dark:text-gray-400" />
                      </button>
                      <span className="text-[12px] text-gray-600 dark:text-gray-300 px-2">
                        {safePage} / {totalPages}
                      </span>
                      <button
                        onClick={() => setPage((p) => Math.min(totalPages, p + 1))}
                        disabled={safePage >= totalPages}
                        className="p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-white/10 transition disabled:opacity-30 disabled:cursor-not-allowed"
                      >
                        <ChevronRight className="h-4 w-4 text-gray-600 dark:text-gray-400" />
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
