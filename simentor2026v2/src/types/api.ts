// ============================================================
// Simentor v1.5 API Types
// Generated from laravel.postman API documentation
// ============================================================

// --- Common / Pagination ---

export interface PaginatedResponse<T> {
  data: T[];
  meta?: {
    current_page?: number;
    per_page?: number;
    has_more?: boolean;
    count?: number;
    last_page?: number;
    from?: number;
    to?: number;
    total?: number;
    path?: string;
  };
  links?: {
    first?: string | null;
    last?: string | null;
    prev?: string | null;
    next?: string | null;
    path?: string | null;
    next_cursor?: string | null;
    prev_cursor?: string | null;
    next_page_url?: string | null;
    prev_page_url?: string | null;
  };
  pagination_info?: {
    total_page: number;
    total_records: number;
  };
}

export interface CursorPaginatedResponse<T> extends PaginatedResponse<T> {}

// --- Auth ---

export interface Role {
  id: number;
  name: string;
  guard_name: string;
  created_at?: string;
  updated_at?: string;
  pivot?: {
    model_type: string;
    model_id: number;
    role_id: number;
  };
}

export interface Permission {
  id: number;
  name: string;
  guard_name: string;
  created_at?: string;
  updated_at?: string;
}

export interface AdminPermissionGroup {
  id: number;
  prefix: string;
  guard_name: string;
  permissions_count: number;
  permissions: Permission[];
}

export interface User {
  id: number;
  name: string;
  email: string;
  email_verified_at: string | null;
  current_team_id: number | null;
  profile_photo_path: string | null;
  created_at: string;
  updated_at: string;
  roles: Role[];
  permissions: Permission[];
  two_factor_confirmed_at?: string | null;
}

export interface LoginData {
  user: User;
  roles: Role[];
  permissions: Permission[];
  token: string;
}

export interface MeData {
  id: number;
  name: string;
  email: string;
  roles: string[];
  permissions: string[];
}

// --- Pegawai ---

export interface Pegawai {
  id: number;
  nama: string;
  pangkat: string | null;
  gol: string | null;
  nip: string | null;
  gelar_depan: string | null;
  gelar_belakang: string | null;
  tempat_lahir: string | null;
  tanggal_lahir: string | null;
  alamat: string | null;
  no_hp: string | null;
  jabatan: string | null;
  kelas: number | null;
  user_id: number | null;
  status: string | null;
  created_at: string;
  updated_at: string;
}

export interface PegawaiFilterOptions {
  jabatanList: string[];
  pangkatList: string[];
  golonganList: string[];
  statusList: string[];
}

export interface PegawaiFormOptions {
  jabatanOptions: string[];
  pangkatOptions: string[];
  golonganOptions: string[];
  statusOptions: string[];
}

// --- Mitra ---

export interface Mitra {
  id: number;
  nama_lengkap: string;
  nik: string | null;
  email?: string | null;
  no_telp?: string | null;
  alamat_detail?: string | null;
  keca?: string | null;
  desa?: string | null;
  posisi?: string | null;
  jenis_kelamin?: string | null;
  agama?: string | null;
  status_seleksi?: string | null;
  status_kawin?: string | null;
  pendidikan?: string | null;
  sobat_id?: string | null;
  tgl_lahir?: string | null;
  penugasan_count?: number;
  umur?: number | null;
  created_at?: string;
  updated_at?: string;
}

export interface MitraFilterOptions {
  kecList: string[];
  posisiList: string[];
  desaList: string[];
}

export interface MitraStatistics {
  total_mitra: number;
  mitra_aktif: number;
  rata_tugas: number;
  tugas_terbanyak: number;
}

export interface MitraPenugasanOptions {
  jabatanTugasOptions: JabatanTugasType[];
  kegiatanOptions: Array<{
    id: number;
    nama: string;
  }>;
}

// --- Kegiatan ---

export type FungsiType = 'Umum' | 'Distribusi' | 'Produksi' | 'Sosial' | 'Nerwilis' | 'IPDS';
export type JenisKegiatanType = 'PERSIAPAN' | 'PENGUMPULAN DATA' | 'PENGOLAHAN' | 'DISEMINASI' | 'PENGAWASAN/SUPERVISI';
export type SatuanType = string;
export type StatusKegiatanType = 'aktif' | 'tidak dicairkan' | 'dibatalkan';

