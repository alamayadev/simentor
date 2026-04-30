import React, { useEffect, useMemo, useState } from 'react';
import { Loader2, Upload, X } from 'lucide-react';

export type SkpJenis =
  | 'SKP Tahunan (Penetapan)'
  | 'SKP Bulanan'
  | 'SKP Tahunan (Penilaian)'
  | 'SKP Evaluasi Tahunan';

export type SkpFileFormValues = {
  jenis: SkpJenis;
  tahun: string;
  bulan: string;
  file: File | null;
};

export const SKP_JENIS_OPTIONS: SkpJenis[] = [
  'SKP Tahunan (Penetapan)',
  'SKP Bulanan',
  'SKP Tahunan (Penilaian)',
  'SKP Evaluasi Tahunan',
];

const DEFAULT_VALUES: SkpFileFormValues = {
  jenis: 'SKP Bulanan',
  tahun: '',
  bulan: '',
  file: null,
};

interface SkpFileModalProps {
  open: boolean;
  onClose: () => void;
  onSubmit: (values: SkpFileFormValues) => void;
  title: string;
  description?: string;
  submitLabel: string;
  yearOptions: string[];
  monthOptions: string[];
  initialValues?: Partial<SkpFileFormValues>;
  isLoading?: boolean;
  requireFile?: boolean;
  errorMessage?: string | null;
}

