import React, { useEffect, useMemo, useState } from 'react';
import {
  Calendar,
  Plus,
  Search,
  Sparkles,
  Pencil,
  Trash2,
  RefreshCw,
  CalendarDays,
} from 'lucide-react';
import { motion } from 'motion/react';
import { useQueryClient } from '@tanstack/react-query';
import { useApiMutation, useApiQuery } from '../hooks/useApi';
import { useDebounce } from '../hooks/useDebounce';
import { PageHeader } from '../components/PageHeader';
import { LoadingSkeleton } from '../components/LoadingSkeleton';
import { ApiErrorBoundary } from '../components/ApiErrorBoundary';
import { EntityFormModal } from '../components/EntityFormModal';
import { ConfirmDialog } from '../components/ConfirmDialog';
import { holidayService } from '../lib/api-services';
import type { Holiday } from '../types/api';

const bulan = [
  'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
  'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember',
];

type HolidayRow = {
  id: number;
  tanggal: string;
  deskripsi: string;
};

type NoticeState = {
  tone: 'success' | 'error';
  message: string;
} | null;

function extractHolidayRows(payload: unknown): Holiday[] {
  if (Array.isArray(payload)) return payload as Holiday[];
  if (payload && typeof payload === 'object' && Array.isArray((payload as { data?: unknown[] }).data)) {
    return (payload as { data: Holiday[] }).data;
  }
  return [];
}

function formatDate(value: string) {
  const parsed = new Date(value);
  if (Number.isNaN(parsed.getTime())) return value;
  return new Intl.DateTimeFormat('id-ID', {
    day: '2-digit',
    month: 'long',
    year: 'numeric',
  }).format(parsed);
}

