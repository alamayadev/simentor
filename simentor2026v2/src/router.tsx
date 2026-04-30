import { 
  createRootRoute, 
  createRoute, 
  createRouter, 
  lazyRouteComponent,
  Outlet,
  redirect,
  type RouteComponent,
  useLocation
} from '@tanstack/react-router';
import React, { useState, useEffect } from 'react';
import { Navigation } from './components/Navigation';
import { MODULES } from './constants';
import { canAccessPath, getStoredUser } from './lib/authz';

const PUBLIC_PATHS = ['/', '/login'];
function enforceRouteRole(pathname: string) {
  const user = getStoredUser();
  if (!canAccessPath(user, pathname)) {
    throw redirect({ to: '/skp/dashboard' as any });
  }
}

// Root Layout
const rootRoute = createRootRoute({
  beforeLoad: ({ location }) => {
    const token = localStorage.getItem('auth_token') ?? sessionStorage.getItem('auth_token');
    const isPublic = PUBLIC_PATHS.includes(location.pathname);

    if (!token && !isPublic) {
      throw redirect({ to: '/login' });
    }

    if (token && location.pathname === '/login') {
      throw redirect({ to: '/skp/dashboard' as any });
    }

    if (token && !isPublic) {
      enforceRouteRole(location.pathname);
    }
  },
  component: () => {
    const location = useLocation();
    const isLogin = location.pathname === '/login';
    const [appWidth, setAppWidth] = useState<string>(
      () => localStorage.getItem('app-width') || 'narrow'
    );

    useEffect(() => {
      const handler = () => setAppWidth(localStorage.getItem('app-width') || 'narrow');
      window.addEventListener('app-width-change', handler);
      return () => window.removeEventListener('app-width-change', handler);
    }, []);

    if (isLogin) {
      return (
        <div className="min-h-screen text-gray-900 dark:text-gray-100 transition-colors duration-300">
          <Outlet />
        </div>
      );
    }

    return (
      <div className="min-h-screen text-gray-900 dark:text-gray-100 transition-colors duration-300">
        {/* Background Blobs */}
        <div className="fixed inset-0 -z-10">
          <div className="absolute inset-0 bg-gradient-to-br from-[#e9f0f7] via-[#f5f7fa] to-[#eef2f8] dark:from-[#050810] dark:via-[#0a1220] dark:to-[#050810]"></div>
          <div className="absolute -top-32 -left-40 w-[600px] h-[600px] bg-blue-200/50 dark:bg-blue-900/20 rounded-full blur-[120px]"></div>
          <div className="absolute top-20 right-[10%] w-[500px] h-[500px] bg-amber-200/60 dark:bg-amber-600/15 rounded-full blur-[130px]"></div>
          <div className="absolute top-[30%] left-[30%] w-[700px] h-[700px] bg-orange-100/70 dark:bg-orange-900/10 rounded-full blur-[140px]"></div>
          <div className="absolute bottom-[-10%] right-[20%] w-[500px] h-[500px] bg-yellow-100/60 dark:bg-yellow-800/10 rounded-full blur-[120px]"></div>
          <div className="absolute bottom-[5%] left-[10%] w-[400px] h-[400px] bg-cyan-100/50 dark:bg-cyan-900/15 rounded-full blur-[100px]"></div>
        </div>

        <div className={`relative z-10 mx-auto px-4 sm:px-6 lg:px-8 py-4 lg:py-6 transition-all duration-300 ${appWidth === 'wide' ? 'max-w-full' : 'max-w-[1440px]'}`}>
          <Navigation />
          <main className="mt-8 lg:mt-10">
            <Outlet />
          </main>
        </div>
      </div>
    );
  },
});

// Helper to create placeholder components
const Placeholder = ({ title }: { title: string }) => (
  <div className="space-y-6">
    <div className="relative z-30 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
      <div className="flex-1">
        <h1 className="text-[32px] lg:text-[40px] font-semibold tracking-tight leading-none text-gray-900 dark:text-white">
          {title}
        </h1>
        <p className="mt-2 text-[14px] text-gray-600 dark:text-gray-400">
          Halaman ini sedang dalam pengembangan untuk modul {title}.
        </p>
      </div>
      <div>
        <button className="flex items-center gap-1.5 px-4 py-2 rounded-full bg-amber-300 hover:bg-amber-400 text-gray-900 text-sm font-semibold shadow-lg shadow-amber-500/20 transition">
          Aksi Utama
        </button>
      </div>
    </div>
    
    <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
      {[1, 2, 3].map(i => (
        <div key={i} className="rounded-2xl border border-gray-100 bg-white p-6 dark:border-white/10 dark:bg-white/5 space-y-4">
          <div className="h-10 w-10 rounded-full bg-gray-100 dark:bg-white/10 flex items-center justify-center">
            <span className="text-gray-500">{i}</span>
          </div>
          <h3 className="font-semibold text-gray-900 dark:text-white">Sample Item {i}</h3>
          <p className="text-sm text-gray-500 dark:text-gray-400">Deskripsi konten halaman {title} akan ditampilkan di sini sebagai representasi UI.</p>
          <div className="h-2 w-full bg-gray-100 dark:bg-white/5 rounded-full overflow-hidden">
            <div className="h-full bg-orange-500 w-1/2"></div>
          </div>
        </div>
      ))}
    </div>
  </div>
);

