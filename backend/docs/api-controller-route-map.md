# API Controller Route Map

Source of truth used for this document:
- Controllers: `backend/app/Http/Controllers/Api`
- Routes: `backend/routes/api.php`

Scope notes:
- This document groups controllers by subfolder under `Api/`.
- Only routes registered in `backend/routes/api.php` are listed.
- Controllers without any registration in `backend/routes/api.php` are marked as `No route found`.
- Non-`Api` controllers referenced by API routes, such as DOCX/PDF helper controllers, are intentionally excluded.

## Root `Api/`

### `AuthApiController.php`
- `POST /api/login`
- `POST /api/auth/login`
- `POST /api/logout`

### `LandingApiController.php`
- `GET /api/landing`

### `ProfileApiController.php`
- `GET /api/profile`
- `PUT /api/profile`
- `PUT /api/profile/password`
- `GET /api/profile/pegawai`
- `PUT /api/profile/pegawai`

### `PdfApiController.php`
- `GET /api/pdf/spk/{id}`
- `GET /api/pdf/bast/{id}`
- `POST /api/spk/pdf/bulk-spks`
- `POST /api/spk/pdf/bulk-basts`

### `GeminiProxyController.php`
- `POST /api/ai/generate`

### `EnumsApiController.php`
- `GET /api/enums`
- `GET /api/enums/fungsi-types`
- `GET /api/enums/jabatan-tugas-types`
- `GET /api/enums/jenis-kegiatan-types`
- `GET /api/enums/satuan-types`
- `GET /api/enums/pangkat-types`
- `GET /api/enums/golongan-types`
- `GET /api/enums/jabatan-types`

### `DirektoriUsahaController.php`
- `GET /api/kantor/direktori-usaha`
- `GET /api/kantor/direktori-usaha/rekap-user`
- `GET /api/kantor/direktori-usaha/progres`
- `GET /api/kantor/direktori-usaha/filters`
- `GET /api/kantor/direktori-usaha/desa-options`
- `GET /api/kantor/direktori-usaha/no-gc-no-loc`
- `GET /api/kantor/direktori-usaha/invalid`
- `GET /api/kantor/direktori-usaha/siap-kirim`
- `GET /api/kantor/direktori-usaha/siap-kirim/csv`
- `GET /api/kantor/direktori-usaha/ganda`
- `GET /api/kantor/direktori-usaha/export/csv`
- `GET /api/kantor/direktori-usaha/csv-update`
- `GET /api/kantor/direktori-usaha/map-desa`
- `GET /api/kantor/direktori-usaha/{id}`
- `PUT /api/kantor/direktori-usaha/{id}`

### `BaseApiController.php`
- `No route found`

### `TestApiController.php`
- `No route found`

## `Api/Admin`

### `PermissionsApiController.php`
- `GET /api/admin/permissions`
- `GET /api/admin/permissions/grouped-prefixes`
- `GET /api/admin/permissions/form-options`
- `GET /api/admin/permissions/list-api-controllers`
- `GET /api/admin/permissions/{id}`
- `POST /api/admin/permissions`
- `POST /api/admin/permissions/bulk-create`
- `PUT /api/admin/permissions/{id}`
- `DELETE /api/admin/permissions/{id}`

### `RolesApiController.php`
- `GET /api/admin/roles/permission-options`
- `GET /api/admin/roles`
- `GET /api/admin/roles/{id}`
- `POST /api/admin/roles`
- `PUT /api/admin/roles/{id}`
- `PUT /api/admin/roles/{id}/permissions`
- `DELETE /api/admin/roles/{id}`

### `UserApiController.php`
- `GET /api/admin/users/organik`
- `GET /api/admin/users/mitra`
- `GET /api/admin/users`
- `POST /api/admin/users`
- `GET /api/admin/users/{id}`
- `PUT /api/admin/users/{id}`
- `PUT /api/admin/users/{id}/permissions`
- `DELETE /api/admin/users/{id}`
- `POST /api/admin/users/bulk-update-roles`

### `BaseApiController.php`
- `No route found`

## `Api/Meta`

### `WilayahApiController.php`
- `GET /api/meta/wilayah/kecamatan`

## `Api/Ipds`

### `TiketApiController.php`
- `GET /api/ipds/tikets/keluhan-options`
- `GET /api/ipds/tikets/statistics`
- `GET /api/ipds/tikets`
- `POST /api/ipds/tikets`
- `GET /api/ipds/tikets/{id}`
- `PUT /api/ipds/tikets/{id}`
- `DELETE /api/ipds/tikets/{id}`