export interface Kegiatan {
  id: number;
  tahun: string;
  fungsi: FungsiType;
  kode_kegiatan: string;
  nama: string;
  tgl_mulai: string;
  tgl_selesai: string;
  jenis_kegiatan: JenisKegiatanType;
  jml_ptgs: number;
  volume: number;
  satuan: SatuanType;
  rate_pcl: number | null;
  rate_pml: number | null;
  rate_entri: number | null;
  status: StatusKegiatanType | null;
  created_at: string;
  updated_at: string;
}

export interface KegiatanCreatePayload {
  tahun: string;
  fungsi: FungsiType;
  kode_kegiatan: string;
  nama: string;
  tgl_mulai: string;
  tgl_selesai: string;
  jenis_kegiatan: JenisKegiatanType;
  jml_ptgs: number;
  volume: number;
  satuan: string;
  rate_pcl?: number | null;
  rate_pml?: number | null;
  rate_entri?: number | null;
  status?: StatusKegiatanType | null;
}

export type KegiatanUpdatePayload = Partial<KegiatanCreatePayload>;

export interface KegiatanFormOptions {
  satuan: string[];
  fungsi: FungsiType[];
  jenis_kegiatan: JenisKegiatanType[];
  status: string[];
}

export interface KegiatanStatistics {
  kegiatan_by_fungsi: Array<{
    tahun: number;
    fungsi: string;
    total_anggaran: number;
    total_penyerapan: number;
    persen: number;
  }>;
  nilai_penugasan_by_month: Array<{
    month: string;
    total_nilai: number;
  }>;
  top_mitra_honor: Array<{
    nama_lengkap: string;
    keca: string;
    tahun: number;
    total_nilai: number;
  }>;
  penyerapan_by_kegiatan: Array<{
    kegiatan: string;
    fungsi: string;
    tahun: string;
    total_nilai: number;
  }>;
}

export interface KegiatanCalendarItem {
  id: number;
  fungsi: string;
  nama: string;
  tgl_mulai: string;
  tgl_selesai: string;
  volume: number;
  satuan: string;
  penugasan_sum_volume: number | null;
}

export interface KegiatanCalendarData {
  kegiatan_berjalan: KegiatanCalendarItem[];
  kegiatan_akan_datang: KegiatanCalendarItem[];
  kegiatan_sudah_selesai: KegiatanCalendarItem[];
}

// --- Penugasan ---

export type JabatanTugasType = 'PCL' | 'PML' | 'OPERATOR' | 'SUPERVISOR';

export interface Penugasan {
  id: number;
  kegiatan_id: number;
  jabatan_tugas: JabatanTugasType;
  pegawai_id: number | null;
  mitra_id: number | null;
  volume: number;
  nilai: number;
  bln_bayar: string;
  created_by: number;
  no_bast: string | null;
  tgl_bast: string | null;
  no_sk: string | null;
  tgl_sk: string | null;
  jangka_waktu_mulai: string | null;
  jangka_waktu_selesai: string | null;
  created_at: string;
  updated_at: string;
  kegiatan?: Kegiatan;
  mitra?: Mitra | null;
  pegawai?: Pegawai | null;
}

export interface PenugasanCreatePayload {
  kegiatan_id: number;
  jabatan_tugas: JabatanTugasType;
  mitra_id?: number | null;
  pegawai_id?: number | null;
  volume: number;
  nilai: number;
  bln_bayar: string;
  no_sk?: string | null;
  tgl_sk?: string | null;
  jangka_waktu_mulai?: string | null;
  jangka_waktu_selesai?: string | null;
}

export interface PenugasanFilterOptions {
  blnBayarList: string[];
  kegiatanList: Array<{
    id: number;
    nama: string;
  }>;
}

export interface PenugasanFormOptions {
  jabatanTugasOptions: JabatanTugasType[];
  fungsiOptions: Array<string | null>;
}

export interface PenugasanMitraOptions {
  mitraOptions: Array<{
    id: number;
    nama_lengkap: string;
    age: number | null;
  }>;
}

// --- BAST ---

export interface BastItem {
  id: number;
  mitra_id: number;
  bln_bayar: string;
  no_sk: string;
  no_bast: string;
  tgl_bast: string;
  total: number;
  jml_tugas: number;
  mitra: Mitra;
}

export interface BastDetail {
  penugasan: Penugasan;
  penugasan_mitra: Penugasan[];
  suggested_tgl_bast: string;
}

export interface BastUpdatePayload {
  no_bast: string | number;
  tgl_bast: string;
}

// --- SKP ---

export interface Skp {
  id: number;
  [key: string]: unknown;
}

