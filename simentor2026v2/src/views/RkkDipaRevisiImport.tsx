import React, { useMemo, useRef, useState } from 'react';
import {
  Check,
  CheckCircle2,
  File,
  FileSpreadsheet,
  Info,
  Terminal,
  Upload,
  XCircle,
} from 'lucide-react';
import { motion } from 'motion/react';
import { useQueryClient } from '@tanstack/react-query';
import { DatePicker } from '../components/DatePicker';
import { PageHeader } from '../components/PageHeader';
import { LoadingSkeleton } from '../components/LoadingSkeleton';
import { ApiErrorBoundary } from '../components/ApiErrorBoundary';
import { useApiQuery } from '../hooks/useApi';
import { dipaBudgetService } from '../lib/api-services';
import type { DipaImportFile } from '../types/api';

// ── Helpers ───────────────────────────────────────────────────
const monthToken = (date: string) => {
  const parsed = new Date(`${date}T00:00:00`);
  const month = new Intl.DateTimeFormat('en-US', { month: 'short' })
    .format(parsed)
    .toUpperCase();
  const day = new Intl.DateTimeFormat('en-US', { day: '2-digit' }).format(parsed);
  const year = new Intl.DateTimeFormat('en-US', { year: 'numeric' }).format(parsed);
  return { month, day, year };
};

const fmtDatetime = (datestr: string) => {
  const d = new Date(datestr);
  if (isNaN(d.getTime())) return datestr;
  return new Intl.DateTimeFormat('id-ID', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  }).format(d);
};

