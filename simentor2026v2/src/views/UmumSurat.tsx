import React, { useEffect, useMemo, useRef, useState } from 'react';
import { Link, useLocation, useNavigate } from '@tanstack/react-router';
import { motion } from 'motion/react';
import {
  AlignCenter,
  AlignJustify,
  AlignLeft,
  AlignRight,
  ArrowLeft,
  Bold,
  Calendar,
  Columns,
  Ellipsis,
  Eye,
  FileText,
  Grid,
  Italic,
  List,
  ListOrdered,
  Loader2,
  Mail,
  PaintBucket,
  Plus,
  Printer,
  RefreshCw,
  Rows,
  Save,
  Search,
  Send,
  Square,
  Table,
  Trash2,
  Underline,
  User,
  X,
} from 'lucide-react';
import { PageHeader } from '../components/PageHeader';
import { LoadingSkeleton } from '../components/LoadingSkeleton';
import { ApiErrorBoundary } from '../components/ApiErrorBoundary';
import { ConfirmDialog } from '../components/ConfirmDialog';
import { DatePicker } from '../components/DatePicker';
import { useApiMutation, useApiQuery } from '../hooks/useApi';
import { useDebounce } from '../hooks/useDebounce';
import { suratKeluarService } from '../lib/api-services';
import { APP_BASE_URL } from '../lib/api';
import type { ApiResponse } from '../lib/api';
import type { PaginatedResponse, SuratKeluar as SuratKeluarItem, SuratKeluarFormOptions } from '../types/api';

type NoticeState = {
  tone: 'success' | 'error';
  message: string;
} | null;

type SuratPageProps = {
  jenis: 'tugas' | 'keputusan' | 'permintaan';
  title: string;
  description: string;
};

type StaticSurat = {
  id: string;
  nomorSurat: string;
  perihal: string;
  tanggal: string;
  pengirim: string;
  penerima: string;
  status: 'draft' | 'terkirim' | 'diterima';
};

type SuratKeluarFormData = {
  thn: string;
  tanggal: string;
  nomor: string;
  dari: string;
  tujuan: string;
  perihal: string;
  lampiran: number;
  sifat: string;
};

type ListPayload<T> = PaginatedResponse<T> | T[];

const pageSize = 15;
const logoBpsSrc = '/logo_bps.png';

const defaultBody = `
  <p>Dengan hormat,</p>
  <p>Sehubungan dengan akan dilaksanakannya kegiatan, maka kami mengundang Bapak/Ibu untuk hadir pada:</p>
  <table style="width: 100%; border-collapse: collapse; border: 1px solid black; margin-top: 10px; margin-bottom: 10px;">
    <tbody>
      <tr>
        <td style="border: 1px solid black; padding: 5px; background-color: #f3f4f6; font-weight: bold;">Kegiatan</td>
        <td style="border: 1px solid black; padding: 5px; background-color: #f3f4f6; font-weight: bold;">Waktu</td>
        <td style="border: 1px solid black; padding: 5px; background-color: #f3f4f6; font-weight: bold;">Lokasi</td>
      </tr>
      <tr>
        <td style="border: 1px solid black; padding: 5px;">Briefing</td>
        <td style="border: 1px solid black; padding: 5px;">09:00 WIB</td>
        <td style="border: 1px solid black; padding: 5px;">Ruang Rapat</td>
      </tr>
    </tbody>
  </table>
  <p>Mengingat pentingnya acara tersebut, kami mengharapkan kehadiran Bapak/Ibu tepat pada waktunya.</p>
  <p>Demikian undangan ini kami sampaikan, atas perhatian dan kehadirannya kami ucapkan terima kasih.</p>
`;

const paperSizes = {
  A4: { width: '210mm', height: '297mm', label: 'A4 (210 x 297 mm)' },
  F4: { width: '215mm', height: '330mm', label: 'F4 (215 x 330 mm)' },
};

const emptyForm = (): SuratKeluarFormData => ({
  thn: String(new Date().getFullYear()),
  tanggal: '',
  nomor: '',
  dari: '',
  tujuan: '',
  perihal: '',
  lampiran: 0,
  sifat: 'Biasa',
});

const staticSuratByJenis: Record<SuratPageProps['jenis'], StaticSurat[]> = {
  tugas: [
    { id: 'ST-001', nomorSurat: 'ST/045/BPS/2026', perihal: 'Pelaksanaan Susenas Maret 2026', tanggal: '2026-03-01', pengirim: 'Kepala BPS', penerima: 'Dodi Darman, Asep Surya', status: 'terkirim' },
    { id: 'ST-002', nomorSurat: 'ST/046/BPS/2026', perihal: 'Pendataan Potensi Desa', tanggal: '2026-03-15', pengirim: 'Kepala BPS', penerima: 'Ali Anwar, Wawan Kurniawan', status: 'terkirim' },
  ],
  keputusan: [
    { id: 'SKK-001', nomorSurat: 'SK/012/BPS/2026', perihal: 'Penetapan petugas lapangan Susenas 2026', tanggal: '2026-02-28', pengirim: 'Kepala BPS', penerima: 'Seluruh Petugas', status: 'terkirim' },
    { id: 'SKK-002', nomorSurat: 'SK/013/BPS/2026', perihal: 'Pembentukan tim survei potensi desa', tanggal: '2026-03-05', pengirim: 'Kepala BPS', penerima: 'Tim Internal', status: 'terkirim' },
  ],
  permintaan: [
    { id: 'SP-001', nomorSurat: 'SP/008/BPS/2026', perihal: 'Permintaan data PDRB 2025', tanggal: '2026-04-10', pengirim: 'Bappeda Kabupaten', penerima: 'Kasi Produksi', status: 'diterima' },
    { id: 'SP-002', nomorSurat: 'SP/009/BPS/2026', perihal: 'Permintaan data kependudukan', tanggal: '2026-04-15', pengirim: 'Disdukcapil', penerima: 'Kasi Distribusi', status: 'diterima' },
  ],
};

const statusStyles: Record<StaticSurat['status'], string> = {
  draft: 'bg-gray-100 text-gray-600 border-gray-200 dark:bg-white/5 dark:text-gray-400',
  terkirim: 'bg-emerald-100 text-emerald-700 border-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-400',
  diterima: 'bg-blue-100 text-blue-700 border-blue-200 dark:bg-blue-500/10 dark:text-blue-400',
};

function asApiData<T>(value: unknown): T | undefined {
  if (value && typeof value === 'object' && 'data' in value) {
    return (value as { data?: T }).data;
  }
  return value as T | undefined;
}

function extractRows<T>(response?: ApiResponse<ListPayload<T>>): T[] {
  const payload = response?.data;
  if (!payload) return [];
  if (Array.isArray(payload)) return payload;
  return Array.isArray(payload.data) ? payload.data : [];
}

function getNestedPayload<T>(response?: ApiResponse<ListPayload<T>>) {
  return response?.data && !Array.isArray(response.data) ? response.data : undefined;
}

function getListTotal<T>(response: ApiResponse<ListPayload<T>> | undefined, rows: T[]) {
  const nested = getNestedPayload(response);
  return (
    response?.pagination_info?.total_records ??
    nested?.pagination_info?.total_records ??
    response?.meta?.total ??
    nested?.meta?.total ??
    rows.length
  );
}

function getListTotalPages<T>(response: ApiResponse<ListPayload<T>> | undefined, fallbackPage: number) {
  const nested = getNestedPayload(response);
  return Math.max(
    1,
    response?.pagination_info?.total_page ??
      nested?.pagination_info?.total_page ??
      response?.meta?.last_page ??
      nested?.meta?.last_page ??
      fallbackPage,
  );
}

function getNextCursor<T>(response: ApiResponse<ListPayload<T>> | undefined) {
  const nested = getNestedPayload(response);
  return response?.links?.next_cursor ?? nested?.links?.next_cursor ?? null;
}

function getCanNextPage<T>(response: ApiResponse<ListPayload<T>> | undefined, page: number, totalPages: number) {
  const nested = getNestedPayload(response);
  return response?.meta?.has_more ?? nested?.meta?.has_more ?? page < totalPages;
}

function formatDate(value?: string | null) {
  if (!value) return '-';
  const parsed = new Date(value);
  if (Number.isNaN(parsed.getTime())) return value;
  return new Intl.DateTimeFormat('id-ID', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
  }).format(parsed);
}

function toText(value: unknown) {
  return String(value ?? '').trim();
}