### `AssetITApiController.php`
- `GET /api/ipds/assets/filters`
- `GET /api/ipds/assets/statistics`
- `GET /api/ipds/assets`
- `POST /api/ipds/assets`
- `GET /api/ipds/assets/{asset}`
- `PUT /api/ipds/assets/{asset}`
- `DELETE /api/ipds/assets/{asset}`

### `AssetITMaintenanceScheduleApiController.php`
- `GET /api/ipds/asset-it-maintenance-schedule`
- `POST /api/ipds/asset-it-maintenance-schedule`
- `GET /api/ipds/asset-it-maintenance-schedule/{asset_it_maintenance_schedule}`
- `PUT /api/ipds/asset-it-maintenance-schedule/{asset_it_maintenance_schedule}`
- `DELETE /api/ipds/asset-it-maintenance-schedule/{asset_it_maintenance_schedule}`

### `RawDataApiController.php`
- `GET /api/ipds/raw-datas`
- `POST /api/ipds/raw-datas`
- `GET /api/ipds/raw-datas/{id}`
- `PUT /api/ipds/raw-datas/{id}`
- `DELETE /api/ipds/raw-datas/{id}`

## `Api/Kantor`

### `PegawaiApiController.php`
- `GET /api/kantor/pegawai`
- `POST /api/kantor/pegawai`
- `GET /api/kantor/pegawai/filters`
- `GET /api/kantor/pegawai/form-options`
- `GET /api/kantor/pegawai/jabatan-pangkat-list`
- `GET /api/kantor/pegawai/{id}`
- `PUT /api/kantor/pegawai/{id}`
- `DELETE /api/kantor/pegawai/{id}`

### `SettingApiController.php`
- `GET /api/kantor/settings`
- `POST /api/kantor/settings`
- `GET /api/kantor/settings/grup-list`
- `GET /api/kantor/settings/officers`
- `GET /api/kantor/settings/key/{key}`
- `GET /api/kantor/settings/{id}`
- `PUT /api/kantor/settings/{id}`
- `DELETE /api/kantor/settings/{id}`

### `MitraApiController.php`
- `GET /api/kantor/mitra`
- `GET /api/kantor/mitra/filters`
- `GET /api/kantor/mitra/statistics`
- `GET /api/kantor/mitra/penugasan-options`
- `POST /api/kantor/mitra/penugasan`

### `KegiatanApiController.php`
- `GET /api/kantor/kegiatan/filter-list`
- `GET /api/kantor/kegiatan/calendar`
- `GET /api/kantor/kegiatan/form-options`
- `GET /api/kantor/kegiatan/by-year`
- `GET /api/kantor/kegiatan/statistics`
- `GET /api/kantor/kegiatan`
- `GET /api/kantor/kegiatan/{id}`
- `POST /api/kantor/kegiatan`
- `PUT /api/kantor/kegiatan/{id}`
- `DELETE /api/kantor/kegiatan/{id}`

### `PenugasanApiController.php`
- `GET /api/kantor/penugasan/mitra-dropdown`
- `GET /api/kantor/penugasan/mitra-options`
- `GET /api/kantor/penugasan/filters`
- `GET /api/kantor/penugasan/form-options`
- `GET /api/kantor/penugasan/kegiatan-options`
- `GET /api/kantor/penugasan/export`
- `GET /api/kantor/penugasan`
- `GET /api/kantor/penugasan/{id}`
- `POST /api/kantor/penugasan`
- `POST /api/kantor/penugasan/{id}/insert`
- `PUT /api/kantor/penugasan/{id}`
- `DELETE /api/kantor/penugasan/{id}`

### `UuApiController.php`
- `GET /api/kantor/uu`
- `POST /api/kantor/uu`
- `GET /api/kantor/uu/{id}`
- `PUT /api/kantor/uu/{id}`
- `DELETE /api/kantor/uu/{id}`

### `UuTambahApiController.php`
- `GET /api/kantor/uu-tambahan`
- `POST /api/kantor/uu-tambahan`
- `GET /api/kantor/uu-tambahan/{id}`
- `PUT /api/kantor/uu-tambahan/{id}`
- `DELETE /api/kantor/uu-tambahan/{id}`

### `BastApiController.php`
- `GET /api/kantor/bast`
- `GET /api/kantor/bast/available-months`
- `GET /api/kantor/bast/{id}`
- `PUT /api/kantor/bast/{id}`

