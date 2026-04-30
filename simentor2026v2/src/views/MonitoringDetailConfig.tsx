import { apiDelete } from '../lib/api';
import React, { useState, useMemo, useEffect } from 'react';
import { usePagination } from '../hooks/usePagination';
import {
  createColumnHelper,
  flexRender,
  getCoreRowModel,
  getPaginationRowModel,
  useReactTable,
} from '@tanstack/react-table';
import {
  Plus,
  Pencil,
  Trash2,
} from 'lucide-react';
import { useApiQuery, useApiMutation } from '../hooks/useApi';
import { PageHeader } from '../components/PageHeader';
import { LoadingSkeleton } from '../components/LoadingSkeleton';
import { ApiErrorBoundary } from '../components/ApiErrorBoundary';
import { EntityFormModal, FormField } from '../components/EntityFormModal';
import { ConfirmDialog } from '../components/ConfirmDialog';
import { detilConfigurationService } from '../lib/api-services';

type DetailConfigRow = {
  id: number;
  name: string;
  field_name: string;
  field_label: string;
  field_type: string;
  field_options: string;
  field_required: boolean;
  field_source: string;
  is_active: boolean;
  original_field: any;
};

// Helper: toSnakeCase
function toSnakeCase(value: string) {
  if (!value) return '';
  return value
    .toLowerCase()
    .trim()
    .replace(/[^a-z0-9]+/g, '_')
    .replace(/^_+|_+$/g, '');
}

