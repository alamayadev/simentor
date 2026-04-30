import React, { useState, useMemo, useCallback } from 'react';
import {
  createColumnHelper,
  flexRender,
  getCoreRowModel,
  getPaginationRowModel,
  useReactTable,
} from '@tanstack/react-table';
import {
  Plus,
  Search,
  Database,
  Pencil,
  Trash2,
  AlertCircle,
  FileText,
  Download,
  FileArchive,
  FileSpreadsheet,
} from 'lucide-react';
import { motion } from 'motion/react';
import { useQueryClient } from '@tanstack/react-query';
import { useApiQuery, useApiMutation } from '../hooks/useApi';
import { useDebounce } from '../hooks/useDebounce';
import { PageHeader } from './PageHeader';
import { LoadingSkeleton } from './LoadingSkeleton';
import { ApiErrorBoundary } from './ApiErrorBoundary';
import { EntityFormModal, type FormField } from './EntityFormModal';
import { rawDataService, enumsService } from '../lib/api-services';
import type { RawData } from '../types/api';

type RawDataRow = RawData & {
  nama?: string;
  fungsi?: string;
  keterangan?: string;
  file?: string;
  created_at?: string;
};

function getFileExtension(filename?: string): string {
  if (!filename) return '';
  const parts = filename.split('.');
  return parts[parts.length - 1]?.toLowerCase() || '';
}

function getFileIcon(filename?: string) {
  const ext = getFileExtension(filename);
  if (ext === 'zip' || ext === 'rar') return <FileArchive className="h-5 w-5 text-slate-600 dark:text-slate-400" />;
  if (ext === 'csv') return <FileText className="h-5 w-5 text-emerald-600 dark:text-emerald-400" />;
  if (ext === 'xlsx' || ext === 'xls') return <FileSpreadsheet className="h-5 w-5 text-emerald-600 dark:text-emerald-400" />;
  return <FileArchive className="h-5 w-5 text-slate-600 dark:text-slate-400" />;
}

export interface BankDataPageProps {
  /** Value for the hidden `type` field sent on every create/update */
  dataType: string;
  /** Page title shown in PageHeader */
  title: string;
  /** Page description shown in PageHeader */
  description: string;
  /** Label for the add button */
  addLabel?: string;
  /** Query key prefix, defaults to 'ipds-raw-datas' */
  queryKey?: string;
}