### `SkpApiController.php`
- `GET /api/kantor/skp/dashboard`
- `GET /api/kantor/skp/stats`
- `GET /api/kantor/skp/stats2`
- `GET /api/kantor/skp/list`
- `GET /api/kantor/skp/{id}`
- `POST /api/kantor/skp`
- `PUT /api/kantor/skp/{id}`
- `DELETE /api/kantor/skp/{id}`

### `SpkApiController.php`
- `GET /api/kantor/spk`
- `GET /api/kantor/spk/{mitra_id}/{bln_bayar}`
- `PUT /api/kantor/spk/{mitra_id}/{bln_bayar}`
- `PUT /api/kantor/spk/bulk-update`
- `PUT /api/kantor/spk/bulk-update-bast`
- `GET /api/kantor/spk/monitoring`

### `LinkApiController.php`
- `GET /api/kantor/links`
- `POST /api/kantor/links`
- `GET /api/kantor/links/{id}`
- `PUT /api/kantor/links/{id}`
- `DELETE /api/kantor/links/{id}`

### `LaporanPerjalananDinasApiController.php`
- `GET /api/kantor/laporan-perjalanan-dinas`
- `POST /api/kantor/laporan-perjalanan-dinas`
- `GET /api/kantor/laporan-perjalanan-dinas/{id}`
- `PUT /api/kantor/laporan-perjalanan-dinas/{id}`
- `DELETE /api/kantor/laporan-perjalanan-dinas/{id}`
- `POST /api/kantor/laporan-perjalanan-dinas/{id}/details`
- `PUT /api/kantor/laporan-perjalanan-dinas/{id}/details/{detailId}`
- `DELETE /api/kantor/laporan-perjalanan-dinas/{id}/details/{detailId}`
- `POST /api/kantor/laporan-perjalanan-dinas/{id}/dokumentasi`
- `POST /api/kantor/laporan-perjalanan-dinas/{id}/dokumentasi/{docId}`
- `DELETE /api/kantor/laporan-perjalanan-dinas/{id}/dokumentasi/{docId}`

### `NotulensiApiController.php`
- `GET /api/kantor/notulensi/form-options`
- `GET /api/kantor/notulensi`
- `POST /api/kantor/notulensi`
- `GET /api/kantor/notulensi/{notulensi}`
- `PUT /api/kantor/notulensi/{notulensi}`
- `DELETE /api/kantor/notulensi/{notulensi}`
- `GET /api/kantor/notulensi/{id}/pdf`
- `GET /api/kantor/notulensi/{id}/docx`

### `TamuApiController.php`
- Public:
  - `POST /api/kantor/tamu`
- Protected:
  - `GET /api/kantor/tamu`
  - `GET /api/kantor/tamu/{id}`
  - `PUT /api/kantor/tamu/{id}`
  - `DELETE /api/kantor/tamu/{id}`

### `PengaduanApiController.php`
- Public:
  - `POST /api/kantor/pengaduan`
- Protected:
  - `GET /api/kantor/pengaduan`
  - `GET /api/kantor/pengaduan/{id}`
  - `PUT /api/kantor/pengaduan/{id}`
  - `DELETE /api/kantor/pengaduan/{id}`

### `HolidayApiController.php`
- `GET /api/kantor/holidays`
- `GET /api/kantor/holidays/{id}`
- `POST /api/kantor/holidays`
- `PUT /api/kantor/holidays/{id}`
- `DELETE /api/kantor/holidays/{id}`

### `MonitoringKegiatanApiController.php`
- `GET /api/kantor/kegiatan/monitoring/kegiatan-options`
- `GET /api/kantor/kegiatan/monitoring/all-kegiatan-options`
- `GET /api/kantor/kegiatan/monitoring/filters-options`
- `GET /api/kantor/kegiatan/monitoring/petugas-options`
- `GET /api/kantor/kegiatan/monitoring/pengawas-options`
- `GET /api/kantor/kegiatan/monitoring/supervisor-options`
- `GET /api/kantor/kegiatan/monitoring/sls-options`
- `GET /api/kantor/kegiatan/monitoring/blok-options`
- `GET /api/kantor/kegiatan/monitoring/kec-options`
- `GET /api/kantor/kegiatan/monitoring/desa-options`
- `GET /api/kantor/kegiatan/monitoring/detil-configuration`
- `GET /api/kantor/kegiatan/monitoring`
- `GET /api/kantor/kegiatan/monitoring/download`
- `GET /api/kantor/kegiatan/monitoring/{id}`
- `POST /api/kantor/kegiatan/monitoring`
- `PUT /api/kantor/kegiatan/monitoring/{id}`
- `DELETE /api/kantor/kegiatan/monitoring/{id}`

