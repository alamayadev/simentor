# API Endpoints Per Page — simentor2026v2

> **Generated:** 2025-04-24  
> **Purpose:** Maps every page/view in `simentor2026v2/src/views/` to its backend API endpoints and implementation status.

---

## Legend

| Status | Meaning |
|--------|---------|
| ✅ **Implemented** | Page imports and calls real API endpoints |
| ⚠️ **Partial** | Service layer exists but page uses hardcoded data or is incomplete |
| ❌ **Not Implemented** | No API integration; placeholder or static data only |

---

## Summary Table

| # | Page (Route) | View File | Status |
|---|-------------|-----------|--------|
| 1 | `/` (Landing) | `LandingPage.tsx` | ❌ |
| 2 | `/login` | `LoginPage.tsx` | ✅ |
| 3 | `/profil` | `ProfilPengguna.tsx` | ✅ |
| 4 | `/pengaturan-pengguna` | `PengaturanPengguna.tsx` | ❌ |
| 5 | `/skp/dashboard` | `SkpDashboard.tsx` | ✅ |
| 6 | `/skp/progres` | `SkpProgres.tsx` | ✅ |
| 7 | `/skp/daftar` | `SkpDaftar.tsx` | ✅ |
| 8 | `/kontraktual/spk-bast` | `SpkBastTable.tsx` | ✅ |
| 9 | `/kontraktual/monitoring` | `KontraktualMonitoring.tsx` | ✅ |
| 10 | `/kegiatan/daftar` | `DaftarKegiatan.tsx` | ✅ |
| 11 | `/kegiatan/penugasan` | `PenugasanTable.tsx` | ✅ |
| 12 | `/kegiatan/petugas` | `PetugasTable.tsx` | ✅ |
| 13 | `/kegiatan/monitoring` | `KegiatanMonitoring.tsx` | ✅ |
| 14 | `/kegiatan/monitoring/config` | `MonitoringConfig.tsx` | ✅ |
| 15 | `/kegiatan/monitoring/detil-configs` | `MonitoringDetailConfig.tsx` | ✅ |
| 16 | `/kegiatan/kalender` | `KegiatanKalender.tsx` | ✅ |
| 17 | `/kegiatan/evaluasi` | `KegiatanEvaluasi.tsx` | ✅ |
| 18 | `/umum/polink` | `Polink.tsx` | ✅ |
| 19 | `/umum/pegawai` | `UmumPegawai.tsx` | ✅ |
| 20 | `/umum/libur` | `UmumLibur.tsx` | ✅ |
| 21 | `/umum/pengaturan` | `UmumPengaturan.tsx` | ✅ |
| 22 | `/umum/surat/keluar` | `UmumSurat.tsx` | ✅ |
| 23 | `/umum/surat/tugas` | `SuratTugasWorkflow.tsx` | ✅ |
| 24 | `/umum/surat/keputusan` | `SuratKeputusanWorkflow.tsx` | ✅ |
| 25 | `/umum/surat/permintaan` | `UmumSuratPermintaan.tsx` | ✅ |
| 26 | `/umum/rkk-dipa/monitoring` | `RkkDipaMonitoring.tsx` | ✅ |
| 27 | `/umum/rkk-dipa/perencanaan` | `RkkDipaPerencanaan.tsx` | ✅ |
| 28 | `/umum/rkk-dipa/pencairan` | `RkkDipaPencairan.tsx` | ✅ |
| 29 | `/umum/rkk-dipa/riwayat` | `RkkDipaRiwayat.tsx` | ✅ |
| 30 | `/umum/rkk-dipa/integritas` | `RkkDipaIntegritas.tsx` | ✅ |
| 31 | `/umum/rkk-dipa/revisi-import` | `RkkDipaRevisiImport.tsx` | ✅ |
| 32 | `/ipds/tiket` | `IpdsTiket.tsx` | ✅ |
| 33 | `/ipds/asset` | `IpdsAsset.tsx` | ✅ |
| 34 | `/bank-data/raw` | `BankDataRaw.tsx` | ✅ |
| 35 | `/bank-data/arsip` | `BankDataArsip.tsx` | ✅ |
| 36 | `/admin/pengguna` | `AdminPengguna.tsx` | ✅ |
| 37 | `/admin/peran` | `AdminPeran.tsx` | ✅ |
| 38 | `/admin/izin` | `AdminIzin.tsx` | ✅ |
| 39 | `/admin/metadata` | `AdminMetadata.tsx` | ✅ |

