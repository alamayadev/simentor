// ============================================================
// Simentor v1.5 API Services
// Organized by domain, using types from types/api.ts
// ============================================================

import { apiGet, apiPost, apiPut, apiPatch, apiDelete, apiPutForm, getToken, API_BASE_URL } from './api';
import type {
  User,
  Role,
  Permission,
  Kegiatan,
  KegiatanCreatePayload,
  KegiatanUpdatePayload,
  Penugasan,
  PenugasanCreatePayload,
  PenugasanFilterOptions,
  PenugasanFormOptions,
  PenugasanMitraOptions,
  BastItem,
  BastDetail,
  BastUpdatePayload,
  Asset,
  Tiket,
  SurtugDetil,
  EnumsData,
  PaginatedResponse,
  AdminUsersListData,
  AdminUserCreatePayload,
  AdminUserUpdatePayload,
  AdminPermissionGroup,
  AdminPermissionFormOptions,
  AdminRolePermissionOptions,
  Skp,
  SpkUpdatePayload,
  Pegawai,
  PegawaiFilterOptions,
  PegawaiFormOptions,
  Mitra,
  MitraFilterOptions,
  MitraPenugasanOptions,
  MitraStatistics,
  Holiday,
  Link,
  Setting,
  SettingOfficer,
  SettingCreatePayload,
  SettingUpdatePayload,
  MonitoringKegiatan,
  MonitoringKegiatanConfig,
  DetilConfiguration,
  LaporanPerjalananDinas,
  RawData,
  SuratKeluar,
  SuratKeluarFormOptions,
  SuratTugas,
  SuratTugasFormOptions,
  SuratTugasKlasifikasiOption,
  SuratKeputusan,
  SuratKeputusanFormOptions,
  SkDetil,
  SuratPermintaan,
  SuratPermintaanFormOptions,
  SkBulkMitraResponse,
  SurtugBulkMitraResponse,
  SurtugKegiatanOption,
  SurtugMitraOption,
  SurtugMitraPenugasanOption,
  SurtugPegawaiOption,
  ProfileUpdatePayload,
  ProfilePasswordPayload,
  KegiatanCalendarData,
  SkpDashboardSummary,
  Meta,
} from '../types/api';

// ============================================================
// Query parameter helper
// ============================================================
type QueryParams = Record<string, string | number | boolean | undefined>;

async function apiDownloadBlob(path: string, accept = 'application/octet-stream, */*'): Promise<Blob> {
  const token = getToken();
  const response = await fetch(`${API_BASE_URL}${path}`, {
    method: 'GET',
    headers: {
      Accept: accept,
      ...(token ? { Authorization: `Bearer ${token}` } : {}),
    },
  });

  if (!response.ok) {
    const text = await response.text();
    throw new Error(text || 'Download gagal');
  }

  return response.blob();
}

// ============================================================
// Enums
// ============================================================
export const enumsService = {
  getAll: () => apiGet<EnumsData>('/enums'),
};

// ============================================================
// Auth / Profile
// ============================================================
export const profileService = {
  get: () => apiGet<User>('/profile'),
  update: (data: ProfileUpdatePayload) => apiPut<User>('/profile', data),
  updatePassword: (data: ProfilePasswordPayload) => apiPut<null>('/profile/password', data),
  getPegawai: () => apiGet<Pegawai>('/profile/pegawai'),
  updatePegawai: (data: Partial<Pegawai>) => apiPut<Pegawai>('/profile/pegawai', data),
};

// ============================================================
// Admin - Users
// ============================================================
export const adminUsersService = {
  list: (params?: QueryParams) => apiGet<AdminUsersListData>('/admin/users', params),
  listOrganik: (params?: QueryParams) => apiGet<PaginatedResponse<User>>('/admin/users/organik', params),
  listMitra: (params?: QueryParams) => apiGet<PaginatedResponse<User>>('/admin/users/mitra', params),
  get: (id: number) => apiGet<User>(`/admin/users/${id}`),
  create: (data: AdminUserCreatePayload) => apiPost<User>('/admin/users', data),
  update: (id: number, data: AdminUserUpdatePayload) => apiPut<User>(`/admin/users/${id}`, data),
  updatePermissions: (id: number, data: { permissions: string[] }) => apiPut<null>(`/admin/users/${id}/permissions`, data),
  delete: (id: number) => apiDelete<null>(`/admin/users/${id}`),
  bulkUpdateRoles: (data: { user_ids: number[]; roles: string[] }) => apiPost<null>('/admin/users/bulk-update-roles', data),
};

// ============================================================
// Admin - Roles
// ============================================================
export const adminRolesService = {
  list: (params?: QueryParams) => apiGet<PaginatedResponse<Role>>('/admin/roles', params),
  get: (id: number) => apiGet<Role>(`/admin/roles/${id}`),
  create: (data: { name: string }) => apiPost<Role>('/admin/roles', data),
  update: (id: number, data: { name: string }) => apiPut<Role>(`/admin/roles/${id}`, data),
  updatePermissions: (id: number, data: { permission_ids: number[] }) => apiPut<Role>(`/admin/roles/${id}/permissions`, data),
  delete: (id: number) => apiDelete<null>(`/admin/roles/${id}`),
  permissionOptions: (params?: QueryParams) => apiGet<AdminRolePermissionOptions[]>('/admin/roles/permission-options', params),
};

