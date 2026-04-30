# Implementasi Workflow Surat Keputusan di `simentor2026v2`

## Summary
Implement workflow Surat Keputusan penuh seperti `backend/docs/surat_keputusan_workflows.md`: penomoran SK, CRUD header, sisip, detail mitra/organik, pemilihan endpoint DOCX, dan download DOCX. Karena backend belum punya API untuk `surat_sk_detil`, implementasi harus mencakup backend endpoint detail SK baru sebelum client bisa lengkap.

## Key Changes
- Buat OpenSpec change proposal terlebih dahulu untuk capability baru “Surat Keputusan workflow”, lalu implement setelah proposal disetujui.
- Backend:
  - Tambah `SkDetilApiController` untuk `GET/POST/PUT/DELETE /api/kantor/surat/sk-detil`, `GET /sk-detil/sk/{sk_id}`, dan option endpoints `pegawai-options`, `mitra-options`, `kegiatan-options`, `mitra-penugasan-options`.
  - Validasi detail:
    - Organik: `sk_id`, `pegawai_id`, `isOrganik=true`, `detil` opsional sebagai jabatan kegiatan.
    - Mitra manual: `sk_id`, `mitra_id`, `isOrganik=false`, `penugasan_id=null`.
    - Mitra dari penugasan: `sk_id`, `mitra_id`, `penugasan_id`, `isOrganik=false`.
  - Tambah bulk create mitra by `kegiatan_id + mitra_ids`, mengikuti pola `SurtugDetilApiController::bulkMitra`.
  - Tambah routes di grup `/api/kantor/surat`.
  - Sesuaikan `generateSkKepalaOrganik()` agar kolom “JABATAN KEGIATAN” memakai `SkDetil.detil`.
- Client:
  - Ganti route `/umum/surat/keputusan` dari `UmumSurat` ke `SuratKeputusanWorkflow`.
  - Tambah typed API untuk `SuratKeputusan`, `SuratKeputusanFormOptions`, `SkDetil`, dan option types.
  - Perluas `suratKeputusanService`: `years`, `dates`, `formOptions`, `downloadDocx`.
  - Tambah `skDetilService` untuk detail SK dan option endpoints.
  - Buat helper `selectSuratKeputusanDocxEndpoint(skId, details, header)`:
    - all organik → `/surat/surat-keputusan/kepala/generate-docx/organik/{skId}`
    - all mitra + semua punya `penugasan_id` → `/surat/surat-keputusan/kpa/generate-docx/mitra/{skId}`
    - all mitra + ada yang tanpa `penugasan_id` → `/surat/surat-keputusan/kpa/generate-docx/mitra/manual/{skId}`
    - mixed/empty → disable DOCX.
  - UI mengikuti pola `SuratTugasWorkflow`: filter tahun/tanggal/search, table header SK, action dropdown, panel detail, modal create/edit/sisip, modal detail organik/mitra/bulk mitra, delete confirmation, dan tombol DOCX.

## Behavior Details
- Header create:
  - Ambil `nomor_baru_sk` dan `pejabat` dari `form-options`.
  - Kirim `thn`, `tanggal`, `nomor=nomor_baru_sk`, `oleh`, `kegiatan`, `kepada`, `perihal`, `kol_lampiran`, optional `kode_klas`.
- Header sisip:
  - Kirim `id` referensi, `type='SK'`, `tanggal`, `oleh`, `kegiatan`, `kepada`, `perihal`, `kol_lampiran`, optional `kode_klas`.
  - Jangan kirim `thn`, `nomor`, atau `no_sisip`; backend mengambil dari referensi.
- Header edit:
  - Nomor/tahun/no_sisip locked.
  - Kirim hanya field yang backend update: `tanggal`, `oleh`, `kegiatan`, `kepada`, `perihal`, `kol_lampiran`, optional `kode_klas`.
- Detail panel:
  - Detail organik menampilkan nama, NIP, pangkat/gol, dan `detil`.
  - Detail mitra menampilkan nama mitra, penugasan/kegiatan bila ada, dan mode manual bila `penugasan_id` kosong.
  - Mixed detail tetap bisa ditampilkan/dikelola, tetapi DOCX disabled dengan pesan “Jenis detail campuran belum didukung”.

## Test Plan
- Backend feature tests:
  - SK detail CRUD: create/list by `sk_id`/update/delete untuk organik dan mitra.
  - Bulk mitra creates multiple rows and maps `penugasan_id`.
  - Option endpoints return pegawai/mitra/kegiatan/penugasan.
  - Organik DOCX generator uses `SkDetil.detil` for jabatan kegiatan.
- Frontend unit tests:
  - `selectSuratKeputusanDocxEndpoint` covers organik, mitra penugasan, mitra manual, mixed, and empty.
  - Workflow renders API list, filters by tahun/tanggal/search, creates header, creates sisip, edits header, deletes header.
  - Adds organik detail, adds manual mitra detail, bulk-adds mitra by kegiatan, deletes detail.
  - DOCX button chooses correct endpoint and is disabled for empty/mixed detail.
- Run:
  - `php artisan test` for new backend tests.
  - `npm test -- SuratKeputusanWorkflow.test.tsx --run`
  - `npm run lint`

## Assumptions
- Full workflow means backend + client changes are allowed.
- New detail endpoint path will be `/api/kantor/surat/sk-detil`.
- Client visual style should mirror `SuratTugasWorkflow`, not the older mock `UmumSurat`.
- `detil` on `SkDetil` is treated as display text for jabatan kegiatan/manual notes, while DOCX mitra-with-penugasan derives jabatan, volume, and rate from `penugasan`.