---

## Detailed Endpoint Mapping

---

### 1. `/` Landing Page — `LandingPage.tsx`
**Status:** ❌ Not Implemented  
**Notes:** Public landing page, no API calls.

---

### 2. `/login` — `LoginPage.tsx`
**Status:** ✅ Implemented  
**Service:** `useAuth()` → `AuthContext` → `lib/auth.ts`

| Method | Endpoint | Description |
|--------|----------|-------------|
| `POST` | `/api/login` | Authenticate user, returns token + user data |
| `POST` | `/api/logout` | Invalidate current session |

---

### 3. `/profil` — `ProfilPengguna.tsx`
**Status:** ✅ Implemented  
**Service:** `profileService`, `pegawaiService`, `AuthContext`
**Notes:** Integrated with authentication and profile management. Handles user details, professional (kepegawaian) data, and password updates.

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/api/profile` | Get authenticated user profile |
| `PUT` | `/api/profile` | Update name and email |
| `PUT` | `/api/profile/password` | Update account password |
| `GET` | `/api/profile/pegawai` | Get linked employee record (Auth::id) |
| `PUT` | `/api/profile/pegawai` | Update linked employee record |
| `GET` | `/api/kantor/pegawai` | List employees (used for lookups) |

---

### 4. `/pengaturan-pengguna` — `PengaturanPengguna.tsx`
**Status:** ❌ Not Implemented  
**Notes:** Only theme toggle and static UI. No API calls.

---

### 5. `/skp/dashboard` — `SkpDashboard.tsx`
**Status:** ✅ Implemented  
**Service:** `skpService`

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/api/kantor/skp/dashboard` | Dashboard summary data |
| `GET` | `/api/kantor/skp` | List SKP records (paginated) |

---

### 6. `/skp/progres` — `SkpProgres.tsx`
**Status:** ✅ Implemented  
**Service:** Direct `apiGet` / `apiPostForm`

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/api/kantor/skp/stats` | SKP progress statistics |
| `POST` (FormData) | `/api/kantor/skp` | Upload SKP file |

---

### 7. `/skp/daftar` — `SkpDaftar.tsx`
**Status:** ✅ Implemented  
**Service:** Direct `apiGet` / `apiPostForm` / `apiPutForm` / `apiDelete`

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/api/kantor/skp/list` | List SKP files (paginated) |
| `POST` (FormData) | `/api/kantor/skp` | Upload new SKP file |
| `PUT` (FormData) | `/api/kantor/skp/{id}` | Update SKP file |
| `DELETE` | `/api/kantor/skp/{id}` | Delete SKP file |

---

### 8. `/kontraktual/spk-bast` — `SpkBastTable.tsx`
**Status:** ✅ Implemented  
**Service:** Direct `apiGet` / `apiPut` + `fetch` for downloads

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/api/kantor/spk` | List SPK/BAST records |
| `PUT` | `/api/kantor/spk/bulk-update` | Bulk update SPK records |
| `PUT` | `/api/kantor/spk/bulk-update-bast` | Bulk update BAST fields |
| `PUT` | `/api/kantor/spk/{mitraId}/{blnBayar}` | Update SPK by mitra & month |
| `POST` (fetch) | `/api/spk/pdf/bulk-spks` | Download bulk SPK PDFs (ZIP) |
| `POST` (fetch) | `/api/spk/pdf/bulk-basts` | Download bulk BAST PDFs (ZIP) |

---

### 9. `/kontraktual/monitoring` — `KontraktualMonitoring.tsx`
**Status:** ✅ Implemented  
**Service:** Direct `apiGet`

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/api/kantor/spk/monitoring` | SPK monitoring data (paginated) |

---