// ============================================================
// Admin - Permissions
// ============================================================
export const adminPermissionsService = {
  list: (params?: QueryParams) => apiGet<PaginatedResponse<Permission>>('/admin/permissions', params),
  groupedList: (params?: QueryParams) => apiGet<PaginatedResponse<AdminPermissionGroup>>('/admin/permissions/grouped-prefixes', params),
  get: (id: number) => apiGet<Permission>(`/admin/permissions/${id}`),
  create: (data: { name: string }) => apiPost<Permission>('/admin/permissions', data),
  bulkCreate: (data: { prefix: string }) => apiPost<{ created: Permission[]; existing: Permission[] }>('/admin/permissions/bulk-create', data),
  update: (id: number, data: { name: string }) => apiPut<Permission>(`/admin/permissions/${id}`, data),
  delete: (id: number) => apiDelete<null>(`/admin/permissions/${id}`),
  formOptions: () => apiGet<AdminPermissionFormOptions>('/admin/permissions/form-options'),
  listApiControllers: () => apiGet<AdminPermissionFormOptions>('/admin/permissions/list-api-controllers'),
};

// ============================================================
// Admin - Metas
// ============================================================
export const adminMetasService = {
  tree: () => apiGet<Meta[]>('/admin/metas/tree'),
  list: (params?: QueryParams) => apiGet<PaginatedResponse<Meta>>('/admin/metas', params),
  get: (id: number) => apiGet<Meta>(`/admin/metas/${id}`),
  create: (data: Partial<Meta>) => apiPost<Meta>('/admin/metas', data),
  update: (id: number, data: Partial<Meta>) => apiPut<Meta>(`/admin/metas/${id}`, data),
  delete: (id: number) => apiDelete<null>(`/admin/metas/${id}`),
};

// ============================================================
// Kegiatan
// ============================================================
export const kegiatanService = {
  list: (params?: QueryParams) => apiGet<PaginatedResponse<Kegiatan>>('/kantor/kegiatan', params),
  get: (id: number) => apiGet<Kegiatan>(`/kantor/kegiatan/${id}`),
  create: (data: KegiatanCreatePayload) => apiPost<Kegiatan>('/kantor/kegiatan', data),
  update: (id: number, data: KegiatanUpdatePayload) => apiPut<Kegiatan>(`/kantor/kegiatan/${id}`, data),
  delete: (id: number) => apiDelete<null>(`/kantor/kegiatan/${id}`),
  formOptions: () => apiGet<Record<string, unknown>>('/kantor/kegiatan/form-options'),
  statistics: () => apiGet<Record<string, unknown>>('/kantor/kegiatan/statistics'),
  calendar: (params?: QueryParams) => apiGet<KegiatanCalendarData>('/kantor/kegiatan/calendar', params),
  filterList: () => apiGet<Record<string, string[]>>('/kantor/kegiatan/filter-list'),
};

// ============================================================
// Penugasan
// ============================================================
export const penugasanService = {
  list: (params?: QueryParams) => apiGet<PaginatedResponse<Penugasan>>('/kantor/penugasan', params),
  get: (id: number) => apiGet<Penugasan>(`/kantor/penugasan/${id}`),
  create: (data: PenugasanCreatePayload) => apiPost<Penugasan>('/kantor/penugasan', data),
  insert: (data: PenugasanCreatePayload) => apiPost<Penugasan>('/kantor/penugasan/insert', data),
  update: (id: number, data: Partial<PenugasanCreatePayload>) => apiPut<Penugasan>(`/kantor/penugasan/${id}`, data),
  delete: (id: number) => apiDelete<null>(`/kantor/penugasan/${id}`),
  filters: () => apiGet<PenugasanFilterOptions>('/kantor/penugasan/filters'),
  formOptions: () => apiGet<PenugasanFormOptions>('/kantor/penugasan/form-options'),
  mitraOptions: () => apiGet<PenugasanMitraOptions>('/kantor/penugasan/mitra-options'),
  kegiatanOptions: (fungsi: string) => apiGet<{ kegiatanOptions: Array<{ id: number; nama: string }> }>('/kantor/penugasan/kegiatan-options', { fungsi }),
};

// ============================================================
// BAST
// ============================================================
export const bastService = {
  list: (params?: QueryParams) => apiGet<PaginatedResponse<BastItem>>('/kantor/bast', params),
  get: (id: number) => apiGet<BastDetail>(`/kantor/bast/${id}`),
  update: (id: number, data: BastUpdatePayload) => apiPut<null>(`/kantor/bast/${id}`, data),
  availableMonths: () => apiGet<{ data: string[] }>('/kantor/bast/available-months'),
};

// ============================================================
// SPK
// ============================================================
export const spkService = {
  updateByMitraBulan: (mitraId: number, blnBayar: string, data: SpkUpdatePayload) =>
    apiPut<null>(`/kantor/spk/${mitraId}/${blnBayar}`, data),
  bulkUpdate: (data: SpkUpdatePayload & { ids?: number[] }) =>
    apiPut<null>('/kantor/spk/bulk-update', data),
  bulkUpdateBast: (data: { no_bast?: string; tgl_bast?: string; ids?: number[] }) =>
    apiPut<null>('/kantor/spk/bulk-update-bast', data),
};

