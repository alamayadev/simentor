import React from 'react';
import { motion } from 'motion/react';
import {
  Activity,
  ArrowRight,
  BarChart3,
  Bell,
  Calendar,
  CheckCircle2,
  ClipboardCheck,
  DatabaseZap,
  FileCheck,
  Landmark,
  LayoutDashboard,
  Monitor,
  Shield,
  Sparkles,
  Target,
  TrendingUp,
  Wallet,
} from 'lucide-react';

const heroMetrics = [
  {
    label: 'Kinerja SKP',
    value: '91%',
    note: 'Rata-rata capaian pegawai aktif',
    color: 'text-emerald-600 dark:text-emerald-400',
  },
  {
    label: 'Realisasi DIPA',
    value: 'Rp 1,98 Jt',
    note: 'Tercatat pada monitoring April 2026',
    color: 'text-sky-600 dark:text-sky-400',
  },
  {
    label: 'Kontrak Aktif',
    value: '8',
    note: 'SPK dan BAST yang masih berjalan',
    color: 'text-amber-600 dark:text-amber-400',
  },
];

const modules = [
  {
    name: 'SKP',
    path: '/skp/dashboard',
    icon: LayoutDashboard,
    accent: 'from-orange-200 via-amber-100 to-white dark:from-orange-500/20 dark:via-amber-500/10 dark:to-transparent',
    iconTone: 'text-orange-600 dark:text-orange-400',
    stat: '91%',
    desc: 'Dashboard kinerja pegawai, progres penilaian, dan rekap capaian.',
  },
  {
    name: 'Kegiatan',
    path: '/kegiatan/monitoring',
    icon: ClipboardCheck,
    accent: 'from-sky-200 via-cyan-100 to-white dark:from-sky-500/20 dark:via-cyan-500/10 dark:to-transparent',
    iconTone: 'text-sky-600 dark:text-sky-400',
    stat: '12 aktif',
    desc: 'Pantau jadwal, monitoring lapangan, evaluasi, dan kalender kegiatan.',
  },
  {
    name: 'RKK DIPA',
    path: '/umum/rkk-dipa/monitoring',
    icon: Wallet,
    accent: 'from-emerald-200 via-lime-100 to-white dark:from-emerald-500/20 dark:via-lime-500/10 dark:to-transparent',
    iconTone: 'text-emerald-600 dark:text-emerald-400',
    stat: '6 menu',
    desc: 'Monitoring pagu, perencanaan, pencairan, integritas, dan revisi import.',
  },
  {
    name: 'SPK & BAST',
    path: '/kontraktual/monitoring',
    icon: FileCheck,
    accent: 'from-violet-200 via-fuchsia-100 to-white dark:from-violet-500/20 dark:via-fuchsia-500/10 dark:to-transparent',
    iconTone: 'text-violet-600 dark:text-violet-400',
    stat: '8 kontrak',
    desc: 'Status dokumen kontraktual, vendor, dan penyelesaian administrasi.',
  },
  {
    name: 'IPDS',
    path: '/ipds/tiket',
    icon: Monitor,
    accent: 'from-cyan-200 via-teal-100 to-white dark:from-cyan-500/20 dark:via-teal-500/10 dark:to-transparent',
    iconTone: 'text-cyan-600 dark:text-cyan-400',
    stat: '3 tiket',
    desc: 'Layanan dukungan TI, tiket bantuan, dan pengelolaan aset perangkat.',
  },
  {
    name: 'Admin',
    path: '/admin/pengguna',
    icon: Shield,
    accent: 'from-rose-200 via-red-100 to-white dark:from-rose-500/20 dark:via-red-500/10 dark:to-transparent',
    iconTone: 'text-rose-600 dark:text-rose-400',
    stat: '12 pengguna',
    desc: 'Pengguna, peran, izin akses, dan pengelolaan metadata aplikasi.',
  },
];

const primaryShortcuts = [
  { name: 'Dashboard Kinerja', path: '/skp/dashboard', icon: BarChart3, badge: 'SKP' },
  { name: 'Monitoring Kegiatan', path: '/kegiatan/monitoring', icon: Activity, badge: 'Lapangan' },
  { name: 'Monitoring DIPA', path: '/umum/rkk-dipa/monitoring', icon: Wallet, badge: 'Anggaran' },
  { name: 'Pencairan DIPA', path: '/umum/rkk-dipa/pencairan', icon: Landmark, badge: 'SP2D' },
  { name: 'Monitoring Kontrak', path: '/kontraktual/monitoring', icon: Bell, badge: 'Kontrak' },
  { name: 'Tiket Bantuan IT', path: '/ipds/tiket', icon: Monitor, badge: 'IPDS' },
];