const LandingPage = lazyRouteComponent(() => import('./views/LandingPage'), 'LandingPage');
const SkpDashboard = lazyRouteComponent(() => import('./views/SkpDashboard'), 'SkpDashboard');
const SkpProgres = lazyRouteComponent(() => import('./views/SkpProgres'), 'SkpProgres');
const SkpDaftar = lazyRouteComponent(() => import('./views/SkpDaftar'), 'SkpDaftar');
const SpkBastTable = lazyRouteComponent(() => import('./views/SpkBastTable'), 'SpkBastTable');
const Polink = lazyRouteComponent(() => import('./views/Polink'), 'Polink');
const DaftarKegiatan = lazyRouteComponent(() => import('./views/DaftarKegiatan'), 'DaftarKegiatan');
const PenugasanTable = lazyRouteComponent(() => import('./views/PenugasanTable'), 'PenugasanTable');
const PetugasTable = lazyRouteComponent(() => import('./views/PetugasTable'), 'PetugasTable');
const KegiatanMonitoring = lazyRouteComponent(() => import('./views/KegiatanMonitoring'), 'KegiatanMonitoring');
const MonitoringDetailConfig = lazyRouteComponent(() => import('./views/MonitoringDetailConfig'), 'MonitoringDetailConfig');
const MonitoringConfig = lazyRouteComponent(() => import('./views/MonitoringConfig'), 'MonitoringConfig');
const KegiatanKalender = lazyRouteComponent(() => import('./views/KegiatanKalender'), 'KegiatanKalender');
const KegiatanEvaluasi = lazyRouteComponent(() => import('./views/KegiatanEvaluasi'), 'KegiatanEvaluasi');
const KontraktualMonitoring = lazyRouteComponent(() => import('./views/KontraktualMonitoring'), 'KontraktualMonitoring');
const UmumPegawai = lazyRouteComponent(() => import('./views/UmumPegawai'), 'UmumPegawai');
const IpdsTiket = lazyRouteComponent(() => import('./views/IpdsTiket'), 'IpdsTiket');
const IpdsAsset = lazyRouteComponent(() => import('./views/IpdsAsset'), 'IpdsAsset');
const BankDataRaw = lazyRouteComponent(() => import('./views/BankDataRaw'), 'BankDataRaw');
const BankDataArsip = lazyRouteComponent(() => import('./views/BankDataArsip'), 'BankDataArsip');
const SuratKeluar = lazyRouteComponent(() => import('./views/UmumSurat'), 'SuratKeluar');
const SuratKeluarForm = lazyRouteComponent(() => import('./views/UmumSurat'), 'SuratKeluarForm');
const SuratTugas = lazyRouteComponent(() => import('./views/SuratTugasWorkflow'), 'SuratTugasWorkflow');
const SuratKeputusan = lazyRouteComponent(() => import('./views/SuratKeputusanWorkflow'), 'SuratKeputusanWorkflow');
const SuratPermintaan = lazyRouteComponent(() => import('./views/UmumSuratPermintaan'), 'SuratPermintaan');
const UmumLibur = lazyRouteComponent(() => import('./views/UmumLibur'), 'UmumLibur');
const UmumPengaturan = lazyRouteComponent(() => import('./views/UmumPengaturan'), 'UmumPengaturan');
const AdminPengguna = lazyRouteComponent(() => import('./views/AdminPengguna'), 'AdminPengguna');
const AdminPeran = lazyRouteComponent(() => import('./views/AdminPeran'), 'AdminPeran');
const AdminIzin = lazyRouteComponent(() => import('./views/AdminIzin'), 'AdminIzin');
const AdminMetadata = lazyRouteComponent(() => import('./views/AdminMetadata'), 'AdminMetadata');
const RkkDipaMonitoring = lazyRouteComponent(() => import('./views/RkkDipaMonitoring'), 'RkkDipaMonitoring');
const RkkDipaPerencanaan = lazyRouteComponent(() => import('./views/RkkDipaPerencanaan'), 'RkkDipaPerencanaan');
const RkkDipaPencairan = lazyRouteComponent(() => import('./views/RkkDipaPencairan'), 'RkkDipaPencairan');
const RkkDipaRiwayat = lazyRouteComponent(() => import('./views/RkkDipaRiwayat'), 'RkkDipaRiwayat');
const RkkDipaIntegritas = lazyRouteComponent(() => import('./views/RkkDipaIntegritas'), 'RkkDipaIntegritas');
const RkkDipaRevisiImport = lazyRouteComponent(() => import('./views/RkkDipaRevisiImport'), 'RkkDipaRevisiImport');
const LoginPage = lazyRouteComponent(() => import('./views/LoginPage'), 'LoginPage');
const ProfilPengguna = lazyRouteComponent(() => import('./views/ProfilPengguna'), 'ProfilPengguna');
const PengaturanPengguna = lazyRouteComponent(() => import('./views/PengaturanPengguna'), 'PengaturanPengguna');

