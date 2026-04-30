# GET API Endpoints - Response Results

Generated at: 2026-04-18 20:51:09 | **Updated: 2026-04-18 21:17:33**

## Statistics

| Metric | Original | After Fix |
|--------|----------|-----------|
| Total Endpoints | 154 | 154 |
| Success (2xx) | 121 | **130** |
| Client Error (4xx) | 33 | **21** |
| Server Error (5xx) | 0 | **3** (code bugs) |

## ✅ Fixed Endpoints (Re-tested with correct params)

| # | Endpoint | Original | Fixed | File |
|---|----------|----------|-------|------|
| 1 | `/meta/wilayah/kecamatan?kdprov=32&kdkab=15` | ❌ 400 | ✅ 200 | [meta_wilayah_kecamatan_qa9829f.md](./meta_wilayah_kecamatan_qa9829f.md) |
| 2 | `/kantor/kegiatan/monitoring/desa-options?kec_id=3215010` | ❌ 422 | ✅ 200 | [kantor_kegiatan_monitoring_desa-options_q896e77.md](./kantor_kegiatan_monitoring_desa-options_q896e77.md) |
| 3 | `/kantor/kegiatan/monitoring/monitoring-kegiatan-config/6` | ❌ 404 | ✅ 200 | [kantor_kegiatan_monitoring_monitoring-kegiatan-config_6.md](./kantor_kegiatan_monitoring_monitoring-kegiatan-config_6.md) |
| 4 | `/kantor/kegiatan/monitoring/download?kegiatan_id=88` | ❌ 404 | ✅ 200 | [kantor_kegiatan_monitoring_download_q8de82f.md](./kantor_kegiatan_monitoring_download_q8de82f.md) |
| 5 | `/kantor/penugasan/kegiatan-options?fungsi=Distribusi` | ❌ 422 | ✅ 200 | [kantor_penugasan_kegiatan-options_q3a0f74.md](./kantor_penugasan_kegiatan-options_q3a0f74.md) |
| 6 | `/kantor/penugasan/583` | ❌ 404 | ✅ 200 | [kantor_penugasan_583.md](./kantor_penugasan_583.md) |
| 7 | `/kantor/bast/583` | ❌ 404 | ✅ 200 | [kantor_bast_583.md](./kantor_bast_583.md) |
| 8 | `/kantor/spk/59/2025-05` | ❌ 404 | ✅ 200 | [kantor_spk_59_2025-05.md](./kantor_spk_59_2025-05.md) |
| 9 | `/kantor/spk/monitoring?type=tanpa_spk` | ❌ 422 | ✅ 200 | [kantor_spk_monitoring_qecf35c.md](./kantor_spk_monitoring_qecf35c.md) |

## 🐛 Server Errors (Code Bugs - 500)

| # | Endpoint | File | Note |
|---|----------|------|------|
| 1 | `/kantor/kegiatan/monitoring/detil-configurations/available-fields?foreign_key_value=88&foreign_key_type=kegiatan_id` | [kantor_kegiatan_monitoring_detil-configurations_available-fields_qc90229.md](./kantor_kegiatan_monitoring_detil-configurations_available-fields_qc90229.md) | 500 Internal Server Error |
| 2 | `/kantor/kegiatan/monitoring/detil-configuration?monitoring_kegiatan_config_id=6` | [kantor_kegiatan_monitoring_detil-configuration_q19785b.md](./kantor_kegiatan_monitoring_detil-configuration_q19785b.md) | 500 Internal Server Error |
| 3 | `/ipds/asset-it-maintenance-schedule/1` | [ipds_asset-it-maintenance-schedule_1.md](./ipds_asset-it-maintenance-schedule_1.md) | 404 - Route model binding issue (table=asset_it_maintenance_schedule, records exist) |

## ⚠️ Remaining Client Error (4xx) Analysis

