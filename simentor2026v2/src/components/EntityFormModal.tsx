import React, { useEffect, useState } from 'react';
import { X, Loader2 } from 'lucide-react';
import { DatePicker } from './DatePicker';
import { SearchableSelect } from '@/components/ui/searchable-select';

export interface FormField {
  name: string;
  label: string;
  type: 'text' | 'textarea' | 'select' | 'number' | 'date' | 'email' | 'password' | 'file';
  placeholder?: string;
  required?: boolean;
  options?: { value: string; label: string }[];
  colSpan?: 1 | 2;
  searchable?: boolean;
  wrapLabel?: boolean;
  searchPlaceholder?: string;
  emptyLabel?: string;
  dateMode?: 'date' | 'month';
  accept?: string;
}

interface EntityFormModalProps {
  open: boolean;
  onClose: () => void;
  onSubmit: (data: Record<string, unknown>) => void;
  title: string;
  description?: string;
  fields: FormField[];
  initialData?: Record<string, unknown> | null;
  isLoading?: boolean;
  isSubmitDisabled?: boolean;
  mode: 'create' | 'edit';
}

/**
 * Reusable modal form for creating/editing entities.
 * Handles form state, validation, and submission.
 */
export function EntityFormModal({
  open,
  onClose,
  onSubmit,
  title,
  description,
  fields,
  initialData,
  isLoading = false,
  isSubmitDisabled = false,
  mode,
}: EntityFormModalProps) {
  const [formData, setFormData] = useState<Record<string, unknown>>({});

  useEffect(() => {
    if (open) {
      const defaults: Record<string, unknown> = {};
      fields.forEach((f) => {
        defaults[f.name] = f.type === 'number' ? 0 : '';
      });
      setFormData(initialData ? { ...defaults, ...initialData } : defaults);
    }
  }, [open, initialData, fields]);

  if (!open) return null;

  const handleChange = (name: string, value: unknown) => {
    setFormData((prev) => ({ ...prev, [name]: value }));
  };

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    onSubmit(formData);
  };

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center">
      {/* Backdrop */}
      <div className="absolute inset-0 bg-black/40 backdrop-blur-sm" onClick={onClose} />

      {/* Modal */}
      <div className="relative bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-white/10 shadow-2xl w-full max-w-lg mx-4 max-h-[85vh] flex flex-col">
        {/* Header */}
        <div className="flex items-center justify-between px-6 py-4 border-b border-gray-200/60 dark:border-white/10">
          <div>
            <h2 className="text-base font-bold text-gray-900 dark:text-white">{title}</h2>
            {description ? (
              <p className="mt-1 text-sm text-gray-500 dark:text-gray-400">{description}</p>
            ) : null}
          </div>
          <button
            data-scan="tombol tutup modal"
            onClick={onClose}
            className="p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-white/10 text-gray-400 transition"
          >
            <X className="h-4 w-4" />
          </button>
        </div>

        {/* Form */}
        <form onSubmit={handleSubmit} className="flex-1 overflow-y-auto px-6 py-4">
          <div className="grid grid-cols-2 gap-4">
            {fields.map((field) => (
              <div key={field.name} className={field.colSpan === 2 ? 'col-span-2' : ''}>
                <label className="block text-[11px] font-bold uppercase tracking-widest text-gray-500 dark:text-gray-400 mb-1.5">
                  {field.label}
                  {field.required && <span className="text-red-500 ml-0.5">*</span>}
                </label>

                {field.type === 'textarea' ? (
                  <textarea
                    data-scan={`input-${field.name}`}
                    value={(formData[field.name] as string) ?? ''}
                    onChange={(e) => handleChange(field.name, e.target.value)}
                    placeholder={field.placeholder}
                    required={field.required}
                    rows={3}
                    className="w-full rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50/50 dark:bg-white/5 px-3 py-2 text-sm text-gray-900 dark:text-white placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 transition"
                  />
                ) : field.type === 'date' ? (
                  <DatePicker
                    data-scan={`input-${field.name}`}
                    value={(formData[field.name] as string) ?? ''}
                    onChange={(val) => handleChange(field.name, val)}
                    placeholder={field.placeholder || 'Pilih tanggal'}
                    className="form-date-picker"
                    mode={field.dateMode || 'date'}
                  />
                ) : field.type === 'select' ? (
                  field.searchable ? (
                    <SearchableSelect
                      data-scan={`input-${field.name}`}
                      value={(formData[field.name] as string) ?? ''}
                      onChange={(value) => handleChange(field.name, value)}
                      options={field.options ?? []}
                      placeholder={field.placeholder || '— Pilih —'}
                      searchPlaceholder={field.searchPlaceholder || `Cari ${field.label.toLowerCase()}...`}
                      emptyLabel={field.emptyLabel || `${field.label} tidak ditemukan.`}
                      wrapLabel={field.wrapLabel}
                    />
                  ) : (
                    <select
                      data-scan={`input-${field.name}`}
                      value={(formData[field.name] as string) ?? ''}
                      onChange={(e) => handleChange(field.name, e.target.value)}
                      required={field.required}
                      className="w-full rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50/50 dark:bg-white/5 px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 transition"
                    >
                      <option value="">— Pilih —</option>
                      {field.options?.map((opt) => (
                        <option key={opt.value} value={opt.value}>
                          {opt.label}
                        </option>
                      ))}
                    </select>
                  )
                ) : field.type === 'file' ? (
                  <input
                    data-scan={`input-${field.name}`}
                    type="file"
                    accept={field.accept}
                    onChange={(e) => {
                      const file = e.target.files?.[0];
                      if (file) {
                        handleChange(field.name, file);
                      }
                    }}
                    required={field.required && mode !== 'edit'}
                    className="w-full rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50/50 dark:bg-white/5 px-3 py-2 text-sm text-gray-900 dark:text-white file:mr-4 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-amber-100 dark:file:bg-amber-500/10 file:text-amber-700 dark:file:text-amber-400 hover:file:bg-amber-200 dark:hover:file:bg-amber-500/20 focus:outline-none focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 transition cursor-pointer"
                  />
                ) : (
                  <input
                    data-scan={`input-${field.name}`}
                    type={field.type}
                    value={(formData[field.name] as string | number) ?? ''}
                    onChange={(e) =>
                      handleChange(field.name, field.type === 'number' ? Number(e.target.value) : e.target.value)
                    }
                    placeholder={field.placeholder}
                    required={field.required}
                    className="w-full rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50/50 dark:bg-white/5 px-3 py-2 text-sm text-gray-900 dark:text-white placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 transition"
                  />
                )}
              </div>
            ))}
          </div>
        </form>

        {/* Footer */}
        <div className="flex items-center justify-end gap-3 px-6 py-4 border-t border-gray-200/60 dark:border-white/10">
          <button
            data-scan="tombol batal"
            type="button"
            onClick={onClose}
            disabled={isLoading}
            className="px-4 py-2 text-[12px] font-bold rounded-full bg-white/70 dark:bg-white/10 border border-white/50 dark:border-white/20 text-gray-700 dark:text-gray-300 hover:bg-white dark:hover:bg-white/10 transition-all shadow-sm disabled:opacity-50"
          >
            Batal
          </button>
          <button
            data-scan="tombol simpan"
            onClick={handleSubmit}
            disabled={isLoading || isSubmitDisabled}
            className="flex items-center gap-1.5 px-5 py-2 text-[12px] font-bold rounded-full bg-amber-400 hover:bg-amber-500 text-gray-900 shadow-sm transition-all disabled:opacity-50"
          >
            {isLoading && <Loader2 className="h-3.5 w-3.5 animate-spin" />}
            {mode === 'create' ? 'Simpan' : 'Perbarui'}
          </button>
        </div>
      </div>
    </div>
  );
}