export function SkpFileModal({
  open,
  onClose,
  onSubmit,
  title,
  description,
  submitLabel,
  yearOptions,
  monthOptions,
  initialValues,
  isLoading = false,
  requireFile = true,
  errorMessage,
}: SkpFileModalProps) {
  const [values, setValues] = useState<SkpFileFormValues>(DEFAULT_VALUES);
  const [localError, setLocalError] = useState<string | null>(null);

  useEffect(() => {
    if (!open) return;
    setValues({
      ...DEFAULT_VALUES,
      ...initialValues,
      file: null,
    });
    setLocalError(null);
  }, [open, initialValues]);

  const mergedYearOptions = useMemo(() => {
    const items = new Set(yearOptions.filter(Boolean));
    if (values.tahun) {
      items.add(values.tahun);
    }
    return Array.from(items).sort();
  }, [yearOptions, values.tahun]);

  if (!open) return null;

  const isBulanan = values.jenis === 'SKP Bulanan';

  const handleSubmit = (event: React.FormEvent) => {
    event.preventDefault();

    if (!values.jenis || !values.tahun) {
      setLocalError('Jenis dan tahun wajib diisi.');
      return;
    }

    if (isBulanan && !values.bulan) {
      setLocalError('Bulan wajib diisi untuk SKP Bulanan.');
      return;
    }

    if (requireFile && !values.file) {
      setLocalError('File PDF wajib diunggah.');
      return;
    }

    setLocalError(null);
    onSubmit(values);
  };

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center">
      <div className="absolute inset-0 bg-black/40 backdrop-blur-sm" onClick={onClose} />

      <div className="relative z-10 bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-white/10 shadow-2xl w-full max-w-lg mx-4 max-h-[85vh] flex flex-col">
        <div className="flex items-center justify-between px-6 py-4 border-b border-gray-200/60 dark:border-white/10">
          <div>
            <h2 className="text-base font-bold text-gray-900 dark:text-white">{title}</h2>
            {description ? (
              <p className="mt-1 text-sm text-gray-500 dark:text-gray-400">{description}</p>
            ) : null}
          </div>
          <button
            type="button"
            onClick={onClose}
            className="p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-white/10 text-gray-400 transition"
          >
            <X className="h-4 w-4" />
          </button>
        </div>

        <form onSubmit={handleSubmit} className="flex-1 overflow-y-auto px-6 py-4 space-y-4">
          <div>
            <label className="block text-[11px] font-bold uppercase tracking-widest text-gray-500 dark:text-gray-400 mb-1.5">
              Jenis SKP <span className="text-red-500">*</span>
            </label>
            <select
              value={values.jenis}
              onChange={(event) =>
                setValues((prev) => ({
                  ...prev,
                  jenis: event.target.value as SkpJenis,
                  bulan: event.target.value === 'SKP Bulanan' ? prev.bulan : '',
                }))
              }
              className="w-full rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50/50 dark:bg-white/5 px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 transition"
            >
              {SKP_JENIS_OPTIONS.map((option) => (
                <option key={option} value={option}>
                  {option}
                </option>
              ))}
            </select>
          </div>

          <div className={`grid gap-4 ${isBulanan ? 'sm:grid-cols-2' : 'grid-cols-1'}`}>
            <div>
              <label className="block text-[11px] font-bold uppercase tracking-widest text-gray-500 dark:text-gray-400 mb-1.5">
                Tahun <span className="text-red-500">*</span>
              </label>
              <select
                value={values.tahun}
                onChange={(event) =>
                  setValues((prev) => ({ ...prev, tahun: event.target.value }))
                }
                className="w-full rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50/50 dark:bg-white/5 px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 transition"
              >
                <option value="">Pilih tahun</option>
                {mergedYearOptions.map((year) => (
                  <option key={year} value={year}>
                    {year}
                  </option>
                ))}
              </select>
            </div>

            {isBulanan ? (
              <div>
                <label className="block text-[11px] font-bold uppercase tracking-widest text-gray-500 dark:text-gray-400 mb-1.5">
                  Bulan <span className="text-red-500">*</span>
                </label>
                <select
                  value={values.bulan}
                  onChange={(event) =>
                    setValues((prev) => ({ ...prev, bulan: event.target.value }))
                  }
                  className="w-full rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50/50 dark:bg-white/5 px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 transition"
                >
                  <option value="">Pilih bulan</option>
                  {monthOptions.map((month) => (
                    <option key={month} value={month}>
                      {month}
                    </option>
                  ))}
                </select>
              </div>
            ) : null}
          </div>

          <div>
            <label className="block text-[11px] font-bold uppercase tracking-widest text-gray-500 dark:text-gray-400 mb-1.5">
              File PDF {requireFile ? <span className="text-red-500">*</span> : <span className="normal-case tracking-normal font-medium">(opsional)</span>}
            </label>
            <label className="flex items-center gap-3 rounded-2xl border border-dashed border-gray-300 dark:border-white/15 bg-gray-50/70 dark:bg-white/5 px-4 py-4 cursor-pointer hover:border-amber-400 dark:hover:border-amber-500/40 transition">
              <div className="h-10 w-10 rounded-xl bg-amber-100 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
                <Upload className="h-4 w-4" />
              </div>
              <div className="min-w-0 flex-1">
                <div className="text-sm font-semibold text-gray-900 dark:text-white truncate">
                  {values.file ? values.file.name : 'Pilih file PDF'}
                </div>
                <div className="text-xs text-gray-500 dark:text-gray-400">
                  Maksimal 7 MB. Format harus PDF.
                </div>
              </div>
              <input
                type="file"
                accept="application/pdf"
                className="hidden"
                onChange={(event) =>
                  setValues((prev) => ({
                    ...prev,
                    file: event.target.files?.[0] ?? null,
                  }))
                }
              />
            </label>
          </div>

          {(localError || errorMessage) ? (
            <div className="rounded-2xl border border-red-200 dark:border-red-500/30 bg-red-50 dark:bg-red-500/10 px-4 py-3 text-sm text-red-700 dark:text-red-300">
              {localError || errorMessage}
            </div>
          ) : null}
        </form>

        <div className="flex items-center justify-end gap-3 px-6 py-4 border-t border-gray-200/60 dark:border-white/10">
          <button
            type="button"
            onClick={onClose}
            disabled={isLoading}
            className="px-4 py-2 text-[12px] font-bold rounded-full bg-white/70 dark:bg-white/10 border border-white/50 dark:border-white/20 text-gray-700 dark:text-gray-300 hover:bg-white dark:hover:bg-white/10 transition-all shadow-sm disabled:opacity-50"
          >
            Batal
          </button>
          <button
            type="submit"
            onClick={handleSubmit}
            disabled={isLoading}
            className="inline-flex items-center gap-2 px-5 py-2 text-[12px] font-bold rounded-full bg-amber-400 hover:bg-amber-500 text-gray-900 shadow-sm transition-all disabled:opacity-50"
          >
            {isLoading ? <Loader2 className="h-3.5 w-3.5 animate-spin" /> : null}
            {submitLabel}
          </button>
        </div>
      </div>
    </div>
  );
}
