import { 
  BarChart3, 
  Calendar, 
  FileText, 
  Settings, 
  Shield, 
  Database, 
  Laptop, 
  Users, 
  LayoutDashboard,
  ClipboardList,
  CheckCircle2,
  Clock,
  UserCheck,
  ClipboardCheck,
  Bell,
  Monitor,
  HardDrive,
  FileBox,
  UserPlus,
  Key,
  DatabaseZap,
  Link,
  Mail,
  Plane,
  FileCheck,
  Wallet,
  Eye,
  PenLine,
  Landmark,
  History,
  ShieldCheck,
  Upload
} from 'lucide-react';
import { ModuleMenu } from './types';

export const MODULES: ModuleMenu[] = [
  {
    id: 'skp',
    name: 'SKP',
    path: '/skp',
    subMenus: [
      { name: 'Dashboard', path: '/skp/dashboard', icon: LayoutDashboard },
      { name: 'Progres', path: '/skp/progres', icon: BarChart3 },
      { name: 'Daftar SKP', path: '/skp/daftar', icon: ClipboardList },
    ]
  },
  {
    id: 'kegiatan',
    name: 'Kegiatan',
    path: '/kegiatan',
    subMenus: [
      { name: 'Daftar Kegiatan', path: '/kegiatan/daftar', icon: ClipboardCheck },
      { name: 'Kalender', path: '/kegiatan/kalender', icon: Calendar },
      { name: 'Penugasan', path: '/kegiatan/penugasan', icon: UserCheck },
      { name: 'Petugas', path: '/kegiatan/petugas', icon: Users },
      { name: 'Evaluasi', path: '/kegiatan/evaluasi', icon: CheckCircle2 },
      { name: 'Monitoring', path: '/kegiatan/monitoring', icon: Clock },
    ]
  },
  {
    id: 'kontraktual',
    name: 'SPK & BAST',
    path: '/kontraktual',
    subMenus: [
      { name: 'Monitoring', path: '/kontraktual/monitoring', icon: Bell },
      { name: 'SPK & BAST', path: '/kontraktual/spk-bast', icon: FileCheck },
    ]
  },
  {
    id: 'umum',
    name: 'Umum',
    path: '/umum',
    subMenus: [
      { name: 'RKK DIPA', path: '/umum/rkk-dipa', icon: Wallet, dropdown: [
        { name: 'Monitoring', path: '/umum/rkk-dipa/monitoring', icon: Eye },
        { name: 'Perencanaan', path: '/umum/rkk-dipa/perencanaan', icon: PenLine },
        { name: 'Pencairan', path: '/umum/rkk-dipa/pencairan', icon: Landmark },
        { name: 'Riwayat Pencairan', path: '/umum/rkk-dipa/riwayat', icon: History },
        { name: 'Integritas Data', path: '/umum/rkk-dipa/integritas', icon: ShieldCheck },
        { name: 'Revisi dan Import Data', path: '/umum/rkk-dipa/revisi-import', icon: Upload },
      ]},
      { name: 'Surat', path: '/umum/surat', icon: Mail, dropdown: [
        { name: 'Surat Keluar', path: '/umum/surat/keluar' },
        { name: 'Surat Tugas', path: '/umum/surat/tugas' },
        { name: 'Surat Keputusan', path: '/umum/surat/keputusan' },
        { name: 'Surat Permintaan', path: '/umum/surat/permintaan' },
      ]},
      { name: 'Polink', path: '/umum/polink', icon: Link },
      { name: 'Pegawai', path: '/umum/pegawai', icon: Users },
      { name: 'Hari Libur', path: '/umum/libur', icon: Plane },
      { name: 'Pengaturan', path: '/umum/pengaturan', icon: Settings },
    ]
  },
  {
    id: 'ipds',
    name: 'IPDS',
    path: '/ipds',
    subMenus: [
      { name: 'Tiket', path: '/ipds/tiket', icon: Monitor },
      { name: 'Asset TI', path: '/ipds/asset', icon: HardDrive },
    ]
  },
  {
    id: 'bank-data',
    name: 'Bank Data',
    path: '/bank-data',
    subMenus: [
      { name: 'Raw Data', path: '/bank-data/raw', icon: DatabaseZap },
      { name: 'Arsip Data', path: '/bank-data/arsip', icon: FileBox },
    ]
  },
  {
    id: 'admin',
    name: 'Admin',
    path: '/admin',
    subMenus: [
      { name: 'Pengguna', path: '/admin/pengguna', icon: UserPlus },
      { name: 'Peran', path: '/admin/peran', icon: Shield },
      { name: 'Izin Akses', path: '/admin/izin', icon: Key },
      { name: 'Meta Data', path: '/admin/metadata', icon: Database },
    ]
  }
];