### 10. `/kegiatan/daftar` — `DaftarKegiatan.tsx`
**Status:** ✅ Implemented  
**Service:** `kegiatanService`

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/api/kantor/kegiatan` | List kegiatan (paginated) |
| `GET` | `/api/kantor/kegiatan/{id}` | Get kegiatan detail |
| `POST` | `/api/kantor/kegiatan` | Create kegiatan |
| `PUT` | `/api/kantor/kegiatan/{id}` | Update kegiatan |
| `DELETE` | `/api/kantor/kegiatan/{id}` | Delete kegiatan |
| `GET` | `/api/kantor/kegiatan/form-options` | Form dropdown options |
| `GET` | `/api/kantor/kegiatan/statistics` | Kegiatan statistics |
| `GET` | `/api/kantor/kegiatan/filter-list` | Filter options |

---

### 11. `/kegiatan/penugasan` — `PenugasanTable.tsx`
**Status:** ✅ Implemented  
**Service:** `penugasanService`

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/api/kantor/penugasan` | List penugasan (paginated) |
| `GET` | `/api/kantor/penugasan/{id}` | Get penugasan detail |
| `POST` | `/api/kantor/penugasan` | Create penugasan |
| `POST` | `/api/kantor/penugasan/insert` | Insert penugasan (alternative) |
| `PUT` | `/api/kantor/penugasan/{id}` | Update penugasan |
| `DELETE` | `/api/kantor/penugasan/{id}` | Delete penugasan |
| `GET` | `/api/kantor/penugasan/filters` | Filter options |
| `GET` | `/api/kantor/penugasan/form-options` | Form dropdown options |
| `GET` | `/api/kantor/penugasan/mitra-options` | Mitra dropdown options |
| `GET` | `/api/kantor/penugasan/kegiatan-options?fungsi=` | Kegiatan options by fungsi |
| `GET` (fetch) | `/api/kantor/penugasan/export` | Export penugasan data |

---

### 12. `/kegiatan/petugas` — `PetugasTable.tsx`
**Status:** ✅ Implemented  
**Service:** `mitraService`, `penugasanService`

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/api/kantor/mitra` | List mitra/pekerja (paginated) |
| `GET` | `/api/kantor/mitra/filters` | Mitra filter options |
| `GET` | `/api/kantor/mitra/statistics` | Mitra statistics |
| `GET` | `/api/kantor/mitra/penugasan-options` | Penugasan options for mitra |
| `POST` | `/api/kantor/mitra/penugasan` | Create penugasan for mitra |
| `GET` | `/api/kantor/mitra/{id}` | Get mitra detail |
| `POST` | `/api/kantor/mitra` | Create mitra |
| `PUT` | `/api/kantor/mitra/{id}` | Update mitra |
| `DELETE` | `/api/kantor/mitra/{id}` | Delete mitra |
| `POST` | `/api/kantor/penugasan` | Create penugasan (from service) |

---

### 13. `/kegiatan/monitoring` — `KegiatanMonitoring.tsx`
**Status:** ✅ Implemented  
**Service:** `monitoringKegiatanService`, `monitoringKegiatanConfigService`

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/api/kantor/kegiatan/monitoring` | List monitoring records |
| `GET` | `/api/kantor/kegiatan/monitoring/{id}` | Get monitoring detail |
| `POST` | `/api/kantor/kegiatan/monitoring` | Create monitoring record |
| `PUT` | `/api/kantor/kegiatan/monitoring/{id}` | Update monitoring record |
| `DELETE` | `/api/kantor/kegiatan/monitoring/{id}` | Delete monitoring record |
| `GET` | `/api/kantor/kegiatan/monitoring/kegiatan-options` | Kegiatan dropdown (by fungsi) |
| `GET` | `/api/kantor/kegiatan/monitoring/filters-options` | Filter options |
| `GET` | `/api/kantor/kegiatan/monitoring/all-kegiatan-options` | All kegiatan options |
| `GET` | `/api/kantor/kegiatan/monitoring/kec-options` | Kecamatan options |
| `GET` | `/api/kantor/kegiatan/monitoring/desa-options?kec_id=` | Desa options |
| `GET` | `/api/kantor/kegiatan/monitoring/petugas-options?kegiatan_id=` | Petugas options |
| `GET` (fetch) | `/api/kantor/kegiatan/monitoring/download` | Download monitoring data |

---