// ── Component ─────────────────────────────────────────────────
export function RkkDipaRevisiImport() {
  const queryClient = useQueryClient();
  const fileInputRef = useRef<HTMLInputElement>(null);

  const [activeTab, setActiveTab] = useState<'dipa' | 'sakti'>('dipa');

  // DIPA Upload State
  const [revision, setRevision] = useState('1');
  const [fiscalYear, setFiscalYear] = useState(new Date().getFullYear().toString());
  const [revisionDate, setRevisionDate] = useState(
    new Date().toISOString().slice(0, 10),
  );
  const [selectedFile, setSelectedFile] = useState<File | null>(null);
  const [isUploading, setIsUploading] = useState(false);
  const [uploadLogs, setUploadLogs] = useState<string | null>(null);
  const [uploadError, setUploadError] = useState<string | null>(null);
  const [uploadSuccess, setUploadSuccess] = useState(false);

  // SAKTI Upload State
  const [fiscalYearSakti, setFiscalYearSakti] = useState(new Date().getFullYear().toString());
  const [usageDateSakti, setUsageDateSakti] = useState(new Date().toISOString().slice(0, 10));
  const [selectedSaktiFile, setSelectedSaktiFile] = useState<File | null>(null);
  const [isUploadingSakti, setIsUploadingSakti] = useState(false);
  const [saktiUploadLogs, setSaktiUploadLogs] = useState<string | null>(null);
  const [saktiUploadError, setSaktiUploadError] = useState<string | null>(null);
  const [saktiSuccess, setSaktiSuccess] = useState(false);

  // ── Queries ──────────────────────────────────────────────────
  const {
    data: filesResp,
    isLoading: filesLoading,
    error: filesError,
    refetch: filesRefetch,
  } = useApiQuery(['dipa-files'], () => dipaBudgetService.getFiles());

  // ── Derived ──────────────────────────────────────────────────
  const importLogs: DipaImportFile[] = useMemo(
    () => (filesResp?.data as DipaImportFile[]) ?? [],
    [filesResp],
  );

  const previewFileName = useMemo(() => {
    const { month, day, year } = monthToken(revisionDate);
    return `RKK_DIPA_REVISI_${revision}_${year}_${month}_${day}.xlsx`;
  }, [revision, revisionDate]);

  // ── Handlers ─────────────────────────────────────────────────
  const handleFileChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0] ?? null;
    setSelectedFile(file);
    setUploadLogs(null);
    setUploadError(null);
    setUploadSuccess(false);
  };

  const handleUpload = async () => {
    if (!selectedFile || !revision || !revisionDate || !fiscalYear) return;

    setIsUploading(true);
    setUploadLogs(null);
    setUploadError(null);
    setUploadSuccess(false);

    const formData = new FormData();
    formData.append('revisi', revision);
    formData.append('tgl_revisi', revisionDate);
    formData.append('tahun_anggaran', fiscalYear);
    formData.append('excel_file', selectedFile);

    try {
      const result = await dipaBudgetService.importDipa(formData);
      if (result.success) {
        setUploadLogs(result.data?.logs ?? 'Import selesai tanpa log tambahan.');
        setUploadSuccess(true);
        setSelectedFile(null);
        if (fileInputRef.current) fileInputRef.current.value = '';
        queryClient.invalidateQueries({ queryKey: ['dipa-files'] });
      } else {
        setUploadError(result.message ?? 'Import gagal.');
      }
    } catch (err: unknown) {
      setUploadError(err instanceof Error ? err.message : 'Terjadi kesalahan.');
    } finally {
      setIsUploading(false);
    }
  };

  const handleSaktiUpload = async () => {
    if (!selectedSaktiFile || !fiscalYearSakti) return;

    setIsUploadingSakti(true);
    setSaktiUploadLogs(null);
    setSaktiUploadError(null);
    setSaktiSuccess(false);

    const formData = new FormData();
    formData.append('tahun_anggaran', fiscalYearSakti);

    // Ensure usage_date is the last day of the selected month
    const [y, m] = usageDateSakti.split('-').map(Number);
    const lastDay = new Date(y, m, 0);
    // Use local time to avoid timezone shifts
    const formattedDate = `${lastDay.getFullYear()}-${String(lastDay.getMonth() + 1).padStart(2, '0')}-${String(lastDay.getDate()).padStart(2, '0')}`;
    
    formData.append('usage_date', formattedDate);
    formData.append('excel_file', selectedSaktiFile);

    try {
      const result = await dipaBudgetService.importSakti(formData);
      if (result.success) {
        setSaktiUploadLogs(result.data?.logs ?? 'Import SAKTI selesai.');
        setSaktiSuccess(true);
        setSelectedSaktiFile(null);
        // Invalidate reconciliation cache
        queryClient.invalidateQueries({ queryKey: ['sakti-reconciliation'] });
      } else {
        setSaktiUploadError(result.message ?? 'Import SAKTI gagal.');
      }
    } catch (err: unknown) {
      setSaktiUploadError(err instanceof Error ? err.message : 'Terjadi kesalahan.');
    } finally {
      setIsUploadingSakti(false);
    }
  };

  // ── Render ───────────────────────────────────────────────────
  return (
    <div className="space-y-12">
      <div className="space-y-8">
        <PageHeader
          title="Import & Sinkronisasi"
          description="Kelola data RKK DIPA (Anggaran) dan Laporan SAKTI (Realisasi) dalam satu tempat."
        />

        {/* ── Custom Tab System ── */}
        <div className="relative flex w-full max-w-md items-center gap-1 rounded-2xl border border-gray-200/60 bg-gray-50/50 p-1.5 dark:border-white/10 dark:bg-white/[0.03]">
          <button
            data-scan="tab rkk dipa"
            onClick={() => setActiveTab('dipa')}
            className={`relative flex flex-1 items-center justify-center gap-2 py-2 text-xs font-bold transition-colors ${
              activeTab === 'dipa' ? 'text-sky-600 dark:text-sky-400' : 'text-gray-500 hover:text-gray-900 dark:hover:text-white'
            }`}
          >
            {activeTab === 'dipa' && (
              <motion.div
                layoutId="activeTabBg"
                className="absolute inset-0 rounded-xl bg-white shadow-sm ring-1 ring-gray-200/50 dark:bg-white/10 dark:ring-white/10"
              />
            )}
            <FileSpreadsheet className="relative z-10 h-3.5 w-3.5" />
            <span className="relative z-10 uppercase tracking-wider">RKK DIPA</span>
          </button>

          <button
            data-scan="tab sakti fa"
            onClick={() => setActiveTab('sakti')}
            className={`relative flex flex-1 items-center justify-center gap-2 py-2 text-xs font-bold transition-colors ${
              activeTab === 'sakti' ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-500 hover:text-gray-900 dark:hover:text-white'
            }`}
          >
            {activeTab === 'sakti' && (
              <motion.div
                layoutId="activeTabBg"
                className="absolute inset-0 rounded-xl bg-white shadow-sm ring-1 ring-gray-200/50 dark:bg-white/10 dark:ring-white/10"
              />
            )}
            <Upload className="relative z-10 h-3.5 w-3.5" />
            <span className="relative z-10 uppercase tracking-wider">SAKTI FA</span>
          </button>
        </div>

        <motion.div
          key={activeTab}
          initial={{ opacity: 0, x: 10 }}
          animate={{ opacity: 1, x: 0 }}
          transition={{ duration: 0.3, ease: 'easeOut' }}
        >
          {activeTab === 'dipa' ? (
            <div className="grid grid-cols-1 gap-8 md:grid-cols-2">
              {/* ── Left: Upload Form ── */}
              <div className="space-y-6">
                <h3 className="flex items-center gap-2 border-b border-gray-200/70 pb-2 text-sm font-bold text-gray-900 dark:border-white/10 dark:text-white">
                  <Upload className="h-4 w-4 text-sky-500" />
                  Pengaturan Upload DIPA
                </h3>

                <div className="space-y-4">
                  <div className="grid grid-cols-3 gap-4">
                    <div className="space-y-2">
                      <label className="text-[10px] uppercase tracking-widest text-gray-500 dark:text-gray-400">
                        Tahun Anggaran
                      </label>
                      <input
                        data-scan="input tahun anggaran"
                        type="number"
                        value={fiscalYear}
                        onChange={(e) => setFiscalYear(e.target.value)}
                        className="h-10 w-full rounded-xl border border-white/50 bg-white/70 px-4 text-sm text-gray-900 outline-none transition focus:border-sky-500 dark:border-white/10 dark:bg-white/[0.04] dark:text-white"
                      />
                    </div>

                    <div className="space-y-2">
                      <label className="text-[10px] uppercase tracking-widest text-gray-500 dark:text-gray-400">
                        Revisi Ke-
                      </label>
                      <input
                        data-scan="input revisi ke"
                        type="number"
                        min={1}
                        value={revision}
                        onChange={(e) => setRevision(e.target.value)}
                        className="h-10 w-full rounded-xl border border-white/50 bg-white/70 px-4 text-sm text-gray-900 outline-none transition focus:border-sky-500 dark:border-white/10 dark:bg-white/[0.04] dark:text-white"
                      />
                    </div>

                    <div className="space-y-2">
                      <label className="text-[10px] uppercase tracking-widest text-gray-500 dark:text-gray-400">
                        Tanggal Revisi
                      </label>
                      <DatePicker value={revisionDate} onChange={setRevisionDate} className="w-full" />
                    </div>
                  </div>

                  {/* Preview filename */}
                  <div className="rounded-[20px] border border-sky-500/20 bg-sky-500/5 p-4">
                    <div className="flex items-start gap-3">
                      <Info className="mt-0.5 h-4 w-4 shrink-0 text-sky-500" />
                      <div className="space-y-1">
                        <p className="text-xs font-bold text-gray-900 underline underline-offset-4 dark:text-white">
                          Naming Format Standar:
                        </p>
                        <p className="break-all font-mono text-xs text-sky-600 dark:text-sky-400">
                          {previewFileName}
                        </p>
                      </div>
                    </div>
                  </div>

                  {/* File dropzone */}
                  <label className="group flex cursor-pointer flex-col items-center justify-center space-y-4 rounded-[20px] border-2 border-dashed border-gray-200 p-8 text-center transition-colors hover:bg-gray-50/70 dark:border-white/10 dark:hover:bg-white/[0.03]">
                    <input
                      data-scan="input file excel"
                      ref={fileInputRef}
                      className="hidden"
                      accept=".xlsx,.xls"
                      type="file"
                      onChange={handleFileChange}
                    />
                    <div className="flex h-12 w-12 items-center justify-center rounded-full border border-white/50 bg-white/70 transition-transform group-hover:scale-110 dark:border-white/10 dark:bg-white/[0.04]">
                      <FileSpreadsheet className="h-6 w-6 text-gray-500 dark:text-gray-400" />
                    </div>
                    <div>
                      <p className="text-sm font-medium text-gray-900 dark:text-white">
                        Klik atau seret file Excel RKK ke sini
                      </p>
                      {selectedFile && (
                        <p className="mt-2 font-mono text-[11px] text-sky-600 dark:text-sky-400">
                          {selectedFile.name}
                        </p>
                      )}
                    </div>
                  </label>

                  <button
                    data-scan="tombol proses ingest"
                    disabled={!selectedFile || isUploading}
                    onClick={handleUpload}
                    className="flex h-12 w-full items-center justify-center gap-2 rounded-xl bg-sky-600 font-bold text-white shadow-lg shadow-sky-500/20 transition-colors hover:bg-sky-700 disabled:cursor-not-allowed disabled:opacity-50"
                  >
                    {isUploading ? (
                      <LoadingSkeleton variant="button" />
                    ) : (
                      <>
                        <CheckCircle2 className="h-4 w-4" />
                        Proses Ingest RKK DIPA
                      </>
                    )}
                  </button>

                  {uploadSuccess && uploadLogs && (
                    <motion.div
                      initial={{ opacity: 0, scale: 0.98 }}
                      animate={{ opacity: 1, scale: 1 }}
                      className="rounded-[16px] border border-emerald-400/30 bg-emerald-500/5 p-4"
                    >
                      <pre className="max-h-40 overflow-y-auto whitespace-pre-wrap font-mono text-[10px] text-gray-600 dark:text-gray-400">
                        {uploadLogs}
                      </pre>
                    </motion.div>
                  )}

                  {uploadError && (
                    <div className="rounded-[16px] border border-red-400/30 bg-red-500/5 p-4 text-[11px] text-red-600">
                      {uploadError}
                    </div>
                  )}
                </div>
              </div>

              {/* ── Right: Instructions + Log Table ── */}
              <div className="space-y-6">
                <h3 className="flex items-center gap-2 border-b border-gray-200/70 pb-2 text-sm font-bold text-gray-900 dark:border-white/10 dark:text-white">
                  <File className="h-4 w-4 text-amber-500" />
                  Log Terakhir DIPA
                </h3>

                <div className="overflow-hidden rounded-[20px] border border-white/50 bg-white/40 dark:border-white/10 dark:bg-white/[0.02]">
                  {filesLoading ? (
                    <div className="p-4">
                      <LoadingSkeleton variant="table" message="Memuat log import..." />
                    </div>
                  ) : (
                    <div className="overflow-x-auto">
                      <table className="w-full min-w-full text-sm">
                        <thead>
                          <tr className="border-b border-gray-200/60 text-left text-gray-900 dark:border-white/10 dark:text-white">
                            <th className="h-10 px-3 font-medium">Waktu</th>
                            <th className="h-10 px-3 font-medium text-center">Tahun</th>
                            <th className="h-10 px-3 font-medium">Nama File</th>
                          </tr>
                        </thead>
                        <tbody>
                          {importLogs.map((item) => (
                            <tr key={item.id} className="border-b border-gray-200/60 last:border-b-0 dark:border-white/10 hover:bg-white/50 dark:hover:bg-white/[0.02] transition-colors">
                              <td className="p-3 text-[11px]">{fmtDatetime(item.imported_at)}</td>
                              <td className="p-3 text-center text-sky-600 font-bold">{item.tahun_anggaran}</td>
                              <td className="p-3 font-mono text-[10px] truncate max-w-[120px]">{item.filename}</td>
                            </tr>
                          ))}
                        </tbody>
                      </table>
                    </div>
                  )}
                </div>
              </div>
            </div>
          ) : (
            <div className="grid grid-cols-1 gap-8 md:grid-cols-2">
              <div className="space-y-4">
                <h3 className="flex items-center gap-2 border-b border-gray-200/70 pb-2 text-sm font-bold text-gray-900 dark:border-white/10 dark:text-white">
                  <Upload className="h-4 w-4 text-emerald-500" />
                  Upload Form SAKTI FA
                </h3>

                <div className="grid grid-cols-2 gap-4">
                  <div className="space-y-2">
                    <label className="text-[10px] uppercase tracking-widest text-gray-500 dark:text-gray-400">
                      Tahun Anggaran
                    </label>
                    <input
                      type="number"
                      value={fiscalYearSakti}
                      onChange={(e) => setFiscalYearSakti(e.target.value)}
                      className="h-10 w-full rounded-xl border border-white/50 bg-white/70 px-4 text-sm text-gray-900 outline-none transition focus:border-emerald-500 dark:border-white/10 dark:bg-white/[0.04] dark:text-white"
                    />
                  </div>

                  <div className="space-y-2">
                    <label className="text-[10px] uppercase tracking-widest text-gray-500 dark:text-gray-400">
                      Bulan Pencairan
                    </label>
                    <DatePicker 
                      value={usageDateSakti} 
                      onChange={usageDateSakti => setUsageDateSakti(usageDateSakti)} 
                      mode="month"
                      className="w-full"
                    />
                  </div>
                </div>

                <label className="group flex cursor-pointer flex-col items-center justify-center space-y-4 rounded-[20px] border-2 border-dashed border-emerald-200/50 p-8 text-center transition-colors hover:bg-emerald-50/50 dark:border-emerald-500/10 dark:hover:bg-emerald-500/[0.03]">
                  <input
                    data-scan="input file sakti"
                    className="hidden"
                    accept=".xlsx,.xls"
                    type="file"
                    onChange={(e) => {
                      const file = e.target.files?.[0] ?? null;
                      setSelectedSaktiFile(file);
                      setSaktiUploadLogs(null);
                      setSaktiUploadError(null);
                      setSaktiSuccess(false);
                    }}
                  />
                  <div className="flex h-12 w-12 items-center justify-center rounded-full border border-emerald-500/20 bg-emerald-500/5 transition-transform group-hover:scale-110">
                    <FileSpreadsheet className="h-6 w-6 text-emerald-500" />
                  </div>
                  <div>
                    <p className="text-sm font-medium text-gray-900 dark:text-white">
                      Pilih Laporan FA Detail SAKTI
                    </p>
                    <p className="mt-1 text-[10px] text-gray-500 dark:text-gray-400">
                      Format: XLSX, XLS (Laporan 16 Segmen)
                    </p>
                    {selectedSaktiFile && (
                      <p className="mt-2 font-mono text-[11px] text-emerald-600 dark:text-emerald-400">
                        {selectedSaktiFile.name}
                      </p>
                    )}
                  </div>
                </label>

                <button
                  data-scan="tombol sync sakti"
                  disabled={!selectedSaktiFile || isUploadingSakti}
                  onClick={handleSaktiUpload}
                  className="flex h-12 w-full items-center justify-center gap-2 rounded-xl bg-emerald-600 font-bold text-white shadow-lg shadow-emerald-500/20 transition-colors hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-50"
                >
                  {isUploadingSakti ? (
                    <LoadingSkeleton variant="button" />
                  ) : (
                    <>
                      <Terminal className="h-4 w-4" />
                      Sync Data Realisasi SAKTI
                    </>
                  )}
                </button>

                {saktiSuccess && saktiUploadLogs && (
                  <motion.div
                    initial={{ opacity: 0, scale: 0.98 }}
                    animate={{ opacity: 1, scale: 1 }}
                    className="rounded-[16px] border border-emerald-400/30 bg-emerald-500/5 p-4"
                  >
                    <code className="block max-h-40 overflow-y-auto whitespace-pre-wrap font-mono text-[10px] text-gray-600 dark:text-gray-400">
                      {saktiUploadLogs}
                    </code>
                  </motion.div>
                )}

                {saktiUploadError && (
                  <div className="rounded-[16px] border border-red-400/30 bg-red-500/5 p-4 text-[11px] text-red-600">
                    {saktiUploadError}
                  </div>
                )}
              </div>

              <div className="rounded-[20px] border border-white/50 bg-gradient-to-br from-emerald-50/50 to-white/30 p-6 dark:border-white/10 dark:from-white/[0.03] dark:to-transparent">
                <h4 className="mb-4 flex items-center gap-2 text-sm font-bold text-gray-900 dark:text-white">
                  <Info className="h-4 w-4 text-emerald-500" />
                  Panduan Import SAKTI
                </h4>
                <ul className="space-y-3 text-xs text-gray-600 dark:text-gray-400">
                  <li className="flex gap-2">
                    <span className="font-bold text-emerald-500">1.</span>
                    Gunakan laporan "FA Detail (16 Segmen)" cumulative dari SAKTI.
                  </li>
                  <li className="flex gap-2">
                    <span className="font-bold text-emerald-500">2.</span>
                    Data sebelumnya untuk tahun yang sama akan <strong>digantikan</strong> secara otomatis.
                  </li>
                  <li className="flex gap-2">
                    <span className="font-bold text-emerald-500">3.</span>
                    Pencocokan dilakukan pada level item menggunakan 8-karakter pertama sebagai pemisah.
                  </li>
                  <li className="flex gap-2">
                    <span className="font-bold text-emerald-500">4.</span>
                    Hasil sinkronisasi dapat dilihat di dashboard "Rekonsiliasi SAKTI".
                  </li>
                </ul>
              </div>
            </div>
          )}
        </motion.div>
      </div>
    </div>
  );
}
