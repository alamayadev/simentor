import React, { useEffect, useMemo, useState } from 'react';
import { Plus, Save, Loader2, Pencil, Trash2, Settings } from 'lucide-react';
import { motion, AnimatePresence } from 'motion/react';
import { PageHeader } from '../components/PageHeader';
import { settingService } from '../lib/api-services';
import type { Setting, SettingCreatePayload, SettingUpdatePayload } from '../types/api';
import { EntityFormModal, type FormField } from '../components/EntityFormModal';
import { ConfirmDialog } from '../components/ConfirmDialog';
import { type ApiResponse } from '../lib/api';

const GROUP_MAPPING: Record<string, string> = {
  '1': 'KANTOR',
  '2': 'PEJABAT',
  '3': 'DIPA',
  '4': 'TEMPLATE',
};

function getGroupName(key: string) {
  return GROUP_MAPPING[key] || key;
}

export function UmumPengaturan() {
  const [data, setData] = useState<Record<string, Setting[]>>({});
  const [activeTab, setActiveTab] = useState<string>('');
  const [isLoading, setIsLoading] = useState(false);
  const [errorMsg, setErrorMsg] = useState<string | null>(null);

  const [isFormOpen, setIsFormOpen] = useState(false);
  const [formMode, setFormMode] = useState<'create' | 'edit'>('create');
  const [selectedSetting, setSelectedSetting] = useState<Setting | null>(null);
  
  const [isDeleteOpen, setIsDeleteOpen] = useState(false);
  const [settingToDelete, setSettingToDelete] = useState<Setting | null>(null);
  const [isSubmitting, setIsSubmitting] = useState(false);

  const fetchSettings = async () => {
    setIsLoading(true);
    setErrorMsg(null);
    try {
      // settingService.list() effectively calls apiGet<Record<string, Setting[]>>
      const res = await settingService.list() as unknown as ApiResponse<Record<string, Setting[]>>;
      if (res.success && res.data) {
        setData(res.data);
        const keys = Object.keys(res.data);
        if (keys.length > 0 && !activeTab) {
          setActiveTab(keys[0]);
        }
      } else {
        setErrorMsg(res.message || 'Gagal memuat pengaturan');
      }
    } catch (err: any) {
      setErrorMsg(err.message || 'Terjadi kesalahan saat memuat data');
    } finally {
      setIsLoading(false);
    }
  };

  useEffect(() => {
    void fetchSettings();
  }, []);

  const groups = useMemo(() => Object.keys(data), [data]);
  
  const activeItems = useMemo(() => {
    if (!activeTab || !data[activeTab]) return [];
    return data[activeTab];
  }, [data, activeTab]);

  const handleOpenCreate = () => {
    setFormMode('create');
    setSelectedSetting(null);
    setIsFormOpen(true);
  };

  const handleOpenEdit = (setting: Setting) => {
    setFormMode('edit');
    setSelectedSetting(setting);
    setIsFormOpen(true);
  };

  const handleDeleteClick = (setting: Setting) => {
    setSettingToDelete(setting);
    setIsDeleteOpen(true);
  };

  const handleFormSubmit = async (formData: Record<string, unknown>) => {
    setIsSubmitting(true);
    try {
      if (formMode === 'create') {
        const payload: SettingCreatePayload = {
          grup: String(formData.grup),
          tahun: String(formData.tahun),
          key: String(formData.key),
          value: String(formData.value),
        };
        await settingService.create(payload);
      } else if (selectedSetting) {
        const payload: SettingUpdatePayload = {
          grup: String(formData.grup),
          tahun: String(formData.tahun),
          value: String(formData.value),
        };
        await settingService.update(selectedSetting.id, payload);
      }
      setIsFormOpen(false);
      void fetchSettings();
    } catch (err: any) {
      alert(err.message || 'Gagal menyimpan pengaturan');
    } finally {
      setIsSubmitting(false);
    }
  };

  const confirmDelete = async () => {
    if (!settingToDelete) return;
    setIsSubmitting(true);
    try {
      await settingService.delete(settingToDelete.id);
      setIsDeleteOpen(false);
      void fetchSettings();
    } catch (err: any) {
      alert(err.message || 'Gagal menghapus pengaturan');
    } finally {
      setIsSubmitting(false);
    }
  };

  const formFields: FormField[] = [
    {
      name: 'grup',
      label: 'Grup Pengaturan',
      type: 'text',
      required: true,
      placeholder: 'Contoh: kantor, dipa, pejabat',
    },
    {
      name: 'tahun',
      label: 'Tahun',
      type: 'text',
      required: true,
      placeholder: 'Contoh: 2025',
    },
    {
      name: 'key',
      label: 'Key',
      type: 'text',
      required: true,
      placeholder: 'Contoh: NAMA_KANTOR',
      // In edit mode, we shouldn't really change the key structure without backend support since UpdatePayload omits it.
    },
    {
      name: 'value',
      label: 'Value',
      type: 'textarea',
      required: true,
      placeholder: 'Nilai pengaturan',
      colSpan: 2,
    },
  ];

  // For edit mode, we might want to inform the user that key cannot be changed effectively by UpdatePayload.
  // We include it so it shows in the form, but UpdatePayload drops it.

  return (
    <div className="space-y-6">
      <PageHeader
        title="Pengaturan"
        description="Kelola parameter umum, data kantor, pejabat, DIPA, dan template dokumen."
        actions={
          <button 
            type="button"
            onClick={fetchSettings}
            className="flex items-center gap-1.5 rounded-full bg-amber-300 px-4 py-2 text-sm font-semibold text-gray-900 shadow-lg shadow-amber-500/20 transition hover:bg-amber-400"
          >
            <Loader2 className={`h-4 w-4 ${isLoading ? 'animate-spin' : ''}`} />
            Refresh
          </button>
        }
      />

      {errorMsg && (
        <div className="rounded-2xl border border-rose-200 bg-rose-50/50 px-4 py-3 text-sm text-rose-600 dark:border-rose-500/40 dark:bg-rose-500/10 dark:text-rose-300 glass">
          {errorMsg}
        </div>
      )}

      <div className="grid gap-6 lg:grid-cols-[220px_1fr]">
        <aside 
          data-scan="tab kategori"
          className="space-y-3 rounded-[24px] border border-white/50 bg-white/65 dark:bg-white/[0.04] glass-strong p-4 dark:border-white/10"
        >
          <div className="text-[10px] items-center gap-1.5 flex font-bold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400 ml-1">
            <Settings className="w-3.5 h-3.5" />
            Kategori
          </div>
          <div className="flex flex-col gap-1.5">
            {isLoading && groups.length === 0 ? (
               <div className="text-center py-4 text-xs text-gray-500">Memuat kategori...</div>
            ) : groups.length === 0 ? (
               <div className="text-center py-4 text-xs text-gray-500">Belum ada grup pengaturan.</div>
            ) : (
                groups.map((groupName) => {
                  const active = groupName === activeTab;
                  return (
                    <button
                      key={groupName}
                      data-scan={`tab kategori ${getGroupName(groupName).toLowerCase()}`}
                      type="button"
                      onClick={() => setActiveTab(groupName)}
                      className={`w-full shrink-0 rounded-[14px] px-4 py-2.5 text-left text-[11px] font-bold uppercase tracking-[0.15em] transition-all duration-200 ${
                        active
                          ? 'bg-amber-400 text-gray-900 shadow-sm shadow-amber-500/20'
                          : 'bg-transparent text-gray-500 hover:bg-white/60 dark:text-gray-400 dark:hover:bg-white/10 dark:hover:text-white'
                      }`}
                    >
                      {getGroupName(groupName)}
                    </button>
                  );
                })
            )}
          </div>
        </aside>

        <div className="space-y-4">
          <div className="flex items-center justify-between">
            <div className="text-xs uppercase flex items-center gap-2 font-bold tracking-[0.2em] text-gray-500 dark:text-gray-400">
              {activeTab ? getGroupName(activeTab) : 'Pilih Grup'}
            </div>
            <button 
              data-scan="tombol tambah"
              type="button"
              onClick={handleOpenCreate}
              className="inline-flex items-center justify-center gap-2 rounded-full bg-amber-300 px-4 py-2 text-sm font-semibold text-gray-900 shadow-lg shadow-amber-500/20 transition hover:bg-amber-400"
            >
              <Plus className="h-4 w-4" />
              Tambah
            </button>
          </div>

          <div 
            data-scan="daftar pengaturan"
            className="rounded-[24px] border border-white/50 bg-white/65 dark:bg-white/[0.04] glass-strong p-5 dark:border-white/10 min-h-[200px]"
          >
             {isLoading ? (
                <div className="flex h-full min-h-[160px] items-center justify-center">
                   <div className="flex items-center gap-3 text-sm text-gray-500 dark:text-gray-400">
                     <Loader2 className="h-5 w-5 animate-spin" />
                     Memuat daftar pengaturan...
                   </div>
                 </div>
             ) : activeItems.length === 0 ? (
                 <div className="flex h-full min-h-[160px] items-center justify-center">
                    <p className="text-sm text-gray-500 dark:text-gray-400">Tidak ada item dalam kategori ini.</p>
                 </div>
             ) : (
                <div className="grid gap-3">
                  <AnimatePresence>
                    {activeItems.map((item, index) => (
                      <motion.div
                        key={item.id}
                        initial={{ opacity: 0, y: 10 }}
                        animate={{ opacity: 1, y: 0 }}
                        exit={{ opacity: 0, scale: 0.95 }}
                        transition={{ delay: index * 0.04 }}
                        className="group flex flex-wrap items-center justify-between gap-3 rounded-[18px] border border-gray-200 bg-white/80 px-4 py-3 text-sm dark:border-white/10 dark:bg-slate-950/60"
                      >
                        <div className="space-y-1">
                          <div className="text-[10px] font-bold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">{item.key}</div>
                          <div className="text-base font-medium text-gray-900 dark:text-white">
                            <span className="block max-w-[240px] truncate sm:max-w-none">{item.value}</span>
                          </div>
                          <div className="text-xs text-amber-600 dark:text-amber-400 font-semibold uppercase">{item.tahun}</div>
                        </div>

                        <div className="flex items-center gap-1 opacity-100 sm:opacity-0 sm:group-hover:opacity-100 transition-opacity">
                          <button
                            data-scan="tombol edit pengaturan"
                            type="button"
                            onClick={() => handleOpenEdit(item)}
                            className="inline-flex size-9 items-center justify-center rounded-xl text-sky-600 hover:bg-sky-50 dark:hover:bg-sky-500/10 dark:text-sky-400 transition-all"
                            title="Edit"
                          >
                            <Pencil className="size-4" />
                          </button>
                          <button
                            data-scan="tombol hapus pengaturan"
                            type="button"
                            onClick={() => handleDeleteClick(item)}
                            className="inline-flex size-9 items-center justify-center rounded-xl text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-500/10 dark:text-rose-400 transition-all"
                            title="Hapus"
                          >
                            <Trash2 className="size-4" />
                          </button>
                        </div>
                      </motion.div>
                    ))}
                  </AnimatePresence>
                </div>
             )}
          </div>
        </div>
      </div>

      <EntityFormModal
        open={isFormOpen}
        onClose={() => setIsFormOpen(false)}
        mode={formMode}
        title={formMode === 'create' ? 'Tambah Pengaturan' : 'Edit Pengaturan'}
        description={
          formMode === 'create'
            ? 'Tambahkan konfigurasi baru ke dalam sistem.'
            : 'Perbarui nilai konfigurasi yang sudah ada.'
        }
        fields={formFields}
        initialData={
          (selectedSetting as unknown as Record<string, unknown>) || (activeTab ? { grup: activeTab, tahun: new Date().getFullYear().toString() } : { tahun: new Date().getFullYear().toString() })
        }
        isLoading={isSubmitting}
        onSubmit={handleFormSubmit}
      />

      <ConfirmDialog
        open={isDeleteOpen}
        onClose={() => setIsDeleteOpen(false)}
        onConfirm={confirmDelete}
        title="Hapus Pengaturan"
        message={`Apakah Anda yakin ingin menghapus pengaturan ${settingToDelete?.key}? Tindakan ini tidak dapat dibatalkan.`}
        confirmLabel="Hapus"
        cancelLabel="Batal"
        variant="danger"
        isLoading={isSubmitting}
      />
    </div>
  );
}