### 14. `/kegiatan/monitoring/config` — `MonitoringConfig.tsx`
**Status:** ✅ Implemented  
**Service:** `monitoringKegiatanConfigService`, `detilConfigurationService`, direct `apiDelete`

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/api/kantor/kegiatan/monitoring/monitoring-kegiatan-config` | List configs |
| `POST` | `/api/kantor/kegiatan/monitoring/monitoring-kegiatan-config` | Create config |
| `PUT` | `/api/kantor/kegiatan/monitoring/monitoring-kegiatan-config/{id}` | Update config |
| `PATCH` | `/api/kantor/kegiatan/monitoring/monitoring-kegiatan-config/{id}` | Patch config |
| `DELETE` | `/api/kantor/kegiatan/monitoring/monitoring-kegiatan-config/{id}` | Delete config |
| `GET` | `/api/kantor/kegiatan/monitoring/detil-configurations` | List detail configs |
| `POST` | `/api/kantor/kegiatan/monitoring/detil-configurations` | Create detail config |
| `PUT` | `/api/kantor/kegiatan/monitoring/detil-configurations/{id}` | Update detail config |

---

### 15. `/kegiatan/monitoring/detil-configs` — `MonitoringDetailConfig.tsx`
**Status:** ✅ Implemented  
**Service:** `detilConfigurationService`, direct `apiDelete`

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/api/kantor/kegiatan/monitoring/detil-configurations` | List detail configs |
| `POST` | `/api/kantor/kegiatan/monitoring/detil-configurations` | Create detail config |
| `PUT` | `/api/kantor/kegiatan/monitoring/detil-configurations/{id}` | Update detail config |
| `DELETE` | `/api/kantor/kegiatan/monitoring/detil-configurations/{id}` | Delete detail config |

---

### 16. `/kegiatan/kalender` — `KegiatanKalender.tsx`
**Status:** ✅ Implemented  
**Service:** `kegiatanService`

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/api/kantor/kegiatan/calendar` | Calendar events data |
| `GET` | `/api/kantor/kegiatan/{id}` | Get kegiatan detail |
| `DELETE` | `/api/kantor/kegiatan/{id}` | Delete kegiatan |

---

### 17. `/kegiatan/evaluasi` — `KegiatanEvaluasi.tsx`
**Status:** ✅ Implemented  
**Service:** `kegiatanService`

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/api/kantor/kegiatan` | List kegiatan for evaluation |
| `GET` | `/api/kantor/kegiatan/{id}` | Get kegiatan detail |
| `PUT` | `/api/kantor/kegiatan/{id}` | Update kegiatan evaluation |
| `DELETE` | `/api/kantor/kegiatan/{id}` | Delete kegiatan |

---

### 18. `/umum/polink` — `Polink.tsx`
**Status:** ✅ Implemented  
**Service:** `linkService`

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/api/kantor/links` | List links (paginated) |
| `GET` | `/api/kantor/links/{id}` | Get link detail |
| `POST` | `/api/kantor/links` | Create link |
| `PUT` | `/api/kantor/links/{id}` | Update link |
| `DELETE` | `/api/kantor/links/{id}` | Delete link |

---

### 19. `/umum/pegawai` — `UmumPegawai.tsx`
**Status:** ✅ Implemented  
**Service:** `pegawaiService`

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/api/kantor/pegawai` | List pegawai (paginated) |
| `GET` | `/api/kantor/pegawai/{id}` | Get pegawai detail |
| `POST` | `/api/kantor/pegawai` | Create pegawai |
| `PUT` | `/api/kantor/pegawai/{id}` | Update pegawai |
| `DELETE` | `/api/kantor/pegawai/{id}` | Delete pegawai |
| `GET` | `/api/kantor/pegawai/filters` | Filter options |
| `GET` | `/api/kantor/pegawai/form-options` | Form dropdown options |

---

### 20. `/umum/libur` — `UmumLibur.tsx`
**Status:** ✅ Implemented  
**Service:** `holidayService`

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/api/kantor/holidays` | List holidays (paginated) |
| `GET` | `/api/kantor/holidays/{id}` | Get holiday detail |
| `POST` | `/api/kantor/holidays` | Create holiday |
| `PUT` | `/api/kantor/holidays/{id}` | Update holiday |
| `DELETE` | `/api/kantor/holidays/{id}` | Delete holiday |

---

### 21. `/umum/pengaturan` — `UmumPengaturan.tsx`
**Status:** ✅ Implemented  
**Service:** `settingService`

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/api/kantor/settings` | List settings |
| `GET` | `/api/kantor/settings/{id}` | Get setting detail |
| `GET` | `/api/kantor/settings/key/{key}` | Get setting by key |
| `POST` | `/api/kantor/settings` | Create setting |
| `PUT` | `/api/kantor/settings/{id}` | Update setting |
| `DELETE` | `/api/kantor/settings/{id}` | Delete setting |
| `GET` | `/api/kantor/settings/grup-list` | List setting groups |
| `GET` | `/api/kantor/settings/officers` | List officer settings |

