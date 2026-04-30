# Route Tree Map — Simentor 2026 v2

```
/ (root)
├── /                                          → LandingPage
├── /login                                     → LoginPage
├── /profil                                    → ProfilPengguna
├── /pengaturan-pengguna                       → PengaturanPengguna
│
├── /skp                                       ── SKP Module
│   ├── /skp/dashboard                         → SkpDashboard
│   ├── /skp/progres                           → SkpProgres
│   └── /skp/daftar                            → SkpDaftar
│
├── /kegiatan                                  ── Kegiatan Module
│   ├── /kegiatan/daftar                       → DaftarKegiatan
│   ├── /kegiatan/kalender                     → KegiatanKalender
│   ├── /kegiatan/penugasan                    → PenugasanTable
│   ├── /kegiatan/petugas                      → PetugasTable
│   ├── /kegiatan/evaluasi                     → KegiatanEvaluasi
│   ├── /kegiatan/monitoring                   → KegiatanMonitoring
│   ├── /kegiatan/monitoring/detil-configs     → MonitoringDetailConfig
│   └── /kegiatan/monitoring/config            → MonitoringConfig
│
├── /kontraktual                               ── SPK & BAST Module
│   ├── /kontraktual/monitoring                → KontraktualMonitoring
│   └── /kontraktual/spk-bast                  → SpkBastTable
│
├── /umum                                      ── Umum Module
│   ├── /umum/rkk-dipa                         ── RKK DIPA (dropdown)
│   │   ├── /umum/rkk-dipa/monitoring          → RkkDipaMonitoring
│   │   ├── /umum/rkk-dipa/perencanaan         → RkkDipaPerencanaan
│   │   ├── /umum/rkk-dipa/pencairan           → RkkDipaPencairan
│   │   ├── /umum/rkk-dipa/riwayat             → RkkDipaRiwayat
│   │   ├── /umum/rkk-dipa/integritas          → RkkDipaIntegritas
│   │   └── /umum/rkk-dipa/revisi-import       → RkkDipaRevisiImport
│   ├── /umum/surat                            ── Surat (dropdown)
│   │   ├── /umum/surat/keluar                 → SuratKeluar (UmumSurat)
│   │   ├── /umum/surat/tugas                  → SuratTugasWorkflow
│   │   ├── /umum/surat/keputusan              → SuratKeputusanWorkflow
│   │   └── /umum/surat/permintaan             → SuratPermintaan (UmumSurat)
│   ├── /umum/polink                           → Polink
│   ├── /umum/pegawai                          → UmumPegawai
│   ├── /umum/libur                            → UmumLibur
│   └── /umum/pengaturan                       → UmumPengaturan
│
├── /ipds                                      ── IPDS Module
│   ├── /ipds/tiket                            → IpdsTiket
│   └── /ipds/asset                            → IpdsAsset
│
├── /bank-data                                 ── Bank Data Module
│   ├── /bank-data/raw                         → BankDataRaw
│   └── /bank-data/arsip                       → BankDataArsip
│
└── /admin                                     ── Admin Module
    ├── /admin/pengguna                        → AdminPengguna
    ├── /admin/peran                           → AdminPeran
    ├── /admin/izin                            → AdminIzin
    └── /admin/metadata                        → AdminMetadata
```

---

## Route → View Component Mapping

