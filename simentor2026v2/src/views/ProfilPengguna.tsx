import React, { useMemo, useState, useEffect } from 'react';
import { 
  User as UserIcon, 
  Briefcase, 
  Lock, 
  Save, 
  Loader2, 
  CheckCircle2, 
  AlertCircle,
  Mail,
  UserCircle,
  BadgeCheck,
  ShieldCheck,
  Camera
} from 'lucide-react';
import { motion, AnimatePresence } from 'motion/react';
import { useAuth } from '../hooks/useAuth';
import { useApiQuery, useApiMutation } from '../hooks/useApi';
import { profileService, pegawaiService } from '../lib/api-services';
import type { User, Pegawai, ProfileUpdatePayload, ProfilePasswordPayload } from '../types/api';

type TabId = 'pengguna' | 'kepegawaian' | 'password';

const tabs = [
  { id: 'pengguna' as const, label: 'Pengguna', icon: UserIcon },
  { id: 'kepegawaian' as const, label: 'Kepegawaian', icon: Briefcase },
  { id: 'password' as const, label: 'Ubah Kata Sandi', icon: Lock },
];

export function ProfilPengguna() {
  const { user: authUser, updateUser } = useAuth();
  const [activeTab, setActiveTab] = useState<TabId>('pengguna');
  const [notice, setNotice] = useState<{ message: string; tone: 'success' | 'error' } | null>(null);

  // Fetch Full User Data
  const { 
    data: profileResponse, 
    isLoading: isLoadingProfile,
    refetch: refetchProfile 
  } = useApiQuery(['profile'], () => profileService.get());

  const user = profileResponse?.data;

  // Fetch Pegawai Data
  const { 
    data: pegawaiResponse, 
    isLoading: isLoadingPegawai,
    refetch: refetchPegawai
  } = useApiQuery(['profile-pegawai'], () => profileService.getPegawai(), {
    retry: false,
    refetchOnWindowFocus: false,
  });

  const pegawai = pegawaiResponse?.data;

  // Mutations
  const updateProfileMutation = useApiMutation(
    (data: ProfileUpdatePayload) => profileService.update(data),
    {
      onSuccess: (res) => {
        setNotice({ message: 'Profil berhasil diperbarui', tone: 'success' });
        if (res.data) updateUser(res.data);
        refetchProfile();
      },
      onError: (err) => setNotice({ message: err.message || 'Gagal memperbarui profil', tone: 'error' })
    }
  );

  const updatePasswordMutation = useApiMutation(
    (data: ProfilePasswordPayload) => profileService.updatePassword(data),
    {
      onSuccess: () => {
        setNotice({ message: 'Kata sandi berhasil diubah', tone: 'success' });
      },
      onError: (err) => setNotice({ message: err.message || 'Gagal mengubah kata sandi', tone: 'error' })
    }
  );

  const updatePegawaiMutation = useApiMutation(
    (data: Record<string, unknown>) => profileService.updatePegawai(data),
    {
      onSuccess: () => {
        setNotice({ message: 'Data kepegawaian berhasil diperbarui', tone: 'success' });
        refetchPegawai();
      },
      onError: (err) => setNotice({ message: err.message || 'Gagal memperbarui data kepegawaian', tone: 'error' })
    }
  );

  useEffect(() => {
    if (notice) {
      const timer = setTimeout(() => setNotice(null), 5000);
      return () => clearTimeout(timer);
    }
  }, [notice]);

  const handleUpdateProfile = (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault();
    const formData = new FormData(e.currentTarget);
    updateProfileMutation.mutate({
      name: formData.get('name') as string,
      email: formData.get('email') as string,
    });
  };

  const handleUpdatePegawai = (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault();
    if (!pegawai) return;
    const formData = new FormData(e.currentTarget);
    updatePegawaiMutation.mutate({
      nip: formData.get('nip') as string,
      nama: formData.get('nama') as string,
      gelar_depan: formData.get('gelar_depan') as string,
      gelar_belakang: formData.get('gelar_belakang') as string,
      jabatan: formData.get('jabatan') as string,
      pangkat: formData.get('pangkat') as string,
      gol: formData.get('gol') as string,
      tempat_lahir: formData.get('tempat_lahir') as string,
      tanggal_lahir: formData.get('tanggal_lahir') as string,
      no_hp: formData.get('no_hp') as string,
      alamat: formData.get('alamat') as string,
    });
  };

  const handleUpdatePassword = (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault();
    const formData = new FormData(e.currentTarget);
    const password = formData.get('password') as string;
    const confirm = formData.get('password_confirmation') as string;

    if (password !== confirm) {
      setNotice({ message: 'Konfirmasi kata sandi tidak cocok', tone: 'error' });
      return;
    }

    updatePasswordMutation.mutate({
      current_password: formData.get('current_password') as string,
      password,
      password_confirmation: confirm,
    });
    e.currentTarget.reset();
  };

  const renderContent = () => {
    if (isLoadingProfile || (activeTab === 'kepegawaian' && isLoadingPegawai)) {
      return (
        <div className="flex h-48 items-center justify-center">
          <Loader2 className="h-8 w-8 animate-spin text-amber-500" />
        </div>
      );
    }

    if (activeTab === 'kepegawaian') {
      if (!pegawai) {
        return (
          <div className="flex flex-col items-center justify-center h-48 text-center p-6 bg-gray-50 dark:bg-white/5 rounded-3xl border border-dashed border-gray-200 dark:border-white/10">
            <AlertCircle className="h-10 w-10 text-gray-400 mb-2" />
            <p className="text-gray-500 dark:text-gray-400 font-medium">Data kepegawaian tidak ditemukan.</p>
            <p className="text-xs text-gray-400 mt-1">Silakan hubungi administrator untuk menghubungkan akun Anda dengan data pegawai.</p>
          </div>
        );
      }

      return (
        <form onSubmit={handleUpdatePegawai} className="space-y-6">
          <div className="grid gap-6 md:grid-cols-2">
            <div className="space-y-2">
              <label className="text-[10px] font-bold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400 ml-1">NIP</label>
              <input 
                name="nip"
                defaultValue={pegawai.nip || ''}
                className="w-full h-11 rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-white/5 px-4 text-sm font-medium outline-none transition focus:border-amber-500 focus:ring-1 focus:ring-amber-500"
              />
            </div>
            <div className="space-y-2">
              <label className="text-[10px] font-bold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400 ml-1">Nama Lengkap (Tanpa Gelar)</label>
              <input 
                name="nama"
                defaultValue={pegawai.nama || ''}
                className="w-full h-11 rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-white/5 px-4 text-sm font-medium outline-none transition focus:border-amber-500 focus:ring-1 focus:ring-amber-500"
              />
            </div>
            
            <div className="grid grid-cols-2 gap-4">
              <div className="space-y-2">
                <label className="text-[10px] font-bold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400 ml-1">Gelar Depan</label>
                <input 
                  name="gelar_depan"
                  defaultValue={pegawai.gelar_depan || ''}
                  placeholder="Dr."
                  className="w-full h-11 rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-white/5 px-4 text-sm font-medium outline-none transition focus:border-amber-500 focus:ring-1 focus:ring-amber-500"
                />
              </div>
              <div className="space-y-2">
                <label className="text-[10px] font-bold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400 ml-1">Gelar Belakang</label>
                <input 
                  name="gelar_belakang"
                  defaultValue={pegawai.gelar_belakang || ''}
                  placeholder="S.Kom"
                  className="w-full h-11 rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-white/5 px-4 text-sm font-medium outline-none transition focus:border-amber-500 focus:ring-1 focus:ring-amber-500"
                />
              </div>
            </div>

            <div className="space-y-2">
              <label className="text-[10px] font-bold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400 ml-1">Jabatan</label>
              <input 
                name="jabatan"
                defaultValue={pegawai.jabatan || ''}
                className="w-full h-11 rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-white/5 px-4 text-sm font-medium outline-none transition focus:border-amber-500 focus:ring-1 focus:ring-amber-500"
              />
            </div>
            
            <div className="space-y-2">
              <label className="text-[10px] font-bold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400 ml-1">Pangkat</label>
              <input 
                name="pangkat"
                defaultValue={pegawai.pangkat || ''}
                className="w-full h-11 rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-white/5 px-4 text-sm font-medium outline-none transition focus:border-amber-500 focus:ring-1 focus:ring-amber-500"
              />
            </div>
            <div className="space-y-2">
              <label className="text-[10px] font-bold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400 ml-1">Golongan</label>
              <input 
                name="gol"
                defaultValue={pegawai.gol || ''}
                className="w-full h-11 rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-white/5 px-4 text-sm font-medium outline-none transition focus:border-amber-500 focus:ring-1 focus:ring-amber-500"
              />
            </div>

            <div className="space-y-2">
              <label className="text-[10px] font-bold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400 ml-1">Tempat Lahir</label>
              <input 
                name="tempat_lahir"
                defaultValue={pegawai.tempat_lahir || ''}
                className="w-full h-11 rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-white/5 px-4 text-sm font-medium outline-none transition focus:border-amber-500 focus:ring-1 focus:ring-amber-500"
              />
            </div>
            <div className="space-y-2">
              <label className="text-[10px] font-bold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400 ml-1">Tanggal Lahir</label>
              <input 
                name="tanggal_lahir"
                type="date"
                defaultValue={pegawai.tanggal_lahir ? pegawai.tanggal_lahir.split('T')[0] : ''}
                className="w-full h-11 rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-white/5 px-4 text-sm font-medium outline-none transition focus:border-amber-500 focus:ring-1 focus:ring-amber-500"
              />
            </div>

            <div className="space-y-2">
              <label className="text-[10px] font-bold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400 ml-1">Nomor HP</label>
              <input 
                name="no_hp"
                defaultValue={pegawai.no_hp || ''}
                className="w-full h-11 rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-white/5 px-4 text-sm font-medium outline-none transition focus:border-amber-500 focus:ring-1 focus:ring-amber-500"
              />
            </div>
            <div className="space-y-2 md:col-span-2">
              <label className="text-[10px] font-bold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400 ml-1">Alamat</label>
              <textarea 
                name="alamat"
                defaultValue={pegawai.alamat || ''}
                rows={2}
                className="w-full rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-white/5 p-4 text-sm font-medium outline-none transition focus:border-amber-500 focus:ring-1 focus:ring-amber-500"
              />
            </div>
          </div>
          <div className="flex justify-end pt-4">
            <button 
              disabled={updatePegawaiMutation.isPending}
              className="flex items-center gap-2 px-6 py-2.5 bg-amber-400 hover:bg-amber-500 text-gray-900 rounded-full font-bold text-sm shadow-lg shadow-amber-500/20 transition-all disabled:opacity-50"
            >
              {updatePegawaiMutation.isPending ? <Loader2 className="h-4 w-4 animate-spin" /> : <Save className="h-4 w-4" />}
              Simpan Perubahan
            </button>
          </div>
        </form>
      );
    }

    if (activeTab === 'password') {
      return (
        <form onSubmit={handleUpdatePassword} className="space-y-6">
          <div className="grid gap-6">
            <div className="space-y-2">
              <label className="text-[10px] font-bold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400 ml-1">Kata Sandi Saat Ini</label>
              <input 
                name="current_password"
                type="password"
                required
                className="w-full h-11 rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-white/5 px-4 text-sm font-medium outline-none transition focus:border-amber-500 focus:ring-1 focus:ring-amber-500"
              />
            </div>
            <div className="grid gap-6 sm:grid-cols-2">
              <div className="space-y-2">
                <label className="text-[10px] font-bold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400 ml-1">Kata Sandi Baru</label>
                <input 
                  name="password"
                  type="password"
                  required
                  className="w-full h-11 rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-white/5 px-4 text-sm font-medium outline-none transition focus:border-amber-500 focus:ring-1 focus:ring-amber-500"
                />
              </div>
              <div className="space-y-2">
                <label className="text-[10px] font-bold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400 ml-1">Konfirmasi Kata Sandi Baru</label>
                <input 
                  name="password_confirmation"
                  type="password"
                  required
                  className="w-full h-11 rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-white/5 px-4 text-sm font-medium outline-none transition focus:border-amber-500 focus:ring-1 focus:ring-amber-500"
                />
              </div>
            </div>
          </div>
          <div className="flex justify-end pt-4">
            <button 
              disabled={updatePasswordMutation.isPending}
              className="flex items-center gap-2 px-6 py-2.5 bg-rose-500 hover:bg-rose-600 text-white rounded-full font-bold text-sm shadow-lg shadow-rose-500/20 transition-all disabled:opacity-50"
            >
              {updatePasswordMutation.isPending ? <Loader2 className="h-4 w-4 animate-spin" /> : <Lock className="h-4 w-4" />}
              Ubah Kata Sandi
            </button>
          </div>
        </form>
      );
    }

    return (
      <form onSubmit={handleUpdateProfile} className="space-y-8">
        <div className="flex flex-col sm:flex-row items-center gap-6 pb-4 border-b border-gray-100 dark:border-white/5">
          <div className="relative group">
            <div className="size-24 rounded-3xl overflow-hidden border-2 border-amber-400/20 bg-gray-50 dark:bg-white/5 flex items-center justify-center">
              {user?.profile_photo_path ? (
                <img src={user.profile_photo_path} alt="Profile" className="w-full h-full object-cover" />
              ) : (
                <UserCircle className="size-16 text-gray-300 dark:text-white/10" />
              )}
            </div>
            <button type="button" className="absolute -bottom-2 -right-2 size-8 rounded-xl bg-white dark:bg-slate-900 border border-gray-200 dark:border-white/10 flex items-center justify-center text-gray-500 hover:text-amber-500 shadow-lg transition-all">
              <Camera className="size-4" />
            </button>
          </div>
          <div className="flex-1 text-center sm:text-left space-y-1">
            <h3 className="text-xl font-bold text-gray-900 dark:text-white">{user?.name || 'User'}</h3>
            <div className="flex flex-wrap items-center justify-center sm:justify-start gap-2">
              <span className="px-2.5 py-0.5 rounded-full bg-amber-100 dark:bg-amber-500/20 text-amber-700 dark:text-amber-400 text-[10px] font-bold uppercase tracking-wider border border-amber-200 dark:border-amber-500/30">
                {user?.roles?.[0] ? (typeof user.roles[0] === 'string' ? user.roles[0] : user.roles[0].name) : 'Member'}
              </span>
              <span className="flex items-center gap-1 text-[11px] text-gray-500 dark:text-gray-400 font-medium">
                <BadgeCheck className="size-3.5 text-sky-500" />
                Verified Account
              </span>
            </div>
          </div>
        </div>

        <div className="grid gap-6 sm:grid-cols-2">
          <div className="space-y-2">
            <label className="text-[10px] font-bold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400 ml-1">Nama Lengkap</label>
            <div className="relative">
              <UserCircle className="absolute left-4 top-1/2 -translate-y-1/2 size-4 text-gray-400" />
              <input 
                name="name"
                defaultValue={user?.name || ''}
                required
                className="w-full h-11 rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-white/5 pl-11 pr-4 text-sm font-medium outline-none transition focus:border-amber-500 focus:ring-1 focus:ring-amber-500"
              />
            </div>
          </div>
          <div className="space-y-2">
            <label className="text-[10px] font-bold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400 ml-1">Alamat Email</label>
            <div className="relative">
              <Mail className="absolute left-4 top-1/2 -translate-y-1/2 size-4 text-gray-400" />
              <input 
                name="email"
                type="email"
                defaultValue={user?.email || ''}
                required
                className="w-full h-11 rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-white/5 pl-11 pr-4 text-sm font-medium outline-none transition focus:border-amber-500 focus:ring-1 focus:ring-amber-500"
              />
            </div>
          </div>
          <div className="space-y-2 sm:col-span-2">
            <label className="text-[10px] font-bold uppercase tracking-[0.2em] text-gray-500 dark:text-gray-400 ml-1">Kewenangan Keamanan</label>
            <div className="flex flex-wrap gap-2 p-4 rounded-2xl bg-gray-50 dark:bg-white/5 border border-gray-100 dark:border-white/10">
              {(user?.permissions || []).slice(0, 8).map((p: any) => (
                <span key={p.id || p} className="px-2 py-1 rounded-lg bg-white dark:bg-white/5 border border-gray-200 dark:border-white/10 text-[9px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-tighter">
                  {typeof p === 'string' ? p : p.name}
                </span>
              ))}
              {(user?.permissions?.length || 0) > 8 && (
                <span className="px-2 py-1 text-[9px] font-bold text-gray-400 uppercase italic">
                  +{(user?.permissions?.length || 0) - 8} others
                </span>
              )}
            </div>
          </div>
        </div>

        <div className="flex justify-end pt-4">
          <button 
            disabled={updateProfileMutation.isPending}
            className="flex items-center gap-2 px-6 py-2.5 bg-amber-400 hover:bg-amber-500 text-gray-900 rounded-full font-bold text-sm shadow-lg shadow-amber-500/20 transition-all disabled:opacity-50"
          >
            {updateProfileMutation.isPending ? <Loader2 className="h-4 w-4 animate-spin" /> : <Save className="h-4 w-4" />}
            Simpan Perubahan
          </button>
        </div>
      </form>
    );
  };

  return (
    <div className="space-y-8 max-w-4xl mx-auto py-4">
      <div className="flex flex-col md:flex-row md:items-end justify-between gap-4">
        <div>
          <div className="flex items-center gap-2 text-[11px] uppercase tracking-[0.3em] text-amber-600 dark:text-amber-400 font-bold mb-1">
            <ShieldCheck className="size-4" />
            Akun & Keamanan
          </div>
          <h1 className="text-3xl font-extrabold text-gray-900 dark:text-white tracking-tight">Profil Pengguna</h1>
          <p className="text-sm text-gray-600 dark:text-gray-400 mt-1">Personalisasi identitas digital dan kelola data kepegawaian Anda.</p>
        </div>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-[240px_1fr] gap-8 items-start">
        {/* Navigation Sidebar */}
        <aside className="sticky top-24 flex flex-row lg:flex-col gap-2 overflow-x-auto lg:overflow-visible pb-2 lg:pb-0 no-scrollbar">
          {tabs.map((tab) => {
            const active = activeTab === tab.id;
            const Icon = tab.icon;
            return (
              <button
                key={tab.id}
                type="button"
                onClick={() => setActiveTab(tab.id)}
                className={`flex items-center gap-3 px-5 py-3.5 rounded-2xl text-[11px] font-bold uppercase tracking-[0.15em] transition-all whitespace-nowrap lg:whitespace-normal ${
                  active
                    ? 'bg-amber-400 text-gray-900 shadow-lg shadow-amber-500/20'
                    : 'bg-white/70 dark:bg-white/[0.04] text-gray-500 dark:text-gray-400 hover:bg-white dark:hover:bg-white/10 hover:text-gray-900 dark:hover:text-white border border-transparent'
                }`}
              >
                <Icon className={`size-4 ${active ? 'text-gray-900' : 'text-current opacity-60'}`} />
                {tab.label}
              </button>
            );
          })}
        </aside>

        {/* Content Area */}
        <div className="space-y-6">
          <AnimatePresence mode="wait">
            {notice && (
              <motion.div
                initial={{ opacity: 0, y: -10 }}
                animate={{ opacity: 1, y: 0 }}
                exit={{ opacity: 0, scale: 0.95 }}
                className={`p-4 rounded-2xl border flex items-center gap-3 ${
                  notice.tone === 'success'
                    ? 'bg-green-50 dark:bg-green-500/10 border-green-200 dark:border-green-500/20 text-green-700 dark:text-green-400'
                    : 'bg-red-50 dark:bg-red-500/10 border-red-200 dark:border-red-500/20 text-red-700 dark:text-red-400'
                }`}
              >
                <div className={`h-8 w-8 rounded-xl flex items-center justify-center shrink-0 ${
                   notice.tone === 'success' ? 'bg-green-100 dark:bg-green-500/20' : 'bg-red-100 dark:bg-red-500/20'
                }`}>
                  {notice.tone === 'success' ? <CheckCircle2 className="h-4 w-4" /> : <AlertCircle className="h-4 w-4" />}
                </div>
                <p className="text-sm font-semibold">{notice.message}</p>
              </motion.div>
            )}
          </AnimatePresence>

          <div className="rounded-[32px] border border-white/50 bg-white/65 p-6 md:p-8 dark:border-white/10 dark:bg-white/[0.04] glass-strong shadow-xl shadow-black/5 overflow-hidden">
            <motion.div
              key={activeTab}
              initial={{ opacity: 0, x: 10 }}
              animate={{ opacity: 1, x: 0 }}
              transition={{ duration: 0.2 }}
            >
              {renderContent()}
            </motion.div>
          </div>

          <div className="p-6 rounded-[28px] bg-sky-500/5 border border-sky-500/10 flex flex-col sm:flex-row items-center gap-4 text-center sm:text-left">
            <div className="size-12 rounded-2xl bg-sky-500/20 flex items-center justify-center shrink-0">
              <BadgeCheck className="size-6 text-sky-500" />
            </div>
            <div>
              <h4 className="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-tight">Butuh Bantuan Keamanan?</h4>
              <p className="text-xs text-gray-500 dark:text-gray-400 mt-1">Pastikan Anda tidak membagikan kredensial login kepada siapapun. Gunakan kata sandi yang kuat dan unik.</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
