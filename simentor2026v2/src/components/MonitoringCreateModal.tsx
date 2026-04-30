import React, { useState, useEffect, useMemo } from 'react';
import { X, Save } from 'lucide-react';
import { useApiQuery } from '../hooks/useApi';
import {
  monitoringKegiatanService,
  monitoringKegiatanConfigService,
} from '../lib/api-services';
import { SearchableSelect } from '@/components/ui/searchable-select';

interface DetailConfigItem {
  id: number;
  name: string;
  field?: {
    name: string;
    label: string;
    type: string;
    options?: string[];
  };
}

interface MonitoringCreateModalProps {
  open: boolean;
  onClose: () => void;
  isEdit: boolean;
  initialData?: any;
  onSubmit: (data: any) => void;
  isPending: boolean;
}

export function MonitoringCreateModal({
  open,
  onClose,
  isEdit,
  initialData,
  onSubmit,
  isPending,
}: MonitoringCreateModalProps) {
  // Field values state
  const [formData, setFormData] = useState<Record<string, any>>({});
  // Dynamic fields values state
  const [detilData, setDetilData] = useState<Record<string, string>>({});

  // Queries
  const { data: filtersResp } = useApiQuery(
    ['monitoring-filters'],
    () => monitoringKegiatanService.filters(),
    { enabled: open }
  );
  
  const { data: allKegiatanResp } = useApiQuery(
    ['monitoring-all-kegiatan'],
    () => monitoringKegiatanService.allKegiatanOptions(),
    { enabled: open }
  );

  const { data: configsResp } = useApiQuery(
    ['monitoring-configs'],
    () => monitoringKegiatanConfigService.list({ per_page: 500 }),
    { enabled: open }
  );

  const { data: kecResp } = useApiQuery(
    ['monitoring-kec-options'],
    () => monitoringKegiatanService.kecOptions(),
    { enabled: open }
  );

  const { data: desaResp } = useApiQuery(
    ['monitoring-desa-options', formData.kec_id],
    () => monitoringKegiatanService.desaOptions(formData.kec_id),
    { enabled: open && !!formData.kec_id }
  );

  const { data: petugasResp } = useApiQuery(
    ['monitoring-petugas-options', formData.kegiatan_id],
    () => monitoringKegiatanService.petugasOptions(formData.kegiatan_id),
    { enabled: open && !!formData.kegiatan_id }
  );

  // Initialize Form Data
  useEffect(() => {
    if (open) {
      if (initialData) {
        setFormData({
          fungsi: initialData.fungsi || '',
          kegiatan_id: initialData.kegiatan_id || initialData.kegiatan?.id || '',
          kec_id: initialData.kec_id || initialData.nmkec || '', // Backend might send nmkec natively, assume we pass correct format when edit
          desa_id: initialData.desa_id || initialData.nmdesa || '',
          kode_sampel: initialData.kode_sampel || '',
          mitra_id: initialData.mitra_id || '',
        });

        // Parse detail configs from initialData
        const detailsData: Record<string, string> = {};
        if (initialData.detil_configurations) {
           initialData.detil_configurations.forEach((c: any) => {
              const fieldName = c.field?.name || c.name;
              if (initialData[fieldName] !== undefined) {
                 detailsData[fieldName] = String(initialData[fieldName]);
              }
           });
        }
        setDetilData(detailsData);
      } else {
        setFormData({
          fungsi: '',
          kegiatan_id: '',
          kec_id: '',
          desa_id: '',
          kode_sampel: '',
          mitra_id: '',
        });
        setDetilData({});
      }
    }
  }, [open, initialData]);

  // Derived Options
  const fungsiOptions = useMemo(() => {
    return (filtersResp?.data?.fungsi || []).map((f: string) => ({ value: f, label: f }));
  }, [filtersResp]);

  const kegiatanFilterOptions = useMemo(() => {
    const raw = allKegiatanResp?.data || allKegiatanResp || [];
    const list = Array.isArray(raw) ? raw : [];
    return list
      .filter((k: any) => !formData.fungsi || k.fungsi === formData.fungsi)
      .map((k: any) => ({ value: String(k.id), label: k.nama }));
  }, [allKegiatanResp, formData.fungsi]);

  const configOptionsList = useMemo(() => {
    const raw = configsResp?.data || configsResp || [];
    return Array.isArray(raw) ? (raw as any[]) : [];
  }, [configsResp]);

  const activeConfig = useMemo(() => {
    if (!formData.kegiatan_id) return null;
    return configOptionsList.find((c: any) => String(c.kegiatan_id) === String(formData.kegiatan_id));
  }, [formData.kegiatan_id, configOptionsList]);

  const dynamicFields = useMemo<DetailConfigItem[]>(() => {
    if (!activeConfig || !activeConfig.detil_configurations) return [];
    return activeConfig.detil_configurations;
  }, [activeConfig]);

  const kecOptions = useMemo(() => {
    const raw = kecResp?.data || kecResp || [];
    const list = Array.isArray(raw) ? raw : [];
    return list.map((k: any) => ({ value: String(k.id), label: k.nama }));
  }, [kecResp]);

  const desaOptions = useMemo(() => {
    const raw = desaResp?.data || desaResp || [];
    const list = Array.isArray(raw) ? raw : [];
    return list.map((d: any) => ({ value: String(d.id), label: d.nama }));
  }, [desaResp]);

  const petugasOptions = useMemo(() => {
    const raw = petugasResp?.data || petugasResp || [];
    const list = Array.isArray(raw) ? raw : [];
    return list.map((p: any) => ({ value: String(p.id), label: `${p.nama} (${p.type})` }));
  }, [petugasResp]);

  // Handlers
  const handleChange = (name: string, value: any) => {
    setFormData(prev => ({ ...prev, [name]: value }));
  };

  const handleDetilChange = (name: string, value: string) => {
    setDetilData(prev => ({ ...prev, [name]: value }));
  };

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    
    const detilConfigurations = dynamicFields.map(f => f.id);
    
    // Fallback dictionary for dynamic values matching field schemas
    const detilPayload = dynamicFields.reduce<Record<string, string>>((acc, config) => {
      const fieldName = config.field?.name || config.name;
      if (detilData[fieldName] !== undefined) {
         acc[fieldName] = detilData[fieldName];
      }
      return acc;
    }, {});

    const payload = {
       ...formData,
       kegiatan_id: Number(formData.kegiatan_id),
       monitoring_kegiatan_config_id: activeConfig ? activeConfig.id : undefined,
       detil_configurations: detilConfigurations,
       detil_data: detilPayload,
    };

    onSubmit(payload);
  };

  if (!open) return null;

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center">
      <div className="absolute inset-0 bg-black/40 backdrop-blur-sm" onClick={onClose} />

      <div className="relative bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-white/10 shadow-2xl w-full max-w-3xl mx-4 max-h-[90vh] flex flex-col">
        <div className="flex items-center justify-between px-6 py-4 border-b border-gray-200/60 dark:border-white/10">
          <div>
            <h2 className="text-base font-bold text-gray-900 dark:text-white">
              {isEdit ? 'Ubah Monitoring Kegiatan' : 'Tambah Monitoring Kegiatan'}
            </h2>
            <p className="mt-1 text-sm text-gray-500 dark:text-gray-400">
              {isEdit ? 'Ubah detil atau status realisasi tugas.' : 'Buat baris realisasi / monitoring baru sesuai format konfigurasi kegiatan.'}
            </p>
          </div>
          <button
            onClick={onClose}
            className="p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-white/10 text-gray-400 transition"
          >
            <X className="h-5 w-5" />
          </button>
        </div>

        <form onSubmit={handleSubmit} className="flex-1 overflow-y-auto px-6 py-6">
          {/* Base Form Segment */}
          <div className="grid grid-cols-2 gap-5">
            <div>
              <label className="block text-[11px] font-bold uppercase tracking-widest text-gray-500 dark:text-gray-400 mb-1.5">
                Fungsi <span className="text-red-500">*</span>
              </label>
              <select
                required
                value={formData.fungsi}
                onChange={e => { handleChange('fungsi', e.target.value); handleChange('kegiatan_id', ''); }}
                className="w-full text-[13px] bg-gray-50 dark:bg-black/20 border border-gray-200 dark:border-white/10 rounded-xl px-4 py-2.5 text-gray-900 dark:text-white focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 outline-none transition"
              >
                <option value="">Pilih Fungsi...</option>
                {fungsiOptions.map((opt: any) => (
                  <option key={opt.value} value={opt.value}>{opt.label}</option>
                ))}
              </select>
            </div>

            <div className="col-span-2 sm:col-span-1">
              <label className="block text-[11px] font-bold uppercase tracking-widest text-gray-500 dark:text-gray-400 mb-1.5">
                Kegiatan <span className="text-red-500">*</span>
              </label>
              <SearchableSelect
                value={formData.kegiatan_id}
                onChange={(val) => handleChange('kegiatan_id', val)}
                options={kegiatanFilterOptions}
                placeholder="Pilih Kegiatan..."
                searchPlaceholder="Cari..."
                emptyLabel="Tidak ditemukan"
                wrapLabel
                className="w-full"
              />
            </div>

            <div>
              <label className="block text-[11px] font-bold uppercase tracking-widest text-gray-500 dark:text-gray-400 mb-1.5">
                Kecamatan
              </label>
              <select
                value={formData.kec_id}
                onChange={e => { handleChange('kec_id', e.target.value); handleChange('desa_id', ''); }}
                className="w-full text-[13px] bg-gray-50 dark:bg-black/20 border border-gray-200 dark:border-white/10 rounded-xl px-4 py-2.5 text-gray-900 dark:text-white focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 outline-none transition"
              >
                <option value="">(Opsional) Pilih Kecamatan</option>
                {kecOptions.map((opt: any) => (
                  <option key={opt.value} value={opt.value}>{opt.label}</option>
                ))}
              </select>
            </div>

            <div>
              <label className="block text-[11px] font-bold uppercase tracking-widest text-gray-500 dark:text-gray-400 mb-1.5">
                Desa
              </label>
              <select
                value={formData.desa_id}
                onChange={e => handleChange('desa_id', e.target.value)}
                disabled={!formData.kec_id}
                className="w-full text-[13px] bg-gray-50 dark:bg-black/20 border border-gray-200 dark:border-white/10 rounded-xl px-4 py-2.5 text-gray-900 dark:text-white focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 outline-none transition disabled:opacity-50"
              >
                <option value="">(Opsional) Pilih Desa</option>
                {desaOptions.map((opt: any) => (
                  <option key={opt.value} value={opt.value}>{opt.label}</option>
                ))}
              </select>
            </div>

            <div>
              <label className="block text-[11px] font-bold uppercase tracking-widest text-gray-500 dark:text-gray-400 mb-1.5">
                Kode Sampel
              </label>
              <input
                type="text"
                value={formData.kode_sampel}
                onChange={e => handleChange('kode_sampel', e.target.value)}
                placeholder="Mis: SMP-001"
                className="w-full text-[13px] bg-gray-50 dark:bg-black/20 border border-gray-200 dark:border-white/10 rounded-xl px-4 py-2.5 text-gray-900 dark:text-white focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 outline-none transition"
              />
            </div>
            
            <div>
              <label className="block text-[11px] font-bold uppercase tracking-widest text-gray-500 dark:text-gray-400 mb-1.5">
                Petugas <span className="text-gray-400 font-normal lowercase">(Organik / Mitra)</span>
              </label>
              <SearchableSelect
                value={formData.mitra_id}
                onChange={(val) => handleChange('mitra_id', val)}
                options={petugasOptions}
                placeholder="Cari Petugas (Opsional)..."
                searchPlaceholder="Ketik Nama..."
                emptyLabel="Tidak ditemukan"
                wrapLabel
                className="w-full"
              />
            </div>
          </div>

          <div className="my-6 border-t border-gray-200/60 dark:border-white/10" />

          {/* Dynamic Forms */}
          {dynamicFields.length > 0 ? (
            <div className="space-y-4">
              <h3 className="text-[13px] font-bold text-emerald-800 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-500/10 px-4 py-2 rounded-lg border border-emerald-100 dark:border-emerald-500/20">
                Kolom Detail (Kustom Konfigurasi Kegiatan)
              </h3>
              <div className="grid grid-cols-2 gap-5">
                {dynamicFields.map(config => {
                   const fieldName = config.field?.name || config.name;
                   const label = config.field?.label || config.name;
                   const type = config.field?.type || 'text';
                   const options = config.field?.options || [];
                   const value = detilData[fieldName] || '';

                   return (
                      <div key={config.id} className="col-span-2 sm:col-span-1">
                        <label className="block text-[11px] font-bold uppercase tracking-widest text-gray-500 dark:text-gray-400 mb-1.5">
                          {label}
                        </label>
                        {type === 'select' || type === 'enum' ? (
                           <select
                              value={value}
                              onChange={e => handleDetilChange(fieldName, e.target.value)}
                              className="w-full text-[13px] bg-gray-50 dark:bg-black/20 border border-gray-200 dark:border-white/10 rounded-xl px-4 py-2.5 text-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 outline-none transition"
                           >
                              <option value="">Pilih...</option>
                              {options.map((opt: string) => (
                                 <option key={opt} value={opt}>{opt}</option>
                              ))}
                           </select>
                        ) : type === 'number' ? (
                           <input
                              type="number"
                              value={value}
                              onChange={e => handleDetilChange(fieldName, e.target.value)}
                              placeholder="..."
                              className="w-full text-[13px] bg-gray-50 dark:bg-black/20 border border-gray-200 dark:border-white/10 rounded-xl px-4 py-2.5 text-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 outline-none transition"
                           />
                        ) : (
                           <input
                              type={type === 'date' ? 'date' : 'text'}
                              value={value}
                              onChange={e => handleDetilChange(fieldName, e.target.value)}
                              placeholder="..."
                              className="w-full text-[13px] bg-gray-50 dark:bg-black/20 border border-gray-200 dark:border-white/10 rounded-xl px-4 py-2.5 text-gray-900 dark:text-white focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 outline-none transition"
                           />
                        )}
                      </div>
                   )
                })}
              </div>
            </div>
          ) : formData.kegiatan_id ? (
            <div className="bg-gray-50 dark:bg-white/5 p-4 rounded-xl text-center text-sm text-gray-500 border border-gray-200/50 dark:border-white/10">
              Tidak ada konfigurasi field dinamis untuk Kegiatan ini.
            </div>
          ) : null}

          {/* Actions */}
          <div className="mt-8 flex items-center justify-end gap-3 pt-4 border-t border-gray-200/60 dark:border-white/10">
            <button
              type="button"
              onClick={onClose}
              disabled={isPending}
              className="px-5 py-2.5 text-sm font-bold rounded-full bg-gray-100 hover:bg-gray-200 text-gray-700 dark:bg-white/5 dark:hover:bg-white/10 dark:text-gray-300 transition"
            >
              Batal
            </button>
            <button
              type="submit"
              disabled={isPending}
              className="px-5 py-2.5 text-sm font-bold rounded-full bg-amber-400 hover:bg-amber-500 text-gray-900 shadow-md shadow-amber-500/20 transition flex items-center gap-2 disabled:opacity-50"
            >
              <Save className="w-4 h-4" />
              {isPending ? 'Menyimpan...' : 'Simpan Data'}
            </button>
          </div>
        </form>
      </div>
    </div>
  );
}