const liveFeed = [
  { module: 'SKP', text: 'Penilaian Budi Santoso selesai diverifikasi', time: '2 menit lalu', tone: 'text-orange-600 dark:text-orange-400' },
  { module: 'DIPA', text: 'Revisi import April 2026 berhasil di-ingest', time: '18 menit lalu', tone: 'text-emerald-600 dark:text-emerald-400' },
  { module: 'Kegiatan', text: 'Susenas Maret memperbarui progres lapangan', time: '34 menit lalu', tone: 'text-sky-600 dark:text-sky-400' },
  { module: 'IPDS', text: 'Tiket printer ruang layanan masuk antrian', time: '1 jam lalu', tone: 'text-cyan-600 dark:text-cyan-400' },
];

const monthBars = [
  { label: 'Jan', value: 64 },
  { label: 'Feb', value: 71 },
  { label: 'Mar', value: 69 },
  { label: 'Apr', value: 84 },
  { label: 'Mei', value: 76 },
  { label: 'Jun', value: 89 },
];

export function LandingPage() {
  const maxBar = Math.max(...monthBars.map((item) => item.value));
  const linePath = monthBars
    .map((item, index) => {
      const x = (index / (monthBars.length - 1)) * 100;
      const y = 100 - (item.value / maxBar) * 100;
      return `${index === 0 ? 'M' : 'L'} ${x} ${y}`;
    })
    .join(' ');

  return (
    <div className="space-y-6">
      <section className="relative overflow-hidden rounded-[30px] border border-white/50 bg-white/65 p-6 shadow-[0_18px_60px_-24px_rgba(0,0,0,0.16)] dark:border-white/10 dark:bg-white/[0.04] md:p-7">
        <div className="absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(251,191,36,0.18),_transparent_30%),radial-gradient(circle_at_bottom_right,_rgba(14,165,233,0.16),_transparent_28%)]" />
        <div className="absolute -left-10 top-10 h-32 w-32 rounded-full bg-orange-300/20 blur-3xl dark:bg-orange-500/10" />
        <div className="absolute right-0 top-0 h-40 w-40 rounded-full bg-sky-300/20 blur-3xl dark:bg-sky-500/10" />

        <div className="relative grid gap-6 xl:grid-cols-[1.2fr_0.8fr]">
          <div className="space-y-6">
            <div className="inline-flex items-center gap-2 rounded-full border border-white/60 bg-white/70 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.22em] text-gray-600 dark:border-white/10 dark:bg-white/[0.05] dark:text-gray-300">
              <Sparkles className="h-3.5 w-3.5 text-amber-500" />
              SImentor Control Center
            </div>

            <div className="max-w-3xl">
              <h1 className="text-[34px] font-semibold leading-[0.95] tracking-tight text-gray-900 dark:text-white md:text-[48px]">
                Satu dashboard untuk membaca ritme kantor, anggaran, dan operasional harian.
              </h1>
              <p className="mt-3 max-w-2xl text-[14px] leading-6 text-gray-600 dark:text-gray-400">
                Landing page ini merangkum modul yang paling sering dipakai: SKP, kegiatan, DIPA, kontraktual, IPDS,
                dan administrasi. Fokusnya dibuat cepat dipindai, bukan penuh kartu tanpa hirarki.
              </p>
            </div>

            <div className="flex flex-wrap gap-3">
              <a
                data-scan="tombol buka dashboard kinerja"
                href="/skp/dashboard"
                className="inline-flex items-center gap-2 rounded-full bg-amber-300 px-4 py-2 text-sm font-semibold text-gray-900 shadow-lg shadow-amber-500/20 transition hover:bg-amber-400"
              >
                Buka Dashboard Kinerja
                <ArrowRight className="h-4 w-4" />
              </a>
              <a
                data-scan="tombol lihat monitoring dipa"
                href="/umum/rkk-dipa/monitoring"
                className="inline-flex items-center gap-2 rounded-full border border-white/50 bg-white/70 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-white dark:border-white/10 dark:bg-white/5 dark:text-gray-300 dark:hover:bg-white/10"
              >
                Lihat Monitoring DIPA
              </a>
            </div>

            <div className="grid gap-3 sm:grid-cols-3">
              {heroMetrics.map((metric, index) => (
                <motion.div
                  key={metric.label}
                  initial={{ opacity: 0, y: 10 }}
                  animate={{ opacity: 1, y: 0 }}
                  transition={{ delay: index * 0.06 }}
                  className="rounded-[20px] border border-white/60 bg-white/75 p-4 backdrop-blur dark:border-white/10 dark:bg-white/[0.05]"
                >
                  <div className="text-[11px] uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">{metric.label}</div>
                  <div className={`mt-2 text-2xl font-black ${metric.color}`}>{metric.value}</div>
                  <div className="mt-1 text-[12px] leading-5 text-gray-500 dark:text-gray-400">{metric.note}</div>
                </motion.div>
              ))}
            </div>

            <motion.div
              initial={{ opacity: 0, y: 12 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ delay: 0.15 }}
              className="overflow-hidden rounded-[24px] border border-white/60 bg-white/78 p-4 backdrop-blur dark:border-white/10 dark:bg-white/[0.05]"
            >
              <div className="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                  <div className="text-[11px] uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">Trend Ringkas</div>
                  <h3 className="mt-1 text-[18px] font-semibold text-gray-900 dark:text-white">Capaian lintas operasional 6 bulan</h3>
                  <p className="mt-1 max-w-xl text-[12.5px] leading-5 text-gray-500 dark:text-gray-400">
                    Membaca pola ritme SKP, kegiatan, dan administrasi secara cepat tanpa harus masuk ke setiap modul.
                  </p>
                </div>
                <div className="inline-flex items-center gap-2 rounded-full bg-emerald-500/10 px-3 py-1 text-[11px] font-semibold text-emerald-600 dark:text-emerald-400">
                  <TrendingUp className="h-3.5 w-3.5" />
                  +11% dibanding awal semester
                </div>
              </div>

              <div className="mt-5 grid gap-5 lg:grid-cols-[1.35fr_0.65fr]">
                <div className="rounded-[20px] border border-gray-200/70 bg-white/85 p-4 dark:border-white/10 dark:bg-white/[0.04]">
                  <div className="flex items-center justify-between">
                    <div className="text-[12px] font-medium text-gray-500 dark:text-gray-400">Skor Operasional</div>
                    <div className="text-[12px] font-semibold text-gray-900 dark:text-white">89 / 100</div>
                  </div>

                  <div className="mt-4 h-[160px]">
                    <svg viewBox="0 0 100 100" preserveAspectRatio="none" className="h-full w-full overflow-visible">
                      <defs>
                        <linearGradient id="landingTrendFill" x1="0" x2="0" y1="0" y2="1">
                          <stop offset="0%" stopColor="rgba(59,130,246,0.28)" />
                          <stop offset="100%" stopColor="rgba(59,130,246,0.02)" />
                        </linearGradient>
                      </defs>

                      {[0, 25, 50, 75, 100].map((y) => (
                        <line
                          key={y}
                          x1="0"
                          y1={y}
                          x2="100"
                          y2={y}
                          stroke="rgba(148,163,184,0.18)"
                          strokeDasharray="2 3"
                        />
                      ))}

                      <path d={`${linePath} L 100 100 L 0 100 Z`} fill="url(#landingTrendFill)" />
                      <path d={linePath} fill="none" stroke="rgb(59,130,246)" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round" />

                      {monthBars.map((item, index) => {
                        const x = (index / (monthBars.length - 1)) * 100;
                        const y = 100 - (item.value / maxBar) * 100;
                        return (
                          <g key={item.label}>
                            <circle cx={x} cy={y} r="2.5" fill="rgb(59,130,246)" />
                            <circle cx={x} cy={y} r="5" fill="rgba(59,130,246,0.14)" />
                          </g>
                        );
                      })}
                    </svg>
                  </div>

                  <div className="mt-3 grid grid-cols-6 gap-2">
                    {monthBars.map((item) => (
                      <div key={item.label} className="text-center">
                        <div className="text-[10px] uppercase tracking-[0.16em] text-gray-400">{item.label}</div>
                        <div className="mt-1 text-[12px] font-semibold text-gray-700 dark:text-gray-300">{item.value}%</div>
                      </div>
                    ))}
                  </div>
                </div>

                <div className="space-y-3">
                  {[
                    { label: 'SKP', value: '91%', tone: 'text-orange-600 dark:text-orange-400' },
                    { label: 'Lapangan', value: '84%', tone: 'text-sky-600 dark:text-sky-400' },
                    { label: 'Administrasi', value: '88%', tone: 'text-emerald-600 dark:text-emerald-400' },
                  ].map((item) => (
                    <div key={item.label} className="rounded-[18px] border border-gray-200/70 bg-white/85 p-4 dark:border-white/10 dark:bg-white/[0.04]">
                      <div className="text-[11px] uppercase tracking-[0.16em] text-gray-400">{item.label}</div>
                      <div className={`mt-1 text-xl font-black ${item.tone}`}>{item.value}</div>
                    </div>
                  ))}
                </div>
              </div>
            </motion.div>
          </div>

          <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-1">
            <motion.div
              initial={{ opacity: 0, x: 16 }}
              animate={{ opacity: 1, x: 0 }}
              className="rounded-[26px] border border-white/60 bg-gray-950 p-5 text-white shadow-[0_20px_40px_-22px_rgba(0,0,0,0.45)]"
            >
              <div className="flex items-center justify-between">
                <div>
                  <div className="text-[11px] uppercase tracking-[0.22em] text-white/50">Focus Today</div>
                  <div className="mt-1 text-xl font-semibold">Ritme Operasional</div>
                </div>
                <div className="flex h-10 w-10 items-center justify-center rounded-2xl bg-white/10">
                  <Target className="h-5 w-5 text-amber-300" />
                </div>
              </div>

              <div className="mt-5 grid grid-cols-3 gap-2">
                {monthBars.map((item) => (
                  <div key={item.label} className="flex flex-col items-center gap-2">
                    <div className="flex h-24 w-full items-end rounded-[14px] bg-white/5 px-2 pb-2">
                      <div
                        className="w-full rounded-[10px] bg-gradient-to-t from-amber-400 via-orange-300 to-yellow-200"
                        style={{ height: `${(item.value / maxBar) * 100}%` }}
                      />
                    </div>
                    <div className="text-[10px] uppercase tracking-[0.16em] text-white/55">{item.label}</div>
                  </div>
                ))}
              </div>

              <div className="mt-4 flex items-center justify-between rounded-[18px] border border-white/10 bg-white/5 px-3 py-3">
                <div>
                  <div className="text-[11px] uppercase tracking-[0.16em] text-white/45">Capaian Semester</div>
                  <div className="mt-1 text-lg font-semibold">84%</div>
                </div>
                <div className="inline-flex items-center gap-1 rounded-full bg-emerald-400/10 px-2.5 py-1 text-[11px] font-semibold text-emerald-300">
                  <TrendingUp className="h-3 w-3" />
                  Stabil Naik
                </div>
              </div>
            </motion.div>

            <motion.div
              initial={{ opacity: 0, x: 16 }}
              animate={{ opacity: 1, x: 0 }}
              transition={{ delay: 0.05 }}
              className="rounded-[24px] border border-white/50 bg-white/75 p-5 dark:border-white/10 dark:bg-white/[0.05]"
            >
              <div className="text-[11px] uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400">Quick Snapshot</div>
              <div className="mt-3 space-y-3">
                {[
                  { label: 'Kegiatan Lapangan', value: '12 aktif', icon: ClipboardCheck, tone: 'text-sky-500' },
                  { label: 'Pencairan DIPA', value: '4 proses', icon: Landmark, tone: 'text-emerald-500' },
                  { label: 'Tiket IT', value: '3 terbuka', icon: Monitor, tone: 'text-cyan-500' },
                ].map((item) => {
                  const Icon = item.icon;
                  return (
                    <div key={item.label} className="flex items-center justify-between rounded-[18px] border border-gray-200/70 bg-white/80 px-3 py-3 dark:border-white/10 dark:bg-white/[0.04]">
                      <div className="flex items-center gap-3">
                        <div className="flex h-10 w-10 items-center justify-center rounded-2xl bg-gray-100 dark:bg-white/10">
                          <Icon className={`h-4 w-4 ${item.tone}`} />
                        </div>
                        <div>
                          <div className="text-[12px] text-gray-500 dark:text-gray-400">{item.label}</div>
                          <div className="text-[14px] font-semibold text-gray-900 dark:text-white">{item.value}</div>
                        </div>
                      </div>
                    </div>
                  );
                })}
              </div>
            </motion.div>
          </div>
        </div>
      </section>

      <section className="grid gap-5 xl:grid-cols-[1.1fr_0.9fr]">
        <div className="rounded-[26px] border border-white/50 bg-white/65 p-5 shadow-[0_12px_40px_-18px_rgba(0,0,0,0.1)] dark:border-white/10 dark:bg-white/[0.04]">
          <div className="mb-4 flex items-end justify-between gap-4">
            <div>
              <div className="text-[11px] uppercase tracking-[0.22em] text-gray-500 dark:text-gray-400">Eksplorasi Modul</div>
              <h2 className="mt-1 text-[22px] font-semibold tracking-tight text-gray-900 dark:text-white">Masuk ke area kerja yang paling sering dipakai</h2>
            </div>
          </div>

          <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            {modules.map((module, index) => {
              const Icon = module.icon;
              return (
                <motion.div
                  key={module.name}
                  initial={{ opacity: 0, y: 12 }}
                  animate={{ opacity: 1, y: 0 }}
                  transition={{ delay: index * 0.05 }}
                >
                  <a
                    data-scan={`kartu modul ${module.name.toLowerCase()}`}
                    href={module.path}
                    className="group block overflow-hidden rounded-[24px] border border-white/50 bg-white/80 transition hover:-translate-y-0.5 hover:shadow-lg dark:border-white/10 dark:bg-white/[0.04]"
                  >
                    <div className={`border-b border-white/50 bg-gradient-to-br ${module.accent} p-4 dark:border-white/10`}>
                      <div className="flex items-start justify-between gap-4">
                        <div className="flex h-12 w-12 items-center justify-center rounded-2xl bg-white/75 shadow-sm dark:bg-white/10">
                          <Icon className={`h-5 w-5 ${module.iconTone}`} />
                        </div>
                        <span className="rounded-full border border-white/70 bg-white/70 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.18em] text-gray-600 dark:border-white/10 dark:bg-white/10 dark:text-gray-300">
                          {module.stat}
                        </span>
                      </div>
                    </div>
                    <div className="p-4">
                      <div className="flex items-center justify-between gap-3">
                        <h3 className="text-[15px] font-semibold text-gray-900 dark:text-white">{module.name}</h3>
                        <ArrowRight className="h-4 w-4 text-gray-400 transition group-hover:translate-x-0.5" />
                      </div>
                      <p className="mt-2 text-[12.5px] leading-5 text-gray-500 dark:text-gray-400">{module.desc}</p>
                    </div>
                  </a>
                </motion.div>
              );
            })}
          </div>
        </div>

        <div className="space-y-5">
          <div className="rounded-[26px] border border-white/50 bg-white/65 p-5 shadow-[0_12px_40px_-18px_rgba(0,0,0,0.1)] dark:border-white/10 dark:bg-white/[0.04]">
            <div className="flex items-center justify-between">
              <div>
                <div className="text-[11px] uppercase tracking-[0.22em] text-gray-500 dark:text-gray-400">Live Feed</div>
                <h3 className="mt-1 text-[18px] font-semibold text-gray-900 dark:text-white">Aktivitas terbaru lintas modul</h3>
              </div>
              <div className="flex items-center gap-1 text-[11px] font-medium text-emerald-600 dark:text-emerald-400">
                <span className="h-2 w-2 rounded-full bg-emerald-500" />
                Aktif
              </div>
            </div>

            <div className="mt-4 space-y-3">
              {liveFeed.map((item, index) => (
                <motion.div
                  key={`${item.module}-${index}`}
                  initial={{ opacity: 0, y: 10 }}
                  animate={{ opacity: 1, y: 0 }}
                  transition={{ delay: 0.08 + index * 0.04 }}
                  className="rounded-[18px] border border-gray-200/70 bg-white/80 p-3 dark:border-white/10 dark:bg-white/[0.04]"
                >
                  <div className="flex items-center justify-between gap-3">
                    <span className={`text-[11px] font-semibold uppercase tracking-[0.18em] ${item.tone}`}>{item.module}</span>
                    <span className="text-[11px] text-gray-400">{item.time}</span>
                  </div>
                  <p className="mt-2 text-[13px] leading-5 text-gray-700 dark:text-gray-300">{item.text}</p>
                </motion.div>
              ))}
            </div>
          </div>

          <div className="rounded-[26px] border border-white/50 bg-white/65 p-5 shadow-[0_12px_40px_-18px_rgba(0,0,0,0.1)] dark:border-white/10 dark:bg-white/[0.04]">
            <div className="text-[11px] uppercase tracking-[0.22em] text-gray-500 dark:text-gray-400">Ringkasan Hari Ini</div>
            <div className="mt-4 grid gap-3 sm:grid-cols-3 xl:grid-cols-1">
              {[
                { label: 'SKP selesai diverifikasi', value: '38 pegawai', icon: CheckCircle2, tone: 'text-emerald-500' },
                { label: 'Agenda lapangan minggu ini', value: '8 jadwal', icon: Calendar, tone: 'text-sky-500' },
                { label: 'Data raw siap arsip', value: '8 file', icon: DatabaseZap, tone: 'text-cyan-500' },
              ].map((item) => {
                const Icon = item.icon;
                return (
                  <div key={item.label} className="rounded-[18px] border border-gray-200/70 bg-white/80 p-4 dark:border-white/10 dark:bg-white/[0.04]">
                    <div className="flex items-center gap-3">
                      <div className="flex h-10 w-10 items-center justify-center rounded-2xl bg-gray-100 dark:bg-white/10">
                        <Icon className={`h-4 w-4 ${item.tone}`} />
                      </div>
                      <div>
                        <div className="text-[12px] text-gray-500 dark:text-gray-400">{item.label}</div>
                        <div className="mt-0.5 text-[15px] font-semibold text-gray-900 dark:text-white">{item.value}</div>
                      </div>
                    </div>
                  </div>
                );
              })}
            </div>
          </div>
        </div>
      </section>

      <section className="rounded-[26px] border border-white/50 bg-white/65 p-5 shadow-[0_12px_40px_-18px_rgba(0,0,0,0.1)] dark:border-white/10 dark:bg-white/[0.04]">
        <div className="mb-4 flex items-end justify-between gap-4">
          <div>
            <div className="text-[11px] uppercase tracking-[0.22em] text-gray-500 dark:text-gray-400">Akses Cepat</div>
            <h2 className="mt-1 text-[20px] font-semibold tracking-tight text-gray-900 dark:text-white">Halaman yang paling sering dibuka</h2>
          </div>
        </div>

        <div className="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
          {primaryShortcuts.map((item, index) => {
            const Icon = item.icon;
            return (
              <motion.div
                key={item.name}
                initial={{ opacity: 0, y: 10 }}
                animate={{ opacity: 1, y: 0 }}
                transition={{ delay: 0.1 + index * 0.03 }}
              >
                <a
                  data-scan={`shortcut ${item.name.toLowerCase()}`}
                  href={item.path}
                  className="group flex items-center justify-between gap-3 rounded-[18px] border border-gray-200/70 bg-white/80 px-4 py-3 transition hover:bg-white hover:shadow-sm dark:border-white/10 dark:bg-white/[0.04] dark:hover:bg-white/[0.07]"
                >
                  <div className="flex items-center gap-3">
                    <div className="flex h-11 w-11 items-center justify-center rounded-2xl bg-gray-100 dark:bg-white/10">
                      <Icon className="h-4.5 w-4.5 text-gray-700 dark:text-gray-300" />
                    </div>
                    <div>
                      <div className="text-[13px] font-semibold text-gray-900 dark:text-white">{item.name}</div>
                      <div className="mt-0.5 text-[11px] uppercase tracking-[0.16em] text-gray-400">{item.badge}</div>
                    </div>
                  </div>
                  <ArrowRight className="h-4 w-4 text-gray-400 transition group-hover:translate-x-0.5" />
                </a>
              </motion.div>
            );
          })}
        </div>
      </section>
    </div>
  );
}
