import React, { useEffect, useMemo, useState } from 'react';
import { Download, FileText } from 'lucide-react';
import { format } from 'date-fns';
import { PageHeader } from '../components/PageHeader';
import { DatePicker } from '../components/DatePicker';
import { useAuth } from '../hooks/useAuth';
import { usePagination } from '../hooks/usePagination';
import { APP_BASE_URL, apiGet, apiPut, type ApiError } from '../lib/api';

type SpkItem = {
  id?: number;
  mitra_id?: number;
  bln_bayar?: string | null;
  no_sk?: string | null;
  tgl_sk?: string | null;
  no_bast?: string | null;
  tgl_bast?: string | null;
  total?: string | number | null;
  jml_tugas?: string | number | null;
  jumlah_tugas?: string | number | null;
  nilai?: string | number | null;
  nominal?: string | number | null;
  bulan?: string | null;
  mitra?: {
    nama_lengkap?: string | null;
    nik?: string | null;
  } | null;
  nama_lengkap?: string | null;
  nama?: string | null;
  nik?: string | null;
};

type SelectedRow = {
  id?: number;
  mitraId: number;
  blnBayar: string;
};

type SpkResponse = {
  success: boolean;
  message: string;
  data: SpkItem[];
  listbln?: string[];
  limit_nilai?: {
    value?: string;
  };
  links?: {
    next_cursor?: string | null;
    next?: string | null;
    prev_cursor?: string | null;
    next_page_url?: string | null;
    prev_page_url?: string | null;
    path?: string | null;
  };
  pagination_info?: {
    total_page?: number;
    total_records?: number;
  };
};

function getNestedValue(item: SpkItem, path: string) {
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

function getValue(item: SpkItem, keys: string[]) {
  for (const key of keys) {
    const value = key.includes('.') ? getNestedValue(item, key) : item[key as keyof SpkItem];
    if (value !== undefined && value !== null && value !== '') {
      return value as string | number;
    }
  }
  return '-';
}

function formatDisplayDate(value: unknown) {
  if (!value || typeof value !== 'string') return '-';
  const date = new Date(value);
  if (Number.isNaN(date.getTime())) return '-';
  return new Intl.DateTimeFormat('id-ID', {
    day: '2-digit',
    month: 'long',
    year: 'numeric',
  }).format(date);
}

function formatCurrency(value: unknown) {
  const numeric = typeof value === 'number' ? value : Number(String(value ?? '').replace(/,/g, ''));
  if (Number.isNaN(numeric)) return '-';
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
  }).format(numeric);
}

function normalizeMonthForPayload(value: string) {
  return format(new Date(value), 'yyyy-MM');
}

