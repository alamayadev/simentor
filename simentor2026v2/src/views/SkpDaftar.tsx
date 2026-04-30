import React, { useEffect, useMemo, useRef, useState } from 'react';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import {
  createColumnHelper,
  flexRender,
  getCoreRowModel,
  useReactTable,
} from '@tanstack/react-table';
import {
  Calendar as CalendarIcon,
  ChevronLeft,
  ChevronRight,
  Download,
  Filter,
  Pencil,
  Plus,
  Search,
  Trash2,
  User,
} from 'lucide-react';
import { PageHeader } from '../components/PageHeader';
import { LoadingSkeleton } from '../components/LoadingSkeleton';
import { ApiErrorBoundary } from '../components/ApiErrorBoundary';
import { ConfirmDialog } from '../components/ConfirmDialog';
import { SkpFileModal, type SkpFileFormValues } from '../components/SkpFileModal';
import { useAuth } from '../hooks/useAuth';
import { useDebounce } from '../hooks/useDebounce';
import { usePagination } from '../hooks/usePagination';
import { APP_BASE_URL, ApiError, apiDelete, apiGet, apiPostForm, apiPutForm } from '../lib/api';

type SkpRow = {
  id: number;
  user_id: number;
  jenis: string;
  nama: string;
  bulan: string | null;
  tahun: string;
  link: string | null;
  konten?: string | null;
  created_at: string | null;
  updated_at: string | null;
  user?: {
    id: number;
    name: string;
    email?: string;
  };
};

type MetaLink = { url: string | null; label: string; active: boolean };

type SkpListResponse = {
  success: boolean;
  message: string;
  data: SkpRow[];
  users?: Record<string, string>;
  listTahun?: string[];
  meta?: {
    per_page?: number;
    has_more?: boolean;
    count?: number;
  };
  links?: {
    next_cursor?: string | null;
    next_page_url?: string | null;
    prev_cursor?: string | null;
    prev_page_url?: string | null;
    path?: string | null;
  };
  pagination_info?: {
    total_page?: number;
    total_records?: number;
  };
};

const columnHelper = createColumnHelper<SkpRow>();

function formatDate(value: string | null): string {
  if (!value) return '-';
  const date = new Date(value);
  if (Number.isNaN(date.getTime())) return value;
  return date.toLocaleDateString('id-ID', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
  });
}

function formatPeriod(row: SkpRow): string {
  return row.bulan ? `${row.bulan}/${row.tahun}` : row.tahun;
}

function buildFormData(values: SkpFileFormValues) {
  const formData = new FormData();
  formData.append('jenis', values.jenis);
  formData.append('tahun', values.tahun);
  if (values.jenis === 'SKP Bulanan' && values.bulan) {
    formData.append('bulan', values.bulan);
  }
  if (values.file) {
    formData.append('file', values.file);
  }
  return formData;
}