// ============================================================
// SKP
// ============================================================
export const skpService = {
  dashboard: () => apiGet<SkpDashboardSummary>('/kantor/skp/dashboard'),
  list: (params?: QueryParams) => apiGet<PaginatedResponse<Skp>>('/kantor/skp', params),
  get: (id: number) => apiGet<Skp>(`/kantor/skp/${id}`),
  create: (data: Record<string, unknown>) => apiPost<Skp>('/kantor/skp', data),
  update: (id: number, data: Record<string, unknown>) => apiPut<Skp>(`/kantor/skp/${id}`, data),
  delete: (id: number) => apiDelete<null>(`/kantor/skp/${id}`),
};

// ============================================================
// IPDS - Assets
// ============================================================
export const assetService = {
  list: (params?: QueryParams) => apiGet<PaginatedResponse<Asset>>('/ipds/assets', params),
  get: (id: number) => apiGet<Asset>(`/ipds/assets/${id}`),
  create: (data: Record<string, unknown>) => apiPost<Asset>('/ipds/assets', data),
  update: (id: number, data: Record<string, unknown>) => apiPut<Asset>(`/ipds/assets/${id}`, data),
  patch: (id: number, data: Record<string, unknown>) => apiPatch<Asset>(`/ipds/assets/${id}`, data),
  delete: (id: number) => apiDelete<null>(`/ipds/assets/${id}`),
  filters: () => apiGet<Record<string, string[]>>('/ipds/assets/filters'),
  statistics: () => apiGet<{
    total_aset: number;
    status_counts: { baik: number; 'rusak berat': number; 'rusak ringan': number; [key: string]: number };
  }>('/ipds/assets/statistics'),
};

// ============================================================
// IPDS - Tikets
// ============================================================
export const tiketService = {
  list: (params?: QueryParams) => apiGet<Tiket[]>('/ipds/tikets', params),
  get: (id: number) => apiGet<Tiket>(`/ipds/tikets/${id}`),
  create: (data: Record<string, unknown>) => apiPost<Tiket>('/ipds/tikets', data),
  update: (id: number, data: Record<string, unknown>) => apiPut<Tiket>(`/ipds/tikets/${id}`, data),
  delete: (id: number) => apiDelete<null>(`/ipds/tikets/${id}`),
  statistics: () => apiGet<{ total_tiket: number; status_counts: Record<string, number> }>('/ipds/tikets/statistics'),
};

// ============================================================
// IPDS - Raw Data
// ============================================================
export const rawDataService = {
  list: (params?: QueryParams) => apiGet<PaginatedResponse<RawData>>('/ipds/raw-datas', params),
  get: (id: number) => apiGet<RawData>(`/ipds/raw-datas/${id}`),
  create: (data: FormData | Record<string, unknown>) =>
    'append' in (data as FormData)
      ? apiPost<RawData>('/ipds/raw-datas', data as FormData)
      : apiPost<RawData>('/ipds/raw-datas', data),
  update: (id: number, data: FormData | Record<string, unknown>) =>
    'append' in (data as FormData)
      ? apiPut<RawData>(`/ipds/raw-datas/${id}`, data as FormData)
      : apiPut<RawData>(`/ipds/raw-datas/${id}`, data),
  delete: (id: number) => apiDelete<null>(`/ipds/raw-datas/${id}`),
};

// ============================================================
// IPDS - Asset Maintenance Schedule
// ============================================================
export const maintenanceScheduleService = {
  list: (params?: QueryParams) => apiGet<PaginatedResponse<unknown>>('/ipds/asset-it-maintenance-schedule', params),
  get: (id: number) => apiGet<unknown>(`/ipds/asset-it-maintenance-schedule/${id}`),
  create: (data: Record<string, unknown>) => apiPost<unknown>('/ipds/asset-it-maintenance-schedule', data),
  update: (id: number, data: Record<string, unknown>) => apiPut<unknown>(`/ipds/asset-it-maintenance-schedule/${id}`, data),
  patch: (id: number, data: Record<string, unknown>) => apiPatch<unknown>(`/ipds/asset-it-maintenance-schedule/${id}`, data),
  delete: (id: number) => apiDelete<null>(`/ipds/asset-it-maintenance-schedule/${id}`),
};

// ============================================================
// Kantor - Pegawai
// ============================================================
export const pegawaiService = {
  list: (params?: QueryParams) => apiGet<PaginatedResponse<Pegawai>>('/kantor/pegawai', params),
  get: (id: number) => apiGet<Pegawai>(`/kantor/pegawai/${id}`),
  create: (data: Record<string, unknown>) => apiPost<Pegawai>('/kantor/pegawai', data),
  update: (id: number, data: Record<string, unknown>) => apiPut<Pegawai>(`/kantor/pegawai/${id}`, data),
  delete: (id: number) => apiDelete<null>(`/kantor/pegawai/${id}`),
  filters: () => apiGet<PegawaiFilterOptions>('/kantor/pegawai/filters'),
  formOptions: () => apiGet<PegawaiFormOptions>('/kantor/pegawai/form-options'),
};

