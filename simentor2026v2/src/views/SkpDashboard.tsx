import React, { useMemo } from 'react';
import { motion } from 'motion/react';
import { formatDistanceToNow, parseISO } from 'date-fns';
import { id } from 'date-fns/locale';
import {
  CheckCircle,
  Clock,
  AlertTriangle,
  TrendingUp,
  TrendingDown,
  UserCheck,
  BarChart3,
  Award,
  Activity,
  Users,
  FileText,
  Target
} from 'lucide-react';
import { PageHeader } from '../components/PageHeader';
import { useApiQuery } from '../hooks/useApi';
import { skpService } from '../lib/api-services';

const statusStyles = {
  Live: 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-400 border border-emerald-200/50 dark:border-emerald-500/20',
  Closed: 'bg-red-50 text-red-700 dark:bg-red-500/15 dark:text-red-400 border border-red-200/50 dark:border-red-500/20',
};

export function SkpDashboard() {
  const { data: dashboardResponse } = useApiQuery(['skp-dashboard'], () => skpService.dashboard(), {
    staleTime: 60 * 60 * 1000,
    refetchInterval: 60 * 60 * 1000,
  });
  const dashboard = dashboardResponse?.data;

  const totalPegawai = dashboard?.total_pegawai ?? 0;
  const bulananByRole = (dashboard?.skp_bulanan_by_role ?? {}) as Record<string, number>;
  const tetapByRoleData = (dashboard?.skp_tetap_by_role?.data ?? {}) as Record<string, number>;
  const tahunTetap = dashboard?.skp_tetap_by_role?.tahun ?? "";
  const nilaiByRoleData = (dashboard?.skp_nilai_by_role?.data ?? {}) as Record<string, number>;
  const tahunNilai = dashboard?.skp_nilai_by_role?.tahun ?? "";
  const bulananByMonth = (dashboard?.skp_bulan_by_month?.data ?? {}) as Record<string, number>;
  const tahunBulan = dashboard?.skp_bulan_by_month?.tahun ?? "";
  const skpPenilaian = (dashboard?.skp_penilaian?.data ?? {}) as Record<string, number>;
  const tahunPenilaianTotal = dashboard?.skp_penilaian?.tahun ?? "";
  const skpEvaluasi = (dashboard?.skp_evaluasi?.data ?? {}) as Record<string, number>;
  const tahunEvaluasi = dashboard?.skp_evaluasi?.tahun ?? "";
  const skpPenetapanTimeline = (dashboard?.skp_penetapan?.data ?? {}) as Record<string, number>;
  const tahunPenetapanTimeline = dashboard?.skp_penetapan?.tahun ?? "";

  const totalSkpBulanan = Object.values(bulananByRole).reduce((sum: number, count: number) => sum + count, 0);
  const totalSkpTetap = Object.values(tetapByRoleData).reduce((sum: number, count: number) => sum + count, 0);
  const totalSkpNilai = Object.values(nilaiByRoleData).reduce((sum: number, count: number) => sum + count, 0);

  const progressPercent = totalPegawai > 0 ? Math.round((totalSkpBulanan / totalPegawai) * 100) : 0;
  const penetapanPercent = totalPegawai > 0 ? Math.round((totalSkpTetap / totalPegawai) * 100) : 0;
  const nilaiPercent = totalPegawai > 0 ? Math.round((totalSkpNilai / totalPegawai) * 100) : 0;

  // Segment logic for Card 3
  const countSelesai = totalSkpNilai;
  const countProses = Math.max(0, totalSkpBulanan - totalSkpNilai);
  const countBelum = Math.max(0, totalPegawai - (countSelesai + countProses));

  const wSelesai = totalPegawai > 0 ? (countSelesai / totalPegawai) * 100 : 0;
  const wProses = totalPegawai > 0 ? (countProses / totalPegawai) * 100 : 0;
  const wBelum = totalPegawai > 0 ? (countBelum / totalPegawai) * 100 : 0;

  const roleColors = ['bg-emerald-600', 'bg-emerald-500', 'bg-teal-500', 'bg-emerald-400'];

  const circumference = Math.PI * 80;
  
  const roleColorMap: Record<string, string> = {
    staf: '#3b82f6',
    katim: '#6366f1',
    madya: '#8b5cf6',
    kepala: '#d946ef'
  };
  const defaultRoleColors = ['#3b82f6', '#6366f1', '#8b5cf6', '#d946ef', '#f43f5e'];

  const penetrationSegments = useMemo(() => {
    let currentOffset = 0;
    return Object.entries(tetapByRoleData).map(([role, count], idx) => {
      const width = totalPegawai > 0 ? (count / totalPegawai) * circumference : 0;
      const segment = {
        role,
        count,
        width,
        offset: currentOffset,
        color: roleColorMap[role.toLowerCase()] || defaultRoleColors[idx % defaultRoleColors.length]
      };
      currentOffset += width;
      return segment;
    });
  }, [tetapByRoleData, totalPegawai, circumference]);



  return (
    <div className="space-y-0">
      <PageHeader
        title="Dashboard Kinerja"
        description="Monitoring Sasaran Kinerja Pegawai tahun berjalan."
        actions={
          <>
            <button 
              data-scan="tombol daftar pegawai"
              className="flex items-center gap-1.5 px-3.5 py-2 rounded-full bg-white/70 dark:bg-white/5 glass border border-white/50 dark:border-white/10 text-sm font-medium hover:bg-white/90 dark:hover:bg-white/10 transition"
            >
              <Users className="h-4 w-4" />
              Daftar Pegawai
            </button>
            <button 
              data-scan="tombol laporan skp"
              className="flex items-center gap-1.5 px-3.5 py-2 rounded-full bg-white/70 dark:bg-white/5 glass border border-white/50 dark:border-white/10 text-sm font-medium hover:bg-white/90 dark:hover:bg-white/10 transition">
              <FileText className="h-4 w-4" />
              Laporan SKP
            </button>
            <button 
              data-scan="tombol input skp"
              className="flex items-center gap-1.5 px-4 py-2 rounded-full bg-amber-300 hover:bg-amber-400 text-gray-900 text-sm font-semibold shadow-lg shadow-amber-500/20 transition"
            >
              <Target className="h-4 w-4" />
              Input SKP
            </button>
          </>
        }
      />

      <div className="mt-8 grid grid-cols-1 lg:grid-cols-12 gap-5">
        {/* Progres Penilaian SKP - mirrors "Getting Started" */}
        <div className="lg:col-span-4 xl:col-span-3">
          <div 
            data-scan="kartu progres skp"
            className="h-full bg-white/65 dark:bg-white/[0.04] glass-strong rounded-[24px] border border-white/50 dark:border-white/10 p-5 shadow-[0_10px_40px_-15px_rgba(0,0,0,0.1)]"
          >
            <h3 className="font-semibold text-[17px]">SKP Bulanan {dashboard?.max_tahun_bulanan}</h3>
            <p className="text-xs text-gray-500 dark:text-gray-400 mt-1 leading-snug">Periode terakhir {dashboard?.last_skp_periode}</p>

            <div className="mt-4">
              <div className="h-1.5 w-full bg-gray-200/70 dark:bg-white/10 rounded-full overflow-hidden">
                <div className="h-full bg-emerald-500 rounded-full" style={{ width: `${progressPercent}%` }}></div>
              </div>
              <div className="text-right text-[11px] text-gray-500 mt-1">{progressPercent}%</div>
            </div>

            <ul className="mt-4 space-y-3.5">
              <li className="flex items-center gap-3">
                <div className="w-5 h-5 rounded-full bg-emerald-500 flex items-center justify-center flex-shrink-0">
                  <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="white" strokeWidth="3"><polyline points="20 6 9 17 4 12" /></svg>
                </div>
                <span className="text-[13.5px] text-gray-700 dark:text-gray-300">
                  {totalSkpBulanan}/{totalPegawai} pegawai sudah mengunggah
                </span>
              </li>
              {Object.entries(bulananByRole).map(([role, count]) => (
                <li key={role} className="flex items-center gap-3">
                  <div className="w-5 h-5 rounded-full bg-emerald-500 flex items-center justify-center flex-shrink-0">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="white" strokeWidth="3"><polyline points="20 6 9 17 4 12" /></svg>
                  </div>
                  <span className="text-[13.5px] text-gray-700 dark:text-gray-300 capitalize">
                    {role}: {count} pegawai
                  </span>
                </li>
              ))}
            </ul>
          </div>
        </div>

        {/* Spacer */}
        <div className="hidden xl:block xl:col-span-6"></div>

        {/* Capaian Kinerja Gauge - mirrors "Compliance Pulse" */}
        <div className="lg:col-span-4 xl:col-span-3 lg:col-start-9 xl:col-start-10">
          <div 
            data-scan="kartu penetapan skp"
            className="h-full bg-white/65 dark:bg-white/[0.04] glass-strong rounded-[24px] border border-white/50 dark:border-white/10 p-5 shadow-[0_10px_40px_-15px_rgba(0,0,0,0.1)]"
          >
            <div className="flex items-start justify-between">
              <h3 className="font-semibold text-[17px]">SKP Penetapan {tahunTetap}</h3>
              <span className="text-[12px] font-medium text-gray-500 dark:text-gray-400 bg-gray-100/80 dark:bg-white/10 px-2 py-1 rounded-md">
                {totalPegawai} orang
              </span>
            </div>

            <div className="mt-5 flex justify-center">
              <div className="relative w-[160px] h-[90px]">
                <svg viewBox="0 0 200 110" className="w-full h-full">
                  {/* Background Track */}
                  <path d="M20 100 A80 80 0 0 1 180 100" fill="none" stroke="currentColor" className="text-gray-200 dark:text-white/10" strokeWidth="18" strokeLinecap="round" />
                  
                  {/* Colored Segments */}
                  {penetrationSegments.map((segment, idx) => (
                    <motion.path
                      key={segment.role}
                      d="M20 100 A80 80 0 0 1 180 100"
                      fill="none"
                      stroke={segment.color}
                      strokeWidth="18"
                      strokeLinecap={idx === 0 || idx === penetrationSegments.length - 1 ? 'round' : 'butt'}
                      initial={{ strokeDasharray: `0 ${circumference}`, strokeDashoffset: 0 }}
                      animate={{ 
                        strokeDasharray: `${segment.width} ${circumference}`,
                        strokeDashoffset: -segment.offset 
                      }}
                      transition={{ duration: 1.5, delay: idx * 0.1, ease: "easeOut" }}
                    />
                  ))}
                </svg>
                <div className="absolute inset-0 flex flex-col items-center justify-end pb-1">
                  <span className="text-[11px] text-gray-500 dark:text-gray-400">Persen</span>
                  <span className="text-[28px] font-semibold leading-none tracking-tight">{penetapanPercent}%</span>
                </div>
              </div>
            </div>

            <div className="mt-5 space-y-2.5">
              {penetrationSegments.map((item, i) => (
                <div key={i} className="flex items-center justify-between text-[12.5px] py-1.5 px-3 rounded-lg bg-gray-50/70 dark:bg-white/5">
                  <div className="flex items-center gap-2">
                    <span className="w-2 h-2 rounded-full" style={{ backgroundColor: item.color }}></span>
                    <span className="text-gray-600 dark:text-gray-400 capitalize">{item.role}</span>
                  </div>
                  <span className="text-[11.5px] font-medium text-gray-700 dark:text-gray-300">
                    {item.count}
                  </span>
                </div>
              ))}
            </div>
          </div>
        </div>
      </div>

      {/* Metrics Row - mirrors the 4 metric cards */}
      <div className="mt-5 grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5">
        {/* Pegawai Aktif - mirrors "Token" */}
        <div 
          data-scan="kartu metrik bulanan"
          className="bg-white/65 dark:bg-white/[0.04] glass-strong rounded-[22px] border border-white/50 dark:border-white/10 p-4.5 shadow-[0_10px_40px_-15px_rgba(0,0,0,0.08)]"
        >
          <div className="flex items-start justify-between">
            <div>
              <p className="text-[13px] text-gray-500 dark:text-gray-400">SKP Bulanan {tahunBulan}</p>
              <div className="flex items-baseline gap-2 mt-1">
                <span className="text-[26px] font-semibold leading-none">{totalSkpBulanan}</span>
                <span className="flex items-center gap-0.5 text-[11px] font-medium text-emerald-600 dark:text-emerald-400">
                   Total Unggah
                </span>
              </div>
            </div>
          </div>
          <p className="text-[11px] text-gray-500 dark:text-gray-400 mt-1">Trend aktifitas bulanan</p>
          <div className="mt-3 h-[44px] flex items-end gap-[4px]">
            {Object.entries(bulananByMonth).sort().map(([month, count]) => {
              const h = totalPegawai > 0 ? (count / totalPegawai) * 100 : 0;
              return (
                <div
                  key={month}
                  className="flex-1 rounded-[2px] bg-emerald-500/80 hover:bg-emerald-500 transition-all duration-300 relative group"
                  style={{ height: `${Math.max(15, h)}%` }}
                >
                  <div className="absolute -top-6 left-1/2 -translate-x-1/2 bg-gray-900 text-white text-[9px] px-1.5 py-0.5 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap z-20">
                    Bulan {month}: {count}
                  </div>
                </div>
              );
            })}
          </div>
        </div>

        <div 
          data-scan="kartu metrik penilaian"
          className="bg-white/65 dark:bg-white/[0.04] glass-strong rounded-[22px] border border-white/50 dark:border-white/10 p-4.5 shadow-[0_10px_40px_-15px_rgba(0,0,0,0.08)]"
        >
          <div className="flex items-start justify-between">
            <div>
              <p className="text-[13px] text-gray-500 dark:text-gray-400">SKP Penilaian {tahunPenilaianTotal}</p>
              <div className="flex items-baseline gap-2 mt-1">
                <span className="text-[26px] font-semibold leading-none">
                  {Object.values(skpPenilaian).sort().pop() || 0}
                </span>
                <span className="flex items-center gap-0.5 text-[11px] font-medium text-emerald-600 dark:text-emerald-400">
                  <Activity className="h-3 w-3" /> Penilaian Terbaru
                </span>
              </div>
            </div>
          </div>
          <p className="text-[11px] text-gray-500 dark:text-gray-400 mt-1">Status periode penilaian terakhir</p>
          <div className="mt-3 h-[44px] relative">
            <svg viewBox="0 0 300 44" className="w-full h-full">
              <defs>
                <linearGradient id="skpGrad" x1="0" x2="0" y1="0" y2="1">
                  <stop offset="0%" stopColor="#10b981" stopOpacity="0.3" />
                  <stop offset="100%" stopColor="#10b981" stopOpacity="0" />
                </linearGradient>
              </defs>
              {(() => {
                const points = Object.entries(skpPenilaian).sort();
                if (points.length < 2) return null;
                
                const step = 300 / (points.length - 1);
                const maxVal = Math.max(totalPegawai, 1);
                
                const coords = points.map(([_, val], i) => ({
                  x: i * step,
                  y: 44 - ((val / maxVal) * 44)
                }));
                
                const dLine = `M ${coords.map(p => `${p.x} ${p.y}`).join(' L ')}`;
                const dArea = `${dLine} L 300 44 L 0 44 Z`;
                
                return (
                  <>
                    <path d={dArea} fill="url(#skpGrad)" className="transition-all duration-700" />
                    <path d={dLine} fill="none" stroke="#10b981" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" className="transition-all duration-700" />
                  </>
                );
              })()}
            </svg>
          </div>
        </div>

        <div 
          data-scan="kartu metrik evaluasi"
          className="bg-white/65 dark:bg-white/[0.04] glass-strong rounded-[22px] border border-white/50 dark:border-white/10 p-4.5 shadow-[0_10px_40px_-15px_rgba(0,0,0,0.08)]"
        >
          <div className="flex items-start justify-between">
            <div>
              <p className="text-[13px] text-gray-500 dark:text-gray-400">SKP Evaluasi {tahunEvaluasi}</p>
              <div className="flex items-baseline gap-2 mt-1">
                <span className="text-[26px] font-semibold leading-none">
                  {Object.values(skpEvaluasi).reduce((sum: number, count: number) => sum + count, 0)}
                </span>
                <span className="flex items-center gap-0.5 text-[11px] font-medium text-emerald-600 dark:text-emerald-400">
                  <UserCheck className="h-3 w-3" /> Pegawai
                </span>
              </div>
            </div>
          </div>
          <p className="text-[11px] text-gray-500 dark:text-gray-400 mt-1">Progres evaluasi tahunan</p>
          <div className="mt-3 h-[44px] flex items-end gap-[6px]">
            {Object.entries(skpEvaluasi).sort().map(([date, count]) => {
              const maxVal = Math.max(totalPegawai, 1);
              const h = (count / maxVal) * 100;
              return (
                <div
                  key={date}
                  className="flex-1 bg-blue-500/60 hover:bg-blue-500 rounded-[1px] transition-all duration-300 relative group"
                  style={{ height: `${Math.max(15, h)}%` }}
                >
                  <div className="absolute -top-6 left-1/2 -translate-x-1/2 bg-gray-900 text-white text-[9px] px-1.5 py-0.5 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap z-20">
                    {date}: {count}
                  </div>
                  <div className="absolute -bottom-5 left-1/2 -translate-x-1/2 text-[8px] text-gray-400 font-mono whitespace-nowrap">
                    {date}
                  </div>
                </div>
              );
            })}
          </div>
        </div>

        <div 
          data-scan="kartu metrik penetapan"
          className="bg-white/65 dark:bg-white/[0.04] glass-strong rounded-[22px] border border-white/50 dark:border-white/10 p-4.5 shadow-[0_10px_40px_-15px_rgba(0,0,0,0.08)]"
        >
          <div className="flex items-start justify-between">
            <div>
              <p className="text-[13px] text-gray-500 dark:text-gray-400">SKP Penetapan {tahunPenetapanTimeline}</p>
              <div className="flex items-baseline gap-2 mt-1">
                <span className="text-[26px] font-semibold leading-none">
                  {Object.values(skpPenetapanTimeline).sort().pop() || 0}
                </span>
                <span className="flex items-center gap-0.5 text-[11px] font-medium text-amber-600 dark:text-amber-400">
                  <Clock className="h-3 w-3" /> Terbaru
                </span>
              </div>
            </div>
          </div>
          <p className="text-[11px] text-gray-500 dark:text-gray-400 mt-1">Riwayat penetapan terakhir</p>
          <div className="mt-3 h-[44px] flex items-end gap-[3px]">
            {Object.entries(skpPenetapanTimeline).sort().map(([date, count]) => {
              const maxVal = Math.max(...Object.values(skpPenetapanTimeline), 1);
              const h = (count / maxVal) * 100;
              return (
                <div
                  key={date}
                  className="flex-1 bg-amber-500/60 hover:bg-amber-500 rounded-[1px] transition-all duration-300 relative group"
                  style={{ height: `${Math.max(15, h)}%` }}
                >
                  <div className="absolute -top-6 left-1/2 -translate-x-1/2 bg-gray-900 text-white text-[9px] px-1.5 py-0.5 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap z-20">
                    {date}: {count}
                  </div>
                </div>
              );
            })}
          </div>
        </div>
      </div>

      {/* Bottom Row - mirrors "30-day Cost Forecast" + "Live Runs Stream" */}
      <div className="mt-5 grid grid-cols-1 xl:grid-cols-12 gap-5">
        <div className="xl:col-span-5">
          <div 
            data-scan="kartu ringkasan penilaian"
            className="h-full bg-white/65 dark:bg-white/[0.04] glass-strong rounded-[24px] border border-white/50 dark:border-white/10 p-5 shadow-[0_10px_40px_-15px_rgba(0,0,0,0.08)]"
          >
            <div className="flex items-start justify-between">
              <div>
                <p className="text-[13px] text-gray-500 dark:text-gray-400">Progres SKP Penilaian {tahunNilai}</p>
                <div className="text-[28px] font-semibold leading-none mt-1">{nilaiPercent}%</div>
              </div>
            </div>
            <div className="flex items-center gap-1.5 mt-2 text-[11px] text-emerald-600 dark:text-emerald-400">
              <TrendingUp className="h-3 w-3" />
              <span>{countSelesai} pegawai sudah dinilai dari {totalPegawai} total</span>
            </div>

            <div className="mt-6">
              <div className="flex justify-between text-[11px] text-gray-500 mb-1.5">
                <span>{Math.round(wBelum)}%</span>
                <span>{Math.round(wProses)}%</span>
                <span className="mr-[2%]">{Math.round(wSelesai)}%</span>
              </div>
              <div className="h-[14px] w-full flex items-center gap-[2px]">
                {/* Belum & Proses (Shades of Blue since hidden from legend) */}
                <div 
                  className="h-full bg-blue-100 dark:bg-blue-900/30 rounded-l-[3px] transition-all duration-500" 
                  style={{ width: `${wBelum}%` }}
                />
                <div 
                  className="h-full bg-blue-300 dark:bg-blue-500/40 transition-all duration-500" 
                  style={{ width: `${wProses}%` }}
                />
                
                {/* Finished Roles (Distinct shades) */}
                {Object.entries(nilaiByRoleData).map(([role, count], idx) => {
                  const roleWidth = totalPegawai > 0 ? (count / totalPegawai) * 100 : 0;
                  return (
                    <div 
                      key={role}
                      className={`h-full ${roleColors[idx % roleColors.length]} transition-all duration-500 ${idx === Object.entries(nilaiByRoleData).length - 1 ? 'rounded-r-[3px]' : ''}`}
                      style={{ width: `${roleWidth}%` }}
                    />
                  );
                })}
              </div>

              <div className="flex flex-wrap items-center gap-x-4 gap-y-2 mt-3 text-[11px]">
                {Object.entries(nilaiByRoleData).map(([role, count], idx) => (
                  <div key={role} className="flex items-center gap-1.5">
                    <span className={`w-2 h-2 rounded-full ${roleColors[idx % roleColors.length]}`}></span>
                    <span className="text-gray-500 capitalize">{role} ({count})</span>
                  </div>
                ))}
                <div className="flex items-center gap-1.5">
                  <span className="w-2 h-2 rounded-full bg-blue-100 dark:bg-blue-900/40"></span>
                  <span className="text-gray-500">Belum ({totalPegawai - totalSkpNilai})</span>
                </div>
              </div>
            </div>

            <div className="mt-5 p-3 rounded-xl bg-amber-50/70 dark:bg-amber-500/10 border border-amber-200/50 dark:border-amber-500/20 flex items-center justify-between">
              <div className="flex items-center gap-2 text-[12px] text-amber-800 dark:text-amber-200">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" /><line x1="12" y1="9" x2="12" y2="13" /><line x1="12" y1="17" x2="12.01" y2="17" /></svg>
                <span>Akurasi:</span>
              </div>
              <div className="text-[12px] font-medium">
                <span className="text-gray-900 dark:text-white">87%</span>
                <span className="text-gray-500 dark:text-gray-400 ml-1">vs triwulan lalu</span>
              </div>
            </div>
          </div>
        </div>

        {/* Aktivitas Terkini - mirrors "Live Runs Stream" */}
        <div className="xl:col-span-7">
          <div 
            data-scan="kartu aktivitas terkini"
            className="h-full bg-white/65 dark:bg-white/[0.04] glass-strong rounded-[24px] border border-white/50 dark:border-white/10 p-5 shadow-[0_10px_40px_-15px_rgba(0,0,0,0.08)]"
          >
            <div className="flex items-center justify-between">
              <div className="flex items-center gap-2">
                <span className="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                <h3 className="font-semibold text-[15px]">Aktivitas Terkini</h3>
              </div>
              <span className="text-[11px] px-2 py-1 rounded-md bg-gray-100/80 dark:bg-white/10 text-gray-600 dark:text-gray-300">New Activities</span>
            </div>

            <div className="mt-4 overflow-x-auto">
              <table className="w-full text-[13px]">
                <thead>
                  <tr className="text-left text-[11.5px] text-gray-500 dark:text-gray-400 border-b border-gray-200/60 dark:border-white/10">
                    <th className="pb-2.5 font-medium">Nama SKP</th>
                    <th className="pb-2.5 font-medium">Pegawai</th>
                    <th className="pb-2.5 font-medium">Waktu</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-gray-100/70 dark:divide-white/5">
                  {(dashboard?.activities || []).map((activity) => (
                    <tr key={activity.id} className="group hover:bg-gray-50/30 dark:hover:bg-white/[0.02] transition-colors">
                      <td className="py-3 text-gray-600 dark:text-gray-400 truncate max-w-[240px] xl:max-w-xs" title={activity.nama}>
                        {activity.nama}
                      </td>
                      <td className="py-3 font-medium whitespace-nowrap px-4 text-gray-700 dark:text-gray-300">
                        {activity.user_name}
                      </td>
                      <td className="py-3 text-gray-500 dark:text-gray-500 whitespace-nowrap text-sm">
                        {formatDistanceToNow(parseISO(activity.created_at), { addSuffix: true, locale: id })}
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <div className="h-8"></div>
    </div>
  );
}