function resolveFileUrl(link: string | null) {
  if (!link) return null;
  if (/^https?:\/\//i.test(link)) return link;
  return `${APP_BASE_URL}/${link.replace(/^\/+/, '')}`;
}

function getDefaultUploadValues() {
  const now = new Date();
  const previousMonth = new Date(now.getFullYear(), now.getMonth() - 1, 1);
  return {
    tahun: String(previousMonth.getFullYear()),
    bulan: String(previousMonth.getMonth() + 1).padStart(2, '0'),
  };
}

export function SkpDaftar() {
  const queryClient = useQueryClient();
  const { user } = useAuth();
  const defaults = getDefaultUploadValues();
  const [search, setSearch] = useState('');
  const [filterUserId, setFilterUserId] = useState('');
  const [filterTahun, setFilterTahun] = useState(String(new Date().getFullYear()));
  const [showFilters, setShowFilters] = useState(false);
  const pagination = usePagination(15);
  const [editingRow, setEditingRow] = useState<SkpRow | null>(null);
  const [deleteTarget, setDeleteTarget] = useState<SkpRow | null>(null);
  const [isUploadOpen, setIsUploadOpen] = useState(false);
  const [formError, setFormError] = useState<string | null>(null);
  const hasAppliedDefaults = useRef(false);
  const debouncedSearch = useDebounce(search, 400);

  const { data: response, isLoading, error, refetch } = useQuery<SkpListResponse, ApiError>({
    queryKey: ['skp-list', pagination.params, debouncedSearch, filterUserId, filterTahun],
    queryFn: async () => {
      const params: Record<string, string | number> = {
        ...pagination.params,
        sort_by: 'nama',
        sort_dir: 'ASC',
      };
      if (debouncedSearch) params.search = debouncedSearch;
      if (filterUserId) params.user_id = filterUserId;
      if (filterTahun) params.tahun = filterTahun;
      const result = await apiGet<SkpRow[]>('/kantor/skp/list', params);
      return result as unknown as SkpListResponse;
    },
  });

  useEffect(() => {
    pagination.sync(response);
  }, [response]);

  useEffect(() => {
    if (hasAppliedDefaults.current || !user?.id) return;
    setFilterUserId(String(user.id));
    hasAppliedDefaults.current = true;
  }, [user]);

  const items = response?.data ?? [];
  const users = response?.users ?? {};
  const years = response?.listTahun ?? [];
  const meta = response?.meta ?? null;

  const uploadMutation = useMutation({
    mutationFn: (values: SkpFileFormValues) => apiPostForm('/kantor/skp', buildFormData(values)),
    onSuccess: async () => {
      setFormError(null);
      setIsUploadOpen(false);
      await Promise.all([
        queryClient.invalidateQueries({ queryKey: ['skp-list'] }),
        queryClient.invalidateQueries({ queryKey: ['skp-progress'] }),
        queryClient.invalidateQueries({ queryKey: ['skp-dashboard'] }),
      ]);
    },
    onError: (mutationError: ApiError) => {
      setFormError(mutationError.message ?? 'Gagal mengunggah SKP.');
    },
  });

  const updateMutation = useMutation({
    mutationFn: ({ id, values }: { id: number; values: SkpFileFormValues }) =>
      apiPutForm(`/kantor/skp/${id}`, buildFormData(values)),
    onSuccess: async () => {
      setFormError(null);
      setEditingRow(null);
      await Promise.all([
        queryClient.invalidateQueries({ queryKey: ['skp-list'] }),
        queryClient.invalidateQueries({ queryKey: ['skp-progress'] }),
        queryClient.invalidateQueries({ queryKey: ['skp-dashboard'] }),
      ]);
    },
    onError: (mutationError: ApiError) => {
      setFormError(mutationError.message ?? 'Gagal memperbarui SKP.');
    },
  });

  const deleteMutation = useMutation({
    mutationFn: (id: number) => apiDelete(`/kantor/skp/${id}`),
    onSuccess: async () => {
      setDeleteTarget(null);
      await Promise.all([
        queryClient.invalidateQueries({ queryKey: ['skp-list'] }),
        queryClient.invalidateQueries({ queryKey: ['skp-progress'] }),
        queryClient.invalidateQueries({ queryKey: ['skp-dashboard'] }),
      ]);
    },
    onError: (mutationError: ApiError) => {
      setFormError(mutationError.message ?? 'Gagal menghapus SKP.');
    },
  });

  const handleSearchChange = (event: React.ChangeEvent<HTMLInputElement>) => {
    setSearch(event.target.value);
    pagination.reset();
  };

  const handleFilterUserChange = (event: React.ChangeEvent<HTMLSelectElement>) => {
    setFilterUserId(event.target.value);
    pagination.reset();
  };

  const handleFilterYearChange = (event: React.ChangeEvent<HTMLSelectElement>) => {
    setFilterTahun(event.target.value);
    pagination.reset();
  };

  const columns = useMemo(
    () => [
      columnHelper.accessor('id', {
        header: 'ID',
        cell: (info) => (
          <span className="font-mono text-[11px] font-bold text-gray-500">
            {info.getValue()}
          </span>
        ),
      }),
      columnHelper.accessor('user_id', {
        header: 'Pegawai',
        cell: (info) => {
          const row = info.row.original;
          const name = row.user?.name ?? users[String(info.getValue())] ?? `User #${info.getValue()}`;
          return (
            <div className="flex items-center gap-2">
              <div className="h-7 w-7 rounded-full bg-amber-100 dark:bg-amber-500/20 flex items-center justify-center flex-shrink-0">
                <User className="h-3.5 w-3.5 text-amber-600 dark:text-amber-400" />
              </div>
              <div className="flex flex-col min-w-0">
                <span
                  className="font-bold text-gray-900 dark:text-white leading-tight truncate max-w-[220px]"
                  title={name}
                >
                  {name}
                </span>
                <span className="text-[11px] text-gray-500">{row.jenis}</span>
              </div>
            </div>
          );
        },
      }),
      columnHelper.accessor('tahun', {
        header: 'Periode',
        cell: (info) => (
          <div className="flex items-start gap-2 text-gray-600 dark:text-gray-400">
            <CalendarIcon className="h-4 w-4 opacity-50 mt-0.5" />
            <div className="min-w-0">
              <div className="text-[13px]">{formatPeriod(info.row.original)}</div>
              <div className="mt-1 text-[11px] text-gray-500 dark:text-gray-400">
                Upload: {formatDate(info.row.original.created_at)}
              </div>
            </div>
          </div>
        ),
      }),
      columnHelper.accessor('nama', {
        header: 'Nama Dokumen',
        cell: (info) => (
          <div className="max-w-[280px]">
            <div
              className="font-medium text-gray-900 dark:text-white truncate"
              title={info.getValue()}
            >
              {info.getValue()}
            </div>
          </div>
        ),
      }),
      columnHelper.accessor('konten', {
        header: 'Konten',
        cell: (info) => (
          <div
            className="max-w-[320px] text-[12px] leading-5 text-gray-600 dark:text-gray-300 line-clamp-4"
            dangerouslySetInnerHTML={{ __html: info.getValue() || '-' }}
          />
        ),
      }),
      columnHelper.display({
        id: 'actions',
        header: 'Aksi',
        cell: (info) => {
          const row = info.row.original;
          const fileUrl = resolveFileUrl(row.link);

          return (
            <div className="flex items-center gap-1">
              {fileUrl ? (
                <a
                  href={fileUrl}
                  target="_blank"
                  rel="noreferrer"
                  className="p-1 px-2 rounded-lg hover:bg-blue-50 dark:hover:bg-blue-500/10 text-blue-500 transition"
                  title="Unduh file"
                >
                  <Download className="h-4 w-4" />
                </a>
              ) : null}
              <button
                type="button"
                onClick={() => {
                  setFormError(null);
                  setEditingRow(row);
                }}
                className="p-1 px-2 rounded-lg hover:bg-amber-50 dark:hover:bg-amber-500/10 text-amber-600 dark:text-amber-400 transition"
                title="Edit SKP"
              >
                <Pencil className="h-4 w-4" />
              </button>
              <button
                type="button"
                onClick={() => {
                  setFormError(null);
                  setDeleteTarget(row);
                }}
                className="p-1 px-2 rounded-lg hover:bg-red-50 dark:hover:bg-red-500/10 text-red-500 transition"
                title="Hapus SKP"
              >
                <Trash2 className="h-4 w-4" />
              </button>
            </div>
          );
        },
      }),
    ],
    [users]
  );

  const table = useReactTable({
    data: items,
    columns,
    getCoreRowModel: getCoreRowModel(),
    manualPagination: true,
    pageCount: pagination.meta?.lastPage ?? 1,
  });

  const hasActiveFilters = search.trim() !== '' || filterUserId !== '' || filterTahun !== '';
  const currentPage = pagination.page;
  const totalPages = pagination.meta?.lastPage ?? 1;
  const totalRecords = pagination.meta?.total ?? 0;
  
  // Robust cursor extraction from multiple potential fields
  const nextCursor = response?.links?.next_cursor || 
                     response?.links?.next_page_url || 
                     null;

  return (
    <div className="space-y-6">
      <PageHeader
        title="Daftar SKP"
        description={`Rekap SKP terunggah dari pegawai dan mitra. Total ${totalRecords} dokumen.`}
        actions={
          <button
            data-scan="tombol upload skp"
            type="button"
            onClick={() => {
              setFormError(null);
              setIsUploadOpen(true);
            }}
            className="flex items-center gap-1.5 px-4 py-2 rounded-full bg-amber-300 hover:bg-amber-400 text-gray-900 text-sm font-semibold shadow-lg shadow-amber-500/20 transition"
          >
            <Plus className="h-4 w-4" />
            Upload SKP
          </button>
        }
      />

      <div 
        data-scan="pencarian dan filter"
        className="bg-white/40 dark:bg-white/5 glass rounded-2xl border border-white/40 dark:border-white/10 overflow-hidden"
      >

        <div className="flex items-center gap-3 p-2">
          <div className="relative flex-1 group">
            <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400 group-focus-within:text-orange-500 transition-colors" />
            <input
              type="text"
              value={search}
              onChange={handleSearchChange}
              placeholder="Cari nama dokumen atau pegawai..."
              className="w-full bg-transparent border-none focus:ring-0 focus:outline-none pl-10 pr-4 py-2 text-sm text-gray-700 dark:text-gray-200 placeholder:text-gray-400"
            />
          </div>
          <button
            type="button"
            onClick={() => setShowFilters((prev) => !prev)}
            className={`flex items-center gap-2 px-4 py-2 rounded-xl transition text-[13px] font-medium ${
              showFilters
                ? 'bg-orange-500 text-white shadow-sm'
                : 'hover:bg-white/60 dark:hover:bg-white/10 text-gray-600 dark:text-gray-300'
            }`}
          >
            <Filter className="h-4 w-4" />
            Filter
            {hasActiveFilters ? <span className="w-2 h-2 rounded-full bg-orange-500" /> : null}
          </button>
        </div>

        {showFilters ? (
          <div className="flex flex-wrap items-center gap-3 px-4 pb-3 pt-1 border-t border-white/30 dark:border-white/10">
            <select
              value={filterUserId}
              onChange={handleFilterUserChange}
              className="h-9 rounded-xl border border-white/50 dark:border-white/10 bg-white/70 dark:bg-white/5 px-3 text-sm text-gray-900 dark:text-gray-100 outline-none cursor-pointer max-w-[220px]"
            >
              <option value="">Semua Pegawai</option>
              {Object.entries(users).map(([id, name]) => (
                <option key={id} value={id}>
                  {name}
                </option>
              ))}
            </select>
            <select
              value={filterTahun}
              onChange={handleFilterYearChange}
              className="h-9 rounded-xl border border-white/50 dark:border-white/10 bg-white/70 dark:bg-white/5 px-3 text-sm text-gray-900 dark:text-gray-100 outline-none cursor-pointer"
            >
              <option value="">Semua Tahun</option>
              {years.map((year) => (
                <option key={year} value={year}>
                  {year}
                </option>
              ))}
            </select>
            {hasActiveFilters ? (
              <button
                type="button"
                onClick={() => {
                  setSearch('');
                  setFilterUserId('');
                  setFilterTahun('');
                  pagination.reset();
                }}
                className="text-[12px] text-orange-500 hover:text-orange-600 font-medium"
              >
                Reset Filter
              </button>
            ) : null}
          </div>
        ) : null}
      </div>

      {formError ? (
        <div className="rounded-2xl border border-red-200 dark:border-red-500/30 bg-red-50 dark:bg-red-500/10 px-4 py-3 text-sm text-red-700 dark:text-red-300">
          {formError}
        </div>
      ) : null}

      <div 
        data-scan="kartu statistik"
        className="grid grid-cols-2 md:grid-cols-4 gap-4"
      >

        <div className="bg-white/40 dark:bg-white/5 glass p-4 rounded-2xl border border-white/40 dark:border-white/10">
          <p className="text-[11px] font-bold uppercase tracking-widest text-gray-400 mb-1">Total SKP</p>
          <p className="text-2xl font-bold text-gray-900 dark:text-white">{totalRecords}</p>
        </div>
        <div className="bg-white/40 dark:bg-white/5 glass p-4 rounded-2xl border border-white/40 dark:border-white/10">
          <p className="text-[11px] font-bold uppercase tracking-widest text-gray-400 mb-1">Halaman</p>
          <p className="text-2xl font-bold text-blue-600 dark:text-blue-400">
            {currentPage}{' '}
            <span className="text-sm font-normal text-gray-400">/ {totalPages}</span>
          </p>
        </div>
        <div className="bg-white/40 dark:bg-white/5 glass p-4 rounded-2xl border border-white/40 dark:border-white/10">
          <p className="text-[11px] font-bold uppercase tracking-widest text-gray-400 mb-1">Tahun Tersedia</p>
          <p className="text-2xl font-bold text-purple-600 dark:text-purple-400">{years.length}</p>
        </div>
        <div className="bg-white/40 dark:bg-white/5 glass p-4 rounded-2xl border border-white/40 dark:border-white/10">
          <p className="text-[11px] font-bold uppercase tracking-widest text-gray-400 mb-1">Pegawai Tercatat</p>
          <p className="text-2xl font-bold text-emerald-600 dark:text-emerald-400">{Object.keys(users).length}</p>
        </div>
      </div>

      <div 
        data-scan="tabel skp"
        className="bg-white/65 dark:bg-white/[0.04] glass-strong rounded-[24px] border border-white/50 dark:border-white/10 shadow-[0_10px_40px_-15px_rgba(0,0,0,0.08)] overflow-hidden"
      >

        {isLoading ? (
          <LoadingSkeleton variant="table" message="Memuat daftar SKP..." />
        ) : error ? (
          <ApiErrorBoundary error={error} onRetry={() => refetch()} title="Gagal Memuat Daftar SKP" />
        ) : (
          <div className="overflow-x-auto">
            <table className="w-full text-[13px]">
              <thead>
                <tr className="text-left text-[11.5px] text-gray-500 dark:text-gray-400 border-b border-gray-200/60 dark:border-white/10">
                  {table.getHeaderGroups().map((headerGroup) => (
                    <React.Fragment key={headerGroup.id}>
                      {headerGroup.headers.map((header) => (
                        <th
                          key={header.id}
                          className="px-6 py-5 font-bold uppercase tracking-widest bg-gray-50/30 dark:bg-white/5 whitespace-nowrap"
                        >
                          {flexRender(header.column.columnDef.header, header.getContext())}
                        </th>
                      ))}
                    </React.Fragment>
                  ))}
                </tr>
              </thead>
              <tbody className="divide-y divide-gray-100/70 dark:divide-white/5">
                {table.getRowModel().rows.map((row) => (
                  <tr key={row.id} className="hover:bg-white/40 dark:hover:bg-white/[0.02] transition-colors">
                    {row.getVisibleCells().map((cell) => (
                      <td key={cell.id} className="px-6 py-4">
                        {flexRender(cell.column.columnDef.cell, cell.getContext())}
                      </td>
                    ))}
                  </tr>
                ))}
                {table.getRowModel().rows.length === 0 ? (
                  <tr>
                    <td colSpan={columns.length} className="px-6 py-12 text-center text-gray-400 text-sm">
                      Belum ada data SKP yang cocok dengan filter saat ini.
                    </td>
                  </tr>
                ) : null}
              </tbody>
            </table>
          </div>
        )}

        <div 
          data-scan="navigasi halaman"
          className="flex items-center justify-between px-6 py-5 border-t border-gray-200/60 dark:border-white/10 dark:bg-white/5"
        >

          <p className="text-[12px] text-gray-500 dark:text-gray-400 font-medium">
            Halaman <span className="text-gray-900 dark:text-white font-bold">{currentPage}</span> dari{' '}
            <span className="text-gray-900 dark:text-white font-bold">{totalPages}</span>
            <span className="mx-2 text-gray-300 dark:text-white/10">|</span>
            Total <span className="text-gray-900 dark:text-white font-bold">{totalRecords}</span> data
          </p>
          <div className="flex items-center gap-1">
            <button
              type="button"
              onClick={() => pagination.handlePrev()}
              disabled={!pagination.canGoPrev || isLoading}
              className="p-2 rounded-lg bg-white/70 dark:bg-white/10 border border-white/50 dark:border-white/20 text-gray-700 dark:text-gray-300 hover:bg-white transition-all disabled:opacity-30 disabled:cursor-not-allowed shadow-sm"
            >
              <ChevronLeft className="h-4 w-4" />
            </button>
            <div className="px-3 text-[12px] font-bold text-gray-600 dark:text-gray-300">
              {currentPage} / {totalPages}
            </div>

            <button
              type="button"
              onClick={() => pagination.handleNext(nextCursor)}
              disabled={!pagination.canGoNext || isLoading}
              className="p-2 rounded-lg bg-white/70 dark:bg-white/10 border border-white/50 dark:border-white/20 text-gray-700 dark:text-gray-300 hover:bg-white transition-all disabled:opacity-30 disabled:cursor-not-allowed shadow-sm"
            >
              <ChevronRight className="h-4 w-4" />
            </button>
          </div>
        </div>
      </div>

      <SkpFileModal
        open={isUploadOpen}
        onClose={() => {
          setIsUploadOpen(false);
          setFormError(null);
        }}
        onSubmit={(values) => uploadMutation.mutate(values)}
        title="Upload SKP"
        description="Tambahkan dokumen SKP baru untuk pegawai aktif."
        submitLabel={uploadMutation.isPending ? 'Mengunggah...' : 'Upload SKP'}
        yearOptions={years}
        monthOptions={Array.from({ length: 12 }, (_, index) => String(index + 1).padStart(2, '0'))}
        initialValues={{
          jenis: 'SKP Bulanan',
          tahun: filterTahun || defaults.tahun,
          bulan: defaults.bulan,
        }}
        isLoading={uploadMutation.isPending}
        requireFile
        errorMessage={formError}
      />

      <SkpFileModal
        open={editingRow !== null}
        onClose={() => {
          setEditingRow(null);
          setFormError(null);
        }}
        onSubmit={(values) => {
          if (!editingRow) return;
          updateMutation.mutate({ id: editingRow.id, values });
        }}
        title="Edit SKP"
        description="Perbarui metadata SKP dan unggah ulang file jika diperlukan."
        submitLabel={updateMutation.isPending ? 'Menyimpan...' : 'Perbarui SKP'}
        yearOptions={years}
        monthOptions={Array.from({ length: 12 }, (_, index) => String(index + 1).padStart(2, '0'))}
        initialValues={
          editingRow
            ? {
                jenis: editingRow.jenis as SkpFileFormValues['jenis'],
                tahun: editingRow.tahun,
                bulan: editingRow.bulan ?? '',
              }
            : undefined
        }
        isLoading={updateMutation.isPending}
        requireFile={false}
        errorMessage={formError}
      />

      <ConfirmDialog
        open={deleteTarget !== null}
        onClose={() => {
          setDeleteTarget(null);
          setFormError(null);
        }}
        onConfirm={() => {
          if (!deleteTarget) return;
          deleteMutation.mutate(deleteTarget.id);
        }}
        title="Hapus SKP"
        message={`Dokumen "${deleteTarget?.nama ?? 'SKP'}" akan dihapus permanen.`}
        confirmLabel={deleteMutation.isPending ? 'Menghapus...' : 'Hapus SKP'}
        isLoading={deleteMutation.isPending}
        variant="danger"
      />
    </div>
  );
}
