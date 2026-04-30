import React, { useState } from 'react';
import { useNavigate } from '@tanstack/react-router';
import { motion } from 'motion/react';
import {
  Activity,
  ArrowRight,
  Eye,
  EyeOff,
  Landmark,
  LogIn,
  ShieldCheck,
  Sparkles,
} from 'lucide-react';
import { ThemeToggle } from '../components/ThemeToggle';
import { useAuth } from '../hooks/useAuth';

const highlights = [
  {
    label: 'SKP',
    title: 'Monitoring Kinerja',
    desc: 'Pantau capaian pegawai, progres penilaian, dan tindak lanjut verifikasi.',
    icon: Activity,
    tone: 'text-orange-600 dark:text-orange-400',
  },
  {
    label: 'RKK DIPA',
    title: 'Kontrol Anggaran',
    desc: 'Lihat realisasi, pencairan, revisi impor, dan integritas data anggaran.',
    icon: Landmark,
    tone: 'text-emerald-600 dark:text-emerald-400',
  },
  {
    label: 'Admin',
    title: 'Akses Terkelola',
    desc: 'Kelola pengguna, peran, dan izin akses dalam satu ekosistem kerja.',
    icon: ShieldCheck,
    tone: 'text-sky-600 dark:text-sky-400',
  },
];