// ============================================================
// Kantor - Mitra
// ============================================================
export const mitraService = {
  list: (params?: QueryParams) => apiGet<PaginatedResponse<Mitra>>('/kantor/mitra', params),
  filters: (params?: QueryParams) => apiGet<MitraFilterOptions>('/kantor/mitra/filters', params),
  statistics: () => apiGet<MitraStatistics>('/kantor/mitra/statistics'),
  penugasanOptions: () => apiGet<MitraPenugasanOptions>('/kantor/mitra/penugasan-options'),
  createPenugasan: (data: Pick<PenugasanCreatePayload, 'kegiatan_id' | 'jabatan_tugas' | 'mitra_id' | 'volume' | 'bln_bayar'>) =>
    apiPost<Penugasan>('/kantor/mitra/penugasan', data),
  get: (id: number) => apiGet<Mitra>(`/kantor/mitra/${id}`),
  create: (data: Record<string, unknown>) => apiPost<Mitra>('/kantor/mitra', data),
  update: (id: number, data: Record<string, unknown>) => apiPut<Mitra>(`/kantor/mitra/${id}`, data),
  delete: (id: number) => apiDelete<null>(`/kantor/mitra/${id}`),
};

// ============================================================
// Kantor - Holidays
// ============================================================
export const holidayService = {
  list: (params?: QueryParams) => apiGet<PaginatedResponse<Holiday>>('/kantor/holidays', params),
  get: (id: number) => apiGet<Holiday>(`/kantor/holidays/${id}`),
  create: (data: Record<string, unknown>) => apiPost<Holiday>('/kantor/holidays', data),
  update: (id: number, data: Record<string, unknown>) => apiPut<Holiday>(`/kantor/holidays/${id}`, data),
  delete: (id: number) => apiDelete<null>(`/kantor/holidays/${id}`),
};

// ============================================================
// Kantor - Links
// ============================================================
export const linkService = {
  list: (params?: QueryParams) => apiGet<PaginatedResponse<Link>>('/kantor/links', params),
  get: (id: number) => apiGet<Link>(`/kantor/links/${id}`),
  create: (data: Record<string, unknown>) => apiPost<Link>('/kantor/links', data),
  update: (id: number, data: Record<string, unknown>) => apiPut<Link>(`/kantor/links/${id}`, data),
  delete: (id: number) => apiDelete<null>(`/kantor/links/${id}`),
};

// ============================================================
// Kantor - Settings
// ============================================================
export const settingService = {
  list: (params?: QueryParams) => apiGet<Record<string, Setting[]>>('/kantor/settings', params),
  get: (id: number) => apiGet<Setting>(`/kantor/settings/${id}`),
  getByKey: (key: string) => apiGet<Setting>(`/kantor/settings/key/${key}`),
  create: (data: SettingCreatePayload) => apiPost<Setting>('/kantor/settings', data),
  update: (id: number, data: SettingUpdatePayload) => apiPut<Setting>(`/kantor/settings/${id}`, data),
  delete: (id: number) => apiDelete<null>(`/kantor/settings/${id}`),
  listGroups: () => apiGet<string[]>('/kantor/settings/grup-list'),
  listOfficers: () => apiGet<SettingOfficer[]>('/kantor/settings/officers'),
};

// ============================================================
// Kantor - Monitoring Kegiatan
// ============================================================
export const monitoringKegiatanService = {
  list: (params?: QueryParams) => apiGet<PaginatedResponse<MonitoringKegiatan>>('/kantor/kegiatan/monitoring', params),
  get: (id: number) => apiGet<MonitoringKegiatan>(`/kantor/kegiatan/monitoring/${id}`),
  create: (data: Record<string, unknown>) => apiPost<MonitoringKegiatan>('/kantor/kegiatan/monitoring', data),
  update: (id: number, data: Record<string, unknown>) => apiPut<MonitoringKegiatan>(`/kantor/kegiatan/monitoring/${id}`, data),
  delete: (id: number) => apiDelete<null>(`/kantor/kegiatan/monitoring/${id}`),
  kegiatanOptions: (fungsi?: string) => apiGet<{ id: number; nama: string }[]>('/kantor/kegiatan/monitoring/kegiatan-options', fungsi ? { fungsi } : undefined),
  filters: () => apiGet<{ fungsi: string[]; kegiatan: { id: number; nama: string }[] }>('/kantor/kegiatan/monitoring/filters-options'),
  allKegiatanOptions: () => apiGet<{ id: string; nama: string; fungsi: string }[]>('/kantor/kegiatan/monitoring/all-kegiatan-options'),
  kecOptions: () => apiGet<{ id: string; nama: string }[]>('/kantor/kegiatan/monitoring/kec-options'),
  desaOptions: (kec_id: string) => apiGet<{ id: string; nama: string }[]>('/kantor/kegiatan/monitoring/desa-options', { kec_id }),
  petugasOptions: (kegiatan_id: string | number) => apiGet<{ id: number; nama: string; type: string }[]>('/kantor/kegiatan/monitoring/petugas-options', { kegiatan_id }),
};

export const monitoringKegiatanConfigService = {
  list: (params?: QueryParams) => apiGet<PaginatedResponse<MonitoringKegiatanConfig>>('/kantor/kegiatan/monitoring/monitoring-kegiatan-config', params),
  create: (data: Record<string, unknown>) => apiPost<MonitoringKegiatanConfig>('/kantor/kegiatan/monitoring/monitoring-kegiatan-config', data),
  update: (id: number, data: Record<string, unknown>) => apiPut<MonitoringKegiatanConfig>(`/kantor/kegiatan/monitoring/monitoring-kegiatan-config/${id}`, data),
  patch: (id: number, data: Record<string, unknown>) => apiPatch<MonitoringKegiatanConfig>(`/kantor/kegiatan/monitoring/monitoring-kegiatan-config/${id}`, data),
};