---

### 22. `/umum/surat/keluar` — `UmumSurat.tsx`
**Status:** ✅ Implemented  
**Service:** `suratKeluarService`
**Notes:** Advanced rich text editor for surat keluar, supporting template-based numbering, print preview, and sisip (insert) logic. Fully connected to the backend CRUD service.

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/api/kantor/surat/surat-keluar` | List surat keluar (paginated) |
| `GET` | `/api/kantor/surat/surat-keluar/{id}` | Get surat detail |
| `POST` | `/api/kantor/surat/surat-keluar` | Create surat keluar |
| `POST` | `/api/kantor/surat/surat-keluar/sisip` | Insert surat (sisip) |
| `PUT` | `/api/kantor/surat/surat-keluar/{id}` | Update surat keluar |
| `DELETE` | `/api/kantor/surat/surat-keluar/{id}` | Delete surat keluar |
| `GET` | `/api/kantor/surat/surat-keluar/form-options` | Fetch form settings and options |

---

### 23. `/umum/surat/tugas` — `SuratTugasWorkflow.tsx`
**Status:** ✅ Implemented  
**Service:** `suratTugasService`, `surtugDetilService`

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/api/kantor/surat/surat-tugas` | List surat tugas |
| `GET` | `/api/kantor/surat/surat-tugas/{id}` | Get surat tugas detail |
| `POST` | `/api/kantor/surat/surat-tugas` | Create surat tugas |
| `POST` | `/api/kantor/surat/surat-tugas/sisip` | Insert surat tugas |
| `PUT` | `/api/kantor/surat/surat-tugas/{id}` | Update surat tugas |
| `PUT` (FormData) | `/api/kantor/surat/surat-tugas/{id}` | Update with file upload |
| `DELETE` | `/api/kantor/surat/surat-tugas/{id}` | Delete surat tugas |
| `GET` | `/api/kantor/surat/surat-tugas/dates` | Get available dates |
| `GET` | `/api/kantor/surat/surat-tugas/years` | Get available years |
| `GET` | `/api/kantor/surat/surat-tugas/klasifikasi` | Get klasifikasi options |
| `GET` | `/api/kantor/surat/surat-tugas/form-options` | Form dropdown options |
| `GET` | `/api/kantor/surat/surtug-detil` | List surtug details |
| `GET` | `/api/kantor/surat/surtug-detil/{id}` | Get surtug detail |
| `GET` | `/api/kantor/surat/surtug-detil/surtug/{surtugId}` | Get details by surtug ID |
| `POST` | `/api/kantor/surat/surtug-detil` | Create surtug detail |
| `POST` | `/api/kantor/surat/surtug-detil/insert` | Insert surtug detail |
| `POST` | `/api/kantor/surat/surtug-detil/bulk-mitra` | Bulk add mitra to surtug |
| `PUT` | `/api/kantor/surat/surtug-detil/{id}` | Update surtug detail |
| `DELETE` | `/api/kantor/surat/surtug-detil/{id}` | Delete surtug detail |
| `GET` | `/api/kantor/surat/surtug-detil/kegiatan-options` | Kegiatan dropdown |
| `GET` | `/api/kantor/surat/surtug-detil/mitra-penugasan-options` | Mitra penugasan dropdown |
| `GET` | `/api/kantor/surat/surtug-detil/pegawai-options` | Pegawai dropdown |
| `GET` | `/api/kantor/surat/surtug-detil/mitra-options` | Mitra dropdown |

---