export interface SkpDashboardSummary {
  total_pegawai: number;
  max_tahun_bulanan: string | null;
  last_skp_periode: string | null;
  skp_bulanan_by_role: Record<string, number>;
  skp_tetap_by_role: {
    tahun: string;
    data: Record<string, number>;
  };
  activities: Array<{
    id: number;
    nama: string;
    user_name: string;
    created_at: string;
  }>;
  skp_nilai_by_role: {
    tahun: string;
    data: Record<string, number>;
  };
  skp_bulan_by_month: {
    tahun: string;
    data: Record<string, number>;
  };
  skp_penilaian: {
    tahun: string;
    data: Record<string, number>;
  };
  skp_evaluasi: {
    tahun: string;
    data: Record<string, number>;
  };
  skp_penetapan: {
    tahun: string;
    data: Record<string, number>;
  };
}

export interface SkpCreatePayload {
  [key: string]: unknown;
}

// --- IPDS Assets ---

export interface MaintenanceSchedule {
  id: number;
  asset_id: number;
  next_maintenance: string;
  responsible_team: string;
  created_at: string;
  updated_at: string;
}

export interface Asset {
  id: number;
  kode_asset: string;
  type: 'hardware' | 'software';
  category: string;
  brand: string;
  model: string;
  serial_number: string;
  name: string | null;
  license_key: string | null;
  device: string | null;
  ip_address: string | null;
  location: string;
  status: string;
  assigned_to: string | null;
  purchase_date: string | null;
  warranty_expiry: string | null;
  expiry_date: string | null;
  delivery_date: string | null;
  purchase_value?: number;
  depreciation_value?: number;
  value_depreciation?: number;
  created_at: string;
  updated_at: string;
  maintenance_schedules: MaintenanceSchedule[];
}

// --- IPDS Tikets ---

export interface TiketUser {
  id: number;
  name: string;
  email: string;
  email_verified_at: string | null;
  current_team_id: number | null;
  profile_photo_path: string | null;
  created_at: string;
  updated_at: string;
  two_factor_confirmed_at?: string | null;
  pegawai?: Pegawai | null;
}

export interface Tiket {
  id: number;
  user_id: number;
  jenis_keluhan: string;
  deskripsi: string;
  status: string;
  keterangan: string | null;
  ditangani_oleh: number | null;
  created_at: string;
  updated_at: string;
  user?: TiketUser;
}

// --- IPDS Raw Data ---

export interface RawData {
  id: number;
  [key: string]: unknown;
}

// --- Surat ---

export interface SuratKeluar {
  id: number;
  bln?: string | null;
  thn?: string | null;
  tanggal?: string | null;
  nomor?: string | null;
  no_sisip?: string | null;
  tanggal_indo?: string | null;
  no_surat?: string | null;
  dari?: string | null;
  tujuan?: string | null;
  perihal?: string | null;
  isi_surat?: string | null;
  lampiran?: number | null;
  file?: string | null;
  tembusan?: string[] | null;
  sifat?: string | null;
  created_by?: number | null;
  created_at?: string | null;
  updated_at?: string | null;
}

export interface SuratKeluarSettingOption {
  id: number;
  tahun: string;
  key: string;
  value: string;
  grup: number | string;
  created_at?: string | null;
  updated_at?: string | null;
}

export interface SuratKeluarFormOptions {
  settings: SuratKeluarSettingOption[];
  nomor_baru: number | string;
}

export interface SuratTugas {
  id: number;
  tahun: string;
  tanggal: string;
  nomor: string;
  no_sisip: string | null;
  no_mix: string;
  no_surat: string;
  tanggal_indo: string;
  kode_klas: string;
  kepada: string;
  menimbang?: string | null;
  uraian?: string | null;
  file?: string | null;
  created_by?: number | null;
  created_at?: string;
  updated_at?: string;
}

export interface SuratTugasKlasifikasiOption {
  kode: string;
  keterangan: string;
}

export interface SuratTugasFormOptions {
  klasifikasi: SuratTugasKlasifikasiOption[];
  nomor_baru: string;
}

export interface SuratKeputusan {
  id: number;
  thn: string;
  tanggal: string;
  nomor: string;
  no_sisip: string | null;
  no_mix: string;
  no_surat: string;
  tanggal_indo: string;
  kode_klas: string | null;
  kepada: string;
  kegiatan: string;
  perihal: string;
  oleh: string;
  kol_lampiran: string;
  file?: string | null;
  created_by?: number | null;
  created_at?: string;
  updated_at?: string;
}

export interface SuratKeputusanFormOptions {
  klasifikasi: { kode: string; keterangan: string }[];
  nomor_baru_sk: string;
  pejabat: { id: number; nama: string; nip: string }[];
}