export const detilConfigurationService = {
  list: (params?: QueryParams) => apiGet<PaginatedResponse<DetilConfiguration>>('/kantor/kegiatan/monitoring/detil-configurations', params),
  create: (data: Record<string, unknown>) => apiPost<DetilConfiguration>('/kantor/kegiatan/monitoring/detil-configurations', data),
  update: (id: number, data: Record<string, unknown>) => apiPut<DetilConfiguration>(`/kantor/kegiatan/monitoring/detil-configurations/${id}`, data),
};

// ============================================================
// Kantor - Surat
// ============================================================
export const suratKeluarService = {
  list: (params?: QueryParams) => apiGet<PaginatedResponse<SuratKeluar> | SuratKeluar[]>('/kantor/surat/surat-keluar', params),
  dates: (params?: QueryParams) => apiGet<string[]>('/kantor/surat/surat-keluar/dates', params),
  years: () => apiGet<string[]>('/kantor/surat/surat-keluar/years'),
  formOptions: (params?: QueryParams) => apiGet<SuratKeluarFormOptions>('/kantor/surat/surat-keluar/form-options', params),
  get: (id: number) => apiGet<SuratKeluar>(`/kantor/surat/surat-keluar/${id}`),
  create: (data: Record<string, unknown>) => apiPost<SuratKeluar>('/kantor/surat/surat-keluar', data),
  insert: (data: Record<string, unknown>) => apiPost<SuratKeluar>('/kantor/surat/surat-keluar/sisip', data),
  update: (id: number, data: Record<string, unknown>) => apiPut<SuratKeluar>(`/kantor/surat/surat-keluar/${id}`, data),
  delete: (id: number) => apiDelete<null>(`/kantor/surat/surat-keluar/${id}`),
};