export function MonitoringDetailConfig() {
  const [showFormModal, setShowFormModal] = useState(false);
  const [editingRow, setEditingRow] = useState<DetailConfigRow | null>(null);
  const [deleteTarget, setDeleteTarget] = useState<DetailConfigRow | null>(null);
  const pagination = usePagination(15);

  const { data: response, isLoading, error, refetch } = useApiQuery(
    ['monitoring-detil-configs', pagination.params],
    () => detilConfigurationService.list({ ...pagination.params }),
  );

  useEffect(() => {
    pagination.sync(response);
  }, [response]);

  // Note: detil-configurations doesn't have a direct delete exposed in api-services.ts currently
  // We'll emulate standard mutation handling setup and just assume it might be available, 
  // or use apiDelete from lib/api.ts directly if needed. But for now we rely on detilConfigurationService
  // Wait, let's just do update/is_active: false for delete if no delete method, 
  // but looking at simentor2026 it had DELETE /api/kantor/kegiatan/monitoring/detil-configurations/:id
  const customDelete = (id: number) => apiDelete(`/kantor/kegiatan/monitoring/detil-configurations/${id}`);

  const createMutation = useApiMutation(
    (data: Record<string, unknown>) => detilConfigurationService.create(data),
    { invalidateKeys: [['monitoring-detil-configs']], onSuccess: () => { setShowFormModal(false); setEditingRow(null); } },
  );

  const updateMutation = useApiMutation(
    (data: Record<string, unknown>) => detilConfigurationService.update(data.id as number, data),
    { invalidateKeys: [['monitoring-detil-configs']], onSuccess: () => { setShowFormModal(false); setEditingRow(null); } },
  );

  const deleteMutation = useApiMutation(
    (data: Record<string, unknown>) => customDelete(data.id as number),
    { invalidateKeys: [['monitoring-detil-configs']], onSuccess: () => setDeleteTarget(null) },
  );

  const data: DetailConfigRow[] = useMemo(() => {
    if (!response?.data) return [];
    const raw = response.data;
    if (!Array.isArray(raw)) return [];
    return raw.map((item: any) => ({
      id: item.id,
      name: item.name || '-',
      field_name: item.field?.name || '-',
      field_label: item.field?.label || '-',
      field_type: item.field?.type || '-',
      field_options: item.field?.options?.join(', ') || '',
      field_required: Boolean(item.field?.required),
      field_source: item.field?.source || 'custom',
      is_active: Boolean(item.is_active),
      original_field: item.field,
    }));
  }, [response]);

  const formFields: FormField[] = useMemo(() => [
    { name: 'name', label: 'Nama Konfigurasi', type: 'text', placeholder: 'e.g., Luas Lahan', required: true },
    { name: 'field_label', label: 'Label Field', type: 'text', placeholder: 'e.g., Luas Lahan (m2)', required: true },
    {
      name: 'field_type', label: 'Tipe Form', type: 'select', required: true,
      options: [
        { value: 'text', label: 'Text' },
        { value: 'number', label: 'Number' },
        { value: 'date', label: 'Date' },
        { value: 'enum', label: 'Dropdown / Enum' },
      ],
    },
    { 
      name: 'field_options', 
      label: 'Options (Hanya jika Enum, pisahkan koma)', 
      type: 'text', 
      placeholder: 'opsi1, opsi2, opsi3', 
      colSpan: 2 
    },
    {
      name: 'field_required', label: 'Wajib Diisi', type: 'select', required: true,
      options: [{ value: 'yes', label: 'Wajib' }, { value: 'no', label: 'Opsional' }],
    },
    {
      name: 'is_active', label: 'Status', type: 'select', required: true,
      options: [{ value: 'active', label: 'Aktif' }, { value: 'inactive', label: 'Nonaktif' }],
    },
  ], []);

  const handleFormSubmit = (formData: Record<string, unknown>) => {
    const rawLabel = String(formData.field_label || '');
    const payload = {
      name: formData.name,
      field: {
        name: toSnakeCase(rawLabel),
        label: rawLabel,
        type: formData.field_type,
        required: formData.field_required === 'yes',
        options: formData.field_options
          ? String(formData.field_options).split(',').map(v => v.trim()).filter(Boolean)
          : [],
      },
      is_active: formData.is_active === 'active',
    };

    if (editingRow) {
      updateMutation.mutate({ ...payload, id: editingRow.id });
    } else {
      createMutation.mutate(payload);
    }
  };

  const columnHelper = createColumnHelper<DetailConfigRow>();

  const columns = useMemo(() => [
    columnHelper.accessor('name', {
      header: 'Nama',
      cell: info => <span className="font-medium text-slate-900 dark:text-white">{info.getValue()}</span>,
    }),
    columnHelper.accessor('field_label', {
      header: 'Label',
      cell: info => <span className="text-slate-600 dark:text-slate-300">{info.getValue()}</span>,
    }),
    columnHelper.accessor('field_name', {
      header: 'Field',
      cell: info => <span className="text-slate-600 dark:text-slate-300 font-mono text-xs px-2 py-1 bg-gray-100 dark:bg-white/10 rounded">{info.getValue()}</span>,
    }),
    columnHelper.accessor('field_type', {
      header: 'Tipe',
      cell: info => <span className="uppercase text-[11px] font-bold tracking-wider text-slate-500 dark:text-slate-400">{info.getValue()}</span>,
    }),
    columnHelper.accessor('field_required', {
      header: 'Wajib',
        cell: info => (
        <span className={`px-2 py-0.5 rounded-md border text-[10px] font-bold uppercase tracking-wider ${info.getValue() ? 'bg-amber-100 text-amber-700 border-amber-200 dark:bg-amber-500/10 dark:text-amber-400' : 'bg-gray-100 text-gray-600 border-gray-200 dark:bg-white/5 dark:text-gray-400'}`}>
            {info.getValue() ? 'Wajib' : 'Opsional'}
        </span>
        ),
    }),
    columnHelper.accessor('is_active', {
      header: 'Status',
      cell: info => (
        <span className={`px-2 py-0.5 rounded-md border text-[10px] font-bold uppercase tracking-wider ${info.getValue() ? 'bg-emerald-100 text-emerald-700 border-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-400' : 'bg-red-100 text-red-700 border-red-200 dark:bg-red-500/10 dark:text-red-400'}`}>
          {info.getValue() ? 'Aktif' : 'Nonaktif'}
        </span>
      ),
    }),
    columnHelper.display({
      id: 'actions',
      header: () => <div className="text-right">Aksi</div>,
      cell: (info) => {
        // Hanya custom yang bisa di edit (sama seperti v1)
        if (info.row.original.field_source !== 'custom') return null;

        return (
          <div className="flex justify-end gap-1 whitespace-nowrap">
            <button
              data-scan="tombol edit konfigurasi"
              onClick={() => { setEditingRow(info.row.original); setShowFormModal(true); }}
              className="p-1.5 px-2.5 rounded-lg hover:bg-gray-100 dark:hover:bg-white/10 text-gray-500 dark:text-gray-400 text-[12px] font-medium flex items-center gap-1.5 transition"
            >
              <Pencil className="h-3.5 w-3.5" />
              Edit
            </button>
            <button
              data-scan="tombol hapus konfigurasi"
              onClick={() => setDeleteTarget(info.row.original)}
              className="p-1.5 px-2.5 rounded-lg hover:bg-red-50 dark:hover:bg-red-500/10 text-red-600 dark:text-red-400 text-[12px] font-medium flex items-center gap-1.5 transition"
            >
              <Trash2 className="h-3.5 w-3.5" />
              Hapus
            </button>
          </div>
        );
      },
    }),
  ], []);

  const table = useReactTable({
    data,
    columns,
    getCoreRowModel: getCoreRowModel(),
    manualPagination: true,
  });

  return (
    <div className="space-y-6">
      <PageHeader
        title="Config Detil Monitoring"
        description="Daftar konfigurasi kolom dinamis untuk monitoring kegiatan."
      />

      <div className="flex items-center justify-between gap-3">
        <button
          onClick={() => { window.history.back(); }}
          className="px-4 py-2 text-sm font-medium rounded-full bg-white/70 dark:bg-white/5 border border-white/50 dark:border-white/10 text-gray-700 dark:text-gray-200 hover:bg-white dark:hover:bg-white/10 transition shadow-sm"
        >
          Kembali
        </button>

        <button
          data-scan="tombol tambah konfigurasi"
          onClick={() => { setEditingRow(null); setShowFormModal(true); }}
          className="flex items-center gap-1.5 px-4 py-2 rounded-full bg-amber-300 hover:bg-amber-400 text-gray-900 text-sm font-semibold shadow-lg shadow-amber-500/20 transition"
        >
          <Plus className="h-4 w-4" />
          Tambah Konfigurasi
        </button>
      </div>

      <div className="bg-white/65 dark:bg-white/[0.04] glass-strong rounded-[24px] border border-white/50 dark:border-white/10 shadow-[0_10px_40px_-15px_rgba(0,0,0,0.08)] overflow-hidden">
        {isLoading ? (
          <LoadingSkeleton variant="table" message="Memuat konfigurasi detail..." />
        ) : error ? (
          <ApiErrorBoundary error={error} onRetry={refetch} title="Gagal Memuat Konfigurasi" />
        ) : (
          <>
            <div className="overflow-x-auto">
              <table className="w-full text-[13px]">
                <thead>
                  <tr className="text-left text-[11.5px] text-gray-500 dark:text-gray-400 border-b border-gray-200/60 dark:border-white/10">
                    {table.getHeaderGroups().map((headerGroup) => (
                      <React.Fragment key={headerGroup.id}>
                        {headerGroup.headers.map((header) => (
                          <th key={header.id} className="px-6 py-5 font-bold uppercase tracking-widest bg-gray-50/30 dark:bg-white/5 whitespace-nowrap">
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
                        <td key={cell.id} className="px-6 py-5">
                          {flexRender(cell.column.columnDef.cell, cell.getContext())}
                        </td>
                      ))}
                    </tr>
                  ))}
                  {table.getRowModel().rows.length === 0 && (
                    <tr>
                      <td colSpan={columns.length} className="px-6 py-12 text-center text-gray-400 text-sm">
                        Tidak ada konfigurasi detail.
                      </td>
                    </tr>
                  )}
                </tbody>
              </table>
            </div>
            
            <div className="flex items-center justify-between px-6 py-5 border-t border-gray-200/60 dark:border-white/10 dark:bg-white/5">
              <div className="flex items-center gap-4">
                <p className="text-[12px] text-gray-500 dark:text-gray-400 font-medium">
                  Halaman <span className="text-gray-900 dark:text-white font-bold">{pagination.page}</span> dari{' '}
                  <span className="text-gray-900 dark:text-white font-bold">{pagination.totalPages}</span>
                </p>
                <div className="h-4 w-px bg-gray-200 dark:bg-white/10" />
                <p className="text-[12px] text-gray-500 dark:text-gray-400 font-medium">
                  Menampilkan <span className="font-bold text-gray-900 dark:text-white">{data.length}</span> dari <span className="font-bold text-gray-900 dark:text-white">{pagination.totalRecords}</span> config
                </p>
              </div>
              <div className="flex gap-2">
                <button
                  onClick={() => pagination.handlePrev()}
                  disabled={!pagination.canPrev || isLoading}
                  className="px-4 py-2 text-[11px] font-bold rounded-full bg-white/70 dark:bg-white/10 border border-white/50 dark:border-white/20 text-gray-700 dark:text-gray-300 hover:bg-white transition-all disabled:opacity-30 disabled:cursor-not-allowed shadow-sm"
                >
                  Sebelumnya
                </button>
                <button
                  onClick={() => pagination.handleNext()}
                  disabled={!pagination.canNext || isLoading}
                  className="px-4 py-2 text-[11px] font-bold rounded-full bg-white/70 dark:bg-white/10 border border-white/50 dark:border-white/20 text-gray-700 dark:text-gray-300 hover:bg-white transition-all disabled:opacity-30 disabled:cursor-not-allowed shadow-sm"
                >
                  Berikutnya
                </button>
              </div>
            </div>
          </>
        )}
      </div>

      <EntityFormModal
        data-scan="modal form konfigurasi"
        open={showFormModal}
        onClose={() => { setShowFormModal(false); setEditingRow(null); }}
        onSubmit={handleFormSubmit}
        title={editingRow ? 'Edit Konfigurasi Detil' : 'Tambah Konfigurasi Detil'}
        fields={formFields}
        initialData={editingRow ? {
          name: editingRow.name,
          field_label: editingRow.field_label,
          field_type: editingRow.field_type,
          field_options: editingRow.field_options,
          field_required: editingRow.field_required ? 'yes' : 'no',
          is_active: editingRow.is_active ? 'active' : 'inactive',
        } : {
          field_required: 'no',
          is_active: 'active',
        }}
        isLoading={createMutation.isPending || updateMutation.isPending}
        mode={editingRow ? 'edit' : 'create'}
      />

      <ConfirmDialog
        open={!!deleteTarget}
        onClose={() => setDeleteTarget(null)}
        onConfirm={() => deleteTarget && deleteMutation.mutate({ id: deleteTarget.id })}
        title="Hapus Konfigurasi"
        message={`Yakin ingin menghapus konfigurasi "${deleteTarget?.name}"? Tindakan ini tidak dapat dibatalkan.`}
        variant="danger"
        isLoading={deleteMutation.isPending}
      />
    </div>
  );
}