export interface SkDetil {
  id: number;
  sk_id: number;
  pegawai_id: number | null;
  mitra_id: number | null;
  penugasan_id: number | null;
  isOrganik: boolean;
  dasar?: string | null;
  nama_kegiatan?: string | null;
  tugas_sebagai?: string | null;
  jabatan_kegiatan?: string | null;
  hari?: number | null;
  wilayah_kerja?: string | null;
  tgl_mulai?: string | null;
  no_dipa?: string | null;
  detil?: Record<string, any> | string | null;
  created_at: string;
  updated_at: string;
  pegawai?: { id: number; nama: string; nip?: string | null; pangkat?: string | null; gol?: string | null } | null;
  mitra?: { id: number; nama_lengkap: string; keca?: string | null } | null;
  nomor?: { id: number; no_surat: string } | null;
  penugasan?: { id: number; kegiatan_id: number; jabatan_tugas: string; volume: number } | null;
}

export interface SkBulkMitraResponse {
  created: number;
  failed: number;
  details: SkDetil[];
  failed_details?: Array<{ mitra_id: number; error: string }>;
}

export interface SuratPermintaan {
  id: number;
  thn: string;
  bulan?: string | null;
  tanggal: string;
  nomor: string;
  no_sisip?: string | null;
  tanggal_indo?: string | null;
  kode_klas: string;
  no_surat: string;
  dari: string;
  perihal: string;
  created_by?: number | null;
  created_at?: string;
  updated_at?: string;
}

export interface SuratPermintaanFormOptions {
  klasifikasi: Array<{ kode: string; keterangan: string }>;
  nomor_baru: string;
}

export interface SurtugDetil {
  id: number;
  surtug_id: number;
  pegawai_id: number | null;
  mitra_id: number | null;
  grup_mitra: number | null;
  grup_pegawai: number | null;
  penugasan_id: number | null;
  dasar: string;
  nama_kegiatan: string;
  tugas_sebagai: string;
  hari: number;
  wilayah_kerja: string;
  tgl_mulai: string;
  jenis_kendaraan: string;
  no_dipa: string;
  isOrganik: boolean;
  sppd: boolean;
  created_at: string;
  updated_at: string;
  pegawai?: { id: number; nama: string; nip?: string | null; jabatan?: string | null; gol?: string | null } | null;
  mitra?: { id: number; nama_lengkap: string; sobat_id?: string | null; keca?: string | null } | null;
  nomor?: { id: number; no_surat: string } | null;
}

export interface SurtugKegiatanOption {
  id: number;
  tahun: string;
  nama: string;
}

export interface SurtugPegawaiOption {
  id: number;
  nama: string;
  nip?: string | null;
  jabatan?: string | null;
}

export interface SurtugMitraOption {
  id: number;
  nama_lengkap: string;
  keca?: string | null;
  sobat_id?: string | null;
}

export interface SurtugMitraPenugasanOption {
  id: number;
  mitra_id: number;
  mitra?: SurtugMitraOption | null;
}

export interface SurtugBulkMitraResponse {
  created: number;
  failed: number;
  details: SurtugDetil[];
  failed_details?: Array<{ mitra_id: number; error: string }>;
}

// --- Holidays ---

export interface Holiday {
  id: number;
  tanggal?: string;
  deskripsi?: string;
  created_at?: string;
  updated_at?: string;
  [key: string]: unknown;
}

// --- Monitoring ---

export interface MonitoringKegiatan {
  id: number;
  [key: string]: unknown;
}

export interface MonitoringKegiatanConfig {
  id: number;
  [key: string]: unknown;
}

export interface DetilConfiguration {
  id: number;
  [key: string]: unknown;
}

// --- Links ---

export interface Link {
  id: number;
  nama: string;
  kategori: string; // 'internal' | 'eksternal'
  link: string;
  deskripsi?: string | null;
  parent_id?: number | null;
  parent?: { id: number; nama: string } | null;
  children?: Link[];
  children_recursive?: Link[];
  created_at?: string;
  updated_at?: string;
}

// --- Settings ---

export interface Setting {
  id: number;
  tahun: string;
  key: string;
  value: string;
  grup: string | number;
  created_at: string;
  updated_at: string;
}

export interface SettingOfficer {
  nama: string;
  nip: string;
}

export interface SettingCreatePayload {
  tahun: string;
  key: string;
  value: string;
  grup: string | number;
}

export interface SettingUpdatePayload {
  tahun: string;
  value: string;
  grup: string | number;
}


// --- Laporan Perjalanan Dinass ---

