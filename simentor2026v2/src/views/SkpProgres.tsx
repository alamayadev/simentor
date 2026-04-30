import React, { useEffect, useMemo, useRef, useState } from 'react';
import { Upload } from 'lucide-react';
import { useQueryClient } from '@tanstack/react-query';
import { PageHeader } from '../components/PageHeader';
import { LoadingSkeleton } from '../components/LoadingSkeleton';
import { ApiErrorBoundary } from '../components/ApiErrorBoundary';
import { SkpFileModal, type SkpFileFormValues } from '../components/SkpFileModal';
import { useApiMutation, useApiQuery } from '../hooks/useApi';
import { useAuth } from '../hooks/useAuth';
import { ApiError, apiGet, apiPostForm } from '../lib/api';

interface SkpStatPeriod {
  not_uploaded_count?: number;
  uploaded_count?: number;
  not_uploaded_users?: string;
  period?: string | number;
}

interface SkpStatsData {
  tahun_options?: string[];
  bulan_options?: string[];
  selected_tahun?: string;
  selected_tahun2?: string;
  selected_bulan?: string;
  skp_monthly_stats?: SkpStatPeriod;
  skp_annual_setting_stats?: SkpStatPeriod;
  skp_annual_determine_stats?: SkpStatPeriod;
  skp_annual_evaluation_stats?: SkpStatPeriod;
  total_employees?: number;
}