function stripHtml(value: string) {
  return value.replace(/<[^>]+>/g, '').replace(/&nbsp;/g, ' ').trim();
}

function parseTembusan(value?: string[] | null) {
  return Array.isArray(value) ? value.join('; ') : '';
}

function splitTembusan(value: string) {
  return value
    .split(';')
    .map((item) => item.trim())
    .filter(Boolean);
}

function getSetting(options: SuratKeluarFormOptions | undefined, key: string, fallback = '') {
  return options?.settings?.find((item) => item.key === key)?.value ?? fallback;
}

function padNomor(value: number | string | undefined) {
  if (value === undefined || value === '') return '';
  return String(value).padStart(4, '0');
}

function buildFormattedNomor(form: SuratKeluarFormData, options: SuratKeluarFormOptions | undefined) {
  const template = getSetting(options, 'FORMAT_SURAT_KELUAR', 'B-{nomor}/32150/KA.220/{tahun}');
  return template
    .replace('{nomor}', form.nomor || '')
    .replace('{tahun}', form.thn || String(new Date().getFullYear()));
}

function getSearchParams(searchStr: string) {
  const params = new URLSearchParams(searchStr);
  const rawId = params.get('id');
  const id = rawId ? Number(rawId) : null;
  return {
    suratId: id && Number.isFinite(id) ? id : null,
    isSisip: params.get('sisip') === 'true',
  };
}

function downloadPrintHtml(html: string) {
  const original = document.body.innerHTML;
  document.body.innerHTML = html;
  window.print();
  document.body.innerHTML = original;
  window.location.reload();
}