| # | HTTP | Endpoint | File | Reason |
|---|------|----------|------|--------|
| 1 | `400` | `/meta/wilayah/kecamatan?kode_kec=110101` | [MetaWilayahKecamatan.md](./MetaWilayahKecamatan.md) | Bad Request: Missing or invalid required parameter. Parameter kdprov dan kdkab wajib diisi |
| 2 | `422` | `/miniapp/surveycraft/share/1` | [MiniappSurveycraftShare.md](./MiniappSurveycraftShare.md) | Validation Error: Missing/invalid required params. survey_id: Survey id wajib diisi. |
| 3 | `403` | `/admin/roles/permission-options` | [AdminRolesPermissionOptions.md](./AdminRolesPermissionOptions.md) | Forbidden: User lacks required permission |
| 4 | `403` | `/admin/roles?per_page=15&page=1` | [AdminRolesIndex.md](./AdminRolesIndex.md) | Forbidden: User lacks required permission |
| 5 | `403` | `/admin/roles/6` | [AdminRolesShow.md](./AdminRolesShow.md) | Forbidden: User lacks required permission |
| 6 | `403` | `/admin/permissions?per_page=15&page=1` | [AdminPermissionsIndex.md](./AdminPermissionsIndex.md) | Forbidden: User lacks required permission |
| 7 | `403` | `/admin/permissions/form-options` | [AdminPermissionsFormOptions.md](./AdminPermissionsFormOptions.md) | Forbidden: User lacks required permission |
| 8 | `403` | `/admin/permissions/list-api-controllers` | [AdminPermissionsListApiControllers.md](./AdminPermissionsListApiControllers.md) | Forbidden: User lacks required permission |
| 9 | `403` | `/admin/permissions/14` | [AdminPermissionsShow.md](./AdminPermissionsShow.md) | Forbidden: User lacks required permission |
| 10 | `403` | `/admin/users/321522090293` | [AdminUsersShow.md](./AdminUsersShow.md) | Forbidden: User lacks required permission |
| 11 | `422` | `/kantor/direktori-usaha/desa-options?kode_kec=110101` | [DirektoriUsahaDesaOptions.md](./DirektoriUsahaDesaOptions.md) | Validation Error: Missing/invalid required params. kdkec: Kdkec wajib diisi. |
| 12 | `422` | `/kantor/kegiatan/monitoring/detil-configurations/available-fields` | [MonitoringDetilConfigAvailableFields.md](./MonitoringDetilConfigAvailableFields.md) | Validation Error: Missing/invalid required params. foreign_key_value: Foreign key value wajib diisi.; foreign_key_type: Foreign key type wajib diisi. |
| 13 | `422` | `/kantor/kegiatan/monitoring/monitoring-kegiatan-config/1/available-detil-configs` | [MonitoringKegiatanConfigAvailableDetilConfigs.md](./MonitoringKegiatanConfigAvailableDetilConfigs.md) | Validation Error: Missing/invalid required params. foreign_key_value: Foreign key value wajib diisi.; foreign_key_type: Foreign key type wajib diisi. |
| 14 | `404` | `/kantor/kegiatan/monitoring/monitoring-kegiatan-config/1` | [MonitoringKegiatanConfigShow.md](./MonitoringKegiatanConfigShow.md) | Not Found: Resource does not exist. Configuration not found |
| 15 | `422` | `/kantor/kegiatan/monitoring/desa-options?kode_kec=110101` | [MonitoringDesaOptions.md](./MonitoringDesaOptions.md) | Validation Error: Missing/invalid required params. kec_id: Kec id wajib diisi. |
| 16 | `422` | `/kantor/kegiatan/monitoring/detil-configuration?kegiatan_id=1` | [MonitoringDetilConfiguration.md](./MonitoringDetilConfiguration.md) | Validation Error: Missing/invalid required params. monitoring_kegiatan_config_id: Monitoring kegiatan config id wajib diisi. |
| 17 | `404` | `/kantor/kegiatan/monitoring/download?kegiatan_id=1` | [MonitoringKegiatanDownload.md](./MonitoringKegiatanDownload.md) | Not Found: Resource does not exist. No monitoring data found for the specified kegiatan_id |
| 18 | `422` | `/kantor/penugasan/kegiatan-options?kegiatan_id=1` | [PenugasanKegiatanOptions.md](./PenugasanKegiatanOptions.md) | Validation Error: Missing/invalid required params. Fungsi parameter is required. |
| 19 | `404` | `/kantor/penugasan/1` | [PenugasanShow.md](./PenugasanShow.md) | Not Found: Resource does not exist. Penugasan not found |
| 20 | `404` | `/kantor/uu-tambahan/1` | [UUTambahanShow.md](./UUTambahanShow.md) | Not Found: Resource does not exist. UU Tambahan not found |
| 21 | `404` | `/kantor/bast/1` | [BASTShow.md](./BASTShow.md) | Not Found: Resource does not exist. Penugasan not found |
| 22 | `404` | `/kantor/spk/1/2025-01` | [SPKShow.md](./SPKShow.md) | Not Found: Resource does not exist. SPK record not found |
| 23 | `422` | `/kantor/spk/monitoring?kegiatan_id=1` | [SPKMonitoring.md](./SPKMonitoring.md) | Validation Error: Missing/invalid required params. type: Type wajib diisi. |
| 24 | `404` | `/kantor/laporan-perjalanan-dinas/1` | [LaporanPerjalananDinasShow.md](./LaporanPerjalananDinasShow.md) | Not Found: Resource does not exist. Laporan perjalanan dinas not found |
| 25 | `404` | `/kantor/notulensi/1` | [NotulensiShow.md](./NotulensiShow.md) | Not Found: Resource does not exist. Notulensi not found |
| 26 | `404` | `/kantor/tamu/1` | [TamuShow.md](./TamuShow.md) | Not Found: Resource does not exist. Tamu not found |
| 27 | `404` | `/kantor/pengaduan/1` | [PengaduanShow.md](./PengaduanShow.md) | Not Found: Resource does not exist. Pengaduan not found |
| 28 | `404` | `/kantor/holidays/1` | [HolidaysShow.md](./HolidaysShow.md) | Not Found: Resource does not exist. Holiday not found |
| 29 | `404` | `/ipds/asset-it-maintenance-schedule/1` | [IPDSAssetMaintenanceScheduleShow.md](./IPDSAssetMaintenanceScheduleShow.md) | Not Found: Resource does not exist. Maintenance schedule not found |
| 30 | `403` | `/ipds/raw-datas?per_page=15&page=1` | [IPDSRawDatasIndex.md](./IPDSRawDatasIndex.md) | Forbidden: User lacks required permission |
| 31 | `403` | `/ipds/raw-datas/1` | [IPDSRawDatasShow.md](./IPDSRawDatasShow.md) | Forbidden: User lacks required permission |
| 32 | `422` | `/miniapp/surveycraft/respond?survey_id=1` | [SurveycraftRespond.md](./SurveycraftRespond.md) | Validation Error: Missing/invalid required params. respond_id: Respond id wajib diisi. |
| 33 | `404` | `/miniapp/surveycraft/1` | [SurveycraftShow.md](./SurveycraftShow.md) | Not Found: Resource does not exist. Surveycraft not found |