export interface LaporanPerjalananDinas {
  id: number;
  [key: string]: unknown;
}

// --- Enums ---

export interface EnumsData {
  fungsi_types: FungsiType[];
  jabatan_tugas_types: JabatanTugasType[];
  jenis_kegiatan_types: JenisKegiatanType[];
  satuan_types: SatuanType[];
  pangkat_types: string[];
  golongan_types: string[];
  jabatan_types: string[];
}

// --- Admin ---

export interface AdminUsersListData {
  organik_data: User[];
  mitra_data: User[];
  roles: Role[];
  organik_meta: { per_page: number };
  mitra_meta: { per_page: number };
}

export interface AdminUserCreatePayload {
  name: string;
  email: string;
  password: string;
  roles?: string[];
  permissions?: string[];
}

export interface AdminUserUpdatePayload {
  name?: string;
  email?: string;
  password?: string;
  roles?: string[];
  permissions?: string[];
}

export interface AdminPermissionFormOptions {
  controllers: string[];
  actions: string[];
}

export interface AdminRolePermissionOptions {
  group: string;
  permissions: Permission[];
}

// --- Meta ---

export interface Meta {
  id: number;
  parent_id: number | null;
  name: string;
  name2: string | null;
  created_at?: string;
  updated_at?: string;
  children?: Meta[];
}

// --- SPK ---

export interface SpkUpdatePayload {
  no_sk?: string;
  tgl_sk?: string;
  jangka_waktu_mulai?: string;
  jangka_waktu_selesai?: string;
}

// --- Profile ---

export interface ProfileUpdatePayload {
  name?: string;
  email?: string;
}

export interface ProfilePasswordPayload {
  current_password: string;
  password: string;
  password_confirmation: string;
}

// --- DIPA Budget ---

export interface DipaImportFile {
  id: number;
  filename: string;
  file_path: string;
  file_hash: string;
  revision_name: string;
  tahun_anggaran?: number;
  imported_at: string;
  created_at: string;
  status: string;
}

export interface DipaBudgetItem {
  short_code: string;
  formatted_description: string;
  funding_source: string;
  composite_key: string;
  budget_item_key: string;
  total_pagu: number;
  relevance_score?: number;
}

export interface DipaMonitoringRow {
  composite_key: string;
  budget_item_key: string;
  formatted_description: string;
  total_pagu: number;
  total_realisasi: number;
  persentase_realisasi: number;
  output_name: string | null;
  component_name: string | null;
}

export interface DipaMonitoringSummaryChartItem {
  month_number: number;
  month_label: string;
  realisasi: number;
  rencana: number;
}

export interface DipaMonitoringSummary {
  year: number;
  current_month: string;
  total_budget_ceiling: number;
  total_budget_realization: number;
  remaining_budget: number;
  current_month_realisasi: number;
  current_month_rencana: number;
  chart: DipaMonitoringSummaryChartItem[];
}

export interface DipaUsageHistory {
  id: number;
  usage_date: string;
  created_at: string;
  budget_item_key: string;
  program_name: string | null;
  output_name: string | null;
  budget_item_at_time: string | null;
  description: string | null;
  usage_description: string | null;
  amount_spent: number;
  data_source: 'manual' | 'sakti';
}

export interface DipaOrphan {
  budget_item_key: string;
  last_description: string | null;
  total_orphan_amount: number;
  transaction_count: number;
}

export interface DipaBudgetPlan {
  id: number;
  budget_item_key: string;
  target_month: string;
  description: string | null;
  planned_amount: number;
  created_at: string;
  program_code: string | null;
  activity_code: string | null;
  account_code: string | null;
  budget_name: string | null;
}

export interface DipaRecordUsagePayload {
  budget_item_key: string;
  amount_spent: number;
  usage_date?: string;
  description?: string;
}

export interface DipaRecordPlanPayload {
  budget_item_key: string;
  planned_amount: number;
  target_month: string;
  description?: string;
}

export interface DipaUpdatePlanPayload {
  planned_amount: number;
  target_month: string;
  description?: string;
}

export interface DipaSaveMappingPayload {
  old_composite_key: string;
  new_composite_key: string;
}

export interface DipaImportResponse {
  filename: string;
  logs: string;
}

export interface DipaDependencyView {
  view_name: string;
  ok: boolean;
  detail: string;
}

export interface SaktiReconciliationItem {
  composite_key: string;
  formatted_description: string;
  total_sakti: number;
  total_internal: number;
  selisih: number;
  tahun_anggaran: number;
}

export interface SaktiImportResponse {
  filename: string;
  logs: string;
}