export function BankDataPage({
  dataType,
  title,
  description,
  addLabel = 'Tambah Data',
  queryKey = 'ipds-raw-datas',
}: BankDataPageProps) {
  const [search, setSearch] = useState('');
  const [filterFungsi, setFilterFungsi] = useState('');
  const debouncedSearch = useDebounce(search, 300);
  const debouncedFilterFungsi = useDebounce(filterFungsi, 300);
  const qc = useQueryClient();

  const [modalOpen, setModalOpen] = useState(false);
  const [modalMode, setModalMode] = useState<'create' | 'edit'>('create');
  const [selectedRow, setSelectedRow] = useState<RawDataRow | null>(null);
  const [deleteConfirm, setDeleteConfirm] = useState<number | null>(null);

  const { data: enums } = useApiQuery(['enums'], enumsService.getAll);
  const fungsiOptions = useMemo(
    () => (enums?.data?.fungsi_types || []).map((f) => ({ value: f, label: f })),
    [enums],
  );

  const listQueryKey = [queryKey, dataType, debouncedSearch, debouncedFilterFungsi];

  const { data: response, isLoading, error, refetch } = useApiQuery(
    listQueryKey,
    () => {
      const params: Record<string, string | number> = { per_page: 50, type: dataType };
      if (debouncedSearch) params.nama = debouncedSearch;
      if (debouncedFilterFungsi) params.fungsi = debouncedFilterFungsi;
      return rawDataService.list(params);
    },
  );

  const invalidate = useCallback(
    () => qc.invalidateQueries({ queryKey: [queryKey, dataType] }),
    [qc, queryKey, dataType],
  );

  const createMutation = useApiMutation(
    (data: FormData) => rawDataService.create(data),
    { onSuccess: () => { invalidate(); setModalOpen(false); } },
  );

  const updateMutation = useApiMutation(
    (payload: { id: number; data: FormData }) => rawDataService.update(payload.id, payload.data),
    { onSuccess: () => { invalidate(); setModalOpen(false); } },
  );

  const deleteMutation = useApiMutation(
    (id: number) => rawDataService.delete(id),
    { onSuccess: () => { invalidate(); setDeleteConfirm(null); } },
  );

  const rawData: RawDataRow[] = useMemo(() => {
    if (!response?.data || !Array.isArray(response.data)) return [];
    return response.data as RawDataRow[];
  }, [response]);

  const total = response?.pagination_info?.total_records || response?.meta?.total || rawData.length;

  const handleCreate = useCallback(() => { setModalMode('create'); setSelectedRow(null); setModalOpen(true); }, []);
  const handleEdit = useCallback((row: RawDataRow) => { setModalMode('edit'); setSelectedRow(row); setModalOpen(true); }, []);
  const handleDelete = useCallback((id: number) => setDeleteConfirm(id), []);
  const confirmDelete = useCallback(() => { if (deleteConfirm) deleteMutation.mutate(deleteConfirm); }, [deleteConfirm, deleteMutation]);

  const handleSubmit = useCallback((data: Record<string, unknown>) => {
    const fd = new FormData();
    fd.append('type', dataType);
    if (data.fungsi) fd.append('fungsi', String(data.fungsi));
    if (data.nama) fd.append('nama', String(data.nama));
    if (data.keterangan) fd.append('keterangan', String(data.keterangan));
    if (data.file instanceof File) fd.append('file', data.file);
    if (modalMode === 'create') {
      createMutation.mutate(fd);
    } else {
      fd.append('_method', 'PUT');
      updateMutation.mutate({ id: selectedRow!.id, data: fd });
    }
  }, [dataType, modalMode, selectedRow, createMutation, updateMutation]);

  const handleDownload = useCallback((item: RawDataRow) => {
    if (!item.file) return;
    const link = document.createElement('a');
    link.href = `${window.location.origin}/${item.file}`;
    link.download = item.file.split('/').pop() || 'download';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  }, []);

  const columnHelper = createColumnHelper<RawDataRow>();

  const columns = useMemo(() => [
    columnHelper.accessor('nama', {
      header: 'Nama Data',
      cell: (info) => (
        <span className="font-bold text-gray-900 dark:text-white text-[13px] whitespace-normal">
          {info.getValue() || '-'}
        </span>
      ),
    }),
    columnHelper.accessor('fungsi', {
      header: 'Fungsi',
      cell: (info) => (
        <span className="text-[13px] text-gray-700 dark:text-gray-300">{info.getValue() || '-'}</span>
      ),
    }),
    columnHelper.accessor('keterangan', {
      header: 'Keterangan',
      cell: (info) => (
        <span className="text-[12px] text-gray-500 max-w-xs truncate block">{info.getValue() || '-'}</span>
      ),
    }),
    columnHelper.accessor('file', {
      header: 'File',
      cell: (info) => {
        const file = info.getValue();
        if (!file) return <span className="text-gray-400">-</span>;
        return (
          <div className="flex items-center gap-2">
            {getFileIcon(file)}
            <span className="text-[12px] text-gray-600 dark:text-gray-400 truncate max-w-[150px]">
              {file.split('/').pop() || '-'}
            </span>
          </div>
        );
      },
    }),
    columnHelper.display({
      id: 'actions',
      header: '',
      cell: (info) => (
        <div className="flex items-center justify-end gap-1">
          {info.row.original.file && (
            <button
              onClick={() => handleDownload(info.row.original)}
              className="p-1.5 rounded-lg hover:bg-emerald-50 dark:hover:bg-emerald-500/10 text-gray-400 hover:text-emerald-600 dark:hover:text-emerald-400 transition"
              title="Download file"
            >
              <Download className="h-3.5 w-3.5" />
            </button>
          )}
          <button
            onClick={() => handleEdit(info.row.original)}
            className="p-1.5 rounded-lg hover:bg-blue-50 dark:hover:bg-blue-500/10 text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition"
            title="Edit"
          >
            <Pencil className="h-3.5 w-3.5" />
          </button>
          <button
            onClick={() => handleDelete(info.row.original.id)}
            className="p-1.5 rounded-lg hover:bg-red-50 dark:hover:bg-red-500/10 text-gray-400 hover:text-red-600 dark:hover:text-red-400 transition"
            title="Hapus"
          >
            <Trash2 className="h-3.5 w-3.5" />
          </button>
        </div>
      ),
    }),
  ], [columnHelper, handleEdit, handleDelete, handleDownload]);

  const table = useReactTable({
    data: rawData,
    columns,
    getCoreRowModel: getCoreRowModel(),
    getPaginationRowModel: getPaginationRowModel(),
    initialState: { pagination: { pageSize: 15 } },
  });

  const isMutating = createMutation.isPending || updateMutation.isPending;

  const fields: FormField[] = useMemo(() => [
    { name: 'fungsi', label: 'Fungsi *', type: 'select', required: true, options: fungsiOptions, placeholder: 'Pilih Fungsi' },
    { name: 'nama', label: 'Nama Data *', type: 'text', required: true, placeholder: 'Misal: Data Penduduk 2024' },
    { name: 'keterangan', label: 'Keterangan', type: 'textarea', placeholder: 'Deskripsi singkat...' },
    { name: 'file', label: 'File Attachment', type: 'file', required: modalMode === 'create', accept: '.csv,.xlsx,.xls,.zip,.rar' },
  ], [fungsiOptions, modalMode]);

  return (
    <div className="space-y-6">
      <PageHeader
        title={title}
        description={description}
        actions={
          <button
            onClick={handleCreate}
            className="flex items-center gap-1.5 px-4 py-2 rounded-full bg-amber-300 hover:bg-amber-400 text-gray-900 text-sm font-semibold shadow-lg shadow-amber-500/20 transition"
          >
            <Plus className="h-4 w-4" />
            {addLabel}
          </button>
        }
      />

      <motion.div
        initial={{ opacity: 0, y: 12 }}
        animate={{ opacity: 1, y: 0 }}
        className="bg-white/65 dark:bg-white/[0.04] glass-strong rounded-[20px] border border-white/50 dark:border-white/10 p-5 flex items-center justify-between w-fit gap-6"
      >
        <div>
          <p className="text-[11px] font-bold uppercase tracking-widest text-gray-500 dark:text-gray-400">Total Data</p>
          <p className="text-2xl font-black text-orange-600 dark:text-orange-400">{isLoading ? '...' : total}</p>
        </div>
        <Database className="h-8 w-8 text-orange-400 opacity-20" />
      </motion.div>

      {/* Filters */}
      <div className="bg-white/40 dark:bg-white/5 glass rounded-2xl border border-white/40 dark:border-white/10 px-4 py-3">
        <div className="flex flex-wrap items-center gap-3">
          <div className="relative flex-1 min-w-[200px] group">
            <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400 group-focus-within:text-orange-500 transition-colors" />
            <input
              type="text"
              value={search}
              onChange={(e) => setSearch(e.target.value)}
              placeholder="Cari nama data..."
              className="w-full bg-white/50 dark:bg-slate-900/50 border border-gray-200 dark:border-white/5 rounded-xl focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 pl-10 pr-4 py-2 text-sm text-gray-700 dark:text-gray-200 placeholder:text-gray-400"
            />
          </div>
          <div className="w-64">
            <select
              value={filterFungsi}
              onChange={(e) => setFilterFungsi(e.target.value)}
              className="w-full bg-white/50 dark:bg-slate-900/50 border border-gray-200 dark:border-white/5 rounded-xl focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 px-3 py-2 text-sm text-gray-700 dark:text-gray-200 focus:outline-none"
            >
              <option value="">Semua Fungsi</option>
              {fungsiOptions.map((f) => (
                <option key={f.value} value={f.value}>{f.label}</option>
              ))}
            </select>
          </div>
        </div>
      </div>

      {/* Table */}
      <div className="bg-white/65 dark:bg-white/[0.04] glass-strong rounded-[24px] border border-white/50 dark:border-white/10 shadow-[0_10px_40px_-15px_rgba(0,0,0,0.08)] overflow-hidden">
        {isLoading ? (
          <LoadingSkeleton variant="table" message={`Memuat ${title.toLowerCase()}...`} />
        ) : error ? (
          <ApiErrorBoundary error={error} onRetry={() => refetch()} title="Gagal Memuat Data" />
        ) : (
          <>
            <div className="overflow-x-auto">
              <table className="w-full text-[13px]">
                <thead>
                  <tr className="text-left text-[11.5px] text-gray-500 dark:text-gray-400 border-b border-gray-200/60 dark:border-white/10">
                    {table.getHeaderGroups().map((hg) => (
                      <React.Fragment key={hg.id}>
                        {hg.headers.map((h) => (
                          <th key={h.id} className="px-6 py-5 font-bold uppercase tracking-widest bg-gray-50/30 dark:bg-white/5 whitespace-nowrap">
                            {flexRender(h.column.columnDef.header, h.getContext())}
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
                  {table.getRowModel().rows.length === 0 && (
                    <tr>
                      <td colSpan={columns.length} className="px-6 py-12 text-center text-gray-400 text-sm">
                        <div className="flex flex-col items-center gap-2">
                          <Database className="h-8 w-8 opacity-30" />
                          <span>Tidak ada data.</span>
                        </div>
                      </td>
                    </tr>
                  )}
                </tbody>
              </table>
            </div>

            <div className="flex items-center justify-between px-6 py-5 border-t border-gray-200/60 dark:border-white/10 dark:bg-white/5">
              <p className="text-[12px] text-gray-500 dark:text-gray-400 font-medium">
                Halaman <span className="text-gray-900 dark:text-white font-bold">{table.getState().pagination.pageIndex + 1}</span> dari{' '}
                <span className="text-gray-900 dark:text-white font-bold">{table.getPageCount() || 1}</span>
              </p>
              <div className="flex gap-2">
                <button onClick={() => table.previousPage()} disabled={!table.getCanPreviousPage()}
                  className="px-4 py-2 text-[11px] font-bold rounded-full bg-white/70 dark:bg-white/10 border border-white/50 dark:border-white/20 text-gray-700 dark:text-gray-300 hover:bg-white transition-all disabled:opacity-30 disabled:cursor-not-allowed shadow-sm">
                  Sebelumnya
                </button>
                <button onClick={() => table.nextPage()} disabled={!table.getCanNextPage()}
                  className="px-4 py-2 text-[11px] font-bold rounded-full bg-white/70 dark:bg-white/10 border border-white/50 dark:border-white/20 text-gray-700 dark:text-gray-300 hover:bg-white transition-all disabled:opacity-30 disabled:cursor-not-allowed shadow-sm">
                  Berikutnya
                </button>
              </div>
            </div>
          </>
        )}
      </div>

      <EntityFormModal
        open={modalOpen}
        onClose={() => setModalOpen(false)}
        onSubmit={handleSubmit}
        title={modalMode === 'create' ? `Tambah ${title}` : `Edit ${title}`}
        fields={fields}
        initialData={selectedRow}
        isLoading={isMutating}
        mode={modalMode}
      />

      {deleteConfirm !== null && (
        <div className="fixed inset-0 z-50 flex items-center justify-center">
          <div className="absolute inset-0 bg-black/40 backdrop-blur-sm" onClick={() => setDeleteConfirm(null)} />
          <div className="relative bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-white/10 shadow-2xl w-full max-w-sm mx-4 p-6 space-y-4">
            <div className="flex items-center gap-3">
              <div className="h-10 w-10 rounded-full bg-red-100 dark:bg-red-500/10 flex items-center justify-center">
                <AlertCircle className="h-5 w-5 text-red-600 dark:text-red-400" />
              </div>
              <div>
                <h3 className="text-sm font-bold text-gray-900 dark:text-white">Hapus Data?</h3>
                <p className="text-[12px] text-gray-500 dark:text-gray-400">Data akan dihapus permanen.</p>
              </div>
            </div>
            <div className="flex justify-end gap-3">
              <button
                onClick={() => setDeleteConfirm(null)}
                className="px-4 py-2 text-[12px] font-bold rounded-full bg-white/70 dark:bg-white/10 border border-white/50 dark:border-white/20 text-gray-700 dark:text-gray-300 hover:bg-white dark:hover:bg-white/10 transition-all shadow-sm"
              >
                Batal
              </button>
              <button
                onClick={confirmDelete}
                disabled={deleteMutation.isPending}
                className="px-4 py-2 text-[12px] font-bold rounded-full bg-red-600 hover:bg-red-700 text-white shadow-sm transition-all disabled:opacity-50"
              >
                {deleteMutation.isPending ? 'Menghapus...' : 'Hapus'}
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