function ActionModal({
  open,
  title,
  description,
  label,
  value,
  onChange,
  error,
  success,
  isSubmitting,
  onClose,
  onSubmit,
}: {
  open: boolean;
  title: string;
  description: string;
  label: string;
  value: string;
  onChange: (value: string) => void;
  error: string | null;
  success: string | null;
  isSubmitting: boolean;
  onClose: () => void;
  onSubmit: () => void;
}) {
  if (!open) return null;

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center">
      <div className="absolute inset-0 bg-black/40 backdrop-blur-sm" onClick={onClose} />
      <div className="relative z-10 w-full max-w-lg mx-4 rounded-2xl border border-gray-200 dark:border-white/10 bg-white dark:bg-gray-900 shadow-2xl">
        <div className="px-6 py-4 border-b border-gray-200/60 dark:border-white/10">
          <h2 className="text-base font-bold text-gray-900 dark:text-white">{title}</h2>
          <p className="mt-1 text-sm text-gray-500 dark:text-gray-400">{description}</p>
        </div>
        <div className="px-6 py-5 space-y-4">
          <div>
            <label className="block text-[11px] font-bold uppercase tracking-widest text-gray-500 dark:text-gray-400 mb-2">
              {label}
            </label>
            <DatePicker
              value={value}
              onChange={onChange}
              placeholder="Pilih tanggal"
            />
          </div>
          {error ? (
            <div className="rounded-xl border border-red-200 dark:border-red-500/30 bg-red-50 dark:bg-red-500/10 px-3 py-2 text-xs text-red-700 dark:text-red-300">
              {error}
            </div>
          ) : null}
          {success ? (
            <div className="rounded-xl border border-emerald-200 dark:border-emerald-500/30 bg-emerald-50 dark:bg-emerald-500/10 px-3 py-2 text-xs text-emerald-700 dark:text-emerald-300">
              {success}
            </div>
          ) : null}
        </div>
        <div className="flex items-center justify-end gap-3 px-6 py-4 border-t border-gray-200/60 dark:border-white/10">
          <button
            type="button"
            onClick={onClose}
            className="px-4 py-2 text-[12px] font-bold rounded-full bg-white/70 dark:bg-white/10 border border-white/50 dark:border-white/20 text-gray-700 dark:text-gray-300 hover:bg-white transition-all shadow-sm"
          >
            Batal
          </button>
          <button
            type="button"
            onClick={onSubmit}
            disabled={isSubmitting}
            className="px-4 py-2 text-[12px] font-bold rounded-full bg-amber-400 hover:bg-amber-500 text-gray-900 shadow-sm transition-all disabled:opacity-50"
          >
            {isSubmitting ? 'Menyimpan...' : 'Terapkan'}
          </button>
        </div>
      </div>
    </div>
  );
}