### 24. `/umum/surat/keputusan` — `SuratKeputusanWorkflow.tsx`
**Status:** ✅ Implemented  
**Service:** `suratKeputusanService`, `skDetilService`

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/api/kantor/surat/surat-keputusan` | List surat keputusan |
| `GET` | `/api/kantor/surat/surat-keputusan/{id}` | Get SK detail |
| `POST` | `/api/kantor/surat/surat-keputusan` | Create SK |
| `POST` | `/api/kantor/surat/surat-keputusan/sisip` | Insert SK |
| `PUT` | `/api/kantor/surat/surat-keputusan/{id}` | Update SK |
| `DELETE` | `/api/kantor/surat/surat-keputusan/{id}` | Delete SK |
| `GET` | `/api/kantor/surat/surat-keputusan/dates` | Get available dates |
| `GET` | `/api/kantor/surat/surat-keputusan/years` | Get available years |
| `GET` | `/api/kantor/surat/surat-keputusan/form-options` | Form dropdown options |
| `GET` | `/api/kantor/surat/sk-detil` | List SK details |
| `GET` | `/api/kantor/surat/sk-detil/{id}` | Get SK detail record |
| `GET` | `/api/kantor/surat/sk-detil/sk/{skId}` | Get details by SK ID |
| `POST` | `/api/kantor/surat/sk-detil` | Create SK detail |
| `POST` | `/api/kantor/surat/sk-detil/bulk-mitra` | Bulk add mitra to SK |
| `PUT` | `/api/kantor/surat/sk-detil/{id}` | Update SK detail |
| `DELETE` | `/api/kantor/surat/sk-detil/{id}` | Delete SK detail |
| `GET` | `/api/kantor/surat/sk-detil/kegiatan-options` | Kegiatan dropdown |
| `GET` | `/api/kantor/surat/sk-detil/mitra-penugasan-options` | Mitra penugasan dropdown |
| `GET` | `/api/kantor/surat/sk-detil/pegawai-options` | Pegawai dropdown |
| `GET` | `/api/kantor/surat/sk-detil/mitra-options` | Mitra dropdown |

---

### 25. `/umum/surat/permintaan` — `UmumSuratPermintaan.tsx`
**Status:** ✅ Implemented  
**Service:** `suratPermintaanService`
**Notes:** Full CRUD for Surat Permintaan with sisip (insert) numbering, year filtering, and pagination.

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/api/kantor/surat/permintaan` | List surat permintaan (paginated) |
| `GET` | `/api/kantor/surat/permintaan/{id}` | Get surat detail |
| `POST` | `/api/kantor/surat/permintaan` | Create surat permintaan |
| `POST` | `/api/kantor/surat/permintaan/sisip` | Insert surat (sisip) |
| `PUT` | `/api/kantor/surat/permintaan/{id}` | Update surat |
| `DELETE` | `/api/kantor/surat/permintaan/{id}` | Delete surat |
| `GET` | `/api/kantor/surat/permintaan/years` | Get available years for filtering |

---

### 26. `/umum/rkk-dipa/monitoring` — `RkkDipaMonitoring.tsx`
**Status:** ✅ Implemented  
**Service:** `dipaBudgetService`

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/api/kantor/dipa/monitoring` | DIPA monitoring data |
| `GET` | `/api/kantor/dipa/monitoring/summary` | DIPA monitoring summary |

---

### 27. `/umum/rkk-dipa/perencanaan` — `RkkDipaPerencanaan.tsx`
**Status:** ✅ Implemented  
**Service:** `dipaBudgetService`

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/api/kantor/dipa/picker` | DIPA budget item picker |
| `POST` | `/api/kantor/dipa/plan` | Record a plan |
| `GET` | `/api/kantor/dipa/plan` | List plans |
| `PUT` | `/api/kantor/dipa/plan/{id}` | Update plan |
| `DELETE` | `/api/kantor/dipa/plan/{id}` | Delete plan |

---

### 28. `/umum/rkk-dipa/pencairan` — `RkkDipaPencairan.tsx`
**Status:** ✅ Implemented  
**Service:** `dipaBudgetService`

| Method | Endpoint | Description |
|--------|----------|-------------|
| `POST` | `/api/kantor/dipa/usage` | Record usage/penggunaan |
| `GET` | `/api/kantor/dipa/history` | Usage history |
| `PUT` | `/api/kantor/dipa/usage/{id}` | Update usage amount |
| `DELETE` | `/api/kantor/dipa/usage/{id}` | Delete usage record |
| `GET` | `/api/kantor/dipa/usage/sync-fa-summary` | SAKTI FA sync summary |
| `POST` | `/api/kantor/dipa/usage/sync-fa` | Sync SAKTI FA |

---

### 29. `/umum/rkk-dipa/riwayat` — `RkkDipaRiwayat.tsx`
**Status:** ✅ Implemented  
**Service:** `dipaBudgetService`

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/api/kantor/dipa/history` | Usage history (paginated) |
| `GET` | `/api/kantor/dipa/reconciliation` | SAKTI reconciliation data |

---

### 30. `/umum/rkk-dipa/integritas` — `RkkDipaIntegritas.tsx`
**Status:** ✅ Implemented  
**Service:** `dipaBudgetService`

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/api/kantor/dipa/orphans` | Find orphan records |
| `POST` | `/api/kantor/dipa/map` | Save DIPA mapping |
| `GET` | `/api/kantor/dipa/dependencies/views` | Verify dependencies |