export function SuratKeluar() {
  const currentYear = new Date().getFullYear();
  const [searchInput, setSearchInput] = useState('');
  const [filterYear, setFilterYear] = useState(String(currentYear));
  const [filterDate, setFilterDate] = useState('');
  const [page, setPage] = useState(1);
  const [cursor, setCursor] = useState<string | null>(null);
  const [cursorHistory, setCursorHistory] = useState<(string | null)[]>([]);
  const [notice, setNotice] = useState<NoticeState>(null);
  const [deleteTarget, setDeleteTarget] = useState<SuratKeluarItem | null>(null);
  const [openActionMenuId, setOpenActionMenuId] = useState<number | null>(null);

  const debouncedSearch = useDebounce(searchInput, 350);

  const resetPagination = () => {
    setPage(1);
    setCursor(null);
    setCursorHistory([]);
  };

  useEffect(() => {
    resetPagination();
  }, [debouncedSearch, filterYear, filterDate]);

  const {
    data: listResponse,
    isLoading,
    error,
    refetch,
  } = useApiQuery(
    ['surat-keluar', debouncedSearch, filterYear, filterDate, cursor, page],
    () =>
      suratKeluarService.list({
        per_page: pageSize,
        page: cursor ? undefined : page,
        cursor: cursor || undefined,
        'filter[search]': debouncedSearch || undefined,
        'filter[thn]': filterYear || undefined,
        'filter[tanggal]': filterDate || undefined,
      }),
  );

  const { data: yearsResponse } = useApiQuery(
    ['surat-keluar-years'],
    () => suratKeluarService.years(),
  );

  const { data: datesResponse } = useApiQuery(
    ['surat-keluar-dates', filterYear],
    () => suratKeluarService.dates({ tahun: filterYear || undefined }),
  );

  const { data: formOptionsResponse } = useApiQuery(
    ['surat-keluar-list-form-options', filterYear],
    () => suratKeluarService.formOptions({ tahun: filterYear || String(currentYear) }),
  );

  const rows = useMemo(() => extractRows(listResponse), [listResponse]);
  const totalRecords = getListTotal(listResponse, rows);
  const totalPages = getListTotalPages(listResponse, page);
  const canNextPage = getCanNextPage(listResponse, page, totalPages);
  const nextCursor = getNextCursor(listResponse);

  const yearOptions = useMemo(() => {
    const years = asApiData<string[]>(yearsResponse) ?? [];
    if (years.length) return years;
    return Array.from({ length: 5 }, (_, index) => String(currentYear - index));
  }, [currentYear, yearsResponse]);

  const dateOptions = asApiData<string[]>(datesResponse) ?? [];
  const formOptions = formOptionsResponse?.data;

  const deleteMutation = useApiMutation(
    (id: number) => suratKeluarService.delete(id),
    {
      invalidateKeys: [['surat-keluar'], ['surat-keluar-years'], ['surat-keluar-dates'], ['surat-keluar-list-form-options']],
      onSuccess: () => {
        setDeleteTarget(null);
        setNotice({ tone: 'success', message: 'Surat keluar berhasil dihapus.' });
      },
      onError: (mutationError) => {
        setNotice({ tone: 'error', message: mutationError.message || 'Gagal menghapus surat keluar.' });
      },
    },
  );

  const stats = [
    { label: 'Total Surat', value: totalRecords, icon: FileText, color: 'text-orange-600 dark:text-orange-400' },
    { label: 'Tahun', value: filterYear || 'Semua', icon: Calendar, color: 'text-blue-600 dark:text-blue-400' },
    { label: 'Ditampilkan', value: rows.length, icon: Mail, color: 'text-emerald-600 dark:text-emerald-400' },
    { label: 'Nomor Baru', value: padNomor(formOptions?.nomor_baru), icon: Send, color: 'text-amber-600 dark:text-amber-400' },
  ];

  const handleNext = () => {
    setCursorHistory((previous) => [...previous, cursor]);
    setCursor(nextCursor);
    setPage((previous) => previous + 1);
  };

  const handlePrevious = () => {
    setCursorHistory((previous) => {
      const nextHistory = previous.slice(0, -1);
      setCursor(previous[previous.length - 1] ?? null);
      return nextHistory;
    });
    setPage((previous) => Math.max(1, previous - 1));
  };

  return (
    <div className="space-y-6">
      <PageHeader
        title="Surat Keluar"
        description="Kelola penomoran, isi, pratinjau, dan cetak surat keluar kantor."
        actions={
          <Link
            to="/umum/surat/keluar/form"
            className="flex items-center gap-1.5 px-4 py-2 rounded-full bg-amber-300 hover:bg-amber-400 text-gray-900 text-sm font-semibold shadow-lg shadow-amber-500/20 transition"
          >
            <Plus className="h-4 w-4" />
            Buat Surat
          </Link>
        }
      />

      {notice && <Notice notice={notice} />}

      <div className="grid grid-cols-2 lg:grid-cols-4 gap-4">
        {stats.map((item, index) => {
          const Icon = item.icon;
          return (
            <motion.div
              key={item.label}
              initial={{ opacity: 0, y: 12 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ delay: index * 0.04 }}
              className="bg-white/65 dark:bg-white/[0.04] glass-strong rounded-[20px] border border-white/50 dark:border-white/10 p-5 space-y-2"
            >
              <div className="flex items-center justify-between gap-3">
                <p className="text-[11px] font-bold uppercase tracking-widest text-gray-500 dark:text-gray-400">{item.label}</p>
                <Icon className="h-4 w-4 text-gray-400 dark:text-gray-500" />
              </div>
              <p className={`text-2xl font-black truncate ${item.color}`}>{isLoading ? '...' : item.value || '-'}</p>
            </motion.div>
          );
        })}
      </div>

      <div className="bg-white/65 dark:bg-white/[0.04] glass-strong rounded-[24px] border border-white/50 dark:border-white/10 p-5 shadow-sm">
        <div className="flex flex-wrap items-end gap-3">
          <div className="flex w-full max-w-sm flex-col gap-2">
            <label className="text-[10px] font-bold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Pencarian</label>
            <div className="relative group">
              <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-3.5 w-3.5 text-gray-400 group-focus-within:text-amber-500 transition-colors" />
              <input
                data-scan="input cari surat"
                type="text"
                placeholder="Cari tujuan, perihal, atau isi surat..."
                value={searchInput}
                onChange={(event) => setSearchInput(event.target.value)}
                className="w-full h-10 rounded-xl border border-gray-200 dark:border-white/10 bg-white/70 dark:bg-white/5 pl-9 pr-4 text-xs text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-amber-500/20 transition-all outline-none"
              />
            </div>
          </div>

          <FilterSelect label="Tahun" value={filterYear} onChange={setFilterYear}>
            <option value="">Semua Tahun</option>
            {yearOptions.map((year) => (
              <option key={year} value={year}>
                {year}
              </option>
            ))}
          </FilterSelect>

          <FilterSelect label="Tanggal" value={filterDate} onChange={setFilterDate}>
            <option value="">Semua Tanggal</option>
            {dateOptions.map((date) => (
              <option key={date} value={date}>
                {formatDate(date)}
              </option>
            ))}
          </FilterSelect>

          <button
            data-scan="tombol reset filter"
            onClick={() => {
              setSearchInput('');
              setFilterYear(String(currentYear));
              setFilterDate('');
              setNotice(null);
            }}
            className="h-10 px-5 rounded-full bg-white/70 dark:bg-white/10 border border-gray-200 dark:border-white/10 text-gray-700 dark:text-gray-300 text-[11px] font-bold shadow-sm hover:bg-white transition-all"
          >
            Reset
          </button>

          <button
            data-scan="tombol muat ulang"
            onClick={() => {
              setNotice(null);
              void refetch();
            }}
            className="h-10 px-5 rounded-full bg-slate-900 dark:bg-white text-white dark:text-slate-900 text-[11px] font-bold shadow-sm transition-all flex items-center gap-2"
          >
            <RefreshCw className="h-3.5 w-3.5" />
            Muat Ulang
          </button>
        </div>
      </div>

      <div className="bg-white/65 dark:bg-white/[0.04] glass-strong rounded-[24px] border border-white/50 dark:border-white/10 shadow-[0_10px_40px_-15px_rgba(0,0,0,0.08)] overflow-hidden">
        {isLoading ? (
          <LoadingSkeleton variant="table" message="Memuat surat keluar..." />
        ) : error ? (
          <ApiErrorBoundary error={error} onRetry={() => refetch()} title="Gagal Memuat Surat Keluar" />
        ) : rows.length === 0 ? (
          <EmptyState title="Belum ada surat keluar." description="Ubah filter atau buat surat keluar baru." />
        ) : (
          <>
            <div className="overflow-x-auto">
              <table className="w-full text-[13px]">
                <thead>
                  <tr className="text-left text-[11.5px] text-gray-500 dark:text-gray-400 border-b border-gray-200/60 dark:border-white/10">
                    {['Nomor Surat', 'Tanggal', 'Tujuan', 'Perihal', 'Sifat', 'Aksi'].map((header) => (
                      <th key={header} className="px-5 py-4 font-bold uppercase tracking-widest bg-gray-50/30 dark:bg-white/5 whitespace-nowrap">
                        {header}
                      </th>
                    ))}
                  </tr>
                </thead>
                <tbody className="divide-y divide-gray-100/70 dark:divide-white/5">
                  {rows.map((item) => (
                    <tr key={item.id} className="hover:bg-white/40 dark:hover:bg-white/[0.02] transition-colors">
                      <td className="px-5 py-4">
                        <div className="flex flex-col gap-0.5">
                          <span className="font-bold text-gray-900 dark:text-white text-[13px] leading-tight">{item.no_surat ?? '-'}</span>
                          <span className="text-[11px] text-gray-500">#{item.id} / {item.nomor ?? '-'}</span>
                        </div>
                      </td>
                      <td className="px-5 py-4 whitespace-nowrap text-gray-600 dark:text-gray-400">{item.tanggal_indo ?? formatDate(item.tanggal)}</td>
                      <td className="px-5 py-4">
                        <span className="line-clamp-2 text-gray-700 dark:text-gray-300">{item.tujuan ?? '-'}</span>
                      </td>
                      <td className="px-5 py-4">
                        <span className="block max-w-[260px] truncate text-gray-700 dark:text-gray-300">{item.perihal ?? '-'}</span>
                      </td>
                      <td className="px-5 py-4">
                        <span className="rounded-md bg-slate-100 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-slate-600 dark:bg-white/10 dark:text-slate-300">
                          {item.sifat || 'Biasa'}
                        </span>
                      </td>
                      <td className="px-5 py-4">
                        <SuratKeluarActionMenu
                          item={item}
                          isOpen={openActionMenuId === item.id}
                          onToggle={() => setOpenActionMenuId(openActionMenuId === item.id ? null : item.id)}
                          onDelete={() => {
                            setDeleteTarget(item);
                            setOpenActionMenuId(null);
                            setNotice(null);
                          }}
                        />
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>

            <div 
              data-scan="navigasi halaman"
              className="flex items-center justify-between px-5 py-4 border-t border-gray-200/60 dark:border-white/10 dark:bg-white/5"
            >
              <p className="text-[12px] text-gray-500 dark:text-gray-400 font-medium">
                Halaman {page} dari {totalPages} | {totalRecords} surat
              </p>
              <div className="flex gap-2">
                <button
                  data-scan="tombol halaman sebelumnya"
                  onClick={handlePrevious}
                  disabled={page === 1}
                  className="px-4 py-2 text-[11px] font-bold rounded-full bg-white/70 dark:bg-white/10 border border-white/50 dark:border-white/20 text-gray-700 dark:text-gray-300 hover:bg-white transition-all disabled:opacity-30 disabled:cursor-not-allowed shadow-sm"
                >
                  Sebelumnya
                </button>
                <button
                  data-scan="tombol halaman berikutnya"
                  onClick={handleNext}
                  disabled={!canNextPage}
                  className="px-4 py-2 text-[11px] font-bold rounded-full bg-white/70 dark:bg-white/10 border border-white/50 dark:border-white/20 text-gray-700 dark:text-gray-300 hover:bg-white transition-all disabled:opacity-30 disabled:cursor-not-allowed shadow-sm"
                >
                  Berikutnya
                </button>
              </div>
            </div>
          </>
        )}
      </div>

      <ConfirmDialog
        open={deleteTarget !== null}
        onClose={() => setDeleteTarget(null)}
        onConfirm={() => {
          if (deleteTarget) void deleteMutation.mutateAsync(deleteTarget.id);
        }}
        title="Hapus Surat Keluar"
        message={`Surat ${deleteTarget?.no_surat ?? ''} akan dihapus dan tidak dapat dikembalikan.`}
        confirmLabel="Hapus"
        isLoading={deleteMutation.isPending}
      />
    </div>
  );
}

export function SuratKeluarForm() {
  const navigate = useNavigate();
  const location = useLocation();
  const { suratId, isSisip } = getSearchParams(location.searchStr);

  const [formData, setFormData] = useState<SuratKeluarFormData>(() => emptyForm());
  const [tembusanInput, setTembusanInput] = useState('');
  const [recipientLocation, setRecipientLocation] = useState('Tempat');
  const [signerName, setSignerName] = useState('Kepala Kantor');
  const [signerPosition, setSignerPosition] = useState('Kepala');
  const [signerNip, setSignerNip] = useState('');
  const [alamatKantor, setAlamatKantor] = useState('Jl. Cakradireja No 36 Nagasari Karawang');
  const [bodyHtml, setBodyHtml] = useState(defaultBody);
  const [notice, setNotice] = useState<NoticeState>(null);
  const [isPreviewModalOpen, setIsPreviewModalOpen] = useState(false);
  const [paperSize, setPaperSize] = useState<keyof typeof paperSizes>('A4');
  const [temaKegiatan, setTemaKegiatan] = useState('');
  const [showTablePopup, setShowTablePopup] = useState(false);
  const [tableRows, setTableRows] = useState(3);
  const [tableCols, setTableCols] = useState(3);
  const [selectedCell, setSelectedCell] = useState<HTMLTableCellElement | null>(null);

  const editorRef = useRef<HTMLDivElement | null>(null);
  const selectionRef = useRef<Range | null>(null);
  const editorInitialized = useRef(false);

  const mode = isSisip ? 'insert' : suratId ? 'edit' : 'create';
  const pageTitle = mode === 'insert' ? 'Sisip Surat Keluar' : mode === 'edit' ? 'Edit Surat Keluar' : 'Tambah Surat Keluar';

  useEffect(() => {
    editorInitialized.current = false;
  }, [suratId, isSisip]);

  const { data: formOptionsResponse } = useApiQuery(
    ['surat-keluar-form-options'],
    () => suratKeluarService.formOptions(),
  );

  const {
    data: detailResponse,
    isLoading: isLoadingDetail,
    error: detailError,
    refetch: refetchDetail,
  } = useApiQuery(
    ['surat-keluar-detail', suratId],
    () => suratKeluarService.get(suratId as number),
    { enabled: suratId !== null },
  );

  const formOptions = formOptionsResponse?.data;
  const selectedItem = detailResponse?.data;

  useEffect(() => {
    if (!formOptions) return;

    setAlamatKantor(getSetting(formOptions, 'ALAMAT_KANTOR', 'Jl. Cakradireja No 36 Nagasari Karawang'));
    setSignerName(getSetting(formOptions, 'KEPALA_KANTOR', 'Kepala Kantor'));
    setSignerNip(getSetting(formOptions, 'NIP_KEPALA', ''));
    setTemaKegiatan(getSetting(formOptions, 'TEMA_KEGIATAN', ''));

    if (mode === 'create') {
      setFormData((previous) => ({
        ...previous,
        nomor: previous.nomor || padNomor(formOptions.nomor_baru),
        dari: previous.dari || getSetting(formOptions, 'NAMA_KANTOR', ''),
      }));
    }
  }, [formOptions, mode]);

  useEffect(() => {
    if (!selectedItem) return;

    setFormData({
      thn: selectedItem.thn ?? String(new Date().getFullYear()),
      tanggal: selectedItem.tanggal ?? '',
      nomor: selectedItem.nomor ?? '',
      dari: selectedItem.dari ?? '',
      tujuan: selectedItem.tujuan ?? '',
      perihal: selectedItem.perihal ?? '',
      lampiran: selectedItem.lampiran ?? 0,
      sifat: selectedItem.sifat ?? 'Biasa',
    });
    setTembusanInput(parseTembusan(selectedItem.tembusan));
    setBodyHtml(selectedItem.isi_surat || defaultBody);
  }, [selectedItem]);

  useEffect(() => {
    if (editorRef.current && !isLoadingDetail && !editorInitialized.current) {
      editorRef.current.innerHTML = bodyHtml;
      editorRef.current.dir = 'ltr';
      editorRef.current.style.direction = 'ltr';
      editorInitialized.current = true;
    }
  }, [bodyHtml, isLoadingDetail]);

  const saveMutation = useApiMutation(
    async (payload: Record<string, unknown>) => {
      if (mode === 'insert') return suratKeluarService.insert(payload);
      if (mode === 'edit' && suratId) return suratKeluarService.update(suratId, payload);
      return suratKeluarService.create(payload);
    },
    {
      invalidateKeys: [['surat-keluar'], ['surat-keluar-years'], ['surat-keluar-dates'], ['surat-keluar-list-form-options']],
      onSuccess: (response) => {
        setNotice({ tone: 'success', message: response.message || 'Surat keluar berhasil disimpan.' });
      },
      onError: (mutationError) => {
        setNotice({ tone: 'error', message: mutationError.message || 'Gagal menyimpan surat keluar.' });
      },
    },
  );

  const updateField = <K extends keyof SuratKeluarFormData>(field: K, value: SuratKeluarFormData[K]) => {
    setFormData((previous) => ({ ...previous, [field]: value }));
  };

  const preventFocusLoss = (event: React.MouseEvent) => {
    event.preventDefault();
  };

  const execCmd = (command: string, value?: string) => {
    document.execCommand(command, false, value);
    if (editorRef.current) {
      editorRef.current.focus();
      setBodyHtml(editorRef.current.innerHTML);
    }
  };

  const handleEditorInput = (event: React.FormEvent<HTMLDivElement>) => {
    const tables = event.currentTarget.querySelectorAll('table');
    tables.forEach((table) => {
      table.style.borderCollapse = 'collapse';
      table.querySelectorAll('td, th').forEach((cell) => {
        (cell as HTMLElement).style.border = '1px dotted #d1d5db';
        (cell as HTMLElement).style.padding ||= '5px';
      });
    });
    setBodyHtml(event.currentTarget.innerHTML);
  };

  const handleEditorMouseDown = (event: React.MouseEvent) => {
    const selection = window.getSelection();
    if (selection && selection.rangeCount > 0 && editorRef.current?.contains(selection.anchorNode)) {
      selectionRef.current = selection.getRangeAt(0);
    }

    let target = event.target as Node | null;
    while (target && target !== editorRef.current) {
      if (target.nodeName === 'TD' || target.nodeName === 'TH') {
        setSelectedCell(target as HTMLTableCellElement);
        return;
      }
      target = target.parentNode;
    }
    setSelectedCell(null);
  };

  const insertTable = () => {
    let html = '<table style="width: 100%; border-collapse: collapse; border: 1px solid black; margin: 10px 0;"><tbody>';
    for (let row = 0; row < tableRows; row += 1) {
      html += '<tr>';
      for (let col = 0; col < tableCols; col += 1) {
        html += '<td style="border: 1px solid black; padding: 5px;">&nbsp;</td>';
      }
      html += '</tr>';
    }
    html += '</tbody></table><p><br /></p>';

    if (editorRef.current) {
      editorRef.current.focus();
      const selection = window.getSelection();
      if (selection && selectionRef.current) {
        selection.removeAllRanges();
        selection.addRange(selectionRef.current);
      }
      document.execCommand('insertHTML', false, html);
      setBodyHtml(editorRef.current.innerHTML);
    }
    setShowTablePopup(false);
  };

  const tableAction = (action: 'addRow' | 'addCol' | 'delRow' | 'delCol' | 'deleteTable') => {
    if (!selectedCell) return;
    const row = selectedCell.closest('tr');
    const table = selectedCell.closest('table');
    if (!row || !table) return;

    const rowIndex = row.rowIndex;
    const cellIndex = selectedCell.cellIndex;

    if (action === 'deleteTable') {
      table.remove();
      setSelectedCell(null);
    } else if (action === 'addRow') {
      const newRow = table.insertRow(rowIndex + 1);
      Array.from(row.cells).forEach((_, index) => {
        const newCell = newRow.insertCell(index);
        newCell.innerHTML = '&nbsp;';
        newCell.style.border = '1px solid black';
        newCell.style.padding = '5px';
      });
    } else if (action === 'delRow') {
      table.deleteRow(rowIndex);
      setSelectedCell(null);
    } else if (action === 'addCol') {
      Array.from(table.rows).forEach((tableRow) => {
        const newCell = tableRow.insertCell(cellIndex + 1);
        newCell.innerHTML = '&nbsp;';
        newCell.style.border = '1px solid black';
        newCell.style.padding = '5px';
      });
    } else if (action === 'delCol') {
      Array.from(table.rows).forEach((tableRow) => {
        if (tableRow.cells.length > cellIndex) tableRow.deleteCell(cellIndex);
      });
      setSelectedCell(null);
    }

    if (editorRef.current) setBodyHtml(editorRef.current.innerHTML);
  };

  const setCellBackground = (color: string) => {
    if (!selectedCell) return;
    selectedCell.style.backgroundColor = color;
    if (editorRef.current) setBodyHtml(editorRef.current.innerHTML);
  };

  const setTableBorder = (style: 'solid' | 'none') => {
    if (!selectedCell) return;
    const table = selectedCell.closest('table');
    if (!table) return;

    table.style.border = style === 'solid' ? '1px solid black' : 'none';
    table.querySelectorAll('td, th').forEach((cell) => {
      (cell as HTMLElement).style.border = style === 'solid' ? '1px solid black' : 'none';
    });
    if (editorRef.current) setBodyHtml(editorRef.current.innerHTML);
  };

  const buildPayload = () => {
    const currentBody = editorRef.current?.innerHTML ?? bodyHtml;
    if (!formData.tanggal || !formData.dari || !formData.tujuan || !formData.perihal || !stripHtml(currentBody)) {
      setNotice({ tone: 'error', message: 'Tanggal, dari, tujuan, perihal, dan isi surat wajib diisi.' });
      return null;
    }

    if (mode === 'edit' && !formData.nomor) {
      setNotice({ tone: 'error', message: 'Nomor wajib diisi untuk edit surat.' });
      return null;
    }

    if (mode === 'insert' && !suratId) {
      setNotice({ tone: 'error', message: 'Data referensi surat sisip tidak ditemukan.' });
      return null;
    }

    const tembusan = splitTembusan(tembusanInput);
    const payload: Record<string, unknown> = {
      thn: formData.thn || String(new Date().getFullYear()),
      tanggal: formData.tanggal,
      nomor: formData.nomor || null,
      dari: formData.dari,
      tujuan: formData.tujuan,
      perihal: formData.perihal,
      isi_surat: currentBody,
      lampiran: formData.lampiran || 0,
      tembusan: tembusan.length ? tembusan : null,
      sifat: formData.sifat || 'Biasa',
    };

    if (mode === 'insert') {
      payload.id = suratId;
      payload.tahun = formData.thn;
      payload.no_sisip = formData.nomor;
      payload.nomor_sisip = formData.nomor;
    }

    return payload;
  };

  const handleSubmit = async () => {
    const payload = buildPayload();
    if (!payload) return;
    setNotice(null);
    await saveMutation.mutateAsync(payload);
  };

  const letterHtml = useMemo(
    () =>
      buildLetterHtml({
        formData,
        bodyHtml,
        tembusanInput,
        recipientLocation,
        signerName,
        signerPosition,
        signerNip,
        alamatKantor,
        formattedNomor: buildFormattedNomor(formData, formOptions),
        paperSize,
        temaKegiatan,
      }),
    [alamatKantor, bodyHtml, formData, formOptions, paperSize, recipientLocation, signerName, signerNip, signerPosition, tembusanInput, temaKegiatan],
  );

  const handlePrint = () => {
    const size = paperSizes[paperSize];
    downloadPrintHtml(`
      <style>
        @page { size: ${size.width} ${size.height}; margin: 0; }
        body { margin: 0; padding: 0; font-family: "Times New Roman", Times, serif; }
        .surat-keluar-paragraph-indent p,
        .surat-keluar-paragraph-indent div { text-indent: 2em; margin: 0 0 0.75em; }
        .surat-keluar-paragraph-indent p:last-child,
        .surat-keluar-paragraph-indent div:last-child { margin-bottom: 0; }
      </style>
      ${letterHtml}
    `);
  };

  if (isLoadingDetail) {
    return (
      <div className="space-y-6">
        <PageHeader title={pageTitle} description="Memuat data surat keluar." />
        <div className="bg-white/65 dark:bg-white/[0.04] glass-strong rounded-[24px] border border-white/50 dark:border-white/10 overflow-hidden">
          <LoadingSkeleton variant="table" message="Memuat data surat..." rows={4} />
        </div>
      </div>
    );
  }

  if (detailError) {
    return (
      <div className="space-y-6">
        <PageHeader title={pageTitle} description="Gagal memuat data surat keluar." />
        <div className="bg-white/65 dark:bg-white/[0.04] glass-strong rounded-[24px] border border-white/50 dark:border-white/10">
          <ApiErrorBoundary error={detailError} onRetry={() => refetchDetail()} title="Gagal Memuat Surat" />
        </div>
      </div>
    );
  }

  return (
    <div className="space-y-6">
      <style>{`
        .rich-text-editor { color: inherit; direction: ltr !important; }
        .rich-text-editor ul { list-style-type: disc!important; padding-left: 1.5rem!important; margin: 0.5rem 0!important; }
        .rich-text-editor ol { list-style-type: decimal!important; padding-left: 1.5rem!important; margin: 0.5rem 0!important; }
        .rich-text-editor li { margin: 0.25rem 0!important; }
        .rich-text-editor table { border-collapse: collapse; width: 100%; }
        .rich-text-editor table td, .rich-text-editor table th { border: 1px dotted #d1d5db !important; padding: 5px; }
        .preview-content table { border-collapse: collapse; }
        .surat-keluar-paragraph-indent p,
        .surat-keluar-paragraph-indent div { text-indent: 2em; margin: 0 0 0.75em; }
        .surat-keluar-paragraph-indent p:last-child,
        .surat-keluar-paragraph-indent div:last-child { margin-bottom: 0; }
      `}</style>

      <PageHeader
        title={pageTitle}
        description="Lengkapi data surat keluar, susun isi surat, lalu pratinjau sebelum cetak."
        actions={
          <div className="flex flex-wrap gap-2">
            <Link
              to={'/umum/surat/keluar' as any}
              className="flex items-center gap-1.5 px-4 py-2 rounded-full bg-white/70 dark:bg-white/10 border border-white/50 dark:border-white/20 text-gray-700 dark:text-gray-300 text-sm font-semibold shadow-sm hover:bg-white transition"
            >
              <ArrowLeft className="h-4 w-4" />
              Kembali
            </Link>
            <button
              type="button"
              onClick={() => setIsPreviewModalOpen(true)}
              className="flex items-center gap-1.5 px-4 py-2 rounded-full bg-white/70 dark:bg-white/10 border border-white/50 dark:border-white/20 text-gray-700 dark:text-gray-300 text-sm font-semibold shadow-sm hover:bg-white transition"
            >
              <Eye className="h-4 w-4" />
              Pratinjau
            </button>
            <button
              type="button"
              onClick={() => void handleSubmit()}
              disabled={saveMutation.isPending}
              className="flex items-center gap-1.5 px-4 py-2 rounded-full bg-amber-300 hover:bg-amber-400 text-gray-900 text-sm font-semibold shadow-lg shadow-amber-500/20 transition disabled:opacity-50"
            >
              {saveMutation.isPending ? <Loader2 className="h-4 w-4 animate-spin" /> : <Save className="h-4 w-4" />}
              {saveMutation.isPending ? 'Menyimpan...' : 'Simpan'}
            </button>
          </div>
        }
      />

      {notice && <Notice notice={notice} />}

      <div className="grid gap-5 xl:grid-cols-[minmax(0,0.92fr)_minmax(460px,1.08fr)]">
        <div className="space-y-5">
          <div className="bg-white/65 dark:bg-white/[0.04] glass-strong rounded-[24px] border border-white/50 dark:border-white/10 p-5 shadow-sm">
            <h2 className="mb-4 text-base font-bold text-gray-900 dark:text-white">Detail Surat</h2>
            <div className="grid gap-4 sm:grid-cols-2">
              <FormField label="Tanggal" required>
                <DatePicker value={formData.tanggal} onChange={(value) => updateField('tanggal', value)} />
              </FormField>
              <FormField label="Tahun">
                <TextInput value={formData.thn} disabled={mode === 'insert'} onChange={(value) => updateField('thn', value)} />
              </FormField>
              <FormField label="Nomor">
                <TextInput value={formData.nomor} disabled={mode === 'insert'} placeholder="Otomatis jika kosong" onChange={(value) => updateField('nomor', value)} />
              </FormField>
              <FormField label="Dari" required>
                <TextInput value={formData.dari} onChange={(value) => updateField('dari', value)} />
              </FormField>
              <FormField label="Tujuan" required>
                <TextInput value={formData.tujuan} onChange={(value) => updateField('tujuan', value)} />
              </FormField>
              <FormField label="Lokasi Penerima">
                <TextInput value={recipientLocation} placeholder="Tempat" onChange={setRecipientLocation} />
              </FormField>
              <FormField label="Perihal" required className="sm:col-span-2">
                <TextInput value={formData.perihal} onChange={(value) => updateField('perihal', value)} />
              </FormField>
              <FormField label="Lampiran">
                <TextInput type="number" value={String(formData.lampiran)} onChange={(value) => updateField('lampiran', Number(value) || 0)} />
              </FormField>
              <FormField label="Sifat">
                <select
                  value={formData.sifat}
                  onChange={(event) => updateField('sifat', event.target.value)}
                  className="h-11 w-full rounded-xl border border-white/50 bg-white/70 px-4 text-sm text-gray-900 outline-none transition focus:border-amber-500 dark:border-white/10 dark:bg-white/[0.04] dark:text-white dark:[color-scheme:dark]"
                >
                  <option value="Biasa">Biasa</option>
                  <option value="Penting">Penting</option>
                  <option value="Segera">Segera</option>
                  <option value="Rahasia">Rahasia</option>
                </select>
              </FormField>
              <FormField label="Tembusan (pisahkan dengan ;)" className="sm:col-span-2">
                <TextInput value={tembusanInput} placeholder="Contoh: Kepala; Sekretaris" onChange={setTembusanInput} />
              </FormField>
            </div>
          </div>

          <div className="bg-white/65 dark:bg-white/[0.04] glass-strong rounded-[24px] border border-white/50 dark:border-white/10 p-5 shadow-sm">
            <h2 className="mb-4 text-base font-bold text-gray-900 dark:text-white">Penandatangan</h2>
            <div className="grid gap-4 sm:grid-cols-2">
              <FormField label="Nama">
                <TextInput value={signerName} onChange={setSignerName} />
              </FormField>
              <FormField label="Jabatan">
                <TextInput value={signerPosition} onChange={setSignerPosition} />
              </FormField>
              <FormField label="NIP" className="sm:col-span-2">
                <TextInput value={signerNip} placeholder="Opsional" onChange={setSignerNip} />
              </FormField>
            </div>
          </div>
        </div>

        <div className="bg-white/65 dark:bg-white/[0.04] glass-strong rounded-[24px] border border-white/50 dark:border-white/10 p-5 shadow-sm">
          <div className="mb-4 flex items-center justify-between gap-3">
            <h2 className="text-base font-bold text-gray-900 dark:text-white">Isi Surat</h2>
            <p className="text-[11px] font-bold uppercase tracking-widest text-gray-500 dark:text-gray-400">{buildFormattedNomor(formData, formOptions)}</p>
          </div>

          <div className="mb-4 flex flex-wrap items-center gap-1 rounded-2xl border border-gray-200/70 bg-white/70 p-2 dark:border-white/10 dark:bg-white/[0.04]">
            <ToolbarButton label="Bold" onMouseDown={preventFocusLoss} onClick={() => execCmd('bold')}><Bold className="h-4 w-4" /></ToolbarButton>
            <ToolbarButton label="Italic" onMouseDown={preventFocusLoss} onClick={() => execCmd('italic')}><Italic className="h-4 w-4" /></ToolbarButton>
            <ToolbarButton label="Underline" onMouseDown={preventFocusLoss} onClick={() => execCmd('underline')}><Underline className="h-4 w-4" /></ToolbarButton>
            <Divider />
            <ToolbarButton label="Rata kiri" onMouseDown={preventFocusLoss} onClick={() => execCmd('justifyLeft')}><AlignLeft className="h-4 w-4" /></ToolbarButton>
            <ToolbarButton label="Rata tengah" onMouseDown={preventFocusLoss} onClick={() => execCmd('justifyCenter')}><AlignCenter className="h-4 w-4" /></ToolbarButton>
            <ToolbarButton label="Rata kanan" onMouseDown={preventFocusLoss} onClick={() => execCmd('justifyRight')}><AlignRight className="h-4 w-4" /></ToolbarButton>
            <ToolbarButton label="Rata penuh" onMouseDown={preventFocusLoss} onClick={() => execCmd('justifyFull')}><AlignJustify className="h-4 w-4" /></ToolbarButton>
            <Divider />
            <ToolbarButton label="Bullet list" onMouseDown={preventFocusLoss} onClick={() => execCmd('insertUnorderedList')}><List className="h-4 w-4" /></ToolbarButton>
            <ToolbarButton label="Numbered list" onMouseDown={preventFocusLoss} onClick={() => execCmd('insertOrderedList')}><ListOrdered className="h-4 w-4" /></ToolbarButton>
            <Divider />

            <div className="relative">
              <ToolbarButton label="Sisipkan tabel" active={showTablePopup} onMouseDown={preventFocusLoss} onClick={() => setShowTablePopup((previous) => !previous)}>
                <Table className="h-4 w-4" />
              </ToolbarButton>
              {showTablePopup && (
                <div className="absolute left-0 top-full z-40 mt-2 w-52 rounded-2xl border border-gray-200 bg-white p-3 shadow-xl dark:border-white/10 dark:bg-gray-950">
                  <p className="mb-3 text-[10px] font-bold uppercase tracking-widest text-gray-500">Buat Tabel</p>
                  <div className="grid grid-cols-2 gap-2">
                    <SmallNumberInput label="Baris" value={tableRows} min={1} max={20} onChange={setTableRows} />
                    <SmallNumberInput label="Kolom" value={tableCols} min={1} max={10} onChange={setTableCols} />
                  </div>
                  <div className="mt-3 flex gap-2">
                    <button type="button" onClick={() => setShowTablePopup(false)} className="flex-1 rounded-full bg-gray-100 py-1.5 text-xs font-bold text-gray-700 hover:bg-gray-200 dark:bg-white/10 dark:text-gray-200">Batal</button>
                    <button type="button" onClick={insertTable} className="flex-1 rounded-full bg-amber-300 py-1.5 text-xs font-bold text-gray-900 hover:bg-amber-400">Sisipkan</button>
                  </div>
                </div>
              )}
            </div>

            {selectedCell && (
              <div className="ml-1 flex flex-wrap items-center gap-1 rounded-xl border border-gray-200 bg-white px-2 py-1 dark:border-white/10 dark:bg-gray-950">
                <span className="px-1 text-[10px] font-bold uppercase tracking-widest text-gray-400">Sel</span>
                <ToolbarButton label="Tambah baris" onMouseDown={preventFocusLoss} onClick={() => tableAction('addRow')}><Rows className="h-4 w-4" /><Plus className="-ml-1 h-2.5 w-2.5" /></ToolbarButton>
                <ToolbarButton label="Tambah kolom" onMouseDown={preventFocusLoss} onClick={() => tableAction('addCol')}><Columns className="h-4 w-4" /><Plus className="-ml-1 h-2.5 w-2.5" /></ToolbarButton>
                <ToolbarButton label="Hapus baris" onMouseDown={preventFocusLoss} onClick={() => tableAction('delRow')}><Rows className="h-4 w-4" /><Trash2 className="-ml-1 h-2.5 w-2.5" /></ToolbarButton>
                <ToolbarButton label="Hapus kolom" onMouseDown={preventFocusLoss} onClick={() => tableAction('delCol')}><Columns className="h-4 w-4" /><Trash2 className="-ml-1 h-2.5 w-2.5" /></ToolbarButton>
                <ToolbarButton label="Border solid" onMouseDown={preventFocusLoss} onClick={() => setTableBorder('solid')}><Grid className="h-4 w-4" /></ToolbarButton>
                <ToolbarButton label="Tanpa border" onMouseDown={preventFocusLoss} onClick={() => setTableBorder('none')}><Square className="h-4 w-4" /></ToolbarButton>
                <ColorPicker onMouseDown={preventFocusLoss} onSelect={setCellBackground} />
                <ToolbarButton label="Hapus tabel" onMouseDown={preventFocusLoss} onClick={() => tableAction('deleteTable')}><Trash2 className="h-4 w-4" /></ToolbarButton>
              </div>
            )}

            <ToolbarButton label="Reset isi" onClick={() => {
              setBodyHtml(defaultBody);
              if (editorRef.current) editorRef.current.innerHTML = defaultBody;
            }}>
              <RefreshCw className="h-4 w-4" />
            </ToolbarButton>
          </div>

          <div className="rounded-2xl border border-gray-200/70 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-gray-950">
            <div
              ref={editorRef}
              contentEditable
              suppressContentEditableWarning
              className="rich-text-editor min-h-[470px] text-sm leading-6 text-gray-900 outline-none dark:text-white"
              style={{ fontFamily: '"Times New Roman", Times, serif', direction: 'ltr' }}
              onInput={handleEditorInput}
              onMouseDown={handleEditorMouseDown}
            />
          </div>
        </div>
      </div>

      <PreviewModal
        open={isPreviewModalOpen}
        onClose={() => setIsPreviewModalOpen(false)}
        paperSize={paperSize}
        setPaperSize={setPaperSize}
        letterHtml={letterHtml}
        onPrint={handlePrint}
      />
    </div>
  );
}

export function SuratTugas() {
  return <SuratPage jenis="tugas" title="Surat Tugas" description="Kelola surat tugas untuk petugas lapangan." />;
}

export function SuratKeputusan() {
  return <SuratPage jenis="keputusan" title="Surat Keputusan" description="Arsip surat keputusan dan penetapan." />;
}


function SuratPage({ jenis, title, description }: SuratPageProps) {
  const data = staticSuratByJenis[jenis] ?? [];

  return (
    <div className="space-y-6">
      <PageHeader
        title={title}
        description={description}
        actions={
          <button 
            data-scan="tombol buat surat"
            className="flex items-center gap-1.5 px-4 py-2 rounded-full bg-amber-300 hover:bg-amber-400 text-gray-900 text-sm font-semibold shadow-lg shadow-amber-500/20 transition"
          >
            <Plus className="h-4 w-4" />
            Buat Surat
          </button>
        }
      />

      <div 
        data-scan="tabel surat"
        className="bg-white/65 dark:bg-white/[0.04] glass-strong rounded-[24px] border border-white/50 dark:border-white/10 shadow-[0_10px_40px_-15px_rgba(0,0,0,0.08)] overflow-hidden"
      >
        <div className="overflow-x-auto">
          <table className="w-full text-[13px]">
            <thead>
              <tr className="text-left text-[11.5px] text-gray-500 dark:text-gray-400 border-b border-gray-200/60 dark:border-white/10">
                {['Nomor Surat', 'Perihal', 'Tanggal', 'Pengirim', 'Penerima', 'Status'].map((header) => (
                  <th key={header} className="px-6 py-5 font-bold uppercase tracking-widest bg-gray-50/30 dark:bg-white/5 whitespace-nowrap">
                    {header}
                  </th>
                ))}
              </tr>
            </thead>
            <tbody className="divide-y divide-gray-100/70 dark:divide-white/5">
              {data.map((item) => (
                <tr key={item.id} className="hover:bg-white/40 dark:hover:bg-white/[0.02] transition-colors">
                  <td className="px-6 py-4">
                    <div className="flex flex-col gap-0.5">
                      <span className="font-bold text-gray-900 dark:text-white text-[13px] leading-tight">{item.nomorSurat}</span>
                      <span className="text-[11px] text-gray-500">{item.id}</span>
                    </div>
                  </td>
                  <td className="px-6 py-4"><span className="block max-w-[300px] truncate text-[13px] font-medium text-gray-700 dark:text-gray-300">{item.perihal}</span></td>
                  <td className="px-6 py-4"><span className="text-[13px] text-gray-600 dark:text-gray-400">{item.tanggal}</span></td>
                  <td className="px-6 py-4"><span className="text-[13px] text-gray-600 dark:text-gray-400">{item.pengirim}</span></td>
                  <td className="px-6 py-4"><span className="text-[13px] text-gray-600 dark:text-gray-400">{item.penerima}</span></td>
                  <td className="px-6 py-4">
                    <span className={`px-2 py-0.5 rounded-md border text-[10px] font-bold uppercase tracking-wider ${statusStyles[item.status]}`}>
                      {item.status}
                    </span>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
        <div 
          data-scan="navigasi halaman"
          className="flex items-center justify-between px-6 py-5 border-t border-gray-200/60 dark:border-white/10 dark:bg-white/5"
        >
          <p className="text-[12px] text-gray-500 dark:text-gray-400 font-medium">
            Total {data.length} surat
          </p>
        </div>
      </div>
    </div>
  );
}

function SuratKeluarActionMenu({
  item,
  isOpen,
  onToggle,
  onDelete,
}: {
  item: SuratKeluarItem;
  isOpen: boolean;
  onToggle: () => void;
  onDelete: () => void;
}) {
  return (
    <div className="relative flex justify-end">
      <button
        type="button"
        aria-label={`Menu aksi ${item.no_surat ?? item.id}`}
        onClick={onToggle}
        className="inline-flex size-9 items-center justify-center rounded-lg text-gray-500 outline-none transition hover:bg-gray-100 hover:text-amber-600 dark:hover:bg-white/10 dark:hover:text-amber-400"
      >
        <Ellipsis className="h-4 w-4" />
      </button>
      {isOpen && (
        <div className="absolute right-0 top-full z-50 mt-1 min-w-[160px] overflow-hidden rounded-xl border border-gray-200 bg-white py-1 shadow-xl dark:border-white/10 dark:bg-gray-950">
          <Link
            data-scan="tombol edit surat"
            to="/umum/surat/keluar/form"
            search={{ id: String(item.id) }}
            className="flex w-full items-center gap-2 px-3 py-2 text-left text-xs font-bold text-gray-700 transition-colors hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-white/5"
          >
            <FileText className="h-3.5 w-3.5 text-amber-500" />
            Edit Surat
          </Link>
          <Link
            data-scan="tombol sisip surat"
            to="/umum/surat/keluar/form"
            search={{ id: String(item.id), sisip: 'true' }}
            className="flex w-full items-center gap-2 px-3 py-2 text-left text-xs font-bold text-gray-700 transition-colors hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-white/5"
          >
            <Plus className="h-3.5 w-3.5 text-blue-500" />
            Sisip Surat
          </Link>
          <div className="mx-2 h-px bg-gray-100 dark:bg-white/5" />
          <button
            data-scan="tombol hapus surat"
            type="button"
            onClick={onDelete}
            className="flex w-full items-center gap-2 px-3 py-2 text-left text-xs font-bold text-red-600 transition-colors hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-500/10"
          >
            <Trash2 className="h-3.5 w-3.5" />
            Hapus Surat
          </button>
        </div>
      )}
    </div>
  );
}

function Notice({ notice }: { notice: Exclude<NoticeState, null> }) {
  return (
    <div
      className={`rounded-[20px] border px-4 py-3 text-sm ${
        notice.tone === 'success'
          ? 'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-300'
          : 'border-red-200 bg-red-50 text-red-700 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-300'
      }`}
    >
      {notice.message}
    </div>
  );
}

function FilterSelect({
  label,
  value,
  onChange,
  children,
}: {
  label: string;
  value: string;
  onChange: (value: string) => void;
  children: React.ReactNode;
}) {
  return (
    <div className="flex flex-col gap-2">
      <label className="text-[10px] font-bold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">{label}</label>
      <select
        data-scan={`filter ${label.toLowerCase()}`}
        value={value}
        onChange={(event) => onChange(event.target.value)}
        className="h-10 rounded-xl border border-gray-200 dark:border-white/10 bg-white/70 dark:bg-white/5 px-4 text-xs font-bold text-gray-700 dark:text-gray-200 outline-none transition focus:border-amber-500 dark:[color-scheme:dark]"
      >
        {children}
      </select>
    </div>
  );
}

function EmptyState({ title, description }: { title: string; description: string }) {
  return (
    <div className="flex flex-col items-center justify-center px-6 py-16 text-center">
      <div className="mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-gray-100 text-gray-400 dark:bg-white/10">
        <FileText className="h-7 w-7" />
      </div>
      <h3 className="text-sm font-bold text-gray-900 dark:text-white">{title}</h3>
      <p className="mt-1 max-w-md text-xs text-gray-500 dark:text-gray-400">{description}</p>
    </div>
  );
}

function FormField({
  label,
  required,
  className = '',
  children,
}: {
  label: string;
  required?: boolean;
  className?: string;
  children: React.ReactNode;
}) {
  return (
    <label className={`block space-y-1.5 ${className}`}>
      <span className="block text-[11px] font-bold uppercase tracking-widest text-gray-500 dark:text-gray-400">
        {label}
        {required ? <span className="ml-0.5 text-red-500">*</span> : null}
      </span>
      {children}
    </label>
  );
}

function TextInput({
  value,
  onChange,
  placeholder,
  disabled,
  type = 'text',
}: {
  value: string;
  onChange: (value: string) => void;
  placeholder?: string;
  disabled?: boolean;
  type?: 'text' | 'number';
}) {
  return (
    <input
      type={type}
      value={value}
      disabled={disabled}
      placeholder={placeholder}
      onChange={(event) => onChange(event.target.value)}
      className="h-11 w-full rounded-xl border border-white/50 bg-white/70 px-4 text-sm text-gray-900 outline-none transition placeholder:text-gray-400 focus:border-amber-500 disabled:cursor-not-allowed disabled:opacity-60 dark:border-white/10 dark:bg-white/[0.04] dark:text-white"
    />
  );
}

function ToolbarButton({
  label,
  children,
  active,
  onMouseDown,
  onClick,
}: {
  label: string;
  children: React.ReactNode;
  active?: boolean;
  onMouseDown?: (event: React.MouseEvent) => void;
  onClick: (event: React.MouseEvent) => void;
}) {
  return (
    <button
      type="button"
      title={label}
      aria-label={label}
      onMouseDown={onMouseDown}
      onClick={onClick}
      className={`inline-flex size-8 items-center justify-center rounded-lg text-gray-600 transition hover:bg-gray-100 hover:text-amber-600 dark:text-gray-300 dark:hover:bg-white/10 dark:hover:text-amber-400 ${
        active ? 'bg-amber-300 text-gray-900 hover:bg-amber-300 hover:text-gray-900' : ''
      }`}
    >
      {children}
    </button>
  );
}

function Divider() {
  return <div className="mx-1 h-6 w-px bg-gray-200 dark:bg-white/10" />;
}

function SmallNumberInput({
  label,
  value,
  min,
  max,
  onChange,
}: {
  label: string;
  value: number;
  min: number;
  max: number;
  onChange: (value: number) => void;
}) {
  return (
    <label className="space-y-1">
      <span className="block text-[10px] font-bold uppercase tracking-widest text-gray-500">{label}</span>
      <input
        type="number"
        value={value}
        min={min}
        max={max}
        onChange={(event) => onChange(Number(event.target.value) || min)}
        className="h-9 w-full rounded-lg border border-gray-200 bg-white px-2 text-sm dark:border-white/10 dark:bg-white/5"
      />
    </label>
  );
}

function ColorPicker({
  onMouseDown,
  onSelect,
}: {
  onMouseDown: (event: React.MouseEvent) => void;
  onSelect: (color: string) => void;
}) {
  return (
    <div className="group relative">
      <ToolbarButton label="Warna sel" onMouseDown={onMouseDown} onClick={() => undefined}>
        <PaintBucket className="h-4 w-4" />
      </ToolbarButton>
      <div className="absolute left-0 top-full z-40 hidden w-28 grid-cols-4 gap-1 rounded-xl border border-gray-200 bg-white p-2 shadow-lg group-hover:grid dark:border-white/10 dark:bg-gray-950">
        {['#ffffff', '#f3f4f6', '#dbeafe', '#dcfce7', '#ffedd5', '#fce7f3', '#d1d5db', '#111827'].map((color) => (
          <button
            key={color}
            type="button"
            onMouseDown={onMouseDown}
            aria-label={`Warna ${color}`}
            className="size-5 rounded border border-gray-200"
            style={{ backgroundColor: color }}
            onClick={() => onSelect(color)}
          />
        ))}
      </div>
    </div>
  );
}

function PreviewModal({
  open,
  onClose,
  paperSize,
  setPaperSize,
  letterHtml,
  onPrint,
}: {
  open: boolean;
  onClose: () => void;
  paperSize: keyof typeof paperSizes;
  setPaperSize: (value: keyof typeof paperSizes) => void;
  letterHtml: string;
  onPrint: () => void;
}) {
  if (!open) return null;
  const size = paperSizes[paperSize];

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center">
      <div className="absolute inset-0 bg-black/50 backdrop-blur-sm" onClick={onClose} />
      <div className="relative mx-4 flex h-[94vh] w-[96vw] flex-col overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-2xl dark:border-white/10 dark:bg-gray-950">
        <div className="flex flex-wrap items-center justify-between gap-3 border-b border-gray-200/70 px-5 py-4 dark:border-white/10">
          <div>
            <h2 className="text-base font-bold text-gray-900 dark:text-white">Pratinjau Surat</h2>
            <p className="text-xs text-gray-500 dark:text-gray-400">Preview cetak sesuai ukuran kertas.</p>
          </div>
          <div className="flex items-center gap-2">
            <select
              value={paperSize}
              onChange={(event) => setPaperSize(event.target.value as keyof typeof paperSizes)}
              className="h-9 rounded-xl border border-gray-200 bg-white px-3 text-xs font-bold text-gray-700 dark:border-white/10 dark:bg-white/5 dark:text-gray-200 dark:[color-scheme:dark]"
            >
              {Object.entries(paperSizes).map(([key, value]) => (
                <option key={key} value={key}>{value.label}</option>
              ))}
            </select>
            <button type="button" onClick={onPrint} className="flex items-center gap-1.5 rounded-full bg-slate-900 px-4 py-2 text-xs font-bold text-white shadow-sm dark:bg-white dark:text-slate-900">
              <Printer className="h-3.5 w-3.5" />
              Cetak
            </button>
            <button type="button" onClick={onClose} className="inline-flex size-9 items-center justify-center rounded-lg text-gray-400 transition hover:bg-gray-100 dark:hover:bg-white/10">
              <X className="h-4 w-4" />
            </button>
          </div>
        </div>
        <div className="flex-1 overflow-auto bg-gray-100 p-6 dark:bg-black/30">
          <div className="mx-auto overflow-hidden bg-white shadow-xl" style={{ width: size.width, minHeight: size.height }} dangerouslySetInnerHTML={{ __html: letterHtml }} />
        </div>
      </div>
    </div>
  );
}

function buildLetterHtml({
  formData,
  bodyHtml,
  tembusanInput,
  recipientLocation,
  signerName,
  signerPosition,
  signerNip,
  alamatKantor,
  formattedNomor,
  temaKegiatan,
}: {
  formData: SuratKeluarFormData;
  bodyHtml: string;
  tembusanInput: string;
  recipientLocation: string;
  signerName: string;
  signerPosition: string;
  signerNip: string;
  alamatKantor: string;
  formattedNomor: string;
  paperSize: keyof typeof paperSizes;
  temaKegiatan?: string;
}) {
  const tanggal = formData.tanggal
    ? new Date(formData.tanggal).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' })
    : '-';
  const tembusan = splitTembusan(tembusanInput);

  return `
    <div style="font-family: 'Times New Roman', Times, serif; color: black; width: 100%; min-height: 100%; background: white;">
      <div style="display: flex; align-items: center; justify-content: space-between; padding: 1mm 15mm 8px 15mm; margin-bottom: 8px;">
        <div style="display: flex; align-items: center;">
          <img src="${logoBpsSrc}" alt="Logo BPS" style="width: 80px; height: 96px; object-fit: contain; margin-right: 16px;" onerror="this.style.display='none'" />
          <div style="text-align: left;">
            <h3 style="margin: 0; font-size: 18px; font-family: Arial, Helvetica, sans-serif; font-weight: bold; font-style: italic;">BADAN PUSAT STATISTIK</h3>
            <p style="margin: 0; font-size: 14px; font-family: Arial, Helvetica, sans-serif; font-weight: bold; font-style: italic;">KABUPATEN KARAWANG</p>
            <p style="margin: 4px 0 0 0; font-size: 10px; font-family: Arial, Helvetica, sans-serif; color: #666;">${alamatKantor}<br />Telp: (0267) 402250, website: https://karawangkab.bps.go.id, email: bps3215@bps.go.id</p>
          </div>
        </div>
        ${
          temaKegiatan
            ? `<img src="${temaKegiatan.startsWith('http') ? temaKegiatan : `${APP_BASE_URL}${temaKegiatan}`}" alt="Tema Kegiatan" style="height: 64px; object-fit: contain;" onerror="this.style.display='none'" />`
            : ''
        }
      </div>
      <div style="border-top: 2px solid black; margin: 0 15mm 12px 15mm;"></div>
      <div style="padding: 0 15mm;">
        <div style="margin-bottom: 24px; font-size: 12pt; line-height: 1.15;">
          <table style="width: 100%; border-collapse: collapse;">
            <tbody>
              <tr>
                <td style="width: 15%; vertical-align: top;">Nomor</td>
                <td style="width: 50%; vertical-align: top;">: ${formattedNomor}</td>
                <td style="width: 35%; vertical-align: top; text-align: right;">Karawang, ${tanggal}</td>
              </tr>
              <tr><td>Sifat</td><td>: ${formData.sifat || 'Biasa'}</td><td></td></tr>
              <tr><td>Lampiran</td><td>: ${formData.lampiran || '-'}</td><td></td></tr>
              <tr><td>Perihal</td><td>: ${formData.perihal || '-'}</td><td></td></tr>
            </tbody>
          </table>
        </div>
        <div style="margin-bottom: 24px; font-size: 12pt;">
          <div>Kepada Yth.</div>
          <div style="font-weight: bold;">${formData.tujuan || '-'}</div>
          <div>Di -</div>
          <div style="padding-left: 24pt;">${recipientLocation || 'Tempat'}</div>
        </div>
        <div class="preview-content surat-keluar-paragraph-indent" style="text-align: justify; line-height: 1.5; margin-bottom: 48px; font-size: 12pt;">
          ${bodyHtml}
        </div>
        <div style="display: flex; justify-content: flex-end; font-size: 12pt;">
          <div style="text-align: center; width: 240pt;">
            <div style="margin-bottom: 10px; font-weight: bold; line-height: 1.2;">Badan Pusat Statistik<br />Kabupaten Karawang<br />${signerPosition || 'Kepala'},</div>
            <div style="margin-bottom: 72pt;"></div>
            <div style="font-weight: bold; text-decoration: underline; text-transform: uppercase;">${signerName || '-'}</div>
            ${signerNip ? `<div style="font-size: 10pt;">NIP. ${signerNip}</div>` : ''}
          </div>
        </div>
        ${
          tembusan.length
            ? `<div style="margin-top: 24px; font-size: 10pt;"><div style="font-weight: bold;">Tembusan:</div>${tembusan.map((item, index) => `<div>${index + 1}. ${item}</div>`).join('')}</div>`
            : ''
        }
      </div>
    </div>
  `;
}