| Route Path | Component | File |
|---|---|---|
| `/` | `LandingPage` | `views/LandingPage.tsx` |
| `/login` | `LoginPage` | `views/LoginPage.tsx` |
| `/profil` | `ProfilPengguna` | `views/ProfilPengguna.tsx` |
| `/pengaturan-pengguna` | `PengaturanPengguna` | `views/PengaturanPengguna.tsx` |
| `/skp/dashboard` | `SkpDashboard` | `views/SkpDashboard.tsx` |
| `/skp/progres` | `SkpProgres` | `views/SkpProgres.tsx` |
| `/skp/daftar` | `SkpDaftar` | `views/SkpDaftar.tsx` |
| `/kegiatan/daftar` | `DaftarKegiatan` | `views/DaftarKegiatan.tsx` |
| `/kegiatan/kalender` | `KegiatanKalender` | `views/KegiatanKalender.tsx` |
| `/kegiatan/penugasan` | `PenugasanTable` | `views/PenugasanTable.tsx` |
| `/kegiatan/petugas` | `PetugasTable` | `views/PetugasTable.tsx` |
| `/kegiatan/evaluasi` | `KegiatanEvaluasi` | `views/KegiatanEvaluasi.tsx` |
| `/kegiatan/monitoring` | `KegiatanMonitoring` | `views/KegiatanMonitoring.tsx` |
| `/kegiatan/monitoring/detil-configs` | `MonitoringDetailConfig` | `views/MonitoringDetailConfig.tsx` |
| `/kegiatan/monitoring/config` | `MonitoringConfig` | `views/MonitoringConfig.tsx` |
| `/kontraktual/monitoring` | `KontraktualMonitoring` | `views/KontraktualMonitoring.tsx` |
| `/kontraktual/spk-bast` | `SpkBastTable` | `views/SpkBastTable.tsx` |
| `/umum/rkk-dipa/monitoring` | `RkkDipaMonitoring` | `views/RkkDipaMonitoring.tsx` |
| `/umum/rkk-dipa/perencanaan` | `RkkDipaPerencanaan` | `views/RkkDipaPerencanaan.tsx` |
| `/umum/rkk-dipa/pencairan` | `RkkDipaPencairan` | `views/RkkDipaPencairan.tsx` |
| `/umum/rkk-dipa/riwayat` | `RkkDipaRiwayat` | `views/RkkDipaRiwayat.tsx` |
| `/umum/rkk-dipa/integritas` | `RkkDipaIntegritas` | `views/RkkDipaIntegritas.tsx` |
| `/umum/rkk-dipa/revisi-import` | `RkkDipaRevisiImport` | `views/RkkDipaRevisiImport.tsx` |
| `/umum/surat/keluar` | `UmumSurat` | `views/UmumSurat.tsx` |
| `/umum/surat/tugas` | `SuratTugasWorkflow` | `views/SuratTugasWorkflow.tsx` |
| `/umum/surat/keputusan` | `SuratKeputusanWorkflow` | `views/SuratKeputusanWorkflow.tsx` |
| `/umum/surat/permintaan` | `UmumSurat` | `views/UmumSurat.tsx` |
| `/umum/polink` | `Polink` | `views/Polink.tsx` |
| `/umum/pegawai` | `UmumPegawai` | `views/UmumPegawai.tsx` |
| `/umum/libur` | `UmumLibur` | `views/UmumLibur.tsx` |
| `/umum/pengaturan` | `UmumPengaturan` | `views/UmumPengaturan.tsx` |
| `/ipds/tiket` | `IpdsTiket` | `views/IpdsTiket.tsx` |
| `/ipds/asset` | `IpdsAsset` | `views/IpdsAsset.tsx` |
| `/bank-data/raw` | `BankDataRaw` | `views/BankDataRaw.tsx` |
| `/bank-data/arsip` | `BankDataArsip` | `views/BankDataArsip.tsx` |
| `/admin/pengguna` | `AdminPengguna` | `views/AdminPengguna.tsx` |
| `/admin/peran` | `AdminPeran` | `views/AdminPeran.tsx` |
| `/admin/izin` | `AdminIzin` | `views/AdminIzin.tsx` |
| `/admin/metadata` | `AdminMetadata` | `views/AdminMetadata.tsx` |

---

## Summary Statistics

| Module | Routes | Components |
|---|---|---|
| **Public** (/, /login, /profil, /pengaturan-pengguna) | 4 | 4 |
| **SKP** | 3 | 3 |
| **Kegiatan** | 8 | 8 |
| **Kontraktual / SPK & BAST** | 2 | 2 |
| **Umum** (RKK DIPA + Surat + lainnya) | 13 | 11 |
| **IPDS** | 2 | 2 |
| **Bank Data** | 2 | 2 |
| **Admin** | 4 | 4 |
| **Total** | **38** | **36** |

> **Note:** `UmumSurat` component is reused for both `/umum/surat/keluar` and `/umum/surat/permintaan`.

---

## Router & Auth Architecture

- **Router:** TanStack Router (`@tanstack/react-router`) with `createRouter` + `createRoute`
- **Auth Guard:** `rootRoute.beforeLoad` checks `localStorage('auth_token')`; unauthenticated users redirected to `/login`
- **Role Guard:** `enforceRouteRole()` checks `canAccessPath()` from `lib/authz.ts` before loading protected routes
- **Layout:** `rootRoute.component` renders `Navigation` sidebar + `<Outlet />` for all non-login routes
- **Lazy Loading:** All view components loaded via `lazyRouteComponent()` for code splitting
- **Config Source:** Route definitions sourced from `MODULES` array in `constants.tsx`