## All Endpoints

| # | Method | Endpoint | File | Status | Time |
|---|--------|----------|------|--------|------|
| 1 | `GET` | `/landing` | [Landing.md](./Landing.md) | ✅ `200` | 0.068s |
| 2 | `GET` | `/meta/wilayah/kecamatan?kode_kec=110101` | [MetaWilayahKecamatan.md](./MetaWilayahKecamatan.md) | ⚠️ `400` | 0.022s |
| 3 | `GET` | `/miniapp/survey` | [MiniappSurveyIndex.md](./MiniappSurveyIndex.md) | ✅ `200` | 0.03s |
| 4 | `GET` | `/miniapp/survey-results` | [MiniappSurveyResultsIndex.md](./MiniappSurveyResultsIndex.md) | ✅ `200` | 0.024s |
| 5 | `GET` | `/miniapp/surveycraft/share/1` | [MiniappSurveycraftShare.md](./MiniappSurveycraftShare.md) | ⚠️ `422` | 0.023s |
| 6 | `GET` | `/profile` | [ProfileShow.md](./ProfileShow.md) | ✅ `200` | 0.036s |
| 7 | `GET` | `/profile/pegawai` | [ProfilePegawai.md](./ProfilePegawai.md) | ✅ `200` | 0.024s |
| 8 | `GET` | `/enums` | [EnumsIndex.md](./EnumsIndex.md) | ✅ `200` | 0.022s |
| 9 | `GET` | `/enums/fungsi-types` | [EnumsFungsiTypes.md](./EnumsFungsiTypes.md) | ✅ `200` | 0.023s |
| 10 | `GET` | `/enums/jabatan-tugas-types` | [EnumsJabatanTugasTypes.md](./EnumsJabatanTugasTypes.md) | ✅ `200` | 0.023s |
| 11 | `GET` | `/enums/jenis-kegiatan-types` | [EnumsJenisKegiatanTypes.md](./EnumsJenisKegiatanTypes.md) | ✅ `200` | 0.022s |
| 12 | `GET` | `/enums/satuan-types` | [EnumsSatuanTypes.md](./EnumsSatuanTypes.md) | ✅ `200` | 0.027s |
| 13 | `GET` | `/enums/pangkat-types` | [EnumsPangkatTypes.md](./EnumsPangkatTypes.md) | ✅ `200` | 0.021s |
| 14 | `GET` | `/enums/golongan-types` | [EnumsGolonganTypes.md](./EnumsGolonganTypes.md) | ✅ `200` | 0.023s |
| 15 | `GET` | `/enums/jabatan-types` | [EnumsJabatanTypes.md](./EnumsJabatanTypes.md) | ✅ `200` | 0.026s |
| 16 | `GET` | `/admin/roles/permission-options` | [AdminRolesPermissionOptions.md](./AdminRolesPermissionOptions.md) | ⚠️ `403` | 0.024s |
| 17 | `GET` | `/admin/roles?per_page=15&page=1` | [AdminRolesIndex.md](./AdminRolesIndex.md) | ⚠️ `403` | 0.023s |
| 18 | `GET` | `/admin/roles/6` | [AdminRolesShow.md](./AdminRolesShow.md) | ⚠️ `403` | 0.023s |
| 19 | `GET` | `/admin/permissions?per_page=15&page=1` | [AdminPermissionsIndex.md](./AdminPermissionsIndex.md) | ⚠️ `403` | 0.023s |
| 20 | `GET` | `/admin/permissions/form-options` | [AdminPermissionsFormOptions.md](./AdminPermissionsFormOptions.md) | ⚠️ `403` | 0.022s |
| 21 | `GET` | `/admin/permissions/list-api-controllers` | [AdminPermissionsListApiControllers.md](./AdminPermissionsListApiControllers.md) | ⚠️ `403` | 0.039s |
| 22 | `GET` | `/admin/permissions/14` | [AdminPermissionsShow.md](./AdminPermissionsShow.md) | ⚠️ `403` | 0.03s |
| 23 | `GET` | `/admin/users/organik` | [AdminUsersOrganik.md](./AdminUsersOrganik.md) | ✅ `200` | 0.038s |
| 24 | `GET` | `/admin/users/mitra` | [AdminUsersMitra.md](./AdminUsersMitra.md) | ✅ `200` | 0.037s |
| 25 | `GET` | `/admin/users?per_page=15&page=1` | [AdminUsersIndex.md](./AdminUsersIndex.md) | ✅ `200` | 0.062s |
| 26 | `GET` | `/admin/users/321522090293` | [AdminUsersShow.md](./AdminUsersShow.md) | ⚠️ `403` | 0.029s |
| 27 | `GET` | `/kantor/pegawai?per_page=15&page=1` | [PegawaiIndex.md](./PegawaiIndex.md) | ✅ `200` | 0.034s |
| 28 | `GET` | `/kantor/pegawai/jabatan-pangkat-list` | [PegawaiJabatanPangkatList.md](./PegawaiJabatanPangkatList.md) | ✅ `200` | 0.032s |
| 29 | `GET` | `/kantor/pegawai/1` | [PegawaiShow.md](./PegawaiShow.md) | ✅ `200` | 0.024s |
| 30 | `GET` | `/kantor/settings?per_page=15&page=1` | [SettingsIndex.md](./SettingsIndex.md) | ✅ `200` | 0.025s |
| 31 | `GET` | `/kantor/settings/grup-list` | [SettingsGrupList.md](./SettingsGrupList.md) | ✅ `200` | 0.028s |
| 32 | `GET` | `/kantor/settings/officers` | [SettingsOfficers.md](./SettingsOfficers.md) | ✅ `200` | 0.022s |
| 33 | `GET` | `/kantor/settings/key/PPK` | [SettingsGetByKey.md](./SettingsGetByKey.md) | ✅ `200` | 0.026s |
| 34 | `GET` | `/kantor/settings/1` | [SettingsShow.md](./SettingsShow.md) | ✅ `200` | 0.023s |
| 35 | `GET` | `/kantor/mitra?per_page=15&page=1` | [MitraIndex.md](./MitraIndex.md) | ✅ `200` | 0.031s |
| 36 | `GET` | `/kantor/mitra/filters` | [MitraFilters.md](./MitraFilters.md) | ✅ `200` | 0.031s |
| 37 | `GET` | `/kantor/mitra/penugasan-options` | [MitraPenugasanOptions.md](./MitraPenugasanOptions.md) | ✅ `200` | 0.026s |
| 38 | `GET` | `/kantor/direktori-usaha?per_page=15&page=1` | [DirektoriUsahaIndex.md](./DirektoriUsahaIndex.md) | ✅ `200` | 0.135s |
| 39 | `GET` | `/kantor/direktori-usaha/rekap-user` | [DirektoriUsahaRekapUser.md](./DirektoriUsahaRekapUser.md) | ✅ `200` | 0.898s |
| 40 | `GET` | `/kantor/direktori-usaha/progres` | [DirektoriUsahaProgres.md](./DirektoriUsahaProgres.md) | ✅ `200` | 1.353s |
| 41 | `GET` | `/kantor/direktori-usaha/filters` | [DirektoriUsahaFilters.md](./DirektoriUsahaFilters.md) | ✅ `200` | 0.986s |
| 42 | `GET` | `/kantor/direktori-usaha/desa-options?kode_kec=110101` | [DirektoriUsahaDesaOptions.md](./DirektoriUsahaDesaOptions.md) | ⚠️ `422` | 0.032s |
| 43 | `GET` | `/kantor/direktori-usaha/no-gc-no-loc` | [DirektoriUsahaNoGcNoLoc.md](./DirektoriUsahaNoGcNoLoc.md) | ✅ `200` | 1.011s |
| 44 | `GET` | `/kantor/direktori-usaha/invalid` | [DirektoriUsahaInvalid.md](./DirektoriUsahaInvalid.md) | ✅ `200` | 1.264s |
| 45 | `GET` | `/kantor/direktori-usaha/siap-kirim` | [DirektoriUsahaSiapKirim.md](./DirektoriUsahaSiapKirim.md) | ✅ `200` | 1.396s |
| 46 | `GET` | `/kantor/direktori-usaha/ganda` | [DirektoriUsahaGanda.md](./DirektoriUsahaGanda.md) | ✅ `200` | 1.318s |
| 47 | `GET` | `/kantor/direktori-usaha/map-desa` | [DirektoriUsahaMapDesa.md](./DirektoriUsahaMapDesa.md) | ✅ `200` | 1.126s |
| 48 | `GET` | `/kantor/direktori-usaha/1` | [DirektoriUsahaShow.md](./DirektoriUsahaShow.md) | ✅ `200` | 0.03s |
| 49 | `GET` | `/kantor/kegiatan/monitoring/detil-configurations/available-fields` | [MonitoringDetilConfigAvailableFields.md](./MonitoringDetilConfigAvailableFields.md) | ⚠️ `422` | 0.024s |
| 50 | `GET` | `/kantor/kegiatan/monitoring/detil-configurations` | [MonitoringDetilConfigIndex.md](./MonitoringDetilConfigIndex.md) | ✅ `200` | 0.026s |
| 51 | `GET` | `/kantor/kegiatan/monitoring/detil-configurations/1` | [MonitoringDetilConfigShow.md](./MonitoringDetilConfigShow.md) | ✅ `200` | 0.022s |
| 52 | `GET` | `/kantor/kegiatan/monitoring/monitoring-kegiatan-config/kegiatan-options` | [MonitoringKegiatanConfigKegiatanOptions.md](./MonitoringKegiatanConfigKegiatanOptions.md) | ✅ `200` | 0.033s |
| 53 | `GET` | `/kantor/kegiatan/monitoring/monitoring-kegiatan-config/1/available-detil-configs` | [MonitoringKegiatanConfigAvailableDetilConfigs.md](./MonitoringKegiatanConfigAvailableDetilConfigs.md) | ⚠️ `422` | 0.022s |
| 54 | `GET` | `/kantor/kegiatan/monitoring/monitoring-kegiatan-config` | [MonitoringKegiatanConfigIndex.md](./MonitoringKegiatanConfigIndex.md) | ✅ `200` | 0.029s |
| 55 | `GET` | `/kantor/kegiatan/monitoring/monitoring-kegiatan-config/1` | [MonitoringKegiatanConfigShow.md](./MonitoringKegiatanConfigShow.md) | ⚠️ `404` | 0.022s |
| 56 | `GET` | `/kantor/kegiatan/monitoring/kegiatan-options` | [MonitoringKegiatanOptions.md](./MonitoringKegiatanOptions.md) | ✅ `200` | 0.022s |
| 57 | `GET` | `/kantor/kegiatan/monitoring/all-kegiatan-options` | [MonitoringAllKegiatanOptions.md](./MonitoringAllKegiatanOptions.md) | ✅ `200` | 0.022s |
| 58 | `GET` | `/kantor/kegiatan/monitoring/filters-options` | [MonitoringFiltersOptions.md](./MonitoringFiltersOptions.md) | ✅ `200` | 0.024s |
| 59 | `GET` | `/kantor/kegiatan/monitoring/petugas-options?kegiatan_id=1` | [MonitoringPetugasOptions.md](./MonitoringPetugasOptions.md) | ✅ `200` | 0.028s |
| 60 | `GET` | `/kantor/kegiatan/monitoring/pengawas-options` | [MonitoringPengawasOptions.md](./MonitoringPengawasOptions.md) | ✅ `200` | 0.032s |
| 61 | `GET` | `/kantor/kegiatan/monitoring/supervisor-options` | [MonitoringSupervisorOptions.md](./MonitoringSupervisorOptions.md) | ✅ `200` | 0.025s |
| 62 | `GET` | `/kantor/kegiatan/monitoring/sls-options` | [MonitoringSLSOptions.md](./MonitoringSLSOptions.md) | ✅ `200` | 0.126s |
| 63 | `GET` | `/kantor/kegiatan/monitoring/blok-options` | [MonitoringBlokOptions.md](./MonitoringBlokOptions.md) | ✅ `200` | 0.043s |
| 64 | `GET` | `/kantor/kegiatan/monitoring/kec-options` | [MonitoringKecOptions.md](./MonitoringKecOptions.md) | ✅ `200` | 0.024s |
| 65 | `GET` | `/kantor/kegiatan/monitoring/desa-options?kode_kec=110101` | [MonitoringDesaOptions.md](./MonitoringDesaOptions.md) | ⚠️ `422` | 0.025s |
| 66 | `GET` | `/kantor/kegiatan/monitoring/detil-configuration?kegiatan_id=1` | [MonitoringDetilConfiguration.md](./MonitoringDetilConfiguration.md) | ⚠️ `422` | 0.023s |
| 67 | `GET` | `/kantor/kegiatan/monitoring?per_page=15&page=1` | [MonitoringKegiatanIndex.md](./MonitoringKegiatanIndex.md) | ✅ `200` | 0.032s |
| 68 | `GET` | `/kantor/kegiatan/monitoring/download?kegiatan_id=1` | [MonitoringKegiatanDownload.md](./MonitoringKegiatanDownload.md) | ⚠️ `404` | 0.024s |
| 69 | `GET` | `/kantor/kegiatan/monitoring/1` | [MonitoringKegiatanShow.md](./MonitoringKegiatanShow.md) | ✅ `200` | 0.027s |
| 70 | `GET` | `/kantor/kegiatan/filter-list` | [KegiatanFilterList.md](./KegiatanFilterList.md) | ✅ `200` | 0.028s |
| 71 | `GET` | `/kantor/kegiatan/calendar?year=2026&month=04` | [KegiatanCalendar.md](./KegiatanCalendar.md) | ✅ `200` | 0.04s |
| 72 | `GET` | `/kantor/kegiatan/form-options` | [KegiatanFormOptions.md](./KegiatanFormOptions.md) | ✅ `200` | 0.023s |
| 73 | `GET` | `/kantor/kegiatan/by-year?year=2026` | [KegiatanByYear.md](./KegiatanByYear.md) | ✅ `200` | 0.034s |
| 74 | `GET` | `/kantor/kegiatan/statistics` | [KegiatanStatistics.md](./KegiatanStatistics.md) | ✅ `200` | 0.047s |
| 75 | `GET` | `/kantor/kegiatan?per_page=15&page=1` | [KegiatanIndex.md](./KegiatanIndex.md) | ✅ `200` | 0.031s |
| 76 | `GET` | `/kantor/kegiatan/1` | [KegiatanShow.md](./KegiatanShow.md) | ✅ `200` | 0.026s |
| 77 | `GET` | `/kantor/penugasan/mitra-dropdown` | [PenugasanMitraDropdown.md](./PenugasanMitraDropdown.md) | ✅ `200` | 0.026s |
| 78 | `GET` | `/kantor/penugasan/mitra-options` | [PenugasanMitraOptions.md](./PenugasanMitraOptions.md) | ✅ `200` | 0.05s |
| 79 | `GET` | `/kantor/penugasan/filters` | [PenugasanFilters.md](./PenugasanFilters.md) | ✅ `200` | 0.042s |
| 80 | `GET` | `/kantor/penugasan/form-options` | [PenugasanFormOptions.md](./PenugasanFormOptions.md) | ✅ `200` | 0.032s |
| 81 | `GET` | `/kantor/penugasan/kegiatan-options?kegiatan_id=1` | [PenugasanKegiatanOptions.md](./PenugasanKegiatanOptions.md) | ⚠️ `422` | 0.023s |
| 82 | `GET` | `/kantor/penugasan/export` | [PenugasanExport.md](./PenugasanExport.md) | ✅ `200` | 0.166s |
| 83 | `GET` | `/kantor/penugasan?per_page=15&page=1` | [PenugasanIndex.md](./PenugasanIndex.md) | ✅ `200` | 0.028s |
| 84 | `GET` | `/kantor/penugasan/1` | [PenugasanShow.md](./PenugasanShow.md) | ⚠️ `404` | 0.025s |
| 85 | `GET` | `/kantor/uu?per_page=15&page=1` | [UUIndex.md](./UUIndex.md) | ✅ `200` | 0.029s |
| 86 | `GET` | `/kantor/uu/1` | [UUShow.md](./UUShow.md) | ✅ `200` | 0.027s |
| 87 | `GET` | `/kantor/uu-tambahan?per_page=15&page=1` | [UUTambahanIndex.md](./UUTambahanIndex.md) | ✅ `200` | 0.037s |
| 88 | `GET` | `/kantor/uu-tambahan/1` | [UUTambahanShow.md](./UUTambahanShow.md) | ⚠️ `404` | 0.038s |
| 89 | `GET` | `/kantor/bast?per_page=15&page=1` | [BASTIndex.md](./BASTIndex.md) | ✅ `200` | 0.038s |
| 90 | `GET` | `/kantor/bast/available-months` | [BASTAvailableMonths.md](./BASTAvailableMonths.md) | ✅ `200` | 0.023s |
| 91 | `GET` | `/kantor/bast/1` | [BASTShow.md](./BASTShow.md) | ⚠️ `404` | 0.023s |
| 92 | `GET` | `/kantor/surat/permintaan?per_page=15&page=1` | [SuratPermintaanIndex.md](./SuratPermintaanIndex.md) | ✅ `200` | 0.033s |
| 93 | `GET` | `/kantor/surat/permintaan/dates` | [SuratPermintaanDates.md](./SuratPermintaanDates.md) | ✅ `200` | 0.028s |
| 94 | `GET` | `/kantor/surat/permintaan/years` | [SuratPermintaanYears.md](./SuratPermintaanYears.md) | ✅ `200` | 0.026s |
| 95 | `GET` | `/kantor/surat/permintaan/klasifikasi` | [SuratPermintaanKlasifikasi.md](./SuratPermintaanKlasifikasi.md) | ✅ `200` | 0.024s |
| 96 | `GET` | `/kantor/surat/permintaan/form-options` | [SuratPermintaanFormOptions.md](./SuratPermintaanFormOptions.md) | ✅ `200` | 0.024s |
| 97 | `GET` | `/kantor/surat/permintaan/1` | [SuratPermintaanShow.md](./SuratPermintaanShow.md) | ✅ `200` | 0.028s |
| 98 | `GET` | `/kantor/surat/surat-keluar?per_page=15&page=1` | [SuratKeluarIndex.md](./SuratKeluarIndex.md) | ✅ `200` | 0.072s |
| 99 | `GET` | `/kantor/surat/surat-keluar/dates` | [SuratKeluarDates.md](./SuratKeluarDates.md) | ✅ `200` | 0.025s |
| 100 | `GET` | `/kantor/surat/surat-keluar/years` | [SuratKeluarYears.md](./SuratKeluarYears.md) | ✅ `200` | 0.023s |
| 101 | `GET` | `/kantor/surat/surat-keluar/form-options` | [SuratKeluarFormOptions.md](./SuratKeluarFormOptions.md) | ✅ `200` | 0.026s |
| 102 | `GET` | `/kantor/surat/surat-keluar/1` | [SuratKeluarShow.md](./SuratKeluarShow.md) | ✅ `200` | 0.024s |
| 103 | `GET` | `/kantor/surat/surat-keputusan?per_page=15&page=1` | [SuratKeputusanIndex.md](./SuratKeputusanIndex.md) | ✅ `200` | 0.031s |
| 104 | `GET` | `/kantor/surat/surat-keputusan/dates` | [SuratKeputusanDates.md](./SuratKeputusanDates.md) | ✅ `200` | 0.024s |
| 105 | `GET` | `/kantor/surat/surat-keputusan/years` | [SuratKeputusanYears.md](./SuratKeputusanYears.md) | ✅ `200` | 0.022s |
| 106 | `GET` | `/kantor/surat/surat-keputusan/form-options` | [SuratKeputusanFormOptions.md](./SuratKeputusanFormOptions.md) | ✅ `200` | 0.027s |
| 107 | `GET` | `/kantor/surat/surat-keputusan/1` | [SuratKeputusanShow.md](./SuratKeputusanShow.md) | ✅ `200` | 0.025s |
| 108 | `GET` | `/kantor/surat/surat-tugas?per_page=15&page=1` | [SuratTugasIndex.md](./SuratTugasIndex.md) | ✅ `200` | 0.044s |
| 109 | `GET` | `/kantor/surat/surat-tugas/dates` | [SuratTugasDates.md](./SuratTugasDates.md) | ✅ `200` | 0.027s |
| 110 | `GET` | `/kantor/surat/surat-tugas/years` | [SuratTugasYears.md](./SuratTugasYears.md) | ✅ `200` | 0.027s |
| 111 | `GET` | `/kantor/surat/surat-tugas/klasifikasi` | [SuratTugasKlasifikasi.md](./SuratTugasKlasifikasi.md) | ✅ `200` | 0.028s |
| 112 | `GET` | `/kantor/surat/surat-tugas/form-options` | [SuratTugasFormOptions.md](./SuratTugasFormOptions.md) | ✅ `200` | 0.03s |
| 113 | `GET` | `/kantor/surat/surat-tugas/1` | [SuratTugasShow.md](./SuratTugasShow.md) | ✅ `200` | 0.025s |
| 114 | `GET` | `/kantor/surat/surtug-detil?per_page=15&page=1` | [SurtugDetilIndex.md](./SurtugDetilIndex.md) | ✅ `200` | 0.034s |
| 115 | `GET` | `/kantor/surat/surtug-detil/kegiatan-options` | [SurtugDetilKegiatanOptions.md](./SurtugDetilKegiatanOptions.md) | ✅ `200` | 0.024s |
| 116 | `GET` | `/kantor/surat/surtug-detil/mitra-penugasan-options` | [SurtugDetilMitraPenugasanOptions.md](./SurtugDetilMitraPenugasanOptions.md) | ✅ `200` | 0.022s |
| 117 | `GET` | `/kantor/surat/surtug-detil/pegawai-options` | [SurtugDetilPegawaiOptions.md](./SurtugDetilPegawaiOptions.md) | ✅ `200` | 0.03s |
| 118 | `GET` | `/kantor/surat/surtug-detil/mitra-options` | [SurtugDetilMitraOptions.md](./SurtugDetilMitraOptions.md) | ✅ `200` | 0.028s |
| 119 | `GET` | `/kantor/surat/surtug-detil/surtug/1` | [SurtugDetilBySurtugID.md](./SurtugDetilBySurtugID.md) | ✅ `200` | 0.023s |
| 120 | `GET` | `/kantor/surat/surtug-detil/1` | [SurtugDetilShow.md](./SurtugDetilShow.md) | ✅ `200` | 0.027s |
| 121 | `GET` | `/kantor/skp/stats` | [SKPStats.md](./SKPStats.md) | ✅ `200` | 0.043s |
| 122 | `GET` | `/kantor/skp/stats2` | [SKPStats2.md](./SKPStats2.md) | ✅ `200` | 0.035s |
| 123 | `GET` | `/kantor/skp/list?per_page=15&page=1` | [SKPList.md](./SKPList.md) | ✅ `200` | 0.032s |
| 124 | `GET` | `/kantor/skp/2` | [SKPShow.md](./SKPShow.md) | ✅ `200` | 0.024s |
| 125 | `GET` | `/kantor/spk?per_page=15&page=1` | [SPKIndex.md](./SPKIndex.md) | ✅ `200` | 0.037s |
| 126 | `GET` | `/kantor/spk/1/2025-01` | [SPKShow.md](./SPKShow.md) | ⚠️ `404` | 0.023s |
| 127 | `GET` | `/kantor/spk/monitoring?kegiatan_id=1` | [SPKMonitoring.md](./SPKMonitoring.md) | ⚠️ `422` | 0.022s |
| 128 | `GET` | `/kantor/links?per_page=15&page=1` | [LinksIndex.md](./LinksIndex.md) | ✅ `200` | 0.03s |
| 129 | `GET` | `/kantor/links/2` | [LinksShow.md](./LinksShow.md) | ✅ `200` | 0.026s |
| 130 | `GET` | `/kantor/laporan-perjalanan-dinas?per_page=15&page=1` | [LaporanPerjalananDinasIndex.md](./LaporanPerjalananDinasIndex.md) | ✅ `200` | 0.037s |
| 131 | `GET` | `/kantor/laporan-perjalanan-dinas/1` | [LaporanPerjalananDinasShow.md](./LaporanPerjalananDinasShow.md) | ⚠️ `404` | 0.031s |
| 132 | `GET` | `/kantor/notulensi/form-options` | [NotulensiFormOptions.md](./NotulensiFormOptions.md) | ✅ `200` | 0.023s |
| 133 | `GET` | `/kantor/notulensi?per_page=15&page=1` | [NotulensiIndex.md](./NotulensiIndex.md) | ✅ `200` | 0.024s |
| 134 | `GET` | `/kantor/notulensi/1` | [NotulensiShow.md](./NotulensiShow.md) | ⚠️ `404` | 0.022s |
| 135 | `GET` | `/kantor/tamu?per_page=15&page=1` | [TamuIndex.md](./TamuIndex.md) | ✅ `200` | 0.029s |
| 136 | `GET` | `/kantor/tamu/1` | [TamuShow.md](./TamuShow.md) | ⚠️ `404` | 0.03s |
| 137 | `GET` | `/kantor/pengaduan?per_page=15&page=1` | [PengaduanIndex.md](./PengaduanIndex.md) | ✅ `200` | 0.033s |
| 138 | `GET` | `/kantor/pengaduan/1` | [PengaduanShow.md](./PengaduanShow.md) | ⚠️ `404` | 0.026s |
| 139 | `GET` | `/kantor/holidays?per_page=15&page=1&year=2026` | [HolidaysIndex.md](./HolidaysIndex.md) | ✅ `200` | 0.027s |
| 140 | `GET` | `/kantor/holidays/1` | [HolidaysShow.md](./HolidaysShow.md) | ⚠️ `404` | 0.026s |
| 141 | `GET` | `/ipds/tikets/keluhan-options` | [IPDSTiketsKeluhanOptions.md](./IPDSTiketsKeluhanOptions.md) | ✅ `200` | 0.023s |
| 142 | `GET` | `/ipds/tikets?per_page=15&page=1` | [IPDSTiketsIndex.md](./IPDSTiketsIndex.md) | ✅ `200` | 0.03s |
| 143 | `GET` | `/ipds/tikets/3` | [IPDSTiketsShow.md](./IPDSTiketsShow.md) | ✅ `200` | 0.031s |
| 144 | `GET` | `/ipds/assets?per_page=15&page=1` | [IPDSAssetsIndex.md](./IPDSAssetsIndex.md) | ✅ `200` | 0.033s |
| 145 | `GET` | `/ipds/assets/1` | [IPDSAssetsShow.md](./IPDSAssetsShow.md) | ✅ `200` | 0.026s |
| 146 | `GET` | `/ipds/asset-it-maintenance-schedule?per_page=15&page=1` | [IPDSAssetMaintenanceScheduleIndex.md](./IPDSAssetMaintenanceScheduleIndex.md) | ✅ `200` | 0.026s |
| 147 | `GET` | `/ipds/asset-it-maintenance-schedule/1` | [IPDSAssetMaintenanceScheduleShow.md](./IPDSAssetMaintenanceScheduleShow.md) | ⚠️ `404` | 0.028s |
| 148 | `GET` | `/ipds/raw-datas?per_page=15&page=1` | [IPDSRawDatasIndex.md](./IPDSRawDatasIndex.md) | ⚠️ `403` | 0.04s |
| 149 | `GET` | `/ipds/raw-datas/1` | [IPDSRawDatasShow.md](./IPDSRawDatasShow.md) | ⚠️ `403` | 0.035s |
| 150 | `GET` | `/miniapp/surveycraft?per_page=15&page=1` | [SurveycraftIndex.md](./SurveycraftIndex.md) | ✅ `200` | 0.032s |
| 151 | `GET` | `/miniapp/surveycraft/options` | [SurveycraftOptions.md](./SurveycraftOptions.md) | ✅ `200` | 0.031s |
| 152 | `GET` | `/miniapp/surveycraft/respond?survey_id=1` | [SurveycraftRespond.md](./SurveycraftRespond.md) | ⚠️ `422` | 0.026s |
| 153 | `GET` | `/miniapp/surveycraft/responds?survey_id=1` | [SurveycraftResponds.md](./SurveycraftResponds.md) | ✅ `200` | 0.05s |
| 154 | `GET` | `/miniapp/surveycraft/1` | [SurveycraftShow.md](./SurveycraftShow.md) | ⚠️ `404` | 0.025s |

---
*Generated by get_results_laravel_runner.php*
