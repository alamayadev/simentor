import React, { useState, useMemo, useEffect } from 'react';
import { usePagination } from '../hooks/usePagination';
import {
  createColumnHelper,
  flexRender,
  getCoreRowModel,
  getPaginationRowModel,
  useReactTable,
} from '@tanstack/react-table';
import { Plus, Pencil, Trash2, X, CheckSquare, Square } from 'lucide-react';
import { useApiQuery, useApiMutation } from '../hooks/useApi';
import { PageHeader } from '../components/PageHeader';
import { LoadingSkeleton } from '../components/LoadingSkeleton';
import { ApiErrorBoundary } from '../components/ApiErrorBoundary';
import { ConfirmDialog } from '../components/ConfirmDialog';
import { monitoringKegiatanConfigService, monitoringKegiatanService, detilConfigurationService } from '../lib/api-services';
import { apiDelete } from '../lib/api';

type DetailConfigItem = {
  id: number;
  name: string;
};

type MonitoringConfigRow = {
  id: number;
  fungsi: string;
  kegiatan_id: number;
  kegiatan?: { id: number; nama: string };
  detil_configurations?: DetailConfigItem[];
};

function ConfigModal({
  open,
  onClose,
  isEdit,
  initialData,
  allKegiatanOptions,
  allConfigs,
  onSubmit,
  isPending
}: any) {
  const [fungsi, setFungsi] = useState<string>('');
  const [kegiatanId, setKegiatanId] = useState<string | number>('');
  const [selectedConfigs, setSelectedConfigs] = useState<number[]>([]);

  useEffect(() => {
    if (open) {
      if (initialData) {
        setFungsi(initialData.fungsi || '');
        setKegiatanId(initialData.kegiatan_id || '');
        setSelectedConfigs(initialData.detil_configurations?.map((c: any) => c.id) || []);
      } else {
        setFungsi('');
        setKegiatanId('');
        setSelectedConfigs([]);
      }
    }
  }, [open, initialData]);

  if (!open) return null;

  const rawKegiatan = allKegiatanOptions?.data || allKegiatanOptions || [];
  // Backend returns data in an array directly in some implementations, handle both cases
  const safeKegiatanOptions = Array.isArray(rawKegiatan) ? rawKegiatan : [];
  
  const fungsiOptions = Array.from(new Set(safeKegiatanOptions.map((k: any) => k.fungsi))).filter(Boolean) as string[];
  const filteredKegiatan = safeKegiatanOptions.filter((k: any) => k.fungsi === fungsi);

  const rawConfigs = allConfigs?.data || allConfigs || [];
  const safeConfigsOptions = Array.isArray(rawConfigs) ? rawConfigs : [];

  const toggleConfig = (id: number) => {
    setSelectedConfigs(prev =>
      prev.includes(id) ? prev.filter(x => x !== id) : [...prev, id]
    );
  };

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    onSubmit({
      fungsi,
      kegiatan_id: Number(kegiatanId),
      detil_configuration_ids: selectedConfigs,
    });
  };

  const title = isEdit ? 'Edit Config Monitoring' : 'Tambah Config Monitoring';

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center">
      <div className="absolute inset-0 bg-black/40 backdrop-blur-sm" onClick={onClose} />

      <div className="relative bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-white/10 shadow-2xl w-full max-w-lg mx-4 max-h-[85vh] flex flex-col">
        <div className="flex items-center justify-between px-6 py-4 border-b border-gray-200/60 dark:border-white/10">
          <h2 className="text-base font-bold text-gray-900 dark:text-white">{title}</h2>
          <button
            onClick={onClose}
            className="p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-white/10 text-gray-400 transition"
          >
            <X className="h-4 w-4" />
          </button>
        </div>

        <form onSubmit={handleSubmit} className="flex-1 overflow-y-auto px-6 py-4">
          <div className="grid grid-cols-1 gap-4">
            <div>
              <label className="block text-[11px] font-bold uppercase tracking-widest text-gray-500 dark:text-gray-400 mb-1.5">
                Fungsi <span className="text-red-500 ml-0.5">*</span>
              </label>
              <select
                required
                value={fungsi}
                onChange={(e) => {
                  setFungsi(e.target.value);
                  setKegiatanId('');
                }}
                className="w-full text-[13px] bg-gray-50 dark:bg-black/20 border border-gray-200 dark:border-white/10 rounded-xl px-4 py-2.5 text-gray-900 dark:text-white focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 dark:focus:ring-amber-500/20 outline-none transition"
              >
                <option value="">Pilih Fungsi</option>
                {fungsiOptions.map(f => (
                  <option key={f} value={f}>{f}</option>
                ))}
              </select>
            </div>

            <div>
              <label className="block text-[11px] font-bold uppercase tracking-widest text-gray-500 dark:text-gray-400 mb-1.5">
                Kegiatan <span className="text-red-500 ml-0.5">*</span>
              </label>
              <select
                required
                value={kegiatanId}
                onChange={(e) => setKegiatanId(e.target.value)}
                disabled={!fungsi}
                className="w-full text-[13px] bg-gray-50 dark:bg-black/20 border border-gray-200 dark:border-white/10 rounded-xl px-4 py-2.5 text-gray-900 dark:text-white focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 dark:focus:ring-amber-500/20 outline-none transition disabled:opacity-50"
              >
                <option value="">Pilih Kegiatan</option>
                {filteredKegiatan.map((k: any) => (
                  <option key={k.id} value={k.id}>{k.nama}</option>
                ))}
              </select>
            </div>

            <div className="mt-2">
              <label className="block text-[11px] font-bold uppercase tracking-widest text-gray-500 dark:text-gray-400 mb-2">
                Pilih Kolom / Detil Config
              </label>
              <div className="space-y-1.5 max-h-48 overflow-y-auto p-2 border border-gray-200 dark:border-white/10 rounded-xl bg-gray-50/50 dark:bg-black/10">
                {safeConfigsOptions.length === 0 ? (
                  <div className="p-2 text-xs text-gray-500">Belum ada config detil.</div>
                ) : (
                  safeConfigsOptions.map((config: any) => {
                    const isSelected = selectedConfigs.includes(config.id);
                    return (
                      <button
                        type="button"
                        key={config.id}
                        onClick={() => toggleConfig(config.id)}
                        className={`w-full flex items-center justify-between p-2.5 rounded-lg border text-sm transition-all text-left ${isSelected ? 'bg-amber-50 dark:bg-amber-500/10 border-amber-200 dark:border-amber-500/30 text-amber-900 dark:text-amber-200' : 'bg-white dark:bg-black/20 border-gray-200 dark:border-white/10 text-gray-700 dark:text-gray-300 hover:border-gray-300 dark:hover:border-white/20'}`}
                      >
                        <span className="font-medium">{config.name}</span>
                        {isSelected ? <CheckSquare className="h-4 w-4 text-amber-500" /> : <Square className="h-4 w-4 text-gray-400" />}
                      </button>
                    );
                  })
                )}
              </div>
            </div>
          </div>

          <div className="mt-8 flex items-center justify-end gap-3 pt-4 border-t border-gray-200/60 dark:border-white/10">
            <button
              type="button"
              onClick={onClose}
              disabled={isPending}
              className="px-4 py-2 text-sm font-bold rounded-full bg-gray-100 hover:bg-gray-200 text-gray-700 dark:bg-white/5 dark:hover:bg-white/10 dark:text-gray-300 transition"
            >
              Batal
            </button>
            <button
              type="submit"
              disabled={isPending}
              className="px-4 py-2 text-sm font-bold rounded-full bg-amber-400 hover:bg-amber-500 text-gray-900 shadow-md shadow-amber-500/20 transition flex items-center disabled:opacity-50"
            >
              {isPending ? 'Menyimpan...' : 'Simpan Konfigurasi'}
            </button>
          </div>
        </form>
      </div>
    </div>
  );
}