### `MonitoringKegiatanConfigApiController.php`
- `GET /api/kantor/kegiatan/monitoring/monitoring-kegiatan-config/kegiatan-options`
- `GET /api/kantor/kegiatan/monitoring/monitoring-kegiatan-config/{id}/available-detil-configs`
- `GET /api/kantor/kegiatan/monitoring/monitoring-kegiatan-config`
- `POST /api/kantor/kegiatan/monitoring/monitoring-kegiatan-config`
- `GET /api/kantor/kegiatan/monitoring/monitoring-kegiatan-config/{monitoring_kegiatan_config}`
- `PUT /api/kantor/kegiatan/monitoring/monitoring-kegiatan-config/{monitoring_kegiatan_config}`
- `DELETE /api/kantor/kegiatan/monitoring/monitoring-kegiatan-config/{monitoring_kegiatan_config}`

### `DetilConfigurationApiController.php`
- `GET /api/kantor/kegiatan/monitoring/detil-configurations/available-fields`
- `GET /api/kantor/kegiatan/monitoring/detil-configurations`
- `GET /api/kantor/kegiatan/monitoring/detil-configurations/{id}`
- `POST /api/kantor/kegiatan/monitoring/detil-configurations`
- `PUT /api/kantor/kegiatan/monitoring/detil-configurations/{id}`
- `DELETE /api/kantor/kegiatan/monitoring/detil-configurations/{id}`

## `Api/Kantor/NomorSurat`

### `PermintaanApiController.php`
- `GET /api/kantor/surat/permintaan`
- `GET /api/kantor/surat/permintaan/dates`
- `GET /api/kantor/surat/permintaan/years`
- `GET /api/kantor/surat/permintaan/klasifikasi`
- `GET /api/kantor/surat/permintaan/form-options`
- `GET /api/kantor/surat/permintaan/{id}`
- `POST /api/kantor/surat/permintaan`
- `POST /api/kantor/surat/permintaan/sisip`
- `PUT /api/kantor/surat/permintaan/{id}`
- `DELETE /api/kantor/surat/permintaan/{id}`
- `POST /api/kantor/surat/permintaan/bulk-update-status`

### `SuratKeluarApiController.php`
- `GET /api/kantor/surat/surat-keluar`
- `GET /api/kantor/surat/surat-keluar/dates`
- `GET /api/kantor/surat/surat-keluar/years`
- `GET /api/kantor/surat/surat-keluar/form-options`
- `GET /api/kantor/surat/surat-keluar/{id}`
- `POST /api/kantor/surat/surat-keluar`
- `POST /api/kantor/surat/surat-keluar/sisip`
- `PUT /api/kantor/surat/surat-keluar/{id}`
- `DELETE /api/kantor/surat/surat-keluar/{id}`

### `SuratKeputusanApiController.php`
- `GET /api/kantor/surat/surat-keputusan`
- `GET /api/kantor/surat/surat-keputusan/dates`
- `GET /api/kantor/surat/surat-keputusan/years`
- `GET /api/kantor/surat/surat-keputusan/form-options`
- `GET /api/kantor/surat/surat-keputusan/{id}`
- `POST /api/kantor/surat/surat-keputusan`
- `POST /api/kantor/surat/surat-keputusan/sisip`
- `PUT /api/kantor/surat/surat-keputusan/{id}`
- `DELETE /api/kantor/surat/surat-keputusan/{id}`

### `SuratTugasApiController.php`
- `GET /api/kantor/surat/surat-tugas`
- `GET /api/kantor/surat/surat-tugas/dates`
- `GET /api/kantor/surat/surat-tugas/years`
- `GET /api/kantor/surat/surat-tugas/klasifikasi`
- `GET /api/kantor/surat/surat-tugas/form-options`
- `GET /api/kantor/surat/surat-tugas/{id}`
- `POST /api/kantor/surat/surat-tugas`
- `POST /api/kantor/surat/surat-tugas/sisip`
- `PUT /api/kantor/surat/surat-tugas/{id}`
- `DELETE /api/kantor/surat/surat-tugas/{id}`