// Index Route
const indexRoute = createRoute({
  getParentRoute: () => rootRoute,
  path: '/',
  component: LandingPage,
});

// Login Route
const loginRoute = createRoute({
  getParentRoute: () => rootRoute,
  path: '/login',
  component: LoginPage,
});

const profilRoute = createRoute({
  getParentRoute: () => rootRoute,
  path: '/profil',
  component: ProfilPengguna,
});

const pengaturanPenggunaRoute = createRoute({
  getParentRoute: () => rootRoute,
  path: '/pengaturan-pengguna',
  component: PengaturanPengguna,
});

// Map all routes from MODULES configuration
const moduleRoutes = MODULES.flatMap(module => 
  module.subMenus.flatMap(sub => {
    // Handle dropdown items
    if (sub.dropdown) {
      return sub.dropdown.map(d => {
        let dropComponent: RouteComponent = () => <Placeholder title={`${module.name} - ${d.name}`} />;
        if (d.path === '/umum/surat/keluar') dropComponent = SuratKeluar;
        if (d.path === '/umum/surat/tugas') dropComponent = SuratTugas;
        if (d.path === '/umum/surat/keputusan') dropComponent = SuratKeputusan;
        if (d.path === '/umum/surat/permintaan') dropComponent = SuratPermintaan;
        if (d.path === '/umum/rkk-dipa/monitoring') dropComponent = RkkDipaMonitoring;
        if (d.path === '/umum/rkk-dipa/perencanaan') dropComponent = RkkDipaPerencanaan;
        if (d.path === '/umum/rkk-dipa/pencairan') dropComponent = RkkDipaPencairan;
        if (d.path === '/umum/rkk-dipa/riwayat') dropComponent = RkkDipaRiwayat;
        if (d.path === '/umum/rkk-dipa/integritas') dropComponent = RkkDipaIntegritas;
        if (d.path === '/umum/rkk-dipa/revisi-import') dropComponent = RkkDipaRevisiImport;
        return createRoute({
          getParentRoute: () => rootRoute,
          path: d.path,
          component: dropComponent
        });
      });
    }
    
    // Use specific components for certain routes
    let component: RouteComponent = () => <Placeholder title={sub.name} />;
    
    if (sub.path === '/skp/dashboard') component = SkpDashboard;
    if (sub.path === '/skp/progres') component = SkpProgres;
    if (sub.path === '/skp/daftar') component = SkpDaftar;
    if (sub.path === '/kegiatan/monitoring') component = KegiatanMonitoring;
    if (sub.path === '/kegiatan/kalender') component = KegiatanKalender;
    if (sub.path === '/kegiatan/evaluasi') component = KegiatanEvaluasi;
    if (sub.path === '/kegiatan/daftar') component = DaftarKegiatan;
    if (sub.path === '/kegiatan/penugasan') component = PenugasanTable;
    if (sub.path === '/kegiatan/petugas') component = PetugasTable;
    if (sub.path === '/kontraktual/spk-bast') component = SpkBastTable;
    if (sub.path === '/kontraktual/monitoring') component = KontraktualMonitoring;
    if (sub.path === '/umum/polink') component = Polink;
    if (sub.path === '/umum/pegawai') component = UmumPegawai;
    if (sub.path === '/umum/libur') component = UmumLibur;
    if (sub.path === '/umum/pengaturan') component = UmumPengaturan;
    if (sub.path === '/ipds/tiket') component = IpdsTiket;
    if (sub.path === '/ipds/asset') component = IpdsAsset;
    if (sub.path === '/bank-data/raw') component = BankDataRaw;
    if (sub.path === '/bank-data/arsip') component = BankDataArsip;
    if (sub.path === '/admin/pengguna') component = AdminPengguna;
    if (sub.path === '/admin/peran') component = AdminPeran;
    if (sub.path === '/admin/izin') component = AdminIzin;
    if (sub.path === '/admin/metadata') component = AdminMetadata;

    return createRoute({
      getParentRoute: () => rootRoute,
      path: sub.path,
      component: component
    });
  })
);

const monitoringDetailConfigRoute = createRoute({
  getParentRoute: () => rootRoute,
  path: '/kegiatan/monitoring/detil-configs',
  component: MonitoringDetailConfig,
});

const monitoringConfigRoute = createRoute({
  getParentRoute: () => rootRoute,
  path: '/kegiatan/monitoring/config',
  component: MonitoringConfig,
});

const suratKeluarFormRoute = createRoute({
  getParentRoute: () => rootRoute,
  path: '/umum/surat/keluar/form',
  component: SuratKeluarForm,
});

export const routeTree = rootRoute.addChildren([
  indexRoute,
  loginRoute,
  profilRoute,
  pengaturanPenggunaRoute,
  monitoringDetailConfigRoute,
  monitoringConfigRoute,
  suratKeluarFormRoute,
  ...moduleRoutes
]);

export const router = createRouter({ routeTree });

declare module '@tanstack/react-router' {
  interface Register {
    router: typeof router;
  }
}