---

### 31. `/umum/rkk-dipa/revisi-import` — `RkkDipaRevisiImport.tsx`
**Status:** ✅ Implemented  
**Service:** `dipaBudgetService`

| Method | Endpoint | Description |
|--------|----------|-------------|
| `POST` (fetch/FormData) | `/api/kantor/dipa/import` | Import DIPA file |
| `POST` (fetch/FormData) | `/api/kantor/dipa/import-sakti` | Import SAKTI file |
| `GET` | `/api/kantor/dipa/files` | List imported files |

---

### 32. `/ipds/tiket` — `IpdsTiket.tsx`
**Status:** ✅ Implemented  
**Service:** `tiketService`

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/api/ipds/tikets` | List tiket (paginated) |
| `GET` | `/api/ipds/tikets/{id}` | Get tiket detail |
| `POST` | `/api/ipds/tikets` | Create tiket |
| `PUT` | `/api/ipds/tikets/{id}` | Update tiket |
| `DELETE` | `/api/ipds/tikets/{id}` | Delete tiket |
| `GET` | `/api/ipds/tikets/statistics` | Tiket statistics |

---

### 33. `/ipds/asset` — `IpdsAsset.tsx`
**Status:** ✅ Implemented  
**Service:** `assetService`, `maintenanceScheduleService`

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/api/ipds/assets` | List assets (paginated) |
| `GET` | `/api/ipds/assets/{id}` | Get asset detail |
| `POST` | `/api/ipds/assets` | Create asset |
| `PUT` | `/api/ipds/assets/{id}` | Update asset |
| `PATCH` | `/api/ipds/assets/{id}` | Patch asset |
| `DELETE` | `/api/ipds/assets/{id}` | Delete asset |
| `GET` | `/api/ipds/assets/filters` | Filter options |
| `GET` | `/api/ipds/assets/statistics` | Asset statistics |
| `GET` | `/api/ipds/asset-it-maintenance-schedule` | List maintenance schedules |
| `GET` | `/api/ipds/asset-it-maintenance-schedule/{id}` | Get maintenance detail |
| `POST` | `/api/ipds/asset-it-maintenance-schedule` | Create maintenance |
| `PUT` | `/api/ipds/asset-it-maintenance-schedule/{id}` | Update maintenance |
| `PATCH` | `/api/ipds/asset-it-maintenance-schedule/{id}` | Patch maintenance |
| `DELETE` | `/api/ipds/asset-it-maintenance-schedule/{id}` | Delete maintenance |

---

### 34. `/bank-data/raw` — `BankDataRaw.tsx`
**Status:** ✅ Implemented  
**Service:** Uses `BankDataPage` component → `rawDataService`, `enumsService`

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/api/ipds/raw-datas` | List raw data (paginated) |
| `GET` | `/api/ipds/raw-datas/{id}` | Get raw data detail |
| `POST` | `/api/ipds/raw-datas` | Create raw data |
| `PUT` | `/api/ipds/raw-datas/{id}` | Update raw data |
| `DELETE` | `/api/ipds/raw-datas/{id}` | Delete raw data |
| `GET` | `/api/enums` | Enums for dropdowns |

---

### 35. `/bank-data/arsip` — `BankDataArsip.tsx`
**Status:** ✅ Implemented  
**Service:** Uses `BankDataPage` component → `rawDataService`, `enumsService`

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/api/ipds/raw-datas` | List arsip data (filtered) |
| `GET` | `/api/ipds/raw-datas/{id}` | Get arsip detail |
| `POST` | `/api/ipds/raw-datas` | Create arsip entry |
| `PUT` | `/api/ipds/raw-datas/{id}` | Update arsip |
| `DELETE` | `/api/ipds/raw-datas/{id}` | Delete arsip |
| `GET` | `/api/enums` | Enums for dropdowns |

---