### `SurtugDetilApiController.php`
- `GET /api/kantor/surat/surtug-detil`
- `POST /api/kantor/surat/surtug-detil`
- `POST /api/kantor/surat/surtug-detil/insert`
- `GET /api/kantor/surat/surtug-detil/kegiatan-options`
- `GET /api/kantor/surat/surtug-detil/mitra-penugasan-options`
- `GET /api/kantor/surat/surtug-detil/pegawai-options`
- `GET /api/kantor/surat/surtug-detil/mitra-options`
- `POST /api/kantor/surat/surtug-detil/bulk-mitra`
- `GET /api/kantor/surat/surtug-detil/surtug/{surtug_id}`
- `GET /api/kantor/surat/surtug-detil/{id}`
- `PUT /api/kantor/surat/surtug-detil/{id}`
- `DELETE /api/kantor/surat/surtug-detil/{id}`

## `Api/Kantor/Dipa`

### `DipaBudgetApiController.php`
- `GET /api/kantor/dipa/monitoring`
- `GET /api/kantor/dipa/monitoring/summary`
- `GET /api/kantor/dipa/picker`
- `POST /api/kantor/dipa/usage`
- `POST /api/kantor/dipa/import`
- `GET /api/kantor/dipa/history`
- `GET /api/kantor/dipa/orphans`
- `POST /api/kantor/dipa/map`
- `GET /api/kantor/dipa/files`
- `PUT /api/kantor/dipa/usage/{id}`
- `DELETE /api/kantor/dipa/usage/{id}`
- `POST /api/kantor/dipa/plan`
- `GET /api/kantor/dipa/plan`
- `GET /api/kantor/dipa/reconciliation`
- `POST /api/kantor/dipa/import-sakti`
- `GET /api/kantor/dipa/dependencies/views`

## `Api/Miniapp`

### `SurveyController.php`
- Public `apiResource`:
  - `GET /api/miniapp/survey`
  - `POST /api/miniapp/survey`
  - `GET /api/miniapp/survey/{survey}`
  - `PUT/PATCH /api/miniapp/survey/{survey}`
  - `DELETE /api/miniapp/survey/{survey}`

### `SurveycraftApiController.php`
- Public:
  - `GET /api/miniapp/surveycraft/file/{path}`
  - `OPTIONS /api/miniapp/surveycraft/file/{path}`
  - `POST /api/miniapp/surveycraft/respond`
  - `GET /api/miniapp/surveycraft/share/{surveycraft}`
  - `POST /api/miniapp/surveycraft/test-generate-ai`
- Protected:
  - `GET /api/miniapp/surveycraft`
  - `POST /api/miniapp/surveycraft`
  - `POST /api/miniapp/surveycraft/generate-ai`
  - `GET /api/miniapp/surveycraft/options`
  - `GET /api/miniapp/surveycraft/respond`
  - `GET /api/miniapp/surveycraft/responds`
  - `GET /api/miniapp/surveycraft/{surveycraft}` with `withoutMiddleware('auth:sanctum')`
  - `POST /api/miniapp/surveycraft/{surveycraft}`
  - `PUT /api/miniapp/surveycraft/{surveycraft}`
  - `DELETE /api/miniapp/surveycraft/{surveycraft}`

## `Api/Miniapp/Surveys/Result`

### `SurveyResultController.php`
- Public `apiResource`:
  - `GET /api/miniapp/survey-results`
  - `POST /api/miniapp/survey-results`
  - `GET /api/miniapp/survey-results/{survey_result}`
  - `PUT/PATCH /api/miniapp/survey-results/{survey_result}`
  - `DELETE /api/miniapp/survey-results/{survey_result}`

## `Api/Adhock`

### `AlokasiApiController.php`
- `No route found`

### `CekGeorefApiController.php`
- `No route found`

### `CekScanApiController.php`
- `No route found`

### `PetaSlsApiController.php`
- `No route found`

### `SlsSipwApiController.php`
- `No route found`

## Quick Summary

Controllers currently present under `backend/app/Http/Controllers/Api` but not registered in `backend/routes/api.php`:
- `Api/BaseApiController.php`
- `Api/TestApiController.php`
- `Api/Admin/BaseApiController.php`
- `Api/Adhock/AlokasiApiController.php`
- `Api/Adhock/CekGeorefApiController.php`
- `Api/Adhock/CekScanApiController.php`
- `Api/Adhock/PetaSlsApiController.php`
- `Api/Adhock/SlsSipwApiController.php`
