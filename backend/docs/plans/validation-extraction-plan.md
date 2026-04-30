# Validation Extraction Plan — Endpoint-by-Endpoint

> **Goal**: Extract all inline validation (`$request->validate()`, `Validator::make()`, `$this->validate()`) from controllers into dedicated FormRequest classes, grouped by endpoint prefix.

---

## Table of Contents

1. [Summary](#summary)
2. [Already Extracted (No Action Needed)](#already-extracted)
3. [Extraction Tasks by Endpoint Group](#extraction-tasks-by-endpoint-group)
4. [Implementation Guidelines](#implementation-guidelines)
5. [Execution Order (Priority)](#execution-order)

---

## Summary

| Metric | Count |
|---|---|
| Total API endpoints (approx) | ~180 |
| Controllers with inline validation | **34** |
| Controllers already using FormRequest | **13** |
| New FormRequest classes to create | **~55** |
| Inline validation calls to extract | **~86** |

---

## Already Extracted

These controllers already use dedicated FormRequest classes. No action needed.

| Controller | FormRequest(s) Used | Status |
|---|---|---|
| `Admin\UserApiController` | `StoreUserRequest`, `UpdateUserRequest`, `DeleteUserRequest`, `ShowUserRequest`, `BulkUpdateUserRolesRequest`, `UpdateUserPermissionsRequest` | ✅ Done |
| `Kantor\KegiatanApiController` | `IndexKegiatanRequest`, `StoreKegiatanRequest`, `UpdateKegiatanRequest` | ✅ Done |
| `Kantor\LinkApiController` | `LinkRequest` | ✅ Done |
| `Kantor\LaporanPerjalananDinasApiController` | `StoreLaperdinRequest`, `UpdateLaperdinRequest`, `StoreLaperdinDetailRequest`, `UpdateLaperdinDetailRequest`, `StoreLaperdinDokumentasiRequest`, `UpdateLaperdinDokumentasiRequest` | ✅ Done |
| `Kantor\MonitoringKegiatanApiController` | `IndexMonitoringRequest`, `StoreMonitoringRequest`, `UpdateMonitoringRequest` | ✅ Done (partially — still has inline for option endpoints) |
| `Ipds\RawDataApiController` | `StoreRawDataRequest`, `UpdateRawDataRequest` | ✅ Done |
| `Kantor\MitraApiController` | `IndexMitraRequest`, `StoreMitraPenugasanRequest` | ✅ Done |
| `Ipds\TiketApiController` | `StoreTiketRequest`, `UpdateTiketRequest` | ✅ Done |
| `Kantor\PenugasanApiController` | `IndexPenugasanRequest`, `StorePenugasanRequest`, `InsertPenugasanRequest`, `UpdatePenugasanRequest` | ✅ Done |
| `Kantor\PegawaiApiController` | `AccessPegawaiRequest`, `IndexPegawaiRequest`, `StorePegawaiRequest`, `UpdatePegawaiRequest` | ✅ Done |
| `Kantor\SpkApiController` | `IndexSpkRequest`, `UpdateSpkRequest`, `BulkUpdateSpkRequest`, `BulkUpdateBastRequest`, `MonitoringSpkRequest` | ✅ Done |
| `Kantor\SkpApiController` | `IndexSkpRequest`, `ListSkpRequest`, `StoreSkpRequest`, `UpdateSkpRequest` | ✅ Done |
| `Miniapp\SurveycraftApiController` | `StoreSurveycraftRequest`, `UpdateSurveycraftRequest`, `StoreSurveyResponseRequest`, `GenerateSurveyAIRequest` | ✅ Done (partially — still has inline for respond endpoints) |

---

## Extraction Tasks by Endpoint Group

### Group 1: Auth (`POST /login`, `POST /logout`)

**Controller**: `AuthApiController`  
**File**: `app/Http/Controllers/Api/AuthApiController.php`

| Endpoint | Method | Current Validation | New FormRequest |
|---|---|---|---|
| `POST /login` | `login()` | `$request->validate(['email' => 'required\|email', 'password' => 'required'])` | `Requests\Auth\LoginRequest` |
| `POST /logout` | `logout()` | None (auth-only) | — |

**Files to create**:
- `app/Http/Requests/Auth/LoginRequest.php`

---

### Group 2: Profile (`PUT /profile`, `PUT /profile/password`)

**Controller**: `ProfileApiController`  
**File**: `app/Http/Controllers/Api/ProfileApiController.php`

| Endpoint | Method | Current Validation | New FormRequest |
|---|---|---|---|
| `PUT /profile` | `update()` | `$request->validate(['name' => 'required\|string\|max:255', 'email' => 'required\|email\|...'])` | `Requests\Profile\UpdateProfileRequest` |
| `PUT /profile/password` | `updatePassword()` | `$request->validate(['current_password' => 'required\|string', ...])` | `Requests\Profile\UpdatePasswordRequest` |

**Files to create**:
- `app/Http/Requests/Profile/UpdateProfileRequest.php`
- `app/Http/Requests/Profile/UpdatePasswordRequest.php`

---

### Group 3: Gemini AI (`POST /ai/generate`)

**Controller**: `GeminiProxyController`  
**File**: `app/Http/Controllers/Api/GeminiProxyController.php`

| Endpoint | Method | Current Validation | New FormRequest |
|---|---|---|---|
| `POST /ai/generate` | `proxy()` | `$request->validate(['prompt' => 'required\|string', ...])` | `Requests\Gemini\GenerateRequest` |

**Files to create**:
- `app/Http/Requests/Gemini/GenerateRequest.php`

---

### Group 4: Admin — Roles (`/admin/roles/*`)

**Controller**: `RolesApiController`  
**File**: `app/Http/Controllers/Api/Admin/RolesApiController.php`

| Endpoint | Method | Current Validation | New FormRequest |
|---|---|---|---|
| `POST /admin/roles` | `store()` | `$request->validate(['name' => 'required\|string\|unique:roles,name', ...])` | `Requests\Admin\Roles\StoreRoleRequest` |
| `PUT /admin/roles/{id}` | `update()` | `$request->validate(['name' => 'required\|string', ...])` | `Requests\Admin\Roles\UpdateRoleRequest` |
| `PUT /admin/roles/{id}/permissions` | `updatePermissions()` | `$request->validate(['permission_ids' => 'required\|array', ...])` | `Requests\Admin\Roles\UpdateRolePermissionsRequest` |

**Files to create**:
- `app/Http/Requests/Admin/Roles/StoreRoleRequest.php`
- `app/Http/Requests/Admin/Roles/UpdateRoleRequest.php`
- `app/Http/Requests/Admin/Roles/UpdateRolePermissionsRequest.php`

---

### Group 5: Admin — Permissions (`/admin/permissions/*`)

**Controller**: `PermissionsApiController`  
**File**: `app/Http/Controllers/Api/Admin/PermissionsApiController.php`

| Endpoint | Method | Current Validation | New FormRequest |
|---|---|---|---|
| `POST /admin/permissions` | `store()` | `$request->validate(['name' => 'required\|string\|unique:permissions,name', ...])` | `Requests\Admin\Permissions\StorePermissionRequest` |
| `POST /admin/permissions/bulk-create` | `bulkCreate()` | `$request->validate(['prefix' => 'required\|string\|max:255', ...])` | `Requests\Admin\Permissions\BulkCreatePermissionRequest` |
| `PUT /admin/permissions/{id}` | `update()` | `$request->validate(['name' => 'required\|string\|unique:permissions,name,' . $id, ...])` | `Requests\Admin\Permissions\UpdatePermissionRequest` |

**Files to create**:
- `app/Http/Requests/Admin/Permissions/StorePermissionRequest.php`
- `app/Http/Requests/Admin/Permissions/BulkCreatePermissionRequest.php`
- `app/Http/Requests/Admin/Permissions/UpdatePermissionRequest.php`

---

### Group 6: Admin — Meta (`/admin/metas/*`)

**Controller**: `MetaApiController`  
**File**: `app/Http/Controllers/Api/Admin/MetaApiController.php`

| Endpoint | Method | Current Validation | New FormRequest |
|---|---|---|---|
| `POST /admin/metas` | `store()` | `Validator::make($request->all(), ['parent_id' => 'nullable\|exists:metas,id', ...])` | `Requests\Admin\Meta\StoreMetaRequest` |
| `PUT /admin/metas/{id}` | `update()` | `Validator::make($request->all(), ['parent_id' => 'nullable\|exists:metas,id', ...])` | `Requests\Admin\Meta\UpdateMetaRequest` |

**Files to create**:
- `app/Http/Requests/Admin/Meta/StoreMetaRequest.php`
- `app/Http/Requests/Admin/Meta/UpdateMetaRequest.php`

---

### Group 7: Kantor — Setting (`/kantor/settings/*`)

**Controller**: `SettingApiController`  
**File**: `app/Http/Controllers/Api/Kantor/SettingApiController.php`

| Endpoint | Method | Current Validation | New FormRequest |
|---|---|---|---|
| `POST /kantor/settings` | `store()` | `Validator::make($request->all(), ['tahun' => 'required\|string\|max:4', ...])` | `Requests\Kantor\Setting\StoreSettingRequest` |
| `PUT /kantor/settings/{id}` | `update()` | `Validator::make($request->all(), ['tahun' => 'sometimes\|required\|string\|max:4', ...])` | `Requests\Kantor\Setting\UpdateSettingRequest` |

**Files to create**:
- `app/Http/Requests/Kantor/Setting/StoreSettingRequest.php`
- `app/Http/Requests/Kantor/Setting/UpdateSettingRequest.php`

---

### Group 8: Kantor — Tamu (`/kantor/tamu/*`)

**Controller**: `TamuApiController`  
**File**: `app/Http/Controllers/Api/Kantor/TamuApiController.php`

| Endpoint | Method | Current Validation | New FormRequest |
|---|---|---|---|
| `POST /kantor/tamu` (public) | `store()` | `$request->validate(['nama' => 'required\|string\|max:255', ...])` | `Requests\Kantor\Tamu\StoreTamuRequest` |
| `PUT /kantor/tamu/{id}` | `update()` | `$request->validate(['nama' => 'sometimes\|string\|max:255', ...])` | `Requests\Kantor\Tamu\UpdateTamuRequest` |

**Files to create**:
- `app/Http/Requests/Kantor/Tamu/StoreTamuRequest.php`
- `app/Http/Requests/Kantor/Tamu/UpdateTamuRequest.php`

---

### Group 9: Kantor — Pengaduan (`/kantor/pengaduan/*`)

**Controller**: `PengaduanApiController`  
**File**: `app/Http/Controllers/Api/Kantor/PengaduanApiController.php`

| Endpoint | Method | Current Validation | New FormRequest |
|---|---|---|---|
| `POST /kantor/pengaduan` (public) | `store()` | `$request->validate(['jenis_pelangaran' => 'required\|string\|max:255', ...])` | `Requests\Kantor\Pengaduan\StorePengaduanRequest` |
| `PUT /kantor/pengaduan/{id}` | `update()` | `$request->validate(['jenis_pelangaran' => 'sometimes\|string\|max:255', ...])` | `Requests\Kantor\Pengaduan\UpdatePengaduanRequest` |

**Files to create**:
- `app/Http/Requests/Kantor/Pengaduan/StorePengaduanRequest.php`
- `app/Http/Requests/Kantor/Pengaduan/UpdatePengaduanRequest.php`

---

### Group 10: Kantor — Holiday (`/kantor/holidays/*`)

**Controller**: `HolidayApiController`  
**File**: `app/Http/Controllers/Api/Kantor/HolidayApiController.php`

| Endpoint | Method | Current Validation | New FormRequest |
|---|---|---|---|
| `POST /kantor/holidays` | `store()` | `Validator::make($request->all(), ['tanggal' => 'required\|date', ...])` | `Requests\Kantor\Holiday\StoreHolidayRequest` |
| `PUT /kantor/holidays/{id}` | `update()` | `Validator::make($request->all(), ['tanggal' => 'sometimes\|required\|date', ...])` | `Requests\Kantor\Holiday\UpdateHolidayRequest` |

**Files to create**:
- `app/Http/Requests/Kantor/Holiday/StoreHolidayRequest.php`
- `app/Http/Requests/Kantor/Holiday/UpdateHolidayRequest.php`

---

### Group 11: Kantor — Notulensi (`/kantor/notulensi/*`)

**Controller**: `NotulensiApiController`  
**File**: `app/Http/Controllers/Api/Kantor/NotulensiApiController.php`

| Endpoint | Method | Current Validation | New FormRequest |
|---|---|---|---|
| `POST /kantor/notulensi` | `store()` | `Validator::make($request->all(), ['judul' => 'required\|string\|max:255', ...])` | `Requests\Kantor\Notulensi\StoreNotulensiRequest` |
| `PUT /kantor/notulensi/{id}` | `update()` | `Validator::make($request->all(), ['judul' => 'required\|string\|max:255', ...])` | `Requests\Kantor\Notulensi\UpdateNotulensiRequest` |

**Files to create**:
- `app/Http/Requests/Kantor/Notulensi/StoreNotulensiRequest.php`
- `app/Http/Requests/Kantor/Notulensi/UpdateNotulensiRequest.php`

---

### Group 12: Kantor — UU (`/kantor/uu/*`)

**Controller**: `UuApiController`  
**File**: `app/Http/Controllers/Api/Kantor/UuApiController.php`

| Endpoint | Method | Current Validation | New FormRequest |
|---|---|---|---|
| `POST /kantor/uu` | `store()` | `Validator::make($request->all(), ['jenis' => 'required\|string\|max:255', ...])` | `Requests\Kantor\Uu\StoreUuRequest` |
| `PUT /kantor/uu/{id}` | `update()` | `Validator::make($request->all(), ['jenis' => 'required\|string\|max:255', ...])` | `Requests\Kantor\Uu\UpdateUuRequest` |

**Files to create**:
- `app/Http/Requests/Kantor/Uu/StoreUuRequest.php`
- `app/Http/Requests/Kantor/Uu/UpdateUuRequest.php`

---

### Group 13: Kantor — UU Tambahan (`/kantor/uu-tambahan/*`)

**Controller**: `UuTambahApiController`  
**File**: `app/Http/Controllers/Api/Kantor/UuTambahApiController.php`

| Endpoint | Method | Current Validation | New FormRequest |
|---|---|---|---|
| `POST /kantor/uu-tambahan` | `store()` | `Validator::make($request->all(), ['jenis_surat' => 'required\|string\|max:255', ...])` | `Requests\Kantor\UuTambah\StoreUuTambahRequest` |
| `PUT /kantor/uu-tambahan/{id}` | `update()` | `Validator::make($request->all(), ['jenis_surat' => 'required\|string\|max:255', ...])` | `Requests\Kantor\UuTambah\UpdateUuTambahRequest` |

**Files to create**:
- `app/Http/Requests/Kantor/UuTambah/StoreUuTambahRequest.php`
- `app/Http/Requests/Kantor/UuTambah/UpdateUuTambahRequest.php`

---

### Group 14: Kantor — BAST (`/kantor/bast/*`)

**Controller**: `BastApiController`  
**File**: `app/Http/Controllers/Api/Kantor/BastApiController.php`

| Endpoint | Method | Current Validation | New FormRequest |
|---|---|---|---|
| `PUT /kantor/bast/{id}` | `update()` | `Validator::make($request->all(), ['no_bast' => 'sometimes\|numeric\|min:1', ...])` | `Requests\Kantor\Bast\UpdateBastRequest` |

**Files to create**:
- `app/Http/Requests/Kantor/Bast/UpdateBastRequest.php`

---

### Group 15: Kantor — Direktori Usaha (`/kantor/direktori-usaha/*`)

**Controller**: `DirektoriUsahaController`  
**File**: `app/Http/Controllers/Api/DirektoriUsahaController.php`

| Endpoint | Method | Current Validation | New FormRequest |
|---|---|---|---|
| `PUT /kantor/direktori-usaha/{id}` | `update()` | `Validator::make($request->all(), ['hasilgc' => 'required\|integer\|in:1,3,4,99', ...])` | `Requests\Kantor\DirektoriUsaha\UpdateDirektoriUsahaRequest` |
| `GET /kantor/direktori-usaha/map-desa` | `mapDesa()` | `Validator::make($request->all(), ['kdkec' => 'required\|string', ...])` | `Requests\Kantor\DirektoriUsaha\MapDesaRequest` |

**Files to create**:
- `app/Http/Requests/Kantor/DirektoriUsaha/UpdateDirektoriUsahaRequest.php`
- `app/Http/Requests/Kantor/DirektoriUsaha/MapDesaRequest.php`

---

### Group 16: Kantor — Detil Configuration (`/kantor/kegiatan/monitoring/detil-configurations/*`)

**Controller**: `DetilConfigurationApiController`  
**File**: `app/Http/Controllers/Api/Kantor/DetilConfigurationApiController.php`

| Endpoint | Method | Current Validation | New FormRequest |
|---|---|---|---|
| `POST /kantor/kegiatan/monitoring/detil-configurations` | `storeConfiguration()` | `$request->validate(['foreign_key_value' => 'required', ...])` | `Requests\Kantor\DetilConfiguration\StoreDetilConfigurationRequest` |
| `PUT /kantor/kegiatan/monitoring/detil-configurations/{id}` | `update()` | `$request->validate(['name' => 'string', ...])` | `Requests\Kantor\DetilConfiguration\UpdateDetilConfigurationRequest` |

**Files to create**:
- `app/Http/Requests/Kantor/DetilConfiguration/StoreDetilConfigurationRequest.php`
- `app/Http/Requests/Kantor/DetilConfiguration/UpdateDetilConfigurationRequest.php`

---

### Group 17: Kantor — Monitoring Kegiatan Config (`/kantor/kegiatan/monitoring/monitoring-kegiatan-config/*`)

**Controller**: `MonitoringKegiatanConfigApiController`  
**File**: `app/Http/Controllers/Api/Kantor/MonitoringKegiatanConfigApiController.php`

| Endpoint | Method | Current Validation | New FormRequest |
|---|---|---|---|
| `POST .../monitoring-kegiatan-config` | `store()` | `$request->validate(['fungsi' => 'required\|string', ...])` | `Requests\Kantor\MonitoringKegiatanConfig\StoreMonitoringConfigRequest` |
| `PUT .../monitoring-kegiatan-config/{id}` | `update()` | `$request->validate(['fungsi' => 'string', ...])` | `Requests\Kantor\MonitoringKegiatanConfig\UpdateMonitoringConfigRequest` |
| `POST .../available-detil-configs` | `getAvailableDetilConfigurations()` | `$request->validate(['foreign_key_value' => 'required'])` | `Requests\Kantor\MonitoringKegiatanConfig\AvailableDetilConfigRequest` |

**Files to create**:
- `app/Http/Requests/Kantor/MonitoringKegiatanConfig/StoreMonitoringConfigRequest.php`
- `app/Http/Requests/Kantor/MonitoringKegiatanConfig/UpdateMonitoringConfigRequest.php`
- `app/Http/Requests/Kantor/MonitoringKegiatanConfig/AvailableDetilConfigRequest.php`

---

### Group 18: Kantor — Monitoring Kegiatan (remaining inline)

**Controller**: `MonitoringKegiatanApiController`  
**File**: `app/Http/Controllers/Api/Kantor/MonitoringKegiatanApiController.php`

| Endpoint | Method | Current Validation | New FormRequest |
|---|---|---|---|
| `GET .../petugas-options` | `petugasOptions()` | `$request->validate(['kegiatan_id' => 'required\|exists:kegiatan,id'])` | `Requests\Kantor\MonitoringKegiatan\PetugasOptionsRequest` |
| `GET .../desa-options` | `desaOptions()` | `$request->validate(['kec_id' => 'required'])` | `Requests\Kantor\MonitoringKegiatan\DesaOptionsRequest` |
| `GET .../detil-configuration` | `getDetilConfiguration()` | `$request->validate(['monitoring_kegiatan_config_id' => 'required'])` | `Requests\Kantor\MonitoringKegiatan\DetilConfigurationFilterRequest` |
| `GET .../download` | `download()` | `$request->validate(['kegiatan_id' => 'required\|string'])` | `Requests\Kantor\MonitoringKegiatan\DownloadMonitoringRequest` |

**Files to create**:
- `app/Http/Requests/Kantor/MonitoringKegiatan/PetugasOptionsRequest.php`
- `app/Http/Requests/Kantor/MonitoringKegiatan/DesaOptionsRequest.php`
- `app/Http/Requests/Kantor/MonitoringKegiatan/DetilConfigurationFilterRequest.php`
- `app/Http/Requests/Kantor/MonitoringKegiatan/DownloadMonitoringRequest.php`

---

### Group 19: Kantor — Nomor Surat — Permintaan (`/kantor/surat/permintaan/*`)

**Controller**: `PermintaanApiController`  
**File**: `app/Http/Controllers/Api/Kantor/NomorSurat/PermintaanApiController.php`

| Endpoint | Method | Current Validation | New FormRequest |
|---|---|---|---|
| `POST /kantor/surat/permintaan` | `store()` | `Validator::make($request->all(), ['thn' => 'required', ...])` | `Requests\Kantor\NomorSurat\Permintaan\StorePermintaanRequest` |
| `POST /kantor/surat/permintaan/sisip` | `sisip()` | `Validator::make($request->all(), ['tanggal' => 'required\|date', ...])` | `Requests\Kantor\NomorSurat\Permintaan\SisipPermintaanRequest` |
| `PUT /kantor/surat/permintaan/{id}` | `update()` | `Validator::make($request->all(), ['id' => 'required\|exists:surat_permintaan,id', ...])` | `Requests\Kantor\NomorSurat\Permintaan\UpdatePermintaanRequest` |
| `POST /kantor/surat/permintaan/bulk-update-status` | `bulkUpdateStatus()` | `Validator::make($request->all(), ['ids' => 'required\|array', ...])` | `Requests\Kantor\NomorSurat\Permintaan\BulkUpdateStatusRequest` |

**Files to create**:
- `app/Http/Requests/Kantor/NomorSurat/Permintaan/StorePermintaanRequest.php`
- `app/Http/Requests/Kantor/NomorSurat/Permintaan/SisipPermintaanRequest.php`
- `app/Http/Requests/Kantor/NomorSurat/Permintaan/UpdatePermintaanRequest.php`
- `app/Http/Requests/Kantor/NomorSurat/Permintaan/BulkUpdateStatusRequest.php`

---

### Group 20: Kantor — Nomor Surat — Surat Keluar (`/kantor/surat/surat-keluar/*`)

**Controller**: `SuratKeluarApiController`  
**File**: `app/Http/Controllers/Api/Kantor/NomorSurat/SuratKeluarApiController.php`

| Endpoint | Method | Current Validation | New FormRequest |
|---|---|---|---|
| `POST /kantor/surat/surat-keluar` | `store()` | `Validator::make($request->all(), ['thn' => 'nullable\|string', ...])` | `Requests\Kantor\NomorSurat\SuratKeluar\StoreSuratKeluarRequest` |
| `POST /kantor/surat/surat-keluar/sisip` | `insert()` | `Validator::make($request->all(), ['thn' => 'required', ...])` | `Requests\Kantor\NomorSurat\SuratKeluar\SisipSuratKeluarRequest` |
| `PUT /kantor/surat/surat-keluar/{id}` | `update()` | `Validator::make($request->all(), ['id' => 'required\|integer\|exists:surat_keluar,id', ...])` | `Requests\Kantor\NomorSurat\SuratKeluar\UpdateSuratKeluarRequest` |

**Files to create**:
- `app/Http/Requests/Kantor/NomorSurat/SuratKeluar/StoreSuratKeluarRequest.php`
- `app/Http/Requests/Kantor/NomorSurat/SuratKeluar/SisipSuratKeluarRequest.php`
- `app/Http/Requests/Kantor/NomorSurat/SuratKeluar/UpdateSuratKeluarRequest.php`

---

### Group 21: Kantor — Nomor Surat — Surat Keputusan (`/kantor/surat/surat-keputusan/*`)

**Controller**: `SuratKeputusanApiController`  
**File**: `app/Http/Controllers/Api/Kantor/NomorSurat/SuratKeputusanApiController.php`

| Endpoint | Method | Current Validation | New FormRequest |
|---|---|---|---|
| `POST /kantor/surat/surat-keputusan` | `store()` | `Validator::make($request->all(), ['thn' => 'required', ...])` | `Requests\Kantor\NomorSurat\SuratKeputusan\StoreSuratKeputusanRequest` |
| `POST /kantor/surat/surat-keputusan/sisip` | `sisip()` | `Validator::make($request->all(), ['tanggal' => 'required\|date', ...])` | `Requests\Kantor\NomorSurat\SuratKeputusan\SisipSuratKeputusanRequest` |
| `PUT /kantor/surat/surat-keputusan/{id}` | `update()` | `Validator::make($request->all(), ['id' => 'required\|integer\|exists:surat_sk_bast,id', ...])` | `Requests\Kantor\NomorSurat\SuratKeputusan\UpdateSuratKeputusanRequest` |

**Files to create**:
- `app/Http/Requests/Kantor/NomorSurat/SuratKeputusan/StoreSuratKeputusanRequest.php`
- `app/Http/Requests/Kantor/NomorSurat/SuratKeputusan/SisipSuratKeputusanRequest.php`
- `app/Http/Requests/Kantor/NomorSurat/SuratKeputusan/UpdateSuratKeputusanRequest.php`

---

### Group 22: Kantor — Nomor Surat — Surat Tugas (`/kantor/surat/surat-tugas/*`)

**Controller**: `SuratTugasApiController`  
**File**: `app/Http/Controllers/Api/Kantor/NomorSurat/SuratTugasApiController.php`

| Endpoint | Method | Current Validation | New FormRequest |
|---|---|---|---|
| `POST /kantor/surat/surat-tugas` | `store()` | `Validator::make($request->all(), ['tahun' => 'required', ...])` | `Requests\Kantor\NomorSurat\SuratTugas\StoreSuratTugasRequest` |
| `POST /kantor/surat/surat-tugas/sisip` | `insert()` | `Validator::make($request->all(), ['tahun' => 'required', ...])` | `Requests\Kantor\NomorSurat\SuratTugas\SisipSuratTugasRequest` |
| `PUT /kantor/surat/surat-tugas/{id}` | `update()` | `Validator::make($request->all(), ['tahun' => 'required', ...])` | `Requests\Kantor\NomorSurat\SuratTugas\UpdateSuratTugasRequest` |

**Files to create**:
- `app/Http/Requests/Kantor/NomorSurat/SuratTugas/StoreSuratTugasRequest.php`
- `app/Http/Requests/Kantor/NomorSurat/SuratTugas/SisipSuratTugasRequest.php`
- `app/Http/Requests/Kantor/NomorSurat/SuratTugas/UpdateSuratTugasRequest.php`

---

### Group 23: Kantor — Nomor Surat — Surtug Detil (`/kantor/surat/surtug-detil/*`)

**Controller**: `SurtugDetilApiController`  
**File**: `app/Http/Controllers/Api/Kantor/NomorSurat/SurtugDetilApiController.php`

| Endpoint | Method | Current Validation | New FormRequest |
|---|---|---|---|
| `POST /kantor/surat/surtug-detil` | `store()` | `Validator::make($request->all(), ['surtug_id' => 'required\|integer\|exists:surat_tugas,id', ...])` | `Requests\Kantor\NomorSurat\SurtugDetil\StoreSurtugDetilRequest` |
| `POST /kantor/surat/surtug-detil/insert` | `insert()` | `Validator::make($request->all(), ['surtug_id' => 'required\|integer\|exists:surat_tugas,id', ...])` | `Requests\Kantor\NomorSurat\SurtugDetil\InsertSurtugDetilRequest` |
| `POST /kantor/surat/surtug-detil/bulk-mitra` | `bulkMitra()` | `Validator::make($request->all(), ['surtug_id' => 'required\|integer\|exists:surat_tugas,id', ...])` | `Requests\Kantor\NomorSurat\SurtugDetil\BulkMitraSurtugDetilRequest` |
| `PUT /kantor/surat/surtug-detil/{id}` | `update()` | `Validator::make($request->all(), ['surtug_id' => 'required\|integer\|exists:surat_tugas,id', ...])` | `Requests\Kantor\NomorSurat\SurtugDetil\UpdateSurtugDetilRequest` |

**Files to create**:
- `app/Http/Requests/Kantor/NomorSurat/SurtugDetil/StoreSurtugDetilRequest.php`
- `app/Http/Requests/Kantor/NomorSurat/SurtugDetil/InsertSurtugDetilRequest.php`
- `app/Http/Requests/Kantor/NomorSurat/SurtugDetil/BulkMitraSurtugDetilRequest.php`
- `app/Http/Requests/Kantor/NomorSurat/SurtugDetil/UpdateSurtugDetilRequest.php`

---

### Group 24: Kantor — Nomor Surat — SK Detil (`/kantor/surat/sk-detil/*`)

**Controller**: `SkDetilApiController`  
**File**: `app/Http/Controllers/Api/Kantor/NomorSurat/SkDetilApiController.php`

| Endpoint | Method | Current Validation | New FormRequest |
|---|---|---|---|
| `POST /kantor/surat/sk-detil` | `store()` | `Validator::make($request->all(), ['sk_id' => 'required\|integer\|exists:sk_bast,id', ...])` | `Requests\Kantor\NomorSurat\SkDetil\StoreSkDetilRequest` |
| `POST /kantor/surat/sk-detil/bulk-mitra` | `bulkMitra()` | `Validator::make($request->all(), ['kegiatan_id' => 'required\|integer', ...])` | `Requests\Kantor\NomorSurat\SkDetil\BulkMitraSkDetilRequest` |
| `PUT /kantor/surat/sk-detil/{id}` | `update()` | `Validator::make($request->all(), ['sk_id' => 'required\|integer\|exists:sk_bast,id', ...])` | `Requests\Kantor\NomorSurat\SkDetil\UpdateSkDetilRequest` |

**Files to create**:
- `app/Http/Requests/Kantor/NomorSurat/SkDetil/StoreSkDetilRequest.php`
- `app/Http/Requests/Kantor/NomorSurat/SkDetil/BulkMitraSkDetilRequest.php`
- `app/Http/Requests/Kantor/NomorSurat/SkDetil/UpdateSkDetilRequest.php`

---

### Group 25: Kantor — DIPA Budget (`/kantor/dipa/*`)

**Controller**: `DipaBudgetApiController`  
**File**: `app/Http/Controllers/Api/Kantor/Dipa/DipaBudgetApiController.php`

| Endpoint | Method | Current Validation | New FormRequest |
|---|---|---|---|
| `POST /kantor/dipa/usage` | `recordUsage()` | `$request->validate(['budget_item_key' => 'required\|string', ...])` | `Requests\Kantor\Dipa\RecordUsageRequest` |
| `POST /kantor/dipa/usage/sync-fa` | `syncFaUsage()` | `$request->validate(['usage_date' => 'required\|date', ...])` | `Requests\Kantor\Dipa\SyncFaUsageRequest` |
| `POST /kantor/dipa/import` | `importDipaFile()` | `$request->validate(['usage_date' => ['required', 'regex:/^\d{4}-(0[1-9]\|1[0-2])$/'], ...])` | `Requests\Kantor\Dipa\ImportDipaRequest` |
| `POST /kantor/dipa/map` | `saveMapping()` | `$request->validate(['revisi' => 'required\|numeric', ...])` | `Requests\Kantor\Dipa\SaveMappingRequest` |
| `POST /kantor/dipa/files/{id}/reimport` | `reimportFile()` | `$request->validate(['old_composite_key' => 'required\|string', ...])` | `Requests\Kantor\Dipa\ReimportFileRequest` |
| `PUT /kantor/dipa/usage/{id}` | `updateUsageAmount()` | `$request->validate(['amount_spent' => 'required\|numeric\|gt:0'])` | `Requests\Kantor\Dipa\UpdateUsageAmountRequest` |
| `DELETE /kantor/dipa/usage/{id}` | `deleteUsage()` | `$request->validate(['budget_item_key' => 'required\|string', ...])` | `Requests\Kantor\Dipa\DeleteUsageRequest` |
| `POST /kantor/dipa/plan` | `recordPlan()` | `$request->validate(['planned_amount' => 'required\|numeric\|gt:0', ...])` | `Requests\Kantor\Dipa\RecordPlanRequest` |
| `PUT /kantor/dipa/plan/{id}` | `updatePlan()` | `$request->validate(['tahun_anggaran' => 'required\|numeric', ...])` | `Requests\Kantor\Dipa\UpdatePlanRequest` |
| `DELETE /kantor/dipa/plan/{id}` | `deletePlan()` | (may have validation) | `Requests\Kantor\Dipa\DeletePlanRequest` |
| `POST /kantor/dipa/import-sakti` | `importSakti()` | (may have validation) | `Requests\Kantor\Dipa\ImportSaktiRequest` |

**Files to create**:
- `app/Http/Requests/Kantor/Dipa/RecordUsageRequest.php`
- `app/Http/Requests/Kantor/Dipa/SyncFaUsageRequest.php`
- `app/Http/Requests/Kantor/Dipa/ImportDipaRequest.php`
- `app/Http/Requests/Kantor/Dipa/SaveMappingRequest.php`
- `app/Http/Requests/Kantor/Dipa/ReimportFileRequest.php`
- `app/Http/Requests/Kantor/Dipa/UpdateUsageAmountRequest.php`
- `app/Http/Requests/Kantor/Dipa/DeleteUsageRequest.php`
- `app/Http/Requests/Kantor/Dipa/RecordPlanRequest.php`
- `app/Http/Requests/Kantor/Dipa/UpdatePlanRequest.php`
- `app/Http/Requests/Kantor/Dipa/DeletePlanRequest.php`
- `app/Http/Requests/Kantor/Dipa/ImportSaktiRequest.php`

---

### Group 26: IPDS — Asset IT (`/ipds/assets/*`)

**Controller**: `AssetITApiController`  
**File**: `app/Http/Controllers/Api/Ipds/AssetITApiController.php`

| Endpoint | Method | Current Validation | New FormRequest |
|---|---|---|---|
| `POST /ipds/assets` | `store()` | `$request->validate(['kode_asset' => 'required\|string\|unique:asset_it,kode_asset', ...])` | `Requests\Ipds\AssetIT\StoreAssetITRequest` |
| `PUT /ipds/assets/{id}` | `update()` | `$request->validate(['kode_asset' => [...], ...])` | `Requests\Ipds\AssetIT\UpdateAssetITRequest` |

**Files to create**:
- `app/Http/Requests/Ipds/AssetIT/StoreAssetITRequest.php`
- `app/Http/Requests/Ipds/AssetIT/UpdateAssetITRequest.php`

---

### Group 27: IPDS — Asset IT Maintenance Schedule (`/ipds/asset-it-maintenance-schedule/*`)

**Controller**: `AssetITMaintenanceScheduleApiController`  
**File**: `app/Http/Controllers/Api/Ipds/AssetITMaintenanceScheduleApiController.php`

| Endpoint | Method | Current Validation | New FormRequest |
|---|---|---|---|
| `POST /ipds/asset-it-maintenance-schedule` | `store()` | `$request->validate(['asset_id' => 'required\|integer\|exists:asset_it,id', ...])` | `Requests\Ipds\AssetITMaintenance\StoreMaintenanceScheduleRequest` |
| `PUT /ipds/asset-it-maintenance-schedule/{id}` | `update()` | `$request->validate(['asset_id' => 'sometimes\|required\|integer\|exists:asset_it,id', ...])` | `Requests\Ipds\AssetITMaintenance\UpdateMaintenanceScheduleRequest` |

**Files to create**:
- `app/Http/Requests/Ipds/AssetITMaintenance/StoreMaintenanceScheduleRequest.php`
- `app/Http/Requests/Ipds/AssetITMaintenance/UpdateMaintenanceScheduleRequest.php`

---

### Group 28: IPDS — Tiket (partial — still uses `$request->only()`)

**Controller**: `TiketApiController`  
**File**: `app/Http/Controllers/Api/Ipds/TiketApiController.php`

> ⚠️ Already uses `StoreTiketRequest` and `UpdateTiketRequest` for store/update, but `store()` still uses `$request->only(['jenis_keluhan', 'deskripsi'])` and `update()` uses `$request->only(['status', 'keterangan'])`. Verify FormRequest rules cover all fields.

**Action**: Review existing `StoreTiketRequest` and `UpdateTiketRequest` to ensure all validated fields match the `only()` calls. Replace `only()` with `validated()`.

---

### Group 29: Miniapp — Survey (`/miniapp/survey/*`)

**Controller**: `SurveyController`  
**File**: `app/Http/Controllers/Api/Miniapp/SurveyController.php`

| Endpoint | Method | Current Validation | New FormRequest |
|---|---|---|---|
| `POST /miniapp/survey` | `store()` | `$request->validate(['survey_name' => 'required\|string\|max:255', ...])` | `Requests\Miniapp\Survey\StoreSurveyRequest` |
| `PUT /miniapp/survey/{id}` | `update()` | `$request->validate(['survey_name' => 'sometimes\|required\|string\|max:255', ...])` | `Requests\Miniapp\Survey\UpdateSurveyRequest` |

**Files to create**:
- `app/Http/Requests/Miniapp/Survey/StoreSurveyRequest.php`
- `app/Http/Requests/Miniapp/Survey/UpdateSurveyRequest.php`

---

### Group 30: Miniapp — Survey Result (`/miniapp/survey-results/*`)

**Controller**: `SurveyResultController`  
**File**: `app/Http/Controllers/Api/Miniapp/Surveys/Result/SurveyResultController.php`

| Endpoint | Method | Current Validation | New FormRequest |
|---|---|---|---|
| `POST /miniapp/survey-results` | `store()` | `$request->validate(['survey_id' => 'required\|exists:surveys,id', ...])` | `Requests\Miniapp\SurveyResult\StoreSurveyResultRequest` |
| `PUT /miniapp/survey-results/{id}` | `update()` | `$request->validate(['survey_id' => 'sometimes\|required\|exists:surveys,id', ...])` | `Requests\Miniapp\SurveyResult\UpdateSurveyResultRequest` |

**Files to create**:
- `app/Http/Requests/Miniapp/SurveyResult/StoreSurveyResultRequest.php`
- `app/Http/Requests/Miniapp/SurveyResult/UpdateSurveyResultRequest.php`

---

### Group 31: Miniapp — Surveycraft (remaining inline)

**Controller**: `SurveycraftApiController`  
**File**: `app/Http/Controllers/Api/Miniapp/SurveycraftApiController.php`

| Endpoint | Method | Current Validation | New FormRequest |
|---|---|---|---|
| `GET /miniapp/surveycraft/respond` | `respond()` | `$request->validate(['survey_id' => 'required\|string'])` | `Requests\Miniapp\Surveycraft\RespondListRequest` |
| `GET /miniapp/surveycraft/responds` | `responds()` | `$request->validate(['survey_id' => 'required\|string'])` | `Requests\Miniapp\Surveycraft\RespondsListRequest` |
| `POST /miniapp/surveycraft/respond` | `respond_store()` | `$request->validate(['respond_id' => ['required', 'string', 'regex:/.../']])` | `Requests\Miniapp\Surveycraft\StoreRespondRequest` |

**Files to create**:
- `app/Http/Requests/Miniapp/Surveycraft/RespondListRequest.php`
- `app/Http/Requests/Miniapp/Surveycraft/RespondsListRequest.php`
- `app/Http/Requests/Miniapp/Surveycraft/StoreRespondRequest.php`

---

### Group 32: PDF (`/pdf/*`, `/spk/pdf/*`)

**Controller**: `PdfApiController`  
**File**: `app/Http/Controllers/Api/PdfApiController.php`

| Endpoint | Method | Current Validation | New FormRequest |
|---|---|---|---|
| `POST /spk/pdf/bulk-spks` | `bulkGenerateSPK()` | `Validator::make($request->all(), ['ids' => 'required\|array', ...])` | `Requests\Pdf\BulkGenerateSpkRequest` |
| `POST /spk/pdf/bulk-basts` | `bulkGenerateBAST()` | `Validator::make($request->all(), ['ids' => 'required\|array', ...])` | `Requests\Pdf\BulkGenerateBastRequest` |

**Files to create**:
- `app/Http/Requests/Pdf/BulkGenerateSpkRequest.php`
- `app/Http/Requests/Pdf/BulkGenerateBastRequest.php`

---

### Group 33: Adhock — Peta SLS

**Controller**: `PetaSlsApiController`  
**File**: `app/Http/Controllers/Api/Adhock/PetaSlsApiController.php`

| Endpoint | Method | Current Validation | New FormRequest |
|---|---|---|---|
| `PUT /peta-sls/{id}` (or similar) | `update()` | `$request->validate(['keterangan' => ['present', 'nullable', 'string', 'max:1000']])` | `Requests\Adhock\PetaSls\UpdatePetaSlsRequest` |

**Files to create**:
- `app/Http/Requests/Adhock/PetaSls/UpdatePetaSlsRequest.php`

---

### Group 34: Adhock — Alokasi

**Controller**: `AlokasiApiController`  
**File**: `app/Http/Controllers/Api/Adhock/AlokasiApiController.php`

| Endpoint | Method | Current Validation | New FormRequest |
|---|---|---|---|
| `POST /alokasi` (or similar) | `store()` | `$this->validate($request, ['keterangan' => ['required', 'string', 'max:1000'], ...])` | `Requests\Adhock\Alokasi\StoreAlokasiRequest` |

**Files to create**:
- `app/Http/Requests/Adhock/Alokasi/StoreAlokasiRequest.php`

---

### Group 35: domPDF Controller (Web)

**Controller**: `domPDFcontroller`  
**File**: `app/Http/Controllers/domPDFcontroller.php`

| Endpoint | Method | Current Validation | New FormRequest |
|---|---|---|---|
| Web route | `generate()` | `$request->validate(['id' => 'required\|integer\|exists:penugasan,id'])` | `Requests\Web\GeneratePdfRequest` |

**Files to create**:
- `app/Http/Requests/Web/GeneratePdfRequest.php`

---

## Implementation Guidelines

### Step-by-step for each extraction:

1. **Read the full controller method** to capture all validation rules
2. **Create the FormRequest class**:
   ```php
   <?php
   
   namespace App\Http\Requests\Kantor\Tamu;
   
   use Illuminate\Foundation\Http\FormRequest;
   
   class StoreTamuRequest extends FormRequest
   {
       public function authorize(): bool
       {
           return true; // or add authorization logic
       }
   
       public function rules(): array
       {
           return [
               'nama' => 'required|string|max:255',
               // ... all rules from the controller
           ];
       }
   
       public function messages(): array
       {
           return [
               // custom messages if any
           ];
       }
   }
   ```
3. **Update the controller method signature** to use the new FormRequest:
   ```php
   // Before:
   public function store(Request $request) { ... }
   
   // After:
   public function store(StoreTamuRequest $request) { ... }
   ```
4. **Replace inline validation + manual data extraction** with `$request->validated()`:
   ```php
   // Before:
   $validatedData = $request->validate([...]);
   // or
   $validator = Validator::make($request->all(), [...]);
   $data = $validator->validated();
   
   // After:
   $validatedData = $request->validated();
   ```
5. **Remove unused imports** (`Validator`, etc.)
6. **Test** the endpoint still works correctly

### Naming Convention:

- **Store**: `Store{Entity}Request` — for POST (create) endpoints
- **Update**: `Update{Entity}Request` — for PUT/PATCH (update) endpoints
- **Index/List**: `Index{Entity}Request` — for GET (list) endpoints with query params
- **Special actions**: `{Action}{Entity}Request` — e.g., `BulkUpdateStatusRequest`, `SisipPermintaanRequest`

### Directory Structure Convention:

```
app/Http/Requests/
├── Auth/
│   └── LoginRequest.php
├── Profile/
│   ├── UpdateProfileRequest.php
│   └── UpdatePasswordRequest.php
├── Admin/
│   ├── Roles/
│   ├── Permissions/
│   └── Meta/
├── Kantor/
│   ├── Setting/
│   ├── Tamu/
│   ├── Pengaduan/
│   ├── Holiday/
│   ├── Notulensi/
│   ├── Uu/
│   ├── UuTambah/
│   ├── Bast/
│   ├── DirektoriUsaha/
│   ├── DetilConfiguration/
│   ├── MonitoringKegiatanConfig/
│   ├── MonitoringKegiatan/  (add remaining)
│   ├── NomorSurat/
│   │   ├── Permintaan/
│   │   ├── SuratKeluar/
│   │   ├── SuratKeputusan/
│   │   ├── SuratTugas/
│   │   ├── SurtugDetil/
│   │   └── SkDetil/
│   └── Dipa/
├── Ipds/
│   ├── AssetIT/
│   └── AssetITMaintenance/
├── Miniapp/
│   ├── Survey/
│   ├── SurveyResult/
│   └── Surveycraft/  (add remaining)
├── Pdf/
├── Gemini/
├── Adhock/
│   ├── PetaSls/
│   └── Alokasi/
└── Web/
```

---

## Execution Order (Priority)

Recommended order based on risk and complexity:

### Phase 1 — Low Risk (Simple CRUD, few rules)
1. **Group 8**: Tamu (2 requests)
2. **Group 9**: Pengaduan (2 requests)
3. **Group 12**: UU (2 requests)
4. **Group 13**: UU Tambahan (2 requests)
5. **Group 11**: Notulensi (2 requests)
6. **Group 14**: BAST (1 request)

### Phase 2 — Medium Risk (More complex rules)
7. **Group 1**: Auth (1 request)
8. **Group 2**: Profile (2 requests)
9. **Group 3**: Gemini AI (1 request)
10. **Group 7**: Setting (2 requests)
11. **Group 10**: Holiday (2 requests)
12. **Group 15**: Direktori Usaha (2 requests)
13. **Group 32**: PDF (2 requests)

### Phase 3 — Admin Endpoints
14. **Group 4**: Admin Roles (3 requests)
15. **Group 5**: Admin Permissions (3 requests)
16. **Group 6**: Admin Meta (2 requests)

### Phase 4 — IPDS Endpoints
17. **Group 26**: Asset IT (2 requests)
18. **Group 27**: Asset IT Maintenance (2 requests)
19. **Group 28**: Tiket (review existing)

### Phase 5 — Miniapp Endpoints
20. **Group 29**: Survey (2 requests)
21. **Group 30**: Survey Result (2 requests)
22. **Group 31**: Surveycraft remaining (3 requests)

### Phase 6 — Nomor Surat (High complexity, many rules)
23. **Group 19**: Permintaan (4 requests)
24. **Group 20**: Surat Keluar (3 requests)
25. **Group 21**: Surat Keputusan (3 requests)
26. **Group 22**: Surat Tugas (3 requests)
27. **Group 23**: Surtug Detil (4 requests)
28. **Group 24**: SK Detil (3 requests)

### Phase 7 — Complex / Specialized
29. **Group 16**: Detil Configuration (2 requests)
30. **Group 17**: Monitoring Kegiatan Config (3 requests)
31. **Group 18**: Monitoring Kegiatan remaining (4 requests)
32. **Group 25**: DIPA Budget (~11 requests)
33. **Group 33**: Peta SLS (1 request)
34. **Group 34**: Alokasi (1 request)
35. **Group 35**: domPDF (1 request)

---

## Checklist Template

For each group, track progress:

- [ ] Read full controller method(s) to capture all rules
- [ ] Create FormRequest class(es)
- [ ] Update controller method signature(s)
- [ ] Replace inline validation with `$request->validated()`
- [ ] Remove unused imports
- [ ] Manual test (or write test)
- [ ] Code review
