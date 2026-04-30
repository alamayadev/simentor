# POST / PUT / PATCH API Endpoints - Parameter Reference

Generated at: 2026-04-18 20:35:30

## Statistics

| Metric | Count |
|--------|-------|
| Total Endpoints | 103 |
| POST | 55 |
| PUT/PATCH | 48 |
| With FormRequest | 37 |

## Endpoints

| # | Method | Endpoint | File | Params |
|---|--------|----------|------|--------|
| 1 | `POST` | `/api/login` | [AuthApiController-login.md](./AuthApiController-login.md) | 2 fields |
| 2 | `POST` | `/api/auth/login` | [AuthApiController-login-1.md](./AuthApiController-login-1.md) | 2 fields |
| 3 | `POST` | `/api/kantor/tamu` | [TamuApiController-store.md](./TamuApiController-store.md) | 8 fields |
| 4 | `POST` | `/api/kantor/pengaduan` | [PengaduanApiController-store.md](./PengaduanApiController-store.md) | 6 fields |
| 5 | `POST` | `/api/miniapp/survey` | [SurveyController-store.md](./SurveyController-store.md) | 7 fields |
| 6 | `PUT` | `/api/miniapp/survey/{survey}` | [api-miniapp-survey-survey-put.md](./api-miniapp-survey-survey-put.md) | 7 fields |
| 7 | `PUT` | `/api/miniapp/survey/{survey}` | [api-miniapp-survey-survey-patch.md](./api-miniapp-survey-survey-patch.md) | 7 fields |
| 8 | `POST` | `/api/miniapp/survey-results` | [SurveyResultController-store.md](./SurveyResultController-store.md) | 2 fields |
| 9 | `PUT` | `/api/miniapp/survey-results/{survey_result}` | [api-miniapp-survey-results-surveyresult-put.md](./api-miniapp-survey-results-surveyresult-put.md) | 2 fields |
| 10 | `PUT` | `/api/miniapp/survey-results/{survey_result}` | [api-miniapp-survey-results-surveyresult-patch.md](./api-miniapp-survey-results-surveyresult-patch.md) | 2 fields |
| 11 | `POST` | `/api/miniapp/surveycraft/respond` | [SurveycraftApiController-respondstore.md](./SurveycraftApiController-respondstore.md) | 2 fields |
| 12 | `POST` | `/api/miniapp/surveycraft/test-generate-ai` | [SurveycraftApiController-generateWithAI.md](./SurveycraftApiController-generateWithAI.md) | 2 fields |
| 13 | `POST` | `/api/logout` | [AuthApiController-logout.md](./AuthApiController-logout.md) | 0 fields |
| 14 | `PUT` | `/api/profile` | [api-profile-put.md](./api-profile-put.md) | 2 fields |
| 15 | `PUT` | `/api/profile/password` | [api-profile-password-put.md](./api-profile-password-put.md) | 2 fields |
| 16 | `PUT` | `/api/profile/pegawai` | [api-profile-pegawai-put.md](./api-profile-pegawai-put.md) | 0 fields |
| 17 | `POST` | `/api/spk/pdf/bulk-spks` | [PdfApiController-bulkGenerateSPK.md](./PdfApiController-bulkGenerateSPK.md) | 1 fields |
| 18 | `POST` | `/api/spk/pdf/bulk-basts` | [PdfApiController-bulkGenerateBAST.md](./PdfApiController-bulkGenerateBAST.md) | 1 fields |
| 19 | `POST` | `/api/ai/generate` | [GeminiProxyController-proxy.md](./GeminiProxyController-proxy.md) | 6 fields |
| 20 | `POST` | `/api/admin/roles` | [RolesApiController-store.md](./RolesApiController-store.md) | 1 fields |
| 21 | `PUT` | `/api/admin/roles/{id}` | [api-admin-roles-id-put.md](./api-admin-roles-id-put.md) | 1 fields |
| 22 | `PUT` | `/api/admin/roles/{id}/permissions` | [api-admin-roles-id-permissions-put.md](./api-admin-roles-id-permissions-put.md) | 1 fields |
| 23 | `POST` | `/api/admin/permissions` | [PermissionsApiController-store.md](./PermissionsApiController-store.md) | 1 fields |
| 24 | `POST` | `/api/admin/permissions/bulk-create` | [PermissionsApiController-bulkCreate.md](./PermissionsApiController-bulkCreate.md) | 1 fields |
| 25 | `PUT` | `/api/admin/permissions/{id}` | [api-admin-permissions-id-put.md](./api-admin-permissions-id-put.md) | 1 fields |
| 26 | `POST` | `/api/admin/users` | [UserApiController-store.md](./UserApiController-store.md) | 7 fields |
| 27 | `PUT` | `/api/admin/users/{id}` | [api-admin-users-id-put.md](./api-admin-users-id-put.md) | 7 fields |
| 28 | `PUT` | `/api/admin/users/{id}/permissions` | [api-admin-users-id-permissions-put.md](./api-admin-users-id-permissions-put.md) | 2 fields |
| 29 | `POST` | `/api/admin/users/bulk-update-roles` | [UserApiController-bulkUpdateRoles.md](./UserApiController-bulkUpdateRoles.md) | 4 fields |
| 30 | `POST` | `/api/kantor/pegawai` | [PegawaiApiController-store.md](./PegawaiApiController-store.md) | 8 fields |
| 31 | `PUT` | `/api/kantor/pegawai/{id}` | [api-kantor-pegawai-id-put.md](./api-kantor-pegawai-id-put.md) | 8 fields |
| 32 | `POST` | `/api/kantor/settings` | [SettingApiController-store.md](./SettingApiController-store.md) | 0 fields |
| 33 | `PUT` | `/api/kantor/settings/{id}` | [api-kantor-settings-id-put.md](./api-kantor-settings-id-put.md) | 0 fields |
| 34 | `POST` | `/api/kantor/mitra/penugasan` | [MitraApiController-store.md](./MitraApiController-store.md) | 5 fields |
| 35 | `PUT` | `/api/kantor/direktori-usaha/{id}` | [api-kantor-direktori-usaha-id-put.md](./api-kantor-direktori-usaha-id-put.md) | 0 fields |
| 36 | `POST` | `/api/kantor/kegiatan/monitoring/detil-configurations` | [DetilConfigurationApiController-storeConfiguration.md](./DetilConfigurationApiController-storeConfiguration.md) | 5 fields |
| 37 | `PUT` | `/api/kantor/kegiatan/monitoring/detil-configurations/{id}` | [api-kantor-kegiatan-monitoring-detil-configurations-id-put.md](./api-kantor-kegiatan-monitoring-detil-configurations-id-put.md) | 5 fields |
| 38 | `POST` | `/api/kantor/kegiatan/monitoring/monitoring-kegiatan-config` | [MonitoringKegiatanConfigApiController-store.md](./MonitoringKegiatanConfigApiController-store.md) | 3 fields |
| 39 | `PUT` | `/api/kantor/kegiatan/monitoring/monitoring-kegiatan-config/{monitoring_kegiatan_config}` | [api-kantor-kegiatan-monitoring-monitoring-kegiatan-config-monitoringkegiatanconfig-put.md](./api-kantor-kegiatan-monitoring-monitoring-kegiatan-config-monitoringkegiatanconfig-put.md) | 3 fields |
| 40 | `PUT` | `/api/kantor/kegiatan/monitoring/monitoring-kegiatan-config/{monitoring_kegiatan_config}` | [api-kantor-kegiatan-monitoring-monitoring-kegiatan-config-monitoringkegiatanconfig-patch.md](./api-kantor-kegiatan-monitoring-monitoring-kegiatan-config-monitoringkegiatanconfig-patch.md) | 3 fields |
| 41 | `POST` | `/api/kantor/kegiatan/monitoring` | [MonitoringKegiatanApiController-store.md](./MonitoringKegiatanApiController-store.md) | 9 fields |
| 42 | `PUT` | `/api/kantor/kegiatan/monitoring/{id}` | [api-kantor-kegiatan-monitoring-id-put.md](./api-kantor-kegiatan-monitoring-id-put.md) | 9 fields |
| 43 | `POST` | `/api/kantor/kegiatan` | [KegiatanApiController-store.md](./KegiatanApiController-store.md) | 14 fields |
| 44 | `PUT` | `/api/kantor/kegiatan/{id}` | [api-kantor-kegiatan-id-put.md](./api-kantor-kegiatan-id-put.md) | 14 fields |
| 45 | `POST` | `/api/kantor/penugasan` | [PenugasanApiController-store.md](./PenugasanApiController-store.md) | 6 fields |
| 46 | `POST` | `/api/kantor/penugasan/{id}/insert` | [PenugasanApiController-insert.md](./PenugasanApiController-insert.md) | 6 fields |
| 47 | `PUT` | `/api/kantor/penugasan/{id}` | [api-kantor-penugasan-id-put.md](./api-kantor-penugasan-id-put.md) | 6 fields |
| 48 | `POST` | `/api/kantor/uu` | [UuApiController-store.md](./UuApiController-store.md) | 0 fields |
| 49 | `PUT` | `/api/kantor/uu/{id}` | [api-kantor-uu-id-put.md](./api-kantor-uu-id-put.md) | 0 fields |
| 50 | `POST` | `/api/kantor/uu-tambahan` | [UuTambahApiController-store.md](./UuTambahApiController-store.md) | 0 fields |
| 51 | `PUT` | `/api/kantor/uu-tambahan/{id}` | [api-kantor-uu-tambahan-id-put.md](./api-kantor-uu-tambahan-id-put.md) | 0 fields |
| 52 | `PUT` | `/api/kantor/bast/{id}` | [api-kantor-bast-id-put.md](./api-kantor-bast-id-put.md) | 0 fields |
| 53 | `POST` | `/api/kantor/surat/permintaan` | [PermintaanApiController-store.md](./PermintaanApiController-store.md) | 0 fields |
| 54 | `POST` | `/api/kantor/surat/permintaan/sisip` | [PermintaanApiController-sisip.md](./PermintaanApiController-sisip.md) | 0 fields |
| 55 | `PUT` | `/api/kantor/surat/permintaan/{id}` | [api-kantor-surat-permintaan-id-put.md](./api-kantor-surat-permintaan-id-put.md) | 0 fields |
| 56 | `POST` | `/api/kantor/surat/permintaan/bulk-update-status` | [PermintaanApiController-bulkUpdateStatus.md](./PermintaanApiController-bulkUpdateStatus.md) | 2 fields |
| 57 | `POST` | `/api/kantor/surat/surat-keluar` | [SuratKeluarApiController-store.md](./SuratKeluarApiController-store.md) | 0 fields |
| 58 | `POST` | `/api/kantor/surat/surat-keluar/sisip` | [SuratKeluarApiController-insert.md](./SuratKeluarApiController-insert.md) | 0 fields |
| 59 | `PUT` | `/api/kantor/surat/surat-keluar/{id}` | [api-kantor-surat-surat-keluar-id-put.md](./api-kantor-surat-surat-keluar-id-put.md) | 0 fields |
| 60 | `POST` | `/api/kantor/surat/surat-keputusan` | [SuratKeputusanApiController-store.md](./SuratKeputusanApiController-store.md) | 0 fields |
| 61 | `POST` | `/api/kantor/surat/surat-keputusan/sisip` | [SuratKeputusanApiController-sisip.md](./SuratKeputusanApiController-sisip.md) | 0 fields |
| 62 | `PUT` | `/api/kantor/surat/surat-keputusan/{id}` | [api-kantor-surat-surat-keputusan-id-put.md](./api-kantor-surat-surat-keputusan-id-put.md) | 7 fields |
| 63 | `POST` | `/api/kantor/surat/surat-tugas` | [SuratTugasApiController-store.md](./SuratTugasApiController-store.md) | 0 fields |
| 64 | `POST` | `/api/kantor/surat/surat-tugas/sisip` | [SuratTugasApiController-insert.md](./SuratTugasApiController-insert.md) | 0 fields |
| 65 | `PUT` | `/api/kantor/surat/surat-tugas/{id}` | [api-kantor-surat-surat-tugas-id-put.md](./api-kantor-surat-surat-tugas-id-put.md) | 1 fields |
| 66 | `POST` | `/api/kantor/surat/surtug-detil` | [SurtugDetilApiController-store.md](./SurtugDetilApiController-store.md) | 0 fields |
| 67 | `POST` | `/api/kantor/surat/surtug-detil/insert` | [SurtugDetilApiController-insert.md](./SurtugDetilApiController-insert.md) | 0 fields |
| 68 | `POST` | `/api/kantor/surat/surtug-detil/bulk-mitra` | [SurtugDetilApiController-bulkMitra.md](./SurtugDetilApiController-bulkMitra.md) | 0 fields |
| 69 | `PUT` | `/api/kantor/surat/surtug-detil/{id}` | [api-kantor-surat-surtug-detil-id-put.md](./api-kantor-surat-surtug-detil-id-put.md) | 0 fields |
| 70 | `POST` | `/api/kantor/skp` | [SkpApiController-store.md](./SkpApiController-store.md) | 4 fields |
| 71 | `PUT` | `/api/kantor/skp/{id}` | [api-kantor-skp-id-put.md](./api-kantor-skp-id-put.md) | 4 fields |
| 72 | `PUT` | `/api/kantor/spk/{mitra_id}/{bln_bayar}` | [api-kantor-spk-mitraid-blnbayar-put.md](./api-kantor-spk-mitraid-blnbayar-put.md) | 4 fields |
| 73 | `PUT` | `/api/kantor/spk/bulk-update` | [api-kantor-spk-bulk-update-put.md](./api-kantor-spk-bulk-update-put.md) | 4 fields |
| 74 | `PUT` | `/api/kantor/spk/bulk-update-bast` | [api-kantor-spk-bulk-update-bast-put.md](./api-kantor-spk-bulk-update-bast-put.md) | 4 fields |
| 75 | `POST` | `/api/kantor/links` | [LinkApiController-store.md](./LinkApiController-store.md) | 3 fields |
| 76 | `PUT` | `/api/kantor/links/{id}` | [api-kantor-links-id-put.md](./api-kantor-links-id-put.md) | 3 fields |
| 77 | `POST` | `/api/kantor/laporan-perjalanan-dinas` | [LaporanPerjalananDinasApiController-store.md](./LaporanPerjalananDinasApiController-store.md) | 7 fields |
| 78 | `PUT` | `/api/kantor/laporan-perjalanan-dinas/{id}` | [api-kantor-laporan-perjalanan-dinas-id-put.md](./api-kantor-laporan-perjalanan-dinas-id-put.md) | 7 fields |
| 79 | `POST` | `/api/kantor/laporan-perjalanan-dinas/{id}/details` | [LaporanPerjalananDinasApiController-storeDetail.md](./LaporanPerjalananDinasApiController-storeDetail.md) | 4 fields |
| 80 | `PUT` | `/api/kantor/laporan-perjalanan-dinas/{id}/details/{detailId}` | [api-kantor-laporan-perjalanan-dinas-id-details-detailId-put.md](./api-kantor-laporan-perjalanan-dinas-id-details-detailId-put.md) | 4 fields |
| 81 | `POST` | `/api/kantor/laporan-perjalanan-dinas/{id}/dokumentasi` | [LaporanPerjalananDinasApiController-uploadDokumentasi.md](./LaporanPerjalananDinasApiController-uploadDokumentasi.md) | 2 fields |
| 82 | `POST` | `/api/kantor/laporan-perjalanan-dinas/{id}/dokumentasi/{docId}` | [LaporanPerjalananDinasApiController-updateDokumentasi.md](./LaporanPerjalananDinasApiController-updateDokumentasi.md) | 2 fields |
| 83 | `POST` | `/api/kantor/notulensi` | [NotulensiApiController-store.md](./NotulensiApiController-store.md) | 0 fields |
| 84 | `PUT` | `/api/kantor/notulensi/{notulensi}` | [api-kantor-notulensi-notulensi-put.md](./api-kantor-notulensi-notulensi-put.md) | 0 fields |
| 85 | `PUT` | `/api/kantor/notulensi/{notulensi}` | [api-kantor-notulensi-notulensi-patch.md](./api-kantor-notulensi-notulensi-patch.md) | 0 fields |
| 86 | `PUT` | `/api/kantor/tamu/{id}` | [api-kantor-tamu-id-put.md](./api-kantor-tamu-id-put.md) | 8 fields |
| 87 | `PUT` | `/api/kantor/pengaduan/{id}` | [api-kantor-pengaduan-id-put.md](./api-kantor-pengaduan-id-put.md) | 6 fields |
| 88 | `POST` | `/api/kantor/holidays` | [HolidayApiController-store.md](./HolidayApiController-store.md) | 0 fields |
| 89 | `PUT` | `/api/kantor/holidays/{id}` | [api-kantor-holidays-id-put.md](./api-kantor-holidays-id-put.md) | 0 fields |
| 90 | `POST` | `/api/ipds/tikets` | [TiketApiController-store.md](./TiketApiController-store.md) | 2 fields |
| 91 | `PUT` | `/api/ipds/tikets/{id}` | [api-ipds-tikets-id-put.md](./api-ipds-tikets-id-put.md) | 2 fields |
| 92 | `POST` | `/api/ipds/assets` | [AssetITApiController-store.md](./AssetITApiController-store.md) | 17 fields |
| 93 | `PUT` | `/api/ipds/assets/{asset}` | [api-ipds-assets-asset-put.md](./api-ipds-assets-asset-put.md) | 0 fields |
| 94 | `PUT` | `/api/ipds/assets/{asset}` | [api-ipds-assets-asset-patch.md](./api-ipds-assets-asset-patch.md) | 0 fields |
| 95 | `POST` | `/api/ipds/asset-it-maintenance-schedule` | [AssetITMaintenanceScheduleApiController-store.md](./AssetITMaintenanceScheduleApiController-store.md) | 3 fields |
| 96 | `PUT` | `/api/ipds/asset-it-maintenance-schedule/{asset_it_maintenance_schedule}` | [api-ipds-asset-it-maintenance-schedule-assetitmaintenanceschedule-put.md](./api-ipds-asset-it-maintenance-schedule-assetitmaintenanceschedule-put.md) | 3 fields |
| 97 | `PUT` | `/api/ipds/asset-it-maintenance-schedule/{asset_it_maintenance_schedule}` | [api-ipds-asset-it-maintenance-schedule-assetitmaintenanceschedule-patch.md](./api-ipds-asset-it-maintenance-schedule-assetitmaintenanceschedule-patch.md) | 3 fields |
| 98 | `POST` | `/api/ipds/raw-datas` | [RawDataApiController-store.md](./RawDataApiController-store.md) | 4 fields |
| 99 | `PUT` | `/api/ipds/raw-datas/{id}` | [api-ipds-raw-datas-id-put.md](./api-ipds-raw-datas-id-put.md) | 4 fields |
| 100 | `POST` | `/api/miniapp/surveycraft` | [SurveycraftApiController-store.md](./SurveycraftApiController-store.md) | 8 fields |
| 101 | `POST` | `/api/miniapp/surveycraft/generate-ai` | [SurveycraftApiController-generateWithAI-1.md](./SurveycraftApiController-generateWithAI-1.md) | 2 fields |
| 102 | `POST` | `/api/miniapp/surveycraft/{surveycraft}` | [SurveycraftApiController-update.md](./SurveycraftApiController-update.md) | 8 fields |
| 103 | `PUT` | `/api/miniapp/surveycraft/{surveycraft}` | [api-miniapp-surveycraft-surveycraft-put.md](./api-miniapp-surveycraft-surveycraft-put.md) | 8 fields |

---
*Powered by extract_post_params.php*
