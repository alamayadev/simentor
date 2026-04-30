# UI Reference Map

## Table of Contents
- [Landing Page](#landing-page)
- [SKP Module](#skp-module)
- [Kegiatan Module](#kegiatan-module)
- [SPK & BAST Module](#spk--bast-module)
- [Umum Module](#umum-module)
- [IPDS Module](#ipds-module)
- [Bank Data Module](#bank-data-module)
- [Admin Module](#admin-module)
- [Global Components](#global-components)

---

## Landing Page

### Hero Section

| Element Name | Physical Location | Resulting Action |
|-------------|-------------------|------------------|
| **SImentor Control Center** badge | Top-left corner with amber icon | Brand identifier - no action |
| **"Buka Dashboard Kinerja"** button | Primary CTA button, orange/amber background | Navigates to `/skp/dashboard` |
| **"Lihat Monitoring DIPA"** button | Secondary button with white background | Navigates to `/umum/rkk-dipa/monitoring` |

### Metrics Cards

| Element Name | Physical Location | Resulting Action |
|-------------|-------------------|------------------|
| **Kinerja SKP** card (91%) | Top metrics row, left position | Displays average employee performance achievement |
| **Realisasi DIPA** card (Rp 1,98 Jt) | Top metrics row, center position | Shows DIPA realization as of April 2026 |
| **Kontrak Aktif** card (8) | Top metrics row, right position | Shows active SPK and BAST documents |

### Module Cards

| Element Name | Physical Location | Resulting Action |
|-------------|-------------------|------------------|
| **SKP** module card | Module grid, positioned by grid layout | Navigates to `/skp/dashboard` |
| **Kegiatan** module card | Module grid, positioned by grid layout | Navigates to `/kegiatan/monitoring` |
| **RKK DIPA** module card | Module grid, positioned by grid layout | Navigates to `/umum/rkk-dipa/monitoring` |
| **SPK & BAST** module card | Module grid, positioned by grid layout | Navigates to `/kontraktual/monitoring` |
| **IPDS** module card | Module grid, positioned by grid layout | Navigates to `/ipds/tiket` |
| **Admin** module card | Module grid, positioned by grid layout | Navigates to `/admin/pengguna` |

### Quick Access Shortcuts

| Element Name | Physical Location | Resulting Action |
|-------------|-------------------|------------------|
| **Dashboard Kinerja** | Bottom shortcuts grid | Navigates to `/skp/dashboard` |
| **Monitoring Kegiatan** | Bottom shortcuts grid | Navigates to `/kegiatan/monitoring` |
| **Monitoring DIPA** | Bottom shortcuts grid | Navigates to `/umum/rkk-dipa/monitoring` |
| **Pencairan DIPA** | Bottom shortcuts grid | Navigates to `/umum/rkk-dipa/pencairan` |
| **Monitoring Kontrak** | Bottom shortcuts grid | Navigates to `/kontraktual/monitoring` |
| **Tiket Bantuan IT** | Bottom shortcuts grid | Navigates to `/ipds/tiket` |

---

---

## SKP Module

### Page Header Navigation

| Element Name | Physical Location | Resulting Action |
|-------------|-------------------|------------------|
| **Dashboard** button | Top navigation bar, leftmost | Navigates to `/skp/dashboard` |
| **Progres** button | Top navigation bar, center-left | Navigates to `/skp/progres` |
| **Daftar SKP** button | Top navigation bar, center-right | Navigates to `/skp/daftar` |

---

## Kegiatan Module

### Page Header Navigation

| Element Name | Physical Location | Resulting Action |
|-------------|-------------------|------------------|
| **Daftar Kegiatan** button | Top navigation bar, leftmost | Navigates to `/kegiatan/daftar` |
| **Kalender** button | Top navigation bar, center-left | Navigates to `/kegiatan/kalender` |
| **Penugasan** button | Top navigation bar, center | Navigates to `/kegiatan/penugasan` |
| **Petugas** button | Top navigation bar, center-right | Navigates to `/kegiatan/petugas` |
| **Evaluasi** button | Top navigation bar, right-center | Navigates to `/kegiatan/evaluasi` |
| **Monitoring** button | Top navigation bar, rightmost | Navigates to `/kegiatan/monitoring` |

---

## SPK & BAST Module

### Page Header Navigation

| Element Name | Physical Location | Resulting Action |
|-------------|-------------------|------------------|
| **Monitoring** button | Top navigation bar, leftmost | Navigates to `/kontraktual/monitoring` |
| **SPK & BAST** button | Top navigation bar, rightmost | Navigates to `/kontraktual/spk-bast` |

---

## Umum Module

### Page Header Navigation

| Element Name | Physical Location | Resulting Action |
|-------------|-------------------|------------------|
| **RKK DIPA** dropdown menu | Top navigation bar, leftmost | Opens dropdown with submenu options |
| **Surat** dropdown menu | Top navigation bar, center-left | Opens dropdown with submenu options |
| **Polink** button | Top navigation bar, center | Navigates to `/umum/polink` |
| **Pegawai** button | Top navigation bar, center-right | Navigates to `/umum/pegawai` |
| **Hari Libur** button | Top navigation bar, right-center | Navigates to `/umum/libur` |
| **Pengaturan** button | Top navigation bar, rightmost | Navigates to `/umum/pengaturan` |

### RKK DIPA Submenu

| Element Name | Physical Location | Resulting Action |
|-------------|-------------------|------------------|
| **Monitoring** submenu item | Dropdown menu, top item | Navigates to `/umum/rkk-dipa/monitoring` |
| **Perencanaan** submenu item | Dropdown menu, second item | Navigates to `/umum/rkk-dipa/perencanaan` |
| **Pencairan** submenu item | Dropdown menu, third item | Navigates to `/umum/rkk-dipa/pencairan` |
| **Riwayat Pencairan** submenu item | Dropdown menu, fourth item | Navigates to `/umum/rkk-dipa/riwayat` |
| **Integritas Data** submenu item | Dropdown menu, fifth item | Navigates to `/umum/rkk-dipa/integritas` |
| **Revisi dan Import Data** submenu item | Dropdown menu, sixth item | Navigates to `/umum/rkk-dipa/revisi-import` |

### Surat Submenu

| Element Name | Physical Location | Resulting Action |
|-------------|-------------------|------------------|
| **Surat Keluar** submenu item | Dropdown menu, top item | Navigates to `/umum/surat/keluar` |
| **Surat Tugas** submenu item | Dropdown menu, second item | Navigates to `/umum/surat/tugas` |
| **Surat Keputusan** submenu item | Dropdown menu, third item | Navigates to `/umum/surat/keputusan` |
| **Surat Permintaan** submenu item | Dropdown menu, fourth item | Navigates to `/umum/surat/permintaan` |

### Surat Management Pages

| Element Name | Physical Location | Resulting Action |
|-------------|-------------------|------------------|
| **Buat Surat** button | Page header, right side with plus icon | Opens form to create new letter |
| **Sebelumnya** pagination button | Bottom of table, left side | Navigates to previous page of letters |
| **Berikutnya** pagination button | Bottom of table, right side | Navigates to next page of letters |

---

## IPDS Module

### Page Header Navigation

| Element Name | Physical Location | Resulting Action |
|-------------|-------------------|------------------|
| **Tiket** button | Top navigation bar, leftmost | Navigates to `/ipds/tiket` |
| **Asset TI** button | Top navigation bar, rightmost | Navigates to `/ipds/asset` |

---

## Bank Data Module

### Page Header Navigation

| Element Name | Physical Location | Resulting Action |
|-------------|-------------------|------------------|
| **Raw Data** button | Top navigation bar, leftmost | Navigates to `/bank-data/raw` |
| **Arsip Data** button | Top navigation bar, rightmost | Navigates to `/bank-data/arsip` |

---

## Admin Module

### Page Header Navigation

| Element Name | Physical Location | Resulting Action |
|-------------|-------------------|------------------|
| **Pengguna** button | Top navigation bar, leftmost | Navigates to `/admin/pengguna` |
| **Peran** button | Top navigation bar, center-left | Navigates to `/admin/peran` |
| **Izin Akses** button | Top navigation bar, center-right | Navigates to `/admin/izin` |
| **Meta Data** button | Top navigation bar, rightmost | Navigates to `/admin/metadata` |

### Meta Data Management

| Element Name | Physical Location | Resulting Action |
|-------------|-------------------|------------------|
| **Tambah Field** button | Page header, right side with plus icon | Opens form to add new metadata field |
| **Edit** icon button | Within each metadata card, top-right | Opens form to edit existing field |
| **Delete** icon button | Within each metadata card, top-right (red) | Deletes the metadata field |

---

---

## Global Components

### User Profile Page

| Element Name | Physical Location | Resulting Action |
|-------------|-------------------|------------------|
| **Pengguna** tab | Profile navigation tabs, leftmost | Shows user account information |
| **Kepegawaian** tab | Profile navigation tabs, center | Shows employee-related information |
| **Ubah Kata Sandi** tab | Profile navigation tabs, rightmost | Shows password management options |

### User Settings Page

| Element Name | Physical Location | Resulting Action |
|-------------|-------------------|------------------|
| **4/5 Layar (Sempit)** option | App width settings, left option | Sets application width to narrow view |
| **Lebar Penuh** option | App width settings, right option | Sets application width to full width |
| **Terang** option | Theme mode settings, left option with sun icon | Sets application to light mode |
| **Gelap** option | Theme mode settings, right option with moon icon | Sets application to dark mode |
| **Oranye** color option | Theme color settings, left option | Sets primary theme color to orange |
| **Indigo** color option | Theme color settings, center option | Sets primary theme color to indigo |
| **Hijau** color option | Theme color settings, right option | Sets primary theme color to green |

### Navigation Elements

| Element Name | Physical Location | Resulting Action |
|-------------|-------------------|------------------|
| **Dropdown Chevron** icon | Next to dropdown menu items | Toggles dropdown menu open/closed |
| **Lainnya (More)** button | Navigation overflow menu, rightmost | Shows additional navigation items |
| **Arrow Right** icon | Module cards and shortcuts | Indicates navigation functionality |
| **Active/Inactive** status indicator | Next to live feed items | Shows real-time system status |

---

## Notes
- All interactive elements include hover states and transitions
- Dropdown menus behave as accordions on mobile devices and as absolute-positioned dropdowns on desktop
- Navigation paths are relative to the application root
- Icons serve as visual indicators for different module types and actions