export function MonitoringConfig() {
  const [showFormModal, setShowFormModal] = useState(false);
  const [editingRow, setEditingRow] = useState<MonitoringConfigRow | null>(null);
  const [deleteTarget, setDeleteTarget] = useState<MonitoringConfigRow | null>(null);
  const pagination = usePagination(15);

  const { data: response, isLoading, error, refetch } = useApiQuery(
    ['monitoring-configs', pagination.params],
    () => monitoringKegiatanConfigService.list({ ...pagination.params }),
  );

  useEffect(() => {
    pagination.sync(response);
  }, [response]);

  const { data: kegiatanOptsResponse } = useApiQuery(
    ['all-kegiatan-options'],
    () => monitoringKegiatanService.allKegiatanOptions(),
  );

  const { data: configsResponse } = useApiQuery(
    ['all-detil-configs'],
    () => detilConfigurationService.list({ per_page: 500 }),
  );

  const customDelete = (id: number) => apiDelete(`/kantor/kegiatan/monitoring/monitoring-kegiatan-config/${id}`);

  const createMutation = useApiMutation(
    (data: Record<string, unknown>) => monitoringKegiatanConfigService.create(data),
    { invalidateKeys: [['monitoring-configs']], onSuccess: () => { setShowFormModal(false); setEditingRow(null); } },
  );

  const updateMutation = useApiMutation(
    (data: Record<string, unknown>) => monitoringKegiatanConfigService.update(data.id as number, data),
    { invalidateKeys: [['monitoring-configs']], onSuccess: () => { setShowFormModal(false); setEditingRow(null); } },
  );

  const deleteMutation = useApiMutation(
    (data: Record<string, unknown>) => customDelete(data.id as number),
    { invalidateKeys: [['monitoring-configs']], onSuccess: () => setDeleteTarget(null) },
  );

  const data: MonitoringConfigRow[] = useMemo(() => {
    if (!response?.data) return [];
    if (!Array.isArray(response.data)) return [];
    return response.data as any[];
  }, [response]);

  const handleFormSubmit = (formData: Record<string, unknown>) => {
    if (editingRow) {
      updateMutation.mutate({ ...formData, id: editingRow.id });
    } else {
      createMutation.mutate(formData);
    }
  };

  const columnHelper = createColumnHelper<MonitoringConfigRow>();

  const columns = useMemo(() => [
    columnHelper.accessor('fungsi', {
      header: 'Fungsi',
      cell: info => <span className="uppercase font-bold tracking-widest text-[11px] px-2 py-0.5 rounded-md bg-gray-100 dark:bg-white/10 text-gray-600 dark:text-gray-300">{info.getValue()}</span>,
    }),
    columnHelper.accessor('kegiatan', {
      header: 'Kegiatan',
      cell: info => <span className="font-medium text-slate-900 dark:text-white">{info.getValue()?.nama || '-'}</span>,
    }),
    columnHelper.accessor('detil_configurations', {
      header: 'Detil Kolom / Configs',
      cell: info => {
        const configs = info.getValue() || [];
        if (configs.length === 0) return <span className="text-gray-400 italic">Tidak ada</span>;
        return (
          <div className="flex flex-wrap gap-1.5 max-w-sm">
             {configs.map((c) => (
                <span key={c.id} className="text-[10px] font-bold px-2 py-1 rounded bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400 border border-amber-100 dark:border-amber-500/20">
                  {c.name}
                </span>
             ))}
          </div>
        );
      },
    }),
    columnHelper.display({
      id: 'actions',
      header: () => <div className="text-right">Aksi</div>,
      cell: (info) => (
        <div className="flex justify-end gap-1 whitespace-nowrap">
          <button
            data-scan="tombol edit mapping"
            onClick={() => { setEditingRow(info.row.original); setShowFormModal(true); }}
            className="p-1.5 px-2.5 rounded-lg hover:bg-gray-100 dark:hover:bg-white/10 text-gray-500 dark:text-gray-400 text-[12px] font-medium flex items-center gap-1.5 transition"
          >
            <Pencil className="h-3.5 w-3.5" />
            Edit
          </button>
          <button
            data-scan="tombol hapus mapping"
            onClick={() => setDeleteTarget(info.row.original)}
            className="p-1.5 px-2.5 rounded-lg hover:bg-red-50 dark:hover:bg-red-500/10 text-red-600 dark:text-red-400 text-[12px] font-medium flex items-center gap-1.5 transition"
          >
            <Trash2 className="h-3.5 w-3.5" />
            Hapus
          </button>
        </div>
      ),
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
        title="Mapping Config Monitoring"
        description="Hubungkan Kegiatan dengan Form / Konfigurasi Detil yang diperlukan."
      />

      <div className="flex items-center justify-between gap-3">
        <button
          onClick={() => { window.history.back(); }}
          className="px-4 py-2 text-sm font-medium rounded-full bg-white/70 dark:bg-white/5 border border-white/50 dark:border-white/10 text-gray-700 dark:text-gray-200 hover:bg-white dark:hover:bg-white/10 transition shadow-sm"
        >
          Kembali
        </button>

        <button
          data-scan="tombol tambah mapping"
          onClick={() => { setEditingRow(null); setShowFormModal(true); }}
          className="flex items-center gap-1.5 px-4 py-2 rounded-full bg-amber-300 hover:bg-amber-400 text-gray-900 text-sm font-semibold shadow-lg shadow-amber-500/20 transition"
        >
          <Plus className="h-4 w-4" />
          Tambah Mapping
        </button>
      </div>

      <div className="bg-white/65 dark:bg-white/[0.04] glass-strong rounded-[24px] border border-white/50 dark:border-white/10 shadow-[0_10px_40px_-15px_rgba(0,0,0,0.08)] overflow-hidden">
        {isLoading ? (
          <LoadingSkeleton variant="table" message="Memuat mapping konfigurasi..." />
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
                        Tidak ada konfigurasi ditemukan.
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

      <ConfigModal
        data-scan="modal form mapping"
        open={showFormModal}
        onClose={() => { setShowFormModal(false); setEditingRow(null); }}
        isEdit={!!editingRow}
        initialData={editingRow}
        allKegiatanOptions={kegiatanOptsResponse}
        allConfigs={configsResponse}
        onSubmit={handleFormSubmit}
        isPending={createMutation.isPending || updateMutation.isPending}
      />

      <ConfirmDialog
        open={!!deleteTarget}
        onClose={() => setDeleteTarget(null)}
        onConfirm={() => deleteTarget && deleteMutation.mutate({ id: deleteTarget.id })}
        title="Hapus Mapping"
        message={`Yakin ingin menghapus mapping untuk kegiatan "${deleteTarget?.kegiatan?.nama}"? Tindakan ini tidak dapat dibatalkan.`}
        variant="danger"
        isLoading={deleteMutation.isPending}
      />
    </div>
  );
}