export function SpkBastTable() {
  const { isAuthenticated } = useAuth();
  const [items, setItems] = useState<SpkItem[]>([]);
  const [bulanOptions, setBulanOptions] = useState<string[]>([]);
  const [selectedBulan, setSelectedBulan] = useState('');
  const [selectedRows, setSelectedRows] = useState<SelectedRow[]>([]);
  const pagination = usePagination(10);
  const [limitValue, setLimitValue] = useState<number | null>(null);
  const [isLoading, setIsLoading] = useState(false);
  const [errorMessage, setErrorMessage] = useState<string | null>(null);
  const [isBulkSpkOpen, setIsBulkSpkOpen] = useState(false);
  const [isBulkBastOpen, setIsBulkBastOpen] = useState(false);
  const [bulkSpkDate, setBulkSpkDate] = useState('');
  const [bulkBastDate, setBulkBastDate] = useState('');
  const [bulkSpkError, setBulkSpkError] = useState<string | null>(null);
  const [bulkBastError, setBulkBastError] = useState<string | null>(null);
  const [bulkSpkSuccess, setBulkSpkSuccess] = useState<string | null>(null);
  const [bulkBastSuccess, setBulkBastSuccess] = useState<string | null>(null);
  const [isBulkSubmitting, setIsBulkSubmitting] = useState(false);
  const [downloadError, setDownloadError] = useState<string | null>(null);
  const [isDownloadingSpkZip, setIsDownloadingSpkZip] = useState(false);
  const [isDownloadingBastZip, setIsDownloadingBastZip] = useState(false);
  const [nextCursor, setNextCursor] = useState<string | null>(null);

  useEffect(() => {
    if (!isAuthenticated) {
      setErrorMessage('Sesi login tidak ditemukan. Silakan login ulang.');
      return;
    }

    let ignore = false;

    const fetchSpk = async () => {
      setIsLoading(true);
      setErrorMessage(null);
      try {
        const params: Record<string, string | number> = {
          ...pagination.params,
        };
        if (selectedBulan) {
          params.selectedbln = selectedBulan;
        }

        const response = await apiGet<SpkItem[]>('/kantor/spk', params);
        const typed = response as unknown as SpkResponse;
        if (ignore) return;

        setItems(Array.isArray(typed.data) ? typed.data : []);
        setBulanOptions(typed.listbln ?? []);
        setSelectedRows([]);
        const parsedLimit = Number(typed.limit_nilai?.value ?? '');
        setLimitValue(Number.isNaN(parsedLimit) ? null : parsedLimit);
        pagination.sync(typed);
        const nc = typed.links?.next_cursor || typed.links?.next || typed.links?.next_page_url || null;
        setNextCursor(nc);
      } catch (error) {
        if (ignore) return;
        const apiError = error as ApiError;
        setErrorMessage(apiError.message ?? 'Gagal memuat data SPK & BAST.');
      } finally {
        if (!ignore) {
          setIsLoading(false);
        }
      }
    };

    void fetchSpk();

    return () => {
      ignore = true;
    };
  }, [isAuthenticated, pagination.params, selectedBulan]);

  useEffect(() => {
    pagination.reset();
  }, [selectedBulan]);

  const selectedIdsForDownload = useMemo(
    () =>
      selectedRows
        .map((row) => row.id)
        .filter((id): id is number => typeof id === 'number' && !Number.isNaN(id)),
    [selectedRows]
  );

  const resetPagination = () => {
    pagination.reset();
  };

  const handleBulkUpdate = async (kind: 'spk' | 'bast') => {
    const dateValue = kind === 'spk' ? bulkSpkDate : bulkBastDate;
    const setError = kind === 'spk' ? setBulkSpkError : setBulkBastError;
    const setSuccess = kind === 'spk' ? setBulkSpkSuccess : setBulkBastSuccess;

    setError(null);
    setSuccess(null);

    if (selectedRows.length === 0) {
      setError('Pilih data terlebih dahulu.');
      return;
    }
    if (!selectedBulan) {
      setError('Pilih filter bulan bayar terlebih dahulu.');
      return;
    }
    if (!dateValue) {
      setError(`Tanggal ${kind === 'spk' ? 'SPK' : 'BAST'} wajib diisi.`);
      return;
    }

    setIsBulkSubmitting(true);
    try {
      await apiPut(
        kind === 'spk' ? '/kantor/spk/bulk-update' : '/kantor/spk/bulk-update-bast',
        {
          mitra_ids: selectedRows.map((row) => row.mitraId),
          bln_bayar: normalizeMonthForPayload(selectedBulan),
          ...(kind === 'spk' ? { tgl_sk: dateValue } : { tgl_bast: dateValue }),
        }
      );

      setSuccess(`Bulk update ${kind === 'spk' ? 'SPK' : 'BAST'} berhasil.`);
      setSelectedRows([]);
      if (kind === 'spk') {
        setBulkSpkDate('');
      } else {
        setBulkBastDate('');
      }
      resetPagination();
    } catch (error) {
      const apiError = error as ApiError;
      setError(apiError.message ?? `Gagal melakukan bulk update ${kind === 'spk' ? 'SPK' : 'BAST'}.`);
    } finally {
      setIsBulkSubmitting(false);
    }
  };

  const handleDownloadZip = async (
    endpoint: string,
    filename: string,
    setIsDownloading: React.Dispatch<React.SetStateAction<boolean>>
  ) => {
    setDownloadError(null);

    if (selectedIdsForDownload.length === 0) {
      setDownloadError('Pilih data yang memiliki ID untuk diunduh.');
      return;
    }

    setIsDownloading(true);
    try {
      const token = localStorage.getItem('auth_token');
      const response = await fetch(`${APP_BASE_URL}${endpoint}`, {
        method: 'POST',
        headers: {
          Accept: 'application/json',
          'Content-Type': 'application/json',
          ...(token ? { Authorization: `Bearer ${token}` } : {}),
        },
        body: JSON.stringify({ ids: selectedIdsForDownload }),
      });

      if (!response.ok) {
        const text = await response.text();
        let message = 'Gagal mengunduh file.';
        if (text) {
          try {
            message = (JSON.parse(text) as { message?: string }).message ?? message;
          } catch {
            message = text;
          }
        }
        setDownloadError(message);
        return;
      }

      const blob = await response.blob();
      const url = window.URL.createObjectURL(blob);
      const link = document.createElement('a');
      link.href = url;
      link.download = filename;
      document.body.appendChild(link);
      link.click();
      link.remove();
      window.URL.revokeObjectURL(url);
    } catch (error) {
      const apiError = error as { message?: string };
      setDownloadError(apiError.message ?? 'Gagal mengunduh file.');
    } finally {
      setIsDownloading(false);
    }
  };

  const allVisibleRowsSelected =
    items.length > 0 &&
    items.every((item) => {
      const mitraId = Number(getNestedValue(item, 'mitra_id'));
      const blnBayar = String(getNestedValue(item, 'bln_bayar') ?? '');
      return selectedRows.some((row) => row.mitraId === mitraId && row.blnBayar === blnBayar);
    });

  return (
    <div className="space-y-6">
      <PageHeader
        title="Daftar SPK & BAST"
        description="Pantau daftar SPK & BAST yang tercatat pada periode aktif."
        actions={
          <button 
            data-scan="tombol dokumen baru"
            className="flex items-center gap-1.5 px-4 py-2 rounded-full bg-amber-300 hover:bg-amber-400 text-gray-900 text-sm font-semibold shadow-lg shadow-amber-500/20 transition"
          >
            <FileText className="h-4 w-4" />
            Dokumen Baru
          </button>
        }
      />
      {errorMessage && (
        <div className="rounded-2xl border border-rose-200 bg-rose-50/50 px-4 py-3 text-sm text-rose-600 dark:border-rose-500/40 dark:bg-rose-500/10 dark:text-rose-300 glass">
          {errorMessage}
        </div>
      )}

      <div className="bg-white/65 dark:bg-white/[0.04] glass-strong rounded-[24px] border border-white/50 dark:border-white/10 shadow-[0_10px_40px_-15px_rgba(0,0,0,0.08)] overflow-hidden">
        {/* Card Header with Filter */}
        <div className="flex flex-wrap items-center justify-between border-b border-gray-200/70 p-1 dark:border-white/10 bg-white/30 dark:bg-black/10">
          <div className="px-5 py-3 text-xs font-bold uppercase tracking-widest text-slate-500 dark:text-slate-400">
            Daftar Dokumen
          </div>

          <div 
            data-scan="filter bulan bayar"
            className="flex items-center gap-3 px-4 py-2"
          >
            <span className="text-[10px] font-bold uppercase tracking-widest text-gray-400">
              Filter Bulan Bayar
            </span>
            <select
              value={selectedBulan}
              onChange={(event) => {
                setSelectedBulan(event.target.value);
                resetPagination();
              }}
              className="h-8 min-w-[140px] rounded-lg border border-white/50 bg-white/70 px-3 text-[11px] font-bold text-gray-700 outline-none transition focus:border-sky-500 dark:border-white/10 dark:bg-white/[0.04] dark:text-gray-300 dark:[color-scheme:dark]"
            >
              <option value="">Semua Periode</option>
              {bulanOptions.map((bulan) => (
                <option key={bulan} value={bulan}>
                  {bulan}
                </option>
              ))}
            </select>
          </div>
        </div>
        <div 
          data-scan="tabel spk dan bast"
          className="overflow-x-auto"
        >
          <table className="w-full text-[13px]">
            <thead>
              <tr className="text-left text-[11.5px] text-gray-500 dark:text-gray-400 border-b border-gray-200/60 dark:border-white/10">
                <th className="px-6 py-4 font-medium uppercase tracking-wider w-10">
                  <input
                    type="checkbox"
                    aria-label="Pilih semua"
                    className="h-4 w-4 rounded-none border border-slate-300 bg-white text-sky-600 dark:border-white/20 dark:bg-slate-950"
                    disabled={!selectedBulan || items.length === 0}
                    checked={allVisibleRowsSelected}
                    onChange={(event) => {
                      if (event.target.checked) {
                        const map = new Map<string, SelectedRow>();
                        items.forEach((item) => {
                          const mitraId = Number(getNestedValue(item, 'mitra_id'));
                          const blnBayar = String(getNestedValue(item, 'bln_bayar') ?? '');
                          const id = Number(getNestedValue(item, 'id'));
                          if (Number.isNaN(mitraId) || !blnBayar) return;
                          map.set(`${mitraId}-${blnBayar}`, {
                            id: Number.isNaN(id) ? undefined : id,
                            mitraId,
                            blnBayar,
                          });
                        });
                        setSelectedRows(Array.from(map.values()));
                      } else {
                        setSelectedRows([]);
                      }
                    }}
                  />
                </th>
                {['Nama Mitra', 'NIK', 'Bulan Bayar', 'Jumlah Tugas', 'Total', 'SPK', 'BAST'].map((header) => (
                  <th key={header} className="px-6 py-4 font-medium uppercase tracking-wider">
                    {header}
                  </th>
                ))}
              </tr>
            </thead>
            <tbody className="divide-y divide-gray-100/70 dark:divide-white/5">
              {isLoading ? (
                <tr>
                  <td colSpan={8} className="px-6 py-12 text-center text-sm text-gray-500 dark:text-gray-400">
                    Memuat data SPK &amp; BAST...
                  </td>
                </tr>
              ) : items.length === 0 ? (
                <tr>
                  <td colSpan={8} className="px-6 py-12 text-center text-sm text-gray-500 dark:text-gray-400">
                    Belum ada data SPK &amp; BAST.
                  </td>
                </tr>
              ) : (
                items.map((item, index) => {
                  const itemKey = (item.id ?? item.mitra_id ?? index).toString();
                  const itemId = Number(getNestedValue(item, 'id'));
                  const mitraId = Number(getNestedValue(item, 'mitra_id'));
                  const blnBayar = String(getNestedValue(item, 'bln_bayar') ?? '');
                  const isChecked =
                    !Number.isNaN(mitraId) &&
                    selectedRows.some((row) => row.mitraId === mitraId && row.blnBayar === blnBayar);

                  const totalRaw = getValue(item, ['total', 'nilai', 'nominal']);
                  const totalNumber = Number(totalRaw);

                  return (
                    <tr key={itemKey} className="hover:bg-white/40 dark:hover:bg-white/[0.02] transition-colors">
                      <td className="px-6 py-4">
                        <input
                          type="checkbox"
                          aria-label="Pilih data"
                          className="h-4 w-4 rounded-none border border-slate-300 bg-white text-sky-600 dark:border-white/20 dark:bg-slate-950"
                          disabled={!selectedBulan}
                          checked={isChecked}
                          onChange={(event) => {
                            if (Number.isNaN(mitraId) || !blnBayar) return;
                            setSelectedRows((prev) => {
                              if (event.target.checked) {
                                if (prev.some((row) => row.mitraId === mitraId && row.blnBayar === blnBayar)) {
                                  return prev;
                                }
                                return [
                                  ...prev,
                                  {
                                    id: Number.isNaN(itemId) ? undefined : itemId,
                                    mitraId,
                                    blnBayar,
                                  },
                                ];
                              }
                              return prev.filter((row) => !(row.mitraId === mitraId && row.blnBayar === blnBayar));
                            });
                          }}
                        />
                      </td>
                      <td className="px-6 py-4 text-gray-700 dark:text-gray-300">
                        {getValue(item, ['mitra.nama_lengkap', 'nama_lengkap', 'nama'])}
                      </td>
                      <td className="px-6 py-4 text-gray-700 dark:text-gray-300">
                        {getValue(item, ['mitra.nik', 'nik'])}
                      </td>
                      <td className="px-6 py-4 text-gray-700 dark:text-gray-300">
                        {formatDisplayDate(getValue(item, ['bln_bayar', 'bulan']))}
                      </td>
                      <td className="px-6 py-4 text-gray-700 dark:text-gray-300">
                        {getValue(item, ['jml_tugas', 'jumlah_tugas'])}
                      </td>
                      <td className="px-6 py-4 text-right">
                        <span
                          className={
                            limitValue !== null && !Number.isNaN(totalNumber) && totalNumber > limitValue
                              ? 'text-rose-500'
                              : 'text-gray-700 dark:text-gray-300'
                          }
                        >
                          {formatCurrency(totalRaw)}
                        </span>
                      </td>
                      <td className="px-6 py-4 text-center text-gray-700 dark:text-gray-300">
                        {getValue(item, ['no_sk'])}
                      </td>
                      <td className="px-6 py-4 text-center text-gray-700 dark:text-gray-300">
                        {getValue(item, ['no_bast'])}
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
          className="flex flex-wrap items-center justify-between gap-4 px-6 py-4 border-t border-gray-200/60 dark:border-white/10 dark:bg-white/5"
        >
          <div className="flex items-center gap-4">
            <p className="text-[11px] text-gray-500 dark:text-gray-400 font-medium">
              Halaman <span className="text-gray-900 dark:text-white font-bold">{pagination.page}</span> dari <span className="text-gray-900 dark:text-white font-bold">{pagination.meta?.lastPage || 1}</span> · Total <span className="text-gray-900 dark:text-white font-bold">{pagination.meta?.total || 0}</span> data
            </p>

            <div className="flex items-center gap-2 border-l border-gray-200 dark:border-white/10 pl-4">
              <span className="text-[10px] font-bold uppercase tracking-widest text-gray-400">Tampilkan:</span>
              <select
                value={pagination.perPage}
                onChange={(e) => {
                  pagination.setPerPage(Number(e.target.value));
                  pagination.reset();
                }}
                className="h-7 rounded-lg border border-white/50 bg-white/70 px-2 text-[10px] font-bold text-gray-700 outline-none transition focus:border-sky-500 dark:border-white/10 dark:bg-white/[0.04] dark:text-gray-300 dark:[color-scheme:dark]"
              >
                {[10, 25, 50, 100].map((limit) => (
                  <option key={limit} value={limit}>{limit}</option>
                ))}
              </select>
            </div>
          </div>
          <div className="flex gap-2">
            <button
              data-scan="tombol halaman sebelumnya"
              type="button"
              onClick={() => pagination.handlePrev()}
              disabled={!pagination.canGoPrev || isLoading}
              className="px-3 py-1.5 text-[11px] font-bold rounded-full bg-white/70 dark:bg-white/10 border border-white/50 dark:border-white/20 text-gray-700 dark:text-gray-300 hover:bg-white transition-colors disabled:opacity-30 disabled:cursor-not-allowed"
            >
              Previous
            </button>
            <button
              data-scan="tombol halaman berikutnya"
              type="button"
              onClick={() => pagination.handleNext(nextCursor)}
              disabled={!pagination.canGoNext || isLoading}
              className="px-3 py-1.5 text-[11px] font-bold rounded-full bg-white/70 dark:bg-white/10 border border-white/50 dark:border-white/20 text-gray-700 dark:text-gray-300 hover:bg-white transition-colors disabled:opacity-30 disabled:cursor-not-allowed"
            >
              Next
            </button>
          </div>
        </div>
      </div>

      {selectedRows.length > 0 ? (
        <div 
          data-scan="bar aksi massal"
          className="sticky bottom-0 z-10 rounded-2xl border border-white/50 dark:border-white/10 bg-white/95 dark:bg-slate-950/90 px-4 py-3 text-sm shadow-sm backdrop-blur"
        >
          <div className="flex flex-wrap items-center justify-between gap-3">
            <div className="text-xs uppercase tracking-[0.2em] text-slate-500 dark:text-slate-400">
              {selectedRows.length} data dipilih
            </div>
            <div className="flex flex-wrap items-center gap-2">
              <button
                type="button"
                data-scan="tombol bulk spk"
                onClick={() => {
                  setBulkSpkError(null);
                  setBulkSpkSuccess(null);
                  setIsBulkSpkOpen(true);
                }}
                className="px-4 py-2 text-[11px] font-bold rounded-full bg-white/70 dark:bg-white/10 border border-white/50 dark:border-white/20 text-gray-700 dark:text-gray-300 hover:bg-white transition-all shadow-sm"
              >
                Bulk SPK
              </button>
              <button
                type="button"
                data-scan="tombol bulk bast"
                onClick={() => {
                  setBulkBastError(null);
                  setBulkBastSuccess(null);
                  setIsBulkBastOpen(true);
                }}
                className="px-4 py-2 text-[11px] font-bold rounded-full bg-white/70 dark:bg-white/10 border border-white/50 dark:border-white/20 text-gray-700 dark:text-gray-300 hover:bg-white transition-all shadow-sm"
              >
                Bulk BAST
              </button>
              <button
                type="button"
                data-scan="tombol unduh spk zip"
                onClick={() =>
                  void handleDownloadZip(
                    '/api/spk/pdf/bulk-spks',
                    `spk-${format(new Date(), 'yyyyMMdd')}.zip`,
                    setIsDownloadingSpkZip
                  )
                }
                disabled={isDownloadingSpkZip}
                className="px-4 py-2 text-[11px] font-bold rounded-full bg-white/70 dark:bg-white/10 border border-white/50 dark:border-white/20 text-gray-700 dark:text-gray-300 hover:bg-white transition-all shadow-sm disabled:opacity-50"
              >
                {isDownloadingSpkZip ? 'Mengunduh SPK...' : 'Unduh ZIP SPK'}
              </button>
              <button
                type="button"
                data-scan="tombol unduh bast zip"
                onClick={() =>
                  void handleDownloadZip(
                    '/api/spk/pdf/bulk-basts',
                    `bast-${format(new Date(), 'yyyyMMdd')}.zip`,
                    setIsDownloadingBastZip
                  )
                }
                disabled={isDownloadingBastZip}
                className="px-4 py-2 text-[11px] font-bold rounded-full bg-white/70 dark:bg-white/10 border border-white/50 dark:border-white/20 text-gray-700 dark:text-gray-300 hover:bg-white transition-all shadow-sm disabled:opacity-50"
              >
                {isDownloadingBastZip ? 'Mengunduh BAST...' : 'Unduh ZIP BAST'}
              </button>
            </div>
          </div>
          {selectedRows.length > 0 && !selectedBulan ? (
            <div className="mt-2 text-xs text-amber-600 dark:text-amber-400">
              Pilih filter bulan bayar untuk melakukan bulk update.
            </div>
          ) : null}
          {downloadError ? (
            <div className="mt-2 text-xs text-rose-600 dark:text-rose-300">
              {downloadError}
            </div>
          ) : null}
        </div>
      ) : null}

      <ActionModal
        open={isBulkSpkOpen}
        title="Bulk Update SPK"
        description={`Atur nomor dan tanggal SPK otomatis untuk ${selectedRows.length} data.`}
        label="Tanggal SPK"
        value={bulkSpkDate}
        onChange={setBulkSpkDate}
        error={bulkSpkError}
        success={bulkSpkSuccess}
        isSubmitting={isBulkSubmitting}
        onClose={() => setIsBulkSpkOpen(false)}
        onSubmit={() => void handleBulkUpdate('spk')}
      />

      <ActionModal
        open={isBulkBastOpen}
        title="Bulk Update BAST"
        description={`Atur nomor dan tanggal BAST otomatis untuk ${selectedRows.length} data.`}
        label="Tanggal BAST"
        value={bulkBastDate}
        onChange={setBulkBastDate}
        error={bulkBastError}
        success={bulkBastSuccess}
        isSubmitting={isBulkSubmitting}
        onClose={() => setIsBulkBastOpen(false)}
        onSubmit={() => void handleBulkUpdate('bast')}
      />
    </div>
  );
}