export const suratTugasService = {
  list: (params?: QueryParams) => apiGet<PaginatedResponse<SuratTugas> | SuratTugas[]>('/kantor/surat/surat-tugas', params),
  dates: (params?: QueryParams) => apiGet<string[]>('/kantor/surat/surat-tugas/dates', params),
  years: () => apiGet<string[]>('/kantor/surat/surat-tugas/years'),
  klasifikasi: () => apiGet<SuratTugasKlasifikasiOption[]>('/kantor/surat/surat-tugas/klasifikasi'),
  formOptions: () => apiGet<SuratTugasFormOptions>('/kantor/surat/surat-tugas/form-options'),
  get: (id: number) => apiGet<SuratTugas>(`/kantor/surat/surat-tugas/${id}`),
  create: (data: Record<string, unknown>) => apiPost<SuratTugas>('/kantor/surat/surat-tugas', data),
  insert: (data: Record<string, unknown>) => apiPost<SuratTugas>('/kantor/surat/surat-tugas/sisip', data),
  update: (id: number, data: FormData | Record<string, unknown>) =>
    data instanceof FormData
      ? apiPutForm<SuratTugas>(`/kantor/surat/surat-tugas/${id}`, data)
      : apiPut<SuratTugas>(`/kantor/surat/surat-tugas/${id}`, data),
  delete: (id: number) => apiDelete<null>(`/kantor/surat/surat-tugas/${id}`),
  downloadDocx: (path: string) =>
    apiDownloadBlob(path, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document, application/octet-stream, */*'),
};

export const suratKeputusanService = {
  list: (params?: QueryParams) => apiGet<PaginatedResponse<SuratKeputusan>>('/kantor/surat/surat-keputusan', params),
  dates: (params?: QueryParams) => apiGet<string[]>('/kantor/surat/surat-keputusan/dates', params),
  years: () => apiGet<string[]>('/kantor/surat/surat-keputusan/years'),
  formOptions: () => apiGet<SuratKeputusanFormOptions>('/kantor/surat/surat-keputusan/form-options'),
  get: (id: number) => apiGet<SuratKeputusan>(`/kantor/surat/surat-keputusan/${id}`),
  create: (data: Record<string, unknown>) => apiPost<SuratKeputusan>('/kantor/surat/surat-keputusan', data),
  insert: (data: Record<string, unknown>) => apiPost<SuratKeputusan>('/kantor/surat/surat-keputusan/sisip', data),
  update: (id: number, data: Record<string, unknown>) => apiPut<SuratKeputusan>(`/kantor/surat/surat-keputusan/${id}`, data),
  delete: (id: number) => apiDelete<null>(`/kantor/surat/surat-keputusan/${id}`),
  downloadDocx: (path: string) =>
    apiDownloadBlob(path, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document, application/octet-stream, */*'),
};

export const suratPermintaanService = {
  list: (params?: QueryParams) => apiGet<SuratPermintaan[]>('/kantor/surat/permintaan', params),
  get: (id: number) => apiGet<SuratPermintaan>(`/kantor/surat/permintaan/${id}`),
  create: (data: Record<string, unknown>) => apiPost<SuratPermintaan>('/kantor/surat/permintaan', data),
  insert: (data: Record<string, unknown>) => apiPost<SuratPermintaan>('/kantor/surat/permintaan/sisip', data),
  update: (id: number, data: Record<string, unknown>) => apiPut<SuratPermintaan>(`/kantor/surat/permintaan/${id}`, data),
  bulkUpdateStatus: (data: { ids: number[]; status: string }) => apiPost<null>('/kantor/surat/permintaan/bulk-update-status', data),
  delete: (id: number) => apiDelete<null>(`/kantor/surat/permintaan/${id}`),
  years: () => apiGet<string[]>('/kantor/surat/permintaan/years'),
  formOptions: () => apiGet<SuratPermintaanFormOptions>('/kantor/surat/permintaan/form-options'),
};

export const surtugDetilService = {
  list: (params?: QueryParams) => apiGet<PaginatedResponse<SurtugDetil>>('/kantor/surat/surtug-detil', params),
  get: (id: number) => apiGet<SurtugDetil>(`/kantor/surat/surtug-detil/${id}`),
  getBySurtug: (surtugId: number) => apiGet<SurtugDetil[]>(`/kantor/surat/surtug-detil/surtug/${surtugId}`),
  create: (data: Record<string, unknown>) => apiPost<SurtugDetil>('/kantor/surat/surtug-detil', data),
  insert: (data: Record<string, unknown>) => apiPost<SurtugDetil>('/kantor/surat/surtug-detil/insert', data),
  bulkMitra: (data: Record<string, unknown>) => apiPost<SurtugBulkMitraResponse>('/kantor/surat/surtug-detil/bulk-mitra', data),
  update: (id: number, data: Record<string, unknown>) => apiPut<SurtugDetil>(`/kantor/surat/surtug-detil/${id}`, data),
  delete: (id: number) => apiDelete<null>(`/kantor/surat/surtug-detil/${id}`),
  kegiatanOptions: (params?: QueryParams) => apiGet<SurtugKegiatanOption[]>('/kantor/surat/surtug-detil/kegiatan-options', params),
  mitraPenugasanOptions: (kegiatanId: number) => apiGet<SurtugMitraPenugasanOption[]>(`/kantor/surat/surtug-detil/mitra-penugasan-options`, { kegiatan_id: kegiatanId }),
  pegawaiOptions: () => apiGet<SurtugPegawaiOption[]>('/kantor/surat/surtug-detil/pegawai-options'),
  mitraOptions: (search?: string) => apiGet<SurtugMitraOption[]>('/kantor/surat/surtug-detil/mitra-options', { search }),
};

export const skDetilService = {
  list: (params?: QueryParams) => apiGet<PaginatedResponse<SkDetil>>('/kantor/surat/sk-detil', params),
  get: (id: number) => apiGet<SkDetil>(`/kantor/surat/sk-detil/${id}`),
  getBySkId: (skId: number) => apiGet<SkDetil[]>(`/kantor/surat/sk-detil/sk/${skId}`),
  create: (data: Record<string, unknown>) => apiPost<SkDetil>('/kantor/surat/sk-detil', data),
  bulkMitra: (data: Record<string, unknown>) => apiPost<SkBulkMitraResponse>('/kantor/surat/sk-detil/bulk-mitra', data),
  update: (id: number, data: Record<string, unknown>) => apiPut<SkDetil>(`/kantor/surat/sk-detil/${id}`, data),
  delete: (id: number) => apiDelete<null>(`/kantor/surat/sk-detil/${id}`),
  kegiatanOptions: (params?: QueryParams) => apiGet<SurtugKegiatanOption[]>('/kantor/surat/sk-detil/kegiatan-options', params),
  mitraPenugasanOptions: (kegiatanId: number) => apiGet<SurtugMitraPenugasanOption[]>(`/kantor/surat/sk-detil/mitra-penugasan-options`, { kegiatan_id: kegiatanId }),
  pegawaiOptions: () => apiGet<SurtugPegawaiOption[]>('/kantor/surat/sk-detil/pegawai-options'),
  mitraOptions: (search?: string) => apiGet<SurtugMitraOption[]>('/kantor/surat/sk-detil/mitra-options', { search }),
};

// ============================================================
// Kantor - Laporan Perjalanan Dinas
// ============================================================
export const laporanPerjalananDinasService = {
  list: (params?: QueryParams) => apiGet<PaginatedResponse<LaporanPerjalananDinas>>('/kantor/laporan-perjalanan-dinas', params),
  get: (id: number) => apiGet<LaporanPerjalananDinas>(`/kantor/laporan-perjalanan-dinas/${id}`),
  create: (data: Record<string, unknown>) => apiPost<LaporanPerjalananDinas>('/kantor/laporan-perjalanan-dinas', data),
  update: (id: number, data: Record<string, unknown>) => apiPut<LaporanPerjalananDinas>(`/kantor/laporan-perjalanan-dinas/${id}`, data),
  delete: (id: number) => apiDelete<null>(`/kantor/laporan-perjalanan-dinas/${id}`),
  storeDetail: (id: number, data: Record<string, unknown>) => apiPost<unknown>(`/kantor/laporan-perjalanan-dinas/${id}/details`, data),
  updateDetail: (id: number, detailId: number, data: Record<string, unknown>) => apiPut<unknown>(`/kantor/laporan-perjalanan-dinas/${id}/details/${detailId}`, data),
  uploadDokumentasi: (id: number, data: Record<string, unknown>) => apiPost<unknown>(`/kantor/laporan-perjalanan-dinas/${id}/dokumentasi`, data),
  updateDokumentasi: (id: number, docId: number, data: Record<string, unknown>) => apiPost<unknown>(`/kantor/laporan-perjalanan-dinas/${id}/dokumentasi/${docId}`, data),
};

// ============================================================
// Kantor - Notulensi
// ============================================================
export const notulensiService = {
  list: (params?: QueryParams) => apiGet<PaginatedResponse<unknown>>('/kantor/notulensi', params),
  get: (id: number) => apiGet<unknown>(`/kantor/notulensi/${id}`),
  create: (data: Record<string, unknown>) => apiPost<unknown>('/kantor/notulensi', data),
  update: (id: number, data: Record<string, unknown>) => apiPut<unknown>(`/kantor/notulensi/${id}`, data),
  patch: (id: number, data: Record<string, unknown>) => apiPatch<unknown>(`/kantor/notulensi/${id}`, data),
  delete: (id: number) => apiDelete<null>(`/kantor/notulensi/${id}`),
};

// ============================================================
// Kantor - Tamu
// ============================================================
export const tamuService = {
  list: (params?: QueryParams) => apiGet<PaginatedResponse<unknown>>('/kantor/tamu', params),
  get: (id: number) => apiGet<unknown>(`/kantor/tamu/${id}`),
  create: (data: Record<string, unknown>) => apiPost<unknown>('/kantor/tamu', data),
  update: (id: number, data: Record<string, unknown>) => apiPut<unknown>(`/kantor/tamu/${id}`, data),
  delete: (id: number) => apiDelete<null>(`/kantor/tamu/${id}`),
};

// ============================================================
// Kantor - Pengaduan
// ============================================================
export const pengaduanService = {
  list: (params?: QueryParams) => apiGet<PaginatedResponse<unknown>>('/kantor/pengaduan', params),
  get: (id: number) => apiGet<unknown>(`/kantor/pengaduan/${id}`),
  create: (data: Record<string, unknown>) => apiPost<unknown>('/kantor/pengaduan', data),
  update: (id: number, data: Record<string, unknown>) => apiPut<unknown>(`/kantor/pengaduan/${id}`, data),
  delete: (id: number) => apiDelete<null>(`/kantor/pengaduan/${id}`),
};

// ============================================================
// Kantor - UU / UU Tambahan
// ============================================================
export const uuService = {
  list: (params?: QueryParams) => apiGet<PaginatedResponse<unknown>>('/kantor/uu', params),
  get: (id: number) => apiGet<unknown>(`/kantor/uu/${id}`),
  create: (data: Record<string, unknown>) => apiPost<unknown>('/kantor/uu', data),
  update: (id: number, data: Record<string, unknown>) => apiPut<unknown>(`/kantor/uu/${id}`, data),
  delete: (id: number) => apiDelete<null>(`/kantor/uu/${id}`),
};

export const uuTambahanService = {
  list: (params?: QueryParams) => apiGet<PaginatedResponse<unknown>>('/kantor/uu-tambahan', params),
  get: (id: number) => apiGet<unknown>(`/kantor/uu-tambahan/${id}`),
  create: (data: Record<string, unknown>) => apiPost<unknown>('/kantor/uu-tambahan', data),
  update: (id: number, data: Record<string, unknown>) => apiPut<unknown>(`/kantor/uu-tambahan/${id}`, data),
  delete: (id: number) => apiDelete<null>(`/kantor/uu-tambahan/${id}`),
};

// ============================================================
// Kantor - Direktori Usaha
// ============================================================
export const direktoriUsahaService = {
  list: (params?: QueryParams) => apiGet<PaginatedResponse<unknown>>('/kantor/direktori-usaha', params),
  get: (id: number) => apiGet<unknown>(`/kantor/direktori-usaha/${id}`),
  update: (id: number, data: Record<string, unknown>) => apiPut<unknown>(`/kantor/direktori-usaha/${id}`, data),
  delete: (id: number) => apiDelete<null>(`/kantor/direktori-usaha/${id}`),
};

// ============================================================
// PDF Generation
// ============================================================
export const pdfService = {
  bulkGenerateSPK: (data: { ids: number[] }) => apiPost<unknown>('/spk/pdf/bulk-spks', data),
  bulkGenerateBAST: (data: { ids: number[] }) => apiPost<unknown>('/spk/pdf/bulk-basts', data),
};

// ============================================================
// AI Service
// ============================================================
export const aiService = {
  generate: (data: { prompt: string; systemInstruction?: string; model?: string }) =>
    apiPost<unknown>('/ai/generate', data),
};

// ============================================================
// Miniapp - Survey / Surveycraft
// ============================================================
export const surveyService = {
  create: (data: Record<string, unknown>) => apiPost<unknown>('/miniapp/survey', data),
  update: (id: number, data: Record<string, unknown>) => apiPut<unknown>(`/miniapp/survey/${id}`, data),
  patch: (id: number, data: Record<string, unknown>) => apiPatch<unknown>(`/miniapp/survey/${id}`, data),
};

export const surveyResultService = {
  create: (data: Record<string, unknown>) => apiPost<unknown>('/miniapp/survey-results', data),
  update: (id: number, data: Record<string, unknown>) => apiPut<unknown>(`/miniapp/survey-results/${id}`, data),
  patch: (id: number, data: Record<string, unknown>) => apiPatch<unknown>(`/miniapp/survey-results/${id}`, data),
};

export const surveycraftService = {
  create: (data: Record<string, unknown>) => apiPost<unknown>('/miniapp/surveycraft', data),
  update: (id: number, data: Record<string, unknown>) => apiPut<unknown>(`/miniapp/surveycraft/${id}`, data),
  respond: (data: Record<string, unknown>) => apiPost<unknown>('/miniapp/surveycraft/respond', data),
  generateWithAI: (data: Record<string, unknown>) => apiPost<unknown>('/miniapp/surveycraft/test-generate-ai', data),
};

// ============================================================
// DIPA Budget
// ============================================================
import type {
  DipaImportFile,
  DipaBudgetItem,
  DipaMonitoringRow,
  DipaMonitoringSummary,
  DipaUsageHistory,
  DipaOrphan,
  DipaBudgetPlan,
  DipaRecordUsagePayload,
  DipaRecordPlanPayload,
  DipaUpdatePlanPayload,
  DipaSaveMappingPayload,
  DipaImportResponse,
  DipaDependencyView,
  SaktiReconciliationItem,
  SaktiImportResponse,
} from '../types/api';

export const dipaBudgetService = {
  // --- Monitoring ---
  getMonitoring: (year?: number) =>
    apiGet<DipaMonitoringRow[]>('/kantor/dipa/monitoring', year ? { year } : undefined),

  getMonitoringSummary: (year?: number) =>
    apiGet<DipaMonitoringSummary>('/kantor/dipa/monitoring/summary', year ? { year } : undefined),

  // --- Picker (autocomplete) ---
  getPicker: (q?: string) =>
    apiGet<DipaBudgetItem[]>('/kantor/dipa/picker', q ? { q } : undefined),

  // --- Usage (Pencairan / Realisasi) ---
  recordUsage: (data: DipaRecordUsagePayload) =>
    apiPost<null>('/kantor/dipa/usage', data),

  getHistory: (params?: {
    page?: number;
    per_page?: number;
    start_date?: string;
    end_date?: string;
    program_name?: string;
    output_name?: string;
  }) =>
    apiGet<DipaUsageHistory[]>('/kantor/dipa/history', params),

  updateUsageAmount: (id: number, amount_spent: number) =>
    apiPut<null>(`/kantor/dipa/usage/${id}`, { amount_spent }),

  deleteUsage: (id: number) =>
    apiDelete<null>(`/kantor/dipa/usage/${id}`),

  // --- Import (multipart/form-data) ---
  importDipa: async (formData: FormData): Promise<{ success: boolean; message: string; data: DipaImportResponse }> => {
    const token = getToken();
    const response = await fetch(`${API_BASE_URL}/kantor/dipa/import`, {
      method: 'POST',
      headers: {
        Accept: 'application/json',
        ...(token ? { Authorization: `Bearer ${token}` } : {}),
      },
      body: formData,
    });
    return response.json();
  },

  getFiles: () =>
    apiGet<DipaImportFile[]>('/kantor/dipa/files'),

  // --- Planning (Perencanaan) ---
  recordPlan: (data: DipaRecordPlanPayload) =>
    apiPost<null>('/kantor/dipa/plan', data),

  getPlans: (params?: { month?: string; page?: number; per_page?: number }) =>
    apiGet<DipaBudgetPlan[]>('/kantor/dipa/plan', params),

  updatePlan: (id: number, data: DipaUpdatePlanPayload) =>
    apiPut<null>(`/kantor/dipa/plan/${id}`, data),

  deletePlan: (id: number) =>
    apiDelete<null>(`/kantor/dipa/plan/${id}`),

  // --- Integrity (Integritas) ---
  getOrphans: () =>
    apiGet<DipaOrphan[]>('/kantor/dipa/orphans'),

  saveMapping: (data: DipaSaveMappingPayload) =>
    apiPost<null>('/kantor/dipa/map', data),

  // --- Diagnostics ---
  verifyDependencies: () =>
    apiGet<DipaDependencyView[]>('/kantor/dipa/dependencies/views'),

  // --- SAKTI Reconciliation ---
  getSaktiReconciliation: (year?: number) =>
    apiGet<SaktiReconciliationItem[]>('/kantor/dipa/reconciliation', year ? { year } : undefined),

  importSakti: async (formData: FormData): Promise<{ success: boolean; message: string; data: SaktiImportResponse }> => {
    const token = getToken();
    const response = await fetch(`${API_BASE_URL}/kantor/dipa/import-sakti`, {
      method: 'POST',
      headers: {
        Accept: 'application/json',
        ...(token ? { Authorization: `Bearer ${token}` } : {}),
      },
      body: formData,
    });
    return response.json();
  },

  getSaktiSummary: (usage_date: string) =>
    apiGet<{ usage_date: string; row_count: number; total_amount: number }>('/kantor/dipa/usage/sync-fa-summary', { usage_date }),

  syncSaktiFa: (data: { usage_date: string }) =>
    apiPost<{ usage_date: string; row_count: number; total_amount: number }>('/kantor/dipa/usage/sync-fa', data),

  // --- Export (Excel) ---
  exportPlansToExcel: async (year: number): Promise<void> => {
    const token = getToken();
    const response = await fetch(`${API_BASE_URL}/kantor/dipa/plan/export?year=${year}`, {
      method: 'GET',
      headers: {
        Accept: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ...(token ? { Authorization: `Bearer ${token}` } : {}),
      },
    });

    if (!response.ok) {
      const text = await response.text();
      throw new Error(`HTTP ${response.status}: ${response.statusText} ${text}`);
    }

    const blob = await response.blob();
    const filename = `Rencana_Realisasi_TA_${year}.xlsx`;
    const url = window.URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = filename;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    window.URL.revokeObjectURL(url);
  },
};

