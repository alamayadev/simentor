import React, { useMemo, useState } from 'react';
import { motion } from 'motion/react';
import {
  Calendar,
  Ellipsis,
  FileText,
  Mail,
  Plus,
  RefreshCw,
  Search,
  Send,
  Trash2,
} from 'lucide-react';
import { PageHeader } from '../components/PageHeader';
import { LoadingSkeleton } from '../components/LoadingSkeleton';
import { ApiErrorBoundary } from '../components/ApiErrorBoundary';
import { ConfirmDialog } from '../components/ConfirmDialog';
import { EntityFormModal, type FormField } from '../components/EntityFormModal';
import { useApiMutation, useApiQuery } from '../hooks/useApi';
import { useDebounce } from '../hooks/useDebounce';
import { suratPermintaanService } from '../lib/api-services';
import type { ApiResponse } from '../lib/api';
import type { PaginatedResponse, SuratPermintaan as SuratPermintaanItem, SuratPermintaanFormOptions } from '../types/api';

const pageSize = 15;

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

function extractRows<T>(response?: ApiResponse<T[]>): T[] {
  return response?.data ?? [];
}

export function SuratPermintaan() {
  const currentYear = new Date().getFullYear();
  const [searchInput, setSearchInput] = useState('');
  const [filterYear, setFilterYear] = useState(String(currentYear));
  const [page, setPage] = useState(1);
  const [deleteTarget, setDeleteTarget] = useState<SuratPermintaanItem | null>(null);
  const [openActionMenuId, setOpenActionMenuId] = useState<number | null>(null);
  
  // Form State
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [modalMode, setModalMode] = useState<'create' | 'edit' | 'sisip'>('create');
  const [selectedItem, setSelectedItem] = useState<SuratPermintaanItem | null>(null);

  const debouncedSearch = useDebounce(searchInput, 350);

  const {
    data: listResponse,
    isLoading,
    error,
    refetch,
  } = useApiQuery(
    ['surat-permintaan', debouncedSearch, filterYear, page],
    () =>
      suratPermintaanService.list({
        per_page: pageSize,
        page,
        search: debouncedSearch || undefined,
        tahun: filterYear || undefined,
      }),
  );

  const { data: yearsResponse } = useApiQuery(
    ['surat-permintaan-years'],
    () => suratPermintaanService.years(),
  );


  const { data: formOptionsResponse } = useApiQuery(
    ['surat-permintaan-form-options'],
    () => suratPermintaanService.formOptions(),
  );

  const rows = useMemo(() => extractRows(listResponse), [listResponse]);
  const totalRecords = listResponse?.pagination_info?.total_records ?? 0;
  const totalPages = listResponse?.pagination_info?.total_page ?? 1;
  const formOptions = formOptionsResponse?.data;

  const mutationKeys = [['surat-permintaan'], ['surat-permintaan-years'], ['surat-permintaan-dates']];

  const createMutation = useApiMutation(
    (data: Record<string, unknown>) => suratPermintaanService.create(data),
    { invalidateKeys: mutationKeys, onSuccess: () => setIsModalOpen(false) }
  );

  const updateMutation = useApiMutation(
    (data: { id: number; payload: Record<string, unknown> }) => 
      suratPermintaanService.update(data.id, data.payload),
    { invalidateKeys: mutationKeys, onSuccess: () => setIsModalOpen(false) }
  );

  const deleteMutation = useApiMutation(
    (id: number) => suratPermintaanService.delete(id),
    { invalidateKeys: mutationKeys, onSuccess: () => setDeleteTarget(null) }
  );

  const sisipMutation = useApiMutation(
    (data: Record<string, unknown>) => suratPermintaanService.insert(data),
    { invalidateKeys: mutationKeys, onSuccess: () => setIsModalOpen(false) }
  );

  const stats = [
    { label: 'Total Surat', value: totalRecords, icon: FileText, color: 'text-orange-600 dark:text-orange-400' },
    { label: 'Tahun', value: filterYear || 'Semua', icon: Calendar, color: 'text-blue-600 dark:text-blue-400' },
    { label: 'Ditampilkan', value: rows.length, icon: Mail, color: 'text-emerald-600 dark:text-emerald-400' },
    { label: 'Nomor Baru', value: formOptions?.nomor_baru || '-', icon: Send, color: 'text-amber-600 dark:text-amber-400' },
  ];

  const formFields: FormField[] = [
    { 
      name: 'tanggal', 
      label: 'Tanggal', 
      type: 'date', 
      required: true,
      colSpan: 1 
    },
    { 
      name: 'thn', 
      label: 'Tahun', 
      type: 'text', 
      required: true,
      colSpan: 1 
    },
    { 
      name: 'nomor', 
      label: 'Nomor Urut', 
      type: 'text', 
      required: modalMode !== 'sisip',
      placeholder: 'Contoh: 1',
      colSpan: 1 
    },
    { 
      name: 'kode_klas', 
      label: 'Klasifikasi', 
      type: 'select', 
      required: true,
      options: formOptions?.klasifikasi.map(k => ({ value: k.kode, label: `${k.kode} - ${k.keterangan}` })) || [],
      colSpan: 1 
    },
    { 
      name: 'dari', 
      label: 'Pengirim / Asal Surat', 
      type: 'text', 
      required: true,
      placeholder: 'Contoh: Kepala BPS Kab. Karawang',
      colSpan: 2 
    },
    { 
      name: 'perihal', 
      label: 'Perihal', 
      type: 'textarea', 
      required: true,
      placeholder: 'Deskripsi singkat perihal surat permintaan...',
      colSpan: 2 
    },
  ];

  const handleOpenCreate = () => {
    setModalMode('create');
    setSelectedItem(null);
    setIsModalOpen(true);
  };

  const handleOpenEdit = (item: SuratPermintaanItem) => {
    setModalMode('edit');
    setSelectedItem(item);
    setIsModalOpen(true);
    setOpenActionMenuId(null);
  };

  const handleOpenSisip = (item: SuratPermintaanItem) => {
    setModalMode('sisip');
    setSelectedItem(item);
    setIsModalOpen(true);
    setOpenActionMenuId(null);
  };

  const handleFormSubmit = (data: Record<string, unknown>) => {
    if (modalMode === 'create') {
      createMutation.mutate(data);
    } else if (modalMode === 'edit' && selectedItem) {
      updateMutation.mutate({ id: selectedItem.id, payload: data });
    } else if (modalMode === 'sisip' && selectedItem) {
      sisipMutation.mutate({ ...data, id: selectedItem.id });
    }
  };

  const initialData = useMemo(() => {
    if (modalMode === 'edit' && selectedItem) {
      return {
        tanggal: selectedItem.tanggal,
        thn: selectedItem.thn,
        nomor: selectedItem.nomor,
        kode_klas: selectedItem.kode_klas,
        dari: selectedItem.dari,
        perihal: selectedItem.perihal,
      };
    }
    if (modalMode === 'sisip' && selectedItem) {
      return {
        tanggal: new Date().toISOString().split('T')[0],
        thn: selectedItem.thn,
        dari: selectedItem.dari,
        perihal: selectedItem.perihal,
        kode_klas: selectedItem.kode_klas,
      };
    }
    return {
      tanggal: new Date().toISOString().split('T')[0],
      thn: String(currentYear),
      nomor: formOptions?.nomor_baru || '',
    };
  }, [modalMode, selectedItem, currentYear, formOptions]);

  return (
    <div className="space-y-6">
      <PageHeader
        title="Surat Permintaan"
        description="Kelola penomoran dan arsip surat permintaan data serta layanan."
        actions={
          <button
            onClick={handleOpenCreate}
            className="flex items-center gap-1.5 px-4 py-2 rounded-full bg-amber-300 hover:bg-amber-400 text-gray-900 text-sm font-semibold shadow-lg shadow-amber-500/20 transition"
          >
            <Plus className="h-4 w-4" />
            Buat Nomor
          </button>
        }
      />

      {/* Stats Grid */}
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

      {/* Filters */}
      <div className="bg-white/65 dark:bg-white/[0.04] glass-strong rounded-[24px] border border-white/50 dark:border-white/10 p-5 shadow-sm">
        <div className="flex flex-wrap items-end gap-3">
          <div className="flex w-full max-w-sm flex-col gap-2">
            <label className="text-[10px] font-bold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Pencarian</label>
            <div className="relative group">
              <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-3.5 w-3.5 text-gray-400 group-focus-within:text-amber-500 transition-colors" />
              <input
                type="text"
                placeholder="Cari pengirim atau perihal..."
                value={searchInput}
                onChange={(e) => setSearchInput(e.target.value)}
                className="w-full h-10 rounded-xl border border-gray-200 dark:border-white/10 bg-white/70 dark:bg-white/5 pl-9 pr-4 text-xs text-gray-700 dark:text-gray-200 focus:ring-2 focus:ring-amber-500/20 transition-all outline-none"
              />
            </div>
          </div>

          <div className="flex flex-col gap-2">
            <label className="text-[10px] font-bold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Tahun</label>
            <select
              value={filterYear}
              onChange={(e) => setFilterYear(e.target.value)}
              className="h-10 rounded-xl border border-gray-200 dark:border-white/10 bg-white/70 dark:bg-white/5 px-4 text-xs font-bold text-gray-700 dark:text-gray-200 outline-none transition focus:border-amber-500 dark:[color-scheme:dark]"
            >
              <option value="">Semua Tahun</option>
              {(yearsResponse?.data ?? []).map((y) => (
                <option key={y} value={y}>{y}</option>
              ))}
            </select>
          </div>

          <button
            onClick={() => {
              setSearchInput('');
              setFilterYear(String(currentYear));
            }}
            className="h-10 px-5 rounded-full bg-white/70 dark:bg-white/10 border border-gray-200 dark:border-white/10 text-gray-700 dark:text-gray-300 text-[11px] font-bold shadow-sm hover:bg-white transition-all"
          >
            Reset
          </button>

          <button
            onClick={() => refetch()}
            className="h-10 px-5 rounded-full bg-slate-900 dark:bg-white text-white dark:text-slate-900 text-[11px] font-bold shadow-sm transition-all flex items-center gap-2"
          >
            <RefreshCw className="h-3.5 w-3.5" />
            Muat Ulang
          </button>
        </div>
      </div>

      {/* Table Section */}
      <div className="bg-white/65 dark:bg-white/[0.04] glass-strong rounded-[24px] border border-white/50 dark:border-white/10 shadow-[0_10px_40px_-15px_rgba(0,0,0,0.08)] overflow-hidden">
        {isLoading ? (
          <LoadingSkeleton variant="table" message="Memuat data surat permintaan..." />
        ) : error ? (
          <ApiErrorBoundary error={error} onRetry={() => refetch()} title="Gagal Memuat Data" />
        ) : rows.length === 0 ? (
          <div className="flex flex-col items-center justify-center px-6 py-16 text-center">
            <div className="mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-gray-100 text-gray-400 dark:bg-white/10">
              <FileText className="h-7 w-7" />
            </div>
            <h3 className="text-sm font-bold text-gray-900 dark:text-white">Belum ada data surat permintaan.</h3>
            <p className="mt-1 max-w-md text-xs text-gray-500 dark:text-gray-400">Ubah filter atau buat nomor surat baru.</p>
          </div>
        ) : (
          <>
            <div className="overflow-x-auto">
              <table className="w-full text-[13px]">
                <thead>
                  <tr className="text-left text-[11.5px] text-gray-500 dark:text-gray-400 border-b border-gray-200/60 dark:border-white/10">
                    <th className="px-6 py-5 font-bold uppercase tracking-widest bg-gray-50/30 dark:bg-white/5 whitespace-nowrap">Nomor Surat</th>
                    <th className="px-6 py-5 font-bold uppercase tracking-widest bg-gray-50/30 dark:bg-white/5 whitespace-nowrap">Tanggal</th>
                    <th className="px-6 py-5 font-bold uppercase tracking-widest bg-gray-50/30 dark:bg-white/5 whitespace-nowrap">Pengirim</th>
                    <th className="px-6 py-5 font-bold uppercase tracking-widest bg-gray-50/30 dark:bg-white/5 whitespace-nowrap">Perihal</th>
                    <th className="px-6 py-5 font-bold uppercase tracking-widest bg-gray-50/30 dark:bg-white/5 whitespace-nowrap text-right">Aksi</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-gray-100/70 dark:divide-white/5">
                  {rows.map((item) => (
                    <tr key={item.id} className="hover:bg-white/40 dark:hover:bg-white/[0.02] transition-colors">
                      <td className="px-6 py-4">
                        <div className="flex flex-col gap-0.5">
                          <span className="font-bold text-gray-900 dark:text-white text-[13px] leading-tight">{item.no_surat}</span>
                          <span className="text-[11px] text-gray-500">#{item.id} / {item.nomor}{item.no_sisip ? `.${item.no_sisip}` : ''}</span>
                        </div>
                      </td>
                      <td className="px-6 py-4 whitespace-nowrap text-gray-600 dark:text-gray-400">{item.tanggal_indo || formatDate(item.tanggal)}</td>
                      <td className="px-6 py-4">
                        <span className="line-clamp-1 font-medium text-gray-700 dark:text-gray-300">{item.dari}</span>
                      </td>
                      <td className="px-6 py-4">
                        <span className="line-clamp-2 text-gray-600 dark:text-gray-400">{item.perihal}</span>
                      </td>
                      <td className="px-6 py-4">
                        <div className="relative flex justify-end">
                          <button
                            onClick={() => setOpenActionMenuId(openActionMenuId === item.id ? null : item.id)}
                            className="inline-flex size-9 items-center justify-center rounded-lg text-gray-500 outline-none transition hover:bg-gray-100 hover:text-amber-600 dark:hover:bg-white/10"
                          >
                            <Ellipsis className="h-4 w-4" />
                          </button>
                          {openActionMenuId === item.id && (
                            <div className="absolute right-0 top-full z-50 mt-1 min-w-[160px] overflow-hidden rounded-xl border border-gray-200 bg-white py-1 shadow-xl dark:border-white/10 dark:bg-gray-950">
                              <button
                                onClick={() => handleOpenEdit(item)}
                                className="flex w-full items-center gap-2 px-3 py-2 text-left text-xs font-bold text-gray-700 transition-colors hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-white/5"
                              >
                                <FileText className="h-3.5 w-3.5 text-amber-500" />
                                Edit Data
                              </button>
                              <button
                                onClick={() => handleOpenSisip(item)}
                                className="flex w-full items-center gap-2 px-3 py-2 text-left text-xs font-bold text-gray-700 transition-colors hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-white/5"
                              >
                                <Plus className="h-3.5 w-3.5 text-blue-500" />
                                Sisip Nomor
                              </button>
                              <div className="mx-2 h-px bg-gray-100 dark:bg-white/5" />
                              <button
                                onClick={() => {
                                  setDeleteTarget(item);
                                  setOpenActionMenuId(null);
                                }}
                                className="flex w-full items-center gap-2 px-3 py-2 text-left text-xs font-bold text-red-600 transition-colors hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-500/10"
                              >
                                <Trash2 className="h-3.5 w-3.5" />
                                Hapus
                              </button>
                            </div>
                          )}
                        </div>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>

            {/* Pagination */}
            <div className="flex items-center justify-between px-6 py-5 border-t border-gray-200/60 dark:border-white/10 dark:bg-white/5">
              <p className="text-[12px] text-gray-500 dark:text-gray-400 font-medium">
                Halaman {page} dari {totalPages} | {totalRecords} data
              </p>
              <div className="flex gap-2">
                <button
                  onClick={() => setPage(p => Math.max(1, p - 1))}
                  disabled={page === 1}
                  className="px-4 py-2 text-[11px] font-bold rounded-full bg-white/70 dark:bg-white/10 border border-white/50 dark:border-white/20 text-gray-700 dark:text-gray-300 hover:bg-white transition-all disabled:opacity-30 disabled:cursor-not-allowed shadow-sm"
                >
                  Sebelumnya
                </button>
                <button
                  onClick={() => setPage(p => Math.min(totalPages, p + 1))}
                  disabled={page === totalPages}
                  className="px-4 py-2 text-[11px] font-bold rounded-full bg-white/70 dark:bg-white/10 border border-white/50 dark:border-white/20 text-gray-700 dark:text-gray-300 hover:bg-white transition-all disabled:opacity-30 disabled:cursor-not-allowed shadow-sm"
                >
                  Berikutnya
                </button>
              </div>
            </div>
          </>
        )}
      </div>

      {/* Form Modal */}
      <EntityFormModal
        open={isModalOpen}
        onClose={() => setIsModalOpen(false)}
        onSubmit={handleFormSubmit}
        title={modalMode === 'create' ? 'Buat Nomor Surat Permintaan' : modalMode === 'sisip' ? 'Sisip Nomor Surat' : 'Edit Surat Permintaan'}
        description={modalMode === 'sisip' ? `Menyisipkan nomor baru setelah nomor ${selectedItem?.nomor}` : 'Lengkapi informasi detail surat permintaan.'}
        mode={modalMode === 'edit' ? 'edit' : 'create'}
        fields={formFields}
        initialData={initialData}
        isLoading={createMutation.isPending || updateMutation.isPending || sisipMutation.isPending}
      />

      {/* Delete Confirmation */}
      <ConfirmDialog
        open={deleteTarget !== null}
        onClose={() => setDeleteTarget(null)}
        onConfirm={() => deleteTarget && deleteMutation.mutate(deleteTarget.id)}
        title="Hapus Surat Permintaan"
        message={`Apakah Anda yakin ingin menghapus surat dari "${deleteTarget?.dari}" dengan perihal "${deleteTarget?.perihal}"? Tindakan ini tidak dapat dibatalkan.`}
        confirmLabel="Hapus Permanen"
        isLoading={deleteMutation.isPending}
      />
    </div>
  );
}