function parseNames(raw?: string): string[] {
  return raw ? raw.split(',').map((item) => item.trim()).filter(Boolean) : [];
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

function MissingUsers({
  names,
  currentUserName,
}: {
  names: string[];
  currentUserName: string;
}) {
  const [expanded, setExpanded] = useState(false);
  const displayed = expanded ? names : names.slice(0, 6);

  if (names.length === 0) {
    return (
      <div className="text-xs text-gray-500 dark:text-gray-400">
        Semua sudah upload.
      </div>
    );
  }

  return (
    <div className="text-xs leading-relaxed text-gray-500 dark:text-gray-400">
      {displayed.map((name, index) => {
        const isCurrentUser = currentUserName !== '' && name.toLowerCase() === currentUserName;
        return (
          <React.Fragment key={`${name}-${index}`}>
            <span className={isCurrentUser ? 'font-semibold text-red-600 dark:text-red-300' : undefined}>
              {name}
            </span>
            {index < displayed.length - 1 ? ', ' : ''}
          </React.Fragment>
        );
      })}
      {names.length > 6 ? (
        <button
          type="button"
          onClick={() => setExpanded((prev) => !prev)}
          className="ml-1 font-semibold text-orange-500 hover:text-orange-600"
        >
          {expanded ? 'Sembunyikan' : `+${names.length - 6} lainnya`}
        </button>
      ) : null}
    </div>
  );
}

function SkpCard({
  title,
  count,
  total,
  names,
  currentUserName,
}: {
  title: string;
  count: number;
  total: number;
  names: string[];
  currentUserName: string;
}) {
  return (
    <div 
      data-scan="kartu progres item"
      className="flex flex-col gap-4 py-6 bg-white/65 dark:bg-white/[0.04] glass-strong rounded-[24px] border border-white/50 dark:border-white/10 shadow-[0_10px_40px_-15px_rgba(0,0,0,0.08)]"
    >
      <div className="px-6">
        <div className="text-sm font-semibold text-gray-500 dark:text-gray-400">
          {title}
        </div>
      </div>
      <div className="px-6 space-y-2 text-sm text-gray-600 dark:text-gray-300">
        <div className="text-2xl font-semibold text-gray-900 dark:text-white">
          {count} belum upload
        </div>
        <div>Total pegawai: {total}</div>
        <MissingUsers names={names} currentUserName={currentUserName} />
      </div>
    </div>
  );
}

export function SkpProgres() {
  const queryClient = useQueryClient();
  const { user } = useAuth();
  const currentUserName = user?.name?.trim().toLowerCase() ?? '';
  const [tahun, setTahun] = useState('');
  const [bulan, setBulan] = useState('');
  const [activeTab, setActiveTab] = useState(0);
  const [isUploadOpen, setIsUploadOpen] = useState(false);
  const [uploadError, setUploadError] = useState<string | null>(null);
  const hasAppliedDefaults = useRef(false);

  const { data: response, isLoading, error, refetch } = useApiQuery(
    ['skp-progress', tahun, bulan],
    () => {
      const params: Record<string, string> = {};
      if (tahun) {
        params.tahun = tahun;
        params.tahun2 = tahun;
      }
      if (bulan) {
        params.bulan = bulan;
      }
      return apiGet<SkpStatsData>('/kantor/skp/stats', params);
    }
  );

  const data = response?.data ?? null;

  useEffect(() => {
    if (!data || hasAppliedDefaults.current) return;
    setTahun(data.selected_tahun ?? '');
    setBulan(data.selected_bulan ?? '');
    hasAppliedDefaults.current = true;
  }, [data]);

  const uploadMutation = useApiMutation(
    (values: SkpFileFormValues) => apiPostForm('/kantor/skp', buildFormData(values)),
    {
      invalidateKeys: [['skp-progress'], ['skp-dashboard'], ['skp-list']],
      onSuccess: () => {
        setUploadError(null);
        setIsUploadOpen(false);
        void queryClient.invalidateQueries({ queryKey: ['skp-progress'] });
      },
      onError: (uploadErr: ApiError) => {
        setUploadError(uploadErr.message ?? 'Gagal mengunggah SKP.');
      },
    }
  );

  const monthOptions = data?.bulan_options ?? Array.from({ length: 12 }, (_, i) => String(i + 1).padStart(2, '0'));
  const yearOptions = data?.tahun_options ?? [];
  const totalEmployees = data?.total_employees ?? 0;

  const monthlyNames = parseNames(data?.skp_monthly_stats?.not_uploaded_users);
  const annualSettingNames = parseNames(data?.skp_annual_setting_stats?.not_uploaded_users);
  const annualDetermineNames = parseNames(data?.skp_annual_determine_stats?.not_uploaded_users);
  const annualEvaluationNames = parseNames(data?.skp_annual_evaluation_stats?.not_uploaded_users);

  const tabs = ['Bulanan & Penetapan', 'Penilaian & Evaluasi'];
  const selectClass =
    'h-10 rounded-xl border border-white/50 dark:border-white/10 bg-white/70 dark:bg-white/5 px-3 text-sm text-gray-900 dark:text-gray-100 outline-none focus:border-orange-400 dark:focus:border-orange-500 transition-colors appearance-none cursor-pointer';

  return (
    <div className="space-y-6">
      <PageHeader
        title="Progres SKP"
        description="Ringkasan progres upload dan status SKP berdasarkan periode."
      />

      <div 
        data-scan="filter dan aksi"
        className="grid gap-4 bg-white/65 dark:bg-white/[0.04] glass-strong rounded-[24px] border border-white/50 dark:border-white/10 p-4 md:grid-cols-3"
      >
        <div className="grid gap-2">
          <label className="text-[11px] uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400 font-medium">
            Tahun Bulanan
          </label>
          <select
            value={tahun}
            onChange={(event) => setTahun(event.target.value)}
            className={selectClass}
          >
            <option value="">Semua</option>
            {yearOptions.map((year) => (
              <option key={year} value={year}>
                {year}
              </option>
            ))}
          </select>
        </div>
        <div className="grid gap-2">
          <label className="text-[11px] uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400 font-medium">
            Bulan
          </label>
          <select
            value={bulan}
            onChange={(event) => setBulan(event.target.value)}
            className={selectClass}
          >
            <option value="">Semua</option>
            {monthOptions.map((month) => (
              <option key={month} value={month}>
                {month}
              </option>
            ))}
          </select>
        </div>
        <div className="flex items-end justify-start md:justify-end">
          <button
            data-scan="tombol upload skp"
            type="button"
            onClick={() => setIsUploadOpen(true)}
            className="inline-flex items-center justify-center gap-2 h-9 px-4 py-2 rounded-full bg-amber-300 hover:bg-amber-400 text-gray-900 text-sm font-semibold shadow-lg shadow-amber-500/20 transition"
          >
            <Upload className="h-4 w-4" />
            Upload SKP
          </button>
        </div>
      </div>

      {isLoading && !data ? (
        <LoadingSkeleton variant="cards" message="Memuat progres SKP..." />
      ) : error ? (
        <ApiErrorBoundary error={error} onRetry={() => refetch()} title="Gagal Memuat Progres SKP" />
      ) : (
        <div className="space-y-3">
          <div className="text-[11px] uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400 font-medium">
            Belum Upload Laporan
          </div>

          <div 
            data-scan="tab kontrol progres"
            className="inline-flex h-10 items-center gap-1 rounded-xl bg-gray-100/80 dark:bg-white/5 p-1"
          >
            {tabs.map((tab, index) => (
              <button
                key={tab}
                type="button"
                onClick={() => setActiveTab(index)}
                className={`px-4 py-1.5 rounded-lg text-sm font-medium transition-all ${
                  activeTab === index
                    ? 'bg-white dark:bg-white/10 text-gray-900 dark:text-white shadow-sm'
                    : 'text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white'
                }`}
              >
                {tab}
              </button>
            ))}
          </div>

          {activeTab === 0 ? (
            <div 
              data-scan="daftar kartu progres"
              className="grid gap-4 md:grid-cols-2"
            >
              <SkpCard
                title={`SKP Bulanan (${data?.skp_monthly_stats?.period ?? '-'})`}
                count={data?.skp_monthly_stats?.not_uploaded_count ?? monthlyNames.length}
                total={totalEmployees}
                names={monthlyNames}
                currentUserName={currentUserName}
              />
              <SkpCard
                title={`Penetapan Tahunan (${data?.skp_annual_setting_stats?.period ?? '-'})`}
                count={annualSettingNames.length}
                total={totalEmployees}
                names={annualSettingNames}
                currentUserName={currentUserName}
              />
            </div>
          ) : (
            <div 
              data-scan="daftar kartu progres"
              className="grid gap-4 md:grid-cols-2"
            >
              <SkpCard
                title={`Penilaian SKP (${data?.skp_annual_determine_stats?.period ?? '-'})`}
                count={data?.skp_annual_determine_stats?.not_uploaded_count ?? annualDetermineNames.length}
                total={totalEmployees}
                names={annualDetermineNames}
                currentUserName={currentUserName}
              />
              <SkpCard
                title={`Evaluasi Kinerja (${data?.skp_annual_evaluation_stats?.period ?? '-'})`}
                count={annualEvaluationNames.length}
                total={totalEmployees}
                names={annualEvaluationNames}
                currentUserName={currentUserName}
              />
            </div>
          )}
        </div>
      )}

      <SkpFileModal
        open={isUploadOpen}
        onClose={() => {
          setIsUploadOpen(false);
          setUploadError(null);
        }}
        onSubmit={(values) => uploadMutation.mutate(values)}
        title="Upload SKP"
        description="Unggah dokumen SKP bulanan atau tahunan dalam format PDF."
        submitLabel={uploadMutation.isPending ? 'Mengunggah...' : 'Upload SKP'}
        yearOptions={yearOptions}
        monthOptions={monthOptions}
        initialValues={{
          jenis: 'SKP Bulanan',
          tahun: tahun || data?.selected_tahun || '',
          bulan: bulan || data?.selected_bulan || '',
        }}
        isLoading={uploadMutation.isPending}
        requireFile
        errorMessage={uploadError}
      />
    </div>
  );
}