export function UmumLibur() {
  const queryClient = useQueryClient();
  const currentYear = new Date().getFullYear();
  const pageSize = 6;
  const yearOptions = useMemo(
    () => Array.from({ length: 7 }, (_, index) => String(currentYear - 3 + index)),
    [currentYear],
  );

  const [searchInput, setSearchInput] = useState('');
  const [filterYear, setFilterYear] = useState(String(currentYear));
  const [filterMonth, setFilterMonth] = useState('');
  const [showFormModal, setShowFormModal] = useState(false);
  const [editingHoliday, setEditingHoliday] = useState<HolidayRow | null>(null);
  const [deleteTarget, setDeleteTarget] = useState<HolidayRow | null>(null);
  const [notice, setNotice] = useState<NoticeState>(null);
  const [page, setPage] = useState(1);

  const debouncedSearch = useDebounce(searchInput, 350);

  const { data: response, isLoading, error, refetch } = useApiQuery(
    ['holidays', debouncedSearch, filterYear],
    () =>
      holidayService.list({
        per_page: 200,
        sort: 'tanggal',
        'filter[search]': debouncedSearch || undefined,
        'filter[year]': filterYear || undefined,
      }),
  );

  const createMutation = useApiMutation(
    (payload: Record<string, unknown>) => holidayService.create(payload),
    {
      invalidateKeys: [['holidays']],
      onSuccess: () => {
        setNotice({ tone: 'success', message: 'Hari libur berhasil ditambahkan.' });
        setShowFormModal(false);
      },
      onError: (mutationError) => {
        setNotice({ tone: 'error', message: mutationError.message || 'Gagal menambahkan hari libur.' });
      },
    },
  );

  const updateMutation = useApiMutation(
    ({ id, data }: { id: number; data: Record<string, unknown> }) => holidayService.update(id, data),
    {
      invalidateKeys: [['holidays']],
      onSuccess: () => {
        setNotice({ tone: 'success', message: 'Hari libur berhasil diperbarui.' });
        setShowFormModal(false);
        setEditingHoliday(null);
      },
      onError: (mutationError) => {
        setNotice({ tone: 'error', message: mutationError.message || 'Gagal memperbarui hari libur.' });
      },
    },
  );

  const deleteMutation = useApiMutation(
    (id: number) => holidayService.delete(id),
    {
      invalidateKeys: [['holidays']],
      onSuccess: () => {
        setNotice({ tone: 'success', message: 'Hari libur berhasil dihapus.' });
        setDeleteTarget(null);
      },
      onError: (mutationError) => {
        setNotice({ tone: 'error', message: mutationError.message || 'Gagal menghapus hari libur.' });
      },
    },
  );

  const holidayData = useMemo<HolidayRow[]>(() => {
    const rows = extractHolidayRows(response?.data);
    return rows
      .map((item) => ({
        id: Number(item.id),
        tanggal: typeof item.tanggal === 'string' ? item.tanggal : '',
        deskripsi: typeof item.deskripsi === 'string' ? item.deskripsi : 'Tanpa deskripsi',
      }))
      .filter((item) => item.id > 0 && item.tanggal);
  }, [response]);

  const filteredData = useMemo(() => {
    if (!filterMonth) return holidayData;
    const monthIndex = Number(filterMonth);
    return holidayData.filter((item) => new Date(item.tanggal).getMonth() === monthIndex);
  }, [holidayData, filterMonth]);

  const grouped = useMemo(
    () =>
      filteredData.reduce<Record<string, HolidayRow[]>>((acc, item) => {
        const parsed = new Date(item.tanggal);
        const monthIndex = parsed.getMonth();
        const key = bulan[monthIndex] || 'Lainnya';
        if (!acc[key]) acc[key] = [];
        acc[key].push(item);
        return acc;
      }, {}),
    [filteredData],
  );

  const stats = useMemo(
    () => ({
      total: holidayData.length,
      tampil: filteredData.length,
      bulanAktif: Object.keys(grouped).length,
      tahun: filterYear || 'Semua',
    }),
    [filteredData.length, filterYear, grouped, holidayData.length],
  );

  const groupedKeys = useMemo(
    () => bulan.filter((item) => grouped[item]?.length),
    [grouped],
  );

  const totalPages = Math.max(1, Math.ceil(groupedKeys.length / pageSize));

  const paginatedMonthKeys = useMemo(() => {
    const startIndex = (page - 1) * pageSize;
    return groupedKeys.slice(startIndex, startIndex + pageSize);
  }, [groupedKeys, page, pageSize]);

  useEffect(() => {
    setPage(1);
  }, [debouncedSearch, filterMonth, filterYear]);

  useEffect(() => {
    if (page > totalPages) {
      setPage(totalPages);
    }
  }, [page, totalPages]);

  const formFields = useMemo(
    () => [
      {
        name: 'tanggal',
        label: 'Tanggal Libur',
        type: 'date' as const,
        placeholder: 'Pilih tanggal',
        required: true,
      },
      {
        name: 'deskripsi',
        label: 'Deskripsi',
        type: 'textarea' as const,
        placeholder: 'Contoh: Hari Raya Nyepi',
        required: true,
        colSpan: 2 as const,
      },
    ],
    [],
  );

  const handleFormSubmit = async (data: Record<string, unknown>) => {
    const payload = {
      tanggal: String(data.tanggal || '').trim(),
      deskripsi: String(data.deskripsi || '').trim(),
    };

    if (!payload.tanggal || !payload.deskripsi) {
      setNotice({ tone: 'error', message: 'Tanggal dan deskripsi hari libur wajib diisi.' });
      return;
    }

    setNotice(null);

    if (editingHoliday) {
      await updateMutation.mutateAsync({ id: editingHoliday.id, data: payload });
      return;
    }

    await createMutation.mutateAsync(payload);
  };

  const handleRefresh = async () => {
    setNotice(null);
    await queryClient.invalidateQueries({ queryKey: ['holidays'] });
    await refetch();
  };

  return (
    <div className="space-y-6">
      <PageHeader
        title="Hari Libur"
        description="Kalender hari libur kantor yang terhubung langsung ke API."
        actions={
          <button
            data-scan="tombol tambah libur"
            onClick={() => {
              setEditingHoliday(null);
              setNotice(null);
              setShowFormModal(true);
            }}
            className="flex items-center gap-1.5 px-4 py-2 rounded-full bg-amber-300 hover:bg-amber-400 text-gray-900 text-sm font-semibold shadow-lg shadow-amber-500/20 transition"
          >
            <Plus className="h-4 w-4" />
            Tambah Libur
          </button>
        }
      />

      {notice && (
        <div
          className={`rounded-[20px] border px-4 py-3 text-sm ${
            notice.tone === 'success'
              ? 'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-300'
              : 'border-red-200 bg-red-50 text-red-700 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-300'
          }`}
        >
          {notice.message}
        </div>
      )}

      <div 
        data-scan="ringkasan statistik"
        className="grid grid-cols-2 lg:grid-cols-4 gap-4"
      >
        {[
          { label: 'Total Tahun Ini', value: stats.total, icon: CalendarDays, color: 'text-orange-600 dark:text-orange-400' },
          { label: 'Data Tampil', value: stats.tampil, icon: Sparkles, color: 'text-emerald-600 dark:text-emerald-400' },
          { label: 'Bulan Aktif', value: stats.bulanAktif, icon: Calendar, color: 'text-blue-600 dark:text-blue-400' },
          { label: 'Filter Tahun', value: stats.tahun, icon: RefreshCw, color: 'text-amber-600 dark:text-amber-400' },
        ].map((item, index) => {
          const Icon = item.icon;
          return (
            <motion.div
              key={item.label}
              initial={{ opacity: 0, y: 12 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ delay: index * 0.05 }}
              className="bg-white/65 dark:bg-white/[0.04] glass-strong rounded-[20px] border border-white/50 dark:border-white/10 p-5 space-y-2"
            >
              <div className="flex items-center justify-between">
                <p className="text-[11px] font-bold uppercase tracking-widest text-gray-500 dark:text-gray-400">{item.label}</p>
                <Icon className="h-4 w-4 text-gray-400 dark:text-gray-500" />
              </div>
              <p className={`text-2xl font-black ${item.color}`}>{isLoading ? '...' : item.value}</p>
            </motion.div>
          );
        })}
      </div>

      <div 
        data-scan="pencarian dan filter"
        className="bg-white/65 dark:bg-white/[0.04] glass-strong rounded-[24px] border border-white/50 dark:border-white/10 p-6 space-y-5 shadow-sm"
      >
        <div className="flex flex-wrap items-end gap-3">
          <div className="flex w-full max-w-sm flex-col gap-2">
            <label className="text-[10px] font-bold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Pencarian</label>
            <div className="relative group">
              <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-3.5 w-3.5 text-gray-400 group-focus-within:text-amber-500 transition-colors" />
              <input
                type="text"
                placeholder="Cari deskripsi hari libur..."
                value={searchInput}
                onChange={(event) => setSearchInput(event.target.value)}
                className="w-full h-10 rounded-xl border border-gray-200 dark:border-white/10 bg-white/70 dark:bg-white/5 pl-9 pr-4 text-xs text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-amber-500/20 transition-all outline-none"
              />
            </div>
          </div>

          <div className="flex flex-col gap-2">
            <label className="text-[10px] font-bold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Tahun</label>
            <select
              value={filterYear}
              onChange={(event) => setFilterYear(event.target.value)}
              className="h-10 rounded-xl border border-gray-200 dark:border-white/10 bg-white/70 dark:bg-white/5 px-4 text-xs font-bold text-gray-700 dark:text-gray-200 outline-none transition focus:border-amber-500 dark:[color-scheme:dark]"
            >
              <option value="">Semua Tahun</option>
              {yearOptions.map((year) => (
                <option key={year} value={year}>
                  {year}
                </option>
              ))}
            </select>
          </div>

          <div className="flex flex-col gap-2">
            <label className="text-[10px] font-bold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Bulan</label>
            <select
              value={filterMonth}
              onChange={(event) => setFilterMonth(event.target.value)}
              className="h-10 rounded-xl border border-gray-200 dark:border-white/10 bg-white/70 dark:bg-white/5 px-4 text-xs font-bold text-gray-700 dark:text-gray-200 outline-none transition focus:border-amber-500 dark:[color-scheme:dark]"
            >
              <option value="">Semua Bulan</option>
              {bulan.map((item, index) => (
                <option key={item} value={String(index)}>
                  {item}
                </option>
              ))}
            </select>
          </div>

          <button
            data-scan="tombol reset"
            onClick={() => {
              setSearchInput('');
              setFilterYear(String(currentYear));
              setFilterMonth('');
              setNotice(null);
            }}
            className="h-10 px-5 rounded-full bg-white/70 dark:bg-white/10 border border-gray-200 dark:border-white/10 text-gray-700 dark:text-gray-300 text-[11px] font-bold shadow-sm hover:bg-white transition-all"
          >
            Reset
          </button>

          <button
            data-scan="tombol muat ulang"
            onClick={() => void handleRefresh()}
            className="h-10 px-5 rounded-full bg-slate-900 dark:bg-white text-white dark:text-slate-900 text-[11px] font-bold shadow-sm transition-all flex items-center gap-2"
          >
            <RefreshCw className="h-3.5 w-3.5" />
            Muat Ulang
          </button>
        </div>
      </div>

      <div 
        data-scan="daftar hari libur"
        className="bg-white/65 dark:bg-white/[0.04] glass-strong rounded-[24px] border border-white/50 dark:border-white/10 p-5 shadow-sm"
      >
        {isLoading ? (
          <LoadingSkeleton variant="cards" message="Memuat hari libur..." />
        ) : error ? (
          <ApiErrorBoundary error={error} onRetry={() => refetch()} title="Gagal Memuat Hari Libur" />
        ) : groupedKeys.length === 0 ? (
          <div className="flex flex-col items-center justify-center py-16 text-center">
            <div className="h-14 w-14 rounded-2xl bg-amber-100 dark:bg-amber-500/10 flex items-center justify-center mb-4">
              <Calendar className="h-7 w-7 text-amber-600 dark:text-amber-400" />
            </div>
            <p className="text-sm font-semibold text-gray-800 dark:text-gray-200">Belum ada data hari libur.</p>
            <p className="mt-1 text-xs text-gray-500 dark:text-gray-400">
              Ubah filter atau tambahkan data baru dari tombol di kanan atas.
            </p>
          </div>
        ) : (
          <>
            <div className="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
              {paginatedMonthKeys.map((item, index) => (
                <motion.div
                  key={item}
                  initial={{ opacity: 0, y: 12 }}
                  animate={{ opacity: 1, y: 0 }}
                  transition={{ delay: index * 0.04 }}
                  className="rounded-[20px] border border-white/50 dark:border-white/10 bg-white/60 dark:bg-white/[0.03] p-5 space-y-4"
                >
                  <div className="flex items-center justify-between">
                    <h3 className="text-sm font-bold uppercase tracking-widest text-gray-500 dark:text-gray-400 flex items-center gap-2">
                      <Calendar className="h-4 w-4" />
                      {item}
                    </h3>
                    <span className="rounded-full bg-amber-100 px-2.5 py-1 text-[10px] font-bold text-amber-700 dark:bg-amber-500/10 dark:text-amber-300">
                      {grouped[item].length} hari
                    </span>
                  </div>

                  <div className="space-y-3">
                    {grouped[item].map((libur) => (
                      <div
                        key={libur.id}
                        className="rounded-[18px] border border-gray-100/80 dark:border-white/5 bg-white/80 dark:bg-white/[0.02] p-4"
                      >
                        <div className="flex items-start justify-between gap-4">
                          <div className="min-w-0">
                            <p className="text-[13px] font-semibold text-gray-900 dark:text-white leading-tight">
                              {libur.deskripsi}
                            </p>
                            <p className="mt-1 text-[11px] text-gray-500 dark:text-gray-400">
                              {formatDate(libur.tanggal)}
                            </p>
                          </div>
                          <div className="flex items-center gap-1 shrink-0">
                            <button
                              onClick={() => {
                                setEditingHoliday(libur);
                                setNotice(null);
                                setShowFormModal(true);
                              }}
                              className="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-white/10 text-gray-500 hover:text-amber-600 dark:hover:text-amber-400 transition"
                              aria-label={`Edit ${libur.deskripsi}`}
                            >
                              <Pencil className="h-3.5 w-3.5" />
                            </button>
                            <button
                              onClick={() => {
                                setDeleteTarget(libur);
                                setNotice(null);
                              }}
                              className="p-2 rounded-lg hover:bg-red-50 dark:hover:bg-red-500/10 text-gray-500 hover:text-red-600 dark:hover:text-red-400 transition"
                              aria-label={`Hapus ${libur.deskripsi}`}
                            >
                              <Trash2 className="h-3.5 w-3.5" />
                            </button>
                          </div>
                        </div>
                      </div>
                    ))}
                  </div>
                </motion.div>
              ))}
            </div>

            {groupedKeys.length > pageSize && (
              <div 
                data-scan="navigasi halaman"
                className="flex items-center justify-between gap-4 border-t border-gray-200/60 dark:border-white/10 px-1 pt-5"
              >
                <p className="text-[12px] text-gray-500 dark:text-gray-400 font-medium">
                  Menampilkan {paginatedMonthKeys.length} kartu bulan dari {groupedKeys.length} kartu
                </p>
                <div className="flex items-center gap-2">
                  <button
                    onClick={() => setPage((prev) => Math.max(1, prev - 1))}
                    disabled={page === 1}
                    className="px-4 py-2 text-[11px] font-bold rounded-full bg-white/70 dark:bg-white/10 border border-white/50 dark:border-white/20 text-gray-700 dark:text-gray-300 hover:bg-white transition-all disabled:opacity-30 disabled:cursor-not-allowed shadow-sm"
                  >
                    Sebelumnya
                  </button>
                  <span className="text-[11px] font-bold text-gray-500 dark:text-gray-400 px-2">
                    Halaman {page} dari {totalPages}
                  </span>
                  <button
                    onClick={() => setPage((prev) => Math.min(totalPages, prev + 1))}
                    disabled={page === totalPages}
                    className="px-4 py-2 text-[11px] font-bold rounded-full bg-white/70 dark:bg-white/10 border border-white/50 dark:border-white/20 text-gray-700 dark:text-gray-300 hover:bg-white transition-all disabled:opacity-30 disabled:cursor-not-allowed shadow-sm"
                  >
                    Berikutnya
                  </button>
                </div>
              </div>
            )}
          </>
        )}
      </div>

      <EntityFormModal
        open={showFormModal}
        onClose={() => {
          setShowFormModal(false);
          setEditingHoliday(null);
        }}
        onSubmit={handleFormSubmit}
        title={editingHoliday ? 'Edit Hari Libur' : 'Tambah Hari Libur'}
        description="Simpan tanggal dan deskripsi hari libur kantor."
        fields={formFields}
        initialData={editingHoliday}
        isLoading={createMutation.isPending || updateMutation.isPending}
        mode={editingHoliday ? 'edit' : 'create'}
      />

      <ConfirmDialog
        open={!!deleteTarget}
        onClose={() => setDeleteTarget(null)}
        onConfirm={() => deleteTarget && deleteMutation.mutate(deleteTarget.id)}
        title="Hapus Hari Libur"
        message={`Yakin ingin menghapus "${deleteTarget?.deskripsi}"? Tindakan ini tidak dapat dibatalkan.`}
        confirmLabel="Hapus"
        variant="danger"
        isLoading={deleteMutation.isPending}
      />
    </div>
  );
}