export function LoginPage() {
  const navigate = useNavigate();
  const { login } = useAuth();
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [showPassword, setShowPassword] = useState(false);
  const [isLoading, setIsLoading] = useState(false);
  const [error, setError] = useState('');
  const [rememberMe, setRememberMe] = useState(false);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError('');

    if (!email || !password) {
      setError('Masukkan email dan password.');
      return;
    }

    setIsLoading(true);
    try {
      await login(email, password, rememberMe);
      navigate({ to: '/skp/dashboard' as any });
    } catch (err: any) {
      setError(err?.message || 'Email atau password salah.');
    } finally {
      setIsLoading(false);
    }
  };

  return (
    <div className="relative min-h-screen overflow-hidden px-4 py-6 text-gray-900 transition-colors duration-300 dark:text-gray-100 sm:px-6 lg:px-8">
      <div className="fixed inset-0 -z-10">
        <div className="absolute inset-0 bg-[linear-gradient(135deg,#edf3fb_0%,#f8f5ee_45%,#e6f3fb_100%)] dark:bg-[linear-gradient(135deg,#040814_0%,#08101e_45%,#04111a_100%)]" />
        <div className="absolute left-[-10%] top-[-8%] h-[380px] w-[380px] rounded-full bg-orange-300/28 blur-[120px] dark:bg-orange-500/12" />
        <div className="absolute right-[-6%] top-[8%] h-[360px] w-[360px] rounded-full bg-sky-300/25 blur-[130px] dark:bg-sky-500/10" />
        <div className="absolute bottom-[-12%] left-[24%] h-[420px] w-[420px] rounded-full bg-amber-200/28 blur-[140px] dark:bg-amber-500/8" />
      </div>

      <div className="mx-auto flex min-h-[calc(100vh-3rem)] max-w-7xl items-center">
        <div className="w-full">
          <div className="mb-5 flex items-center justify-end">
            <ThemeToggle />
          </div>

          <div className="grid overflow-hidden rounded-[34px] border border-white/60 bg-white/60 shadow-[0_30px_90px_-30px_rgba(0,0,0,0.22)] backdrop-blur-xl dark:border-white/10 dark:bg-white/[0.04] lg:grid-cols-[1.08fr_0.92fr]">
            <div className="relative overflow-hidden border-b border-white/50 p-7 dark:border-white/10 lg:border-b-0 lg:border-r">
              <div className="absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(251,191,36,0.18),_transparent_32%),radial-gradient(circle_at_bottom_right,_rgba(14,165,233,0.16),_transparent_28%)]" />
              <div className="relative">
                <div className="inline-flex items-center gap-2 rounded-full border border-white/60 bg-white/70 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.22em] text-gray-600 dark:border-white/10 dark:bg-white/[0.05] dark:text-gray-300">
                  <Sparkles className="h-3.5 w-3.5 text-amber-500" />
                  SImentor Workspace
                </div>

                <div className="mt-8 max-w-xl">
                  <h1 className="text-[38px] font-semibold leading-[0.92] tracking-tight text-gray-900 dark:text-white sm:text-[46px]">
                    Masuk ke ruang kendali operasional kantor.
                  </h1>
                  <p className="mt-4 max-w-lg text-[15px] leading-7 text-gray-600 dark:text-gray-400">
                    SImentor menyatukan monitoring kinerja pegawai, kegiatan, anggaran DIPA, layanan IPDS, dan
                    administrasi dalam satu dashboard kerja yang ringkas.
                  </p>
                </div>

                <div className="mt-8 grid gap-4">
                  {highlights.map((item, index) => {
                    const Icon = item.icon;
                    return (
                      <motion.div
                        key={item.title}
                        initial={{ opacity: 0, y: 12 }}
                        animate={{ opacity: 1, y: 0 }}
                        transition={{ delay: index * 0.08 }}
                        className="rounded-[24px] border border-white/60 bg-white/70 p-4 backdrop-blur dark:border-white/10 dark:bg-white/[0.05]"
                      >
                        <div className="flex items-start gap-4">
                          <div className="flex h-12 w-12 items-center justify-center rounded-2xl bg-gray-100/90 shadow-sm dark:bg-white/10">
                            <Icon className={`h-5 w-5 ${item.tone}`} />
                          </div>
                          <div className="min-w-0">
                            <div className="text-[11px] font-semibold uppercase tracking-[0.18em] text-gray-400">{item.label}</div>
                            <div className="mt-1 text-[17px] font-semibold text-gray-900 dark:text-white">{item.title}</div>
                            <p className="mt-1 text-[13px] leading-6 text-gray-500 dark:text-gray-400">{item.desc}</p>
                          </div>
                        </div>
                      </motion.div>
                    );
                  })}
                </div>

                <div className="mt-8 rounded-[26px] bg-gray-950 p-5 text-white shadow-[0_25px_50px_-24px_rgba(0,0,0,0.5)]">
                  <div className="flex items-center justify-between">
                    <div>
                      <div className="text-[11px] uppercase tracking-[0.2em] text-white/45">Status Ringkas</div>
                      <div className="mt-1 text-lg font-semibold">SImentor Aktif</div>
                    </div>
                    <div className="inline-flex items-center gap-1 rounded-full bg-emerald-400/10 px-2.5 py-1 text-[11px] font-semibold text-emerald-300">
                      <span className="h-2 w-2 rounded-full bg-emerald-400" />
                      Online
                    </div>
                  </div>

                  <div className="mt-5 grid grid-cols-3 gap-3">
                    {[
                      { label: 'Modul', value: '7' },
                      { label: 'Menu DIPA', value: '6' },
                      { label: 'Pengguna', value: '12' },
                    ].map((item) => (
                      <div key={item.label} className="rounded-[18px] border border-white/10 bg-white/5 p-3">
                        <div className="text-[10px] uppercase tracking-[0.16em] text-white/45">{item.label}</div>
                        <div className="mt-2 text-xl font-semibold">{item.value}</div>
                      </div>
                    ))}
                  </div>
                </div>
              </div>
            </div>

            <div className="flex items-center justify-center p-6 sm:p-8 lg:p-10">
              <motion.div
                initial={{ opacity: 0, y: 24, scale: 0.98 }}
                animate={{ opacity: 1, y: 0, scale: 1 }}
                transition={{ duration: 0.45, ease: [0.16, 1, 0.3, 1] }}
                className="w-full max-w-[430px]"
              >
                <div className="rounded-[30px] border border-white/60 bg-white/75 p-7 shadow-[0_24px_60px_-28px_rgba(0,0,0,0.18)] backdrop-blur dark:border-white/10 dark:bg-white/[0.06] sm:p-8">
                  <div className="mb-7 flex items-start justify-between gap-4">
                    <div>
                      <div className="flex items-center gap-3">
                        <div className="flex h-14 w-14 items-center justify-center rounded-[20px] bg-gradient-to-br from-orange-400 to-orange-600 shadow-lg shadow-orange-500/30">
                          <img src="/icon.svg" alt="SImentor" className="h-10 w-10" />
                        </div>
                        <div>
                          <h2 className="text-[26px] font-semibold tracking-tight text-gray-900 dark:text-white">SImentor</h2>
                          <p className="text-[12px] uppercase tracking-[0.18em] text-gray-400">Internal Access</p>
                        </div>
                      </div>
                      <p className="mt-4 text-[13px] leading-6 text-gray-500 dark:text-gray-400">
                        Gunakan akun internal untuk mengakses dashboard operasional, monitoring anggaran, dan modul pendukung kantor.
                      </p>
                    </div>
                  </div>

                  <form onSubmit={handleSubmit} className="space-y-5">
                    <div>
                      <label className="mb-2 block text-[11px] font-bold uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">
                        Email
                      </label>
                      <input
                        type="email"
                        value={email}
                        onChange={(e) => setEmail(e.target.value)}
                        placeholder="nama@bps.go.id"
                        className="h-12 w-full rounded-2xl border border-white/60 bg-white/80 px-4 text-[14px] text-gray-900 outline-none transition placeholder:text-gray-400 focus:border-orange-400/50 focus:ring-2 focus:ring-orange-400/40 dark:border-white/10 dark:bg-white/[0.05] dark:text-white"
                      />
                    </div>

                    <div>
                      <label className="mb-2 block text-[11px] font-bold uppercase tracking-[0.18em] text-gray-500 dark:text-gray-400">
                        Password
                      </label>
                      <div className="relative">
                        <input
                          type={showPassword ? 'text' : 'password'}
                          value={password}
                          onChange={(e) => setPassword(e.target.value)}
                          placeholder="Masukkan password"
                          className="h-12 w-full rounded-2xl border border-white/60 bg-white/80 px-4 pr-12 text-[14px] text-gray-900 outline-none transition placeholder:text-gray-400 focus:border-orange-400/50 focus:ring-2 focus:ring-orange-400/40 dark:border-white/10 dark:bg-white/[0.05] dark:text-white"
                        />
                        <button
                          type="button"
                          onClick={() => setShowPassword(!showPassword)}
                          className="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 transition hover:text-gray-600 dark:hover:text-gray-300"
                        >
                          {showPassword ? <EyeOff className="h-4.5 w-4.5" /> : <Eye className="h-4.5 w-4.5" />}
                        </button>
                      </div>
                    </div>

                    <div className="flex flex-col gap-4">
                      <label className="inline-flex items-center gap-2 text-[14px] text-gray-600 dark:text-gray-300">
                        <input
                          type="checkbox"
                          checked={rememberMe}
                          onChange={(e) => setRememberMe(e.target.checked)}
                          className="h-4 w-4 rounded border-gray-300 text-orange-500 focus:ring-orange-400"
                        />
                        Ingat saya di perangkat ini
                      </label>

                      {error && (
                        <motion.div
                          initial={{ opacity: 0, y: -4 }}
                          animate={{ opacity: 1, y: 0 }}
                          className="rounded-2xl border border-red-200/50 bg-red-50 px-4 py-3 text-[13px] font-medium text-red-600 dark:border-red-500/20 dark:bg-red-500/10 dark:text-red-400"
                        >
                          {error}
                        </motion.div>
                      )}
                    </div>

                    <button
                      type="submit"
                      disabled={isLoading}
                      className="flex h-12 w-full items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-orange-400 to-orange-500 text-[14px] font-semibold text-white shadow-lg shadow-orange-500/25 transition-all hover:from-orange-500 hover:to-orange-600 disabled:cursor-not-allowed disabled:opacity-60"
                    >
                      {isLoading ? (
                        <>
                          <svg className="h-4.5 w-4.5 animate-spin" viewBox="0 0 24 24" fill="none">
                            <circle cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="3" className="opacity-25" />
                            <path d="M4 12a8 8 0 018-8" stroke="currentColor" strokeWidth="3" strokeLinecap="round" className="opacity-75" />
                          </svg>
                          Memproses...
                        </>
                      ) : (
                        <>
                          <LogIn className="h-4.5 w-4.5" />
                          Masuk ke SImentor
                        </>
                      )}
                    </button>
                  </form>

                  <div className="mt-5 rounded-[22px] border border-white/60 bg-white/80 px-4 py-3 dark:border-white/10 dark:bg-white/[0.04]">
                    <div className="flex items-start justify-between gap-3">
                      <div>
                        <div className="text-[11px] font-semibold uppercase tracking-[0.18em] text-gray-400">Demo Access</div>
                        <div className="mt-1 text-[12.5px] text-gray-600 dark:text-gray-400">
                          <span className="font-mono font-medium">admin@bps.go.id</span> / <span className="font-mono font-medium">admin</span>
                        </div>
                      </div>
                      <ArrowRight className="mt-1 h-4 w-4 text-gray-300" />
                    </div>
                  </div>

                  <div className="mt-6 text-center text-[11px] text-gray-400 dark:text-gray-500">
                    BPS Kabupaten Cintalangeng &copy; 2026
                  </div>
                </div>
              </motion.div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