### 36. `/admin/pengguna` — `AdminPengguna.tsx`
**Status:** ✅ Implemented  
**Service:** `adminUsersService`, `adminPermissionsService`

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/api/admin/users` | List users (paginated) |
| `GET` | `/api/admin/users/organik` | List organik users |
| `GET` | `/api/admin/users/mitra` | List mitra users |
| `GET` | `/api/admin/users/{id}` | Get user detail |
| `POST` | `/api/admin/users` | Create user |
| `PUT` | `/api/admin/users/{id}` | Update user |
| `PUT` | `/api/admin/users/{id}/permissions` | Update user permissions |
| `DELETE` | `/api/admin/users/{id}` | Delete user |
| `POST` | `/api/admin/users/bulk-update-roles` | Bulk update user roles |
| `GET` | `/api/admin/permissions/grouped-prefixes` | Grouped permissions list |

---

### 37. `/admin/peran` — `AdminPeran.tsx`
**Status:** ✅ Implemented  
**Service:** `adminRolesService`, `adminUsersService`

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/api/admin/roles` | List roles (paginated) |
| `GET` | `/api/admin/roles/{id}` | Get role detail |
| `POST` | `/api/admin/roles` | Create role |
| `PUT` | `/api/admin/roles/{id}` | Update role |
| `PUT` | `/api/admin/roles/{id}/permissions` | Update role permissions |
| `DELETE` | `/api/admin/roles/{id}` | Delete role |
| `GET` | `/api/admin/roles/permission-options` | Permission options for assignment |

---

### 38. `/admin/izin` — `AdminIzin.tsx`
**Status:** ✅ Implemented  
**Service:** `adminPermissionsService`

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/api/admin/permissions` | List permissions (paginated) |
| `GET` | `/api/admin/permissions/grouped-prefixes` | Grouped permissions |
| `GET` | `/api/admin/permissions/{id}` | Get permission detail |
| `POST` | `/api/admin/permissions` | Create permission |
| `POST` | `/api/admin/permissions/bulk-create` | Bulk create permissions |
| `PUT` | `/api/admin/permissions/{id}` | Update permission |
| `DELETE` | `/api/admin/permissions/{id}` | Delete permission |
| `GET` | `/api/admin/permissions/form-options` | Form options |
| `GET` | `/api/admin/permissions/list-api-controllers` | List API controllers |

---

### 39. `/admin/metadata` — `AdminMetadata.tsx`
**Status:** ✅ Implemented  
**Service:** `adminMetasService`
**Notes:** Manages hierarchical metadata (tags/layers) using a tree structure. Supports recursive CRUD operations and parent-child linking.

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/api/admin/metas/tree` | Get hierarchical metadata tree |
| `GET` | `/api/admin/metas/{id}` | Get metadata detail |
| `POST` | `/api/admin/metas` | Create metadata (with parent_id) |
| `PUT` | `/api/admin/metas/{id}` | Update metadata |
| `DELETE` | `/api/admin/metas/{id}` | Delete metadata |

---

## Available Services Not Yet Connected to Pages

These services are defined in `lib/api-services.ts` but not used by any view:

| Service | Endpoints Base | Status |
|---------|---------------|--------|
| `notulensiService` | `/api/kantor/notulensi` | No page exists yet |
| `tamuService` | `/api/kantor/tamu` | No page exists yet |
| `pengaduanService` | `/api/kantor/pengaduan` | No page exists yet |
| `uuService` | `/api/kantor/uu` | No page exists yet |
| `uuTambahanService` | `/api/kantor/uu-tambahan` | No page exists yet |
| `direktoriUsahaService` | `/api/kantor/direktori-usaha` | No page exists yet |
| `laporanPerjalananDinasService` | `/api/kantor/laporan-perjalanan-dinas` | No page exists yet |
| `surveyService` | `/api/miniapp/survey` | No page exists yet |
| `surveyResultService` | `/api/miniapp/survey-results` | No page exists yet |
| `surveycraftService` | `/api/miniapp/surveycraft` | No page exists yet |
| `bastService` | `/api/kantor/bast` | No dedicated page (part of SpkBastTable) |

---

## Statistics

| Category | Count |
|----------|-------|
| Total pages/views | 39 |
| ✅ Fully implemented | **37** (95%) |
| ⚠️ Partial (service exists, page uses static data) | **0** (0%) |
| ❌ Not implemented | **2** (5%) |
| Unused services (no page) | **11** |
| Total unique API endpoints | **~150+** |
</task_progress>
</task_progress>
</write_to_file>