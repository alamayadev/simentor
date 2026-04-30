## Workflow SuratKeputusan — dari Penomoran sampai Generate DOCX

Berikut adalah workflow lengkap modul Surat Keputusan di folder `backend/app/`:

---

### 📁 File-File Terlibat

| File | Peran |
|---|---|
| `Http/Controllers/Api/Kantor/NomorSurat/SuratKeputusanApiController.php` | CRUD & **Penomoran** otomatis |
| `Http/Controllers/SuratKeputusanController.php` | **Generate DOCX** (3 jenis template) |
| `Models/SkBast.php` | Model utama → tabel `surat_sk_bast` |
| `Models/SkDetil.php` | Model detil → tabel `surat_sk_detil` |
| `Models/Penugasan.php` | Relasi penugasan mitra/pegawai |
| `public/templates/format_sk_kpa_mitra.docx` | Template SK KPA Mitra |
| `public/templates/format_sk_kepala_mitra.docx` | Template SK Kepala Mitra (manual) |
| `public/templates/format_sk_kepala_organik.docx` | Template SK Kepala Organik |

---

### 🔢 FASE 1: PENOMORAN SURAT (`SuratKeputusanApiController`)

#### Step 1 — Ambil Form Options
**`GET /api/kantor/nomor-surat/surat-keputusan/form-options`**

```
formOptions() → Menghitung nomor baru:
  - Cari MAX(nomor) dari surat_sk_bast WHERE type='SK' & thn=tahun_sekarang
  - nomor_baru = MAX + 1, di-pad 4 digit → "0001", "0002", dst.
  - Juga mengembalikan daftar pejabat: ["KPA", "Kepala Kantor"]
```

#### Step 2 — Simpan / Buat Surat Keputusan Baru
**`POST /api/kantor/nomor-surat/surat-keputusan`**

Alur `store()`:
1. **Validasi** input: `thn`, `tanggal`, `nomor`, `kepada`, `perihal` (wajib)
2. **Set `type` = `'SK'`** (pembeda dengan BAST)
3. **Konversi bulan ke Romawi**: `convertToRoman(bulan dari tanggal)` → `bln` = "I", "II", ... "XII"
4. **Ambil format** dari tabel `settings` → key `FORMAT_SK` (contoh: `"{nomor}/SK/{bln}/{tahun}"`)
5. **Pad nomor** jadi 4 digit: `str_pad("1", 4, "0", STR_PAD_LEFT)` → `"0001"`
6. **Generate `no_surat`**:
   - Jika ada `no_sisip`: `no = "0001.1"` (nomor.sisip)
   - Jika tidak: `no = "0001"`
   - Replace placeholder: `str_replace("{nomor}", $no, $format)` → lalu `{bln}` → lalu `{tahun}`
   - **Hasil**: `"0001/SK/I/2024"`
7. **Simpan** ke `SkBast::create($data)` → tabel `surat_sk_bast`

#### Step 2b — Buat Surat Sisipan
**`POST /api/kantor/nomor-surat/surat-keputusan/sisip`**

Alur `sisip()`:
1. Ambil `thn` dan `nomor` dari surat referensi (ID yang di-input)
2. Hitung `no_sisip` otomatis: `MAX(no_sisip) + 1` untuk kombinasi nomor+tahun yang sama
3. Generate `no_surat` dengan format sisipan → `"0001.1/SK/I/2024"`, `"0001.2/SK/I/2024"`, dst.
4. Simpan record baru (bukan update, tapi record baru)

#### Step 3 — Update / Edit
**`PUT /api/kantor/nomor-surat/surat-keputusan/{id}`**

Alur `update()`:
- `thn`, `nomor`, `no_sisip` **TIDAK diubah** (locked)
- Hanya update: `tanggal`, `oleh`, `kegiatan`, `kepada`, `perihal`, `kol_lampiran`
- Regenerasi `bln` (Romawi) dan `no_surat` dari data existing

---

### 📄 FASE 2: GENERATE DOCX (`SuratKeputusanController`)

Setelah surat punya nomor, detil mitra/pegawai di-input ke `surat_sk_detil`, user bisa generate DOCX. Ada **3 jenis** generate:

#### Jenis 1: SK KPA Mitra (dari Penugasan)
**`GET /api/surat/surat-keputusan/kpa/generate-docx/mitra/{sk}`**

Alur `generateSkKpaMitra($sk)`:
1. **Query detil** — `SkDetil` dengan eager loading:
   - `pegawai`, `mitra`, `nomor` (SkBast), `penugasan` (kegiatan, jabatan, volume)
   - Di-join dengan `mitra_kepka`, di-order by `desaid` (wilayah)
2. **Ambil settings** (cached): `KEPALA_KANTOR`, `NIP*`, `%_DIPA`
3. **Ambil data kegiatan** (cached) untuk rate PCL/PML/Entri
4. **Load template** → `TemplateProcessor('templates/format_sk_kpa_mitra.docx')`
5. **Set nilai template**: `nomor_sk`, `perihal`, `kepada`, `nama_kegiatan`, `tgl_sk`, `kepala_kantor`, `nip_kepala`, `nomor_dipa`, `tanggal_dipa`, dll.
6. **Proses daftar UU** (`processUuData()`):
   - Ambil dari tabel `uu` WHERE `jenis` = `$sk->oleh`
   - Gabung dengan `uu_tambahan` WHERE `jenis_surat`='SK' AND `surat_id`=$skId
   - `cloneRowAndSetValues('i', $uu)` → baris UU di template di-clone
7. **Bangun tabel lampiran** (`buildLampiranTable()`):
   - Header: NO | NAMA | JABATAN TUGAS | BEBAN TUGAS | RATE SATUAN
   - Mapping jabatan: PCL→Pencacah, PML→Pengawas, lainnya→Operator
   - Rate diambil dari kegiatan: `rate_pcl`/`rate_pml`/`rate_entri` per satuan
   - `cloneRowAndSetValues('n', $lampiran)`
8. **Simpan** file → `public/SK_{oleh}_{no_surat_sanitized}.docx`
9. **Download** response dengan `deleteFileAfterSend(true)`

#### Jenis 2: SK KPA Mitra Manual (tanpa Penugasan detail)
**`GET /api/surat/surat-keputusan/kpa/generate-docx/mitra/manual/{sk}`**

Alur `generateSkKpaMitraManual($sk)`:
- Mirip jenis 1, tapi:
  - Tabel lampiran **hanya berisi NAMA** (jabatan, beban, rate dikosongkan)
  - Template dipilih berdasarkan `$sk->oleh`:
    - `'KPA'` → `format_sk_kpa_mitra.docx`
    - lainnya → `format_sk_kepala_mitra.docx`

#### Jenis 3: SK Kepala Organik (Pegawai)
**`GET /api/surat/surat-keputusan/kepala/generate-docx/organik/{sk}`**

Alur `generateSkKepalaOrganik($sk)`:
- Query `SkDetil` dengan eager loading `pegawai` (nama, nip, pangkat, gol)
- Template → `format_sk_kepala_organik.docx`
- Tabel lampiran berisi: NO | NAMA | NIP | PANGKAT/GOL | JABATAN KEGIATAN

---

### 🔄 DIAGRAM ALUR LENGKAP

```
┌─────────────────────────────────────────────────────────────────┐
│                    FRONTEND (React/Vue)                         │
└──────────┬──────────────────────────────────────────┬───────────┘
           │                                          │
     [FASE 1: PENOMORAN]                        [FASE 2: DOCX]
           │                                          │
           ▼                                          ▼
┌─────────────────────────┐              ┌───────────────────────────┐
│ SuratKeputusanApiController │              │ SuratKeputusanController  │
│ (Api/Kantor/NomorSurat)  │              │                           │
├─────────────────────────┤              ├───────────────────────────┤
│ 1. formOptions()        │              │ generateSkKpaMitra()      │
│    → nomor baru (max+1) │              │   → Template KPA Mitra    │
│                         │              │   → Dengan rate & volume  │
│ 2. store()              │              │                           │
│    → FORMAT_SK setting  │              │ generateSkKpaMitraManual()│
│    → convertToRoman()   │              │   → Template KPA/Kepala   │
│    → no_surat generated │              │   → Tanpa rate            │
│    → SkBast::create()   │              │                           │
│                         │              │ generateSkKepalaOrganik() │
│ 3. sisip()              │              │   → Template Organik      │
│    → auto no_sisip      │              │   → NIP, Pangkat/Gol      │
│    → "0001.1/SK/I/2024" │              │                           │
│                         │              │ Semua:                     │
│ 4. update()             │              │ → processUuData()         │
│    → regenerasi no_surat│              │ → buildLampiranTable()    │
│    → nomor locked       │              │ → TemplateProcessor       │
│                         │              │ → saveAs .docx            │
│ 5. index/show/destroy() │              │ → response()->download()  │
└────────┬────────────────┘              └────────┬──────────────────┘
         │                                        │
         ▼                                        ▼
┌────────────────────┐               ┌────────────────────────────┐
│   surat_sk_bast    │               │ surat_sk_detil (SkDetil)   │
│   (Model: SkBast)  │◄── 1:N ────► │   + mitra / pegawai        │
│                    │               │   + penugasan (kegiatan)   │
│ - id               │               │   + nomor (SkBast)         │
│ - thn              │               └────────────────────────────┘
│ - bln (Romawi)     │
│ - nomor ("0001")   │               ┌────────────────────────────┐
│ - no_sisip         │               │ Referensi Data:            │
│ - no_surat (final) │               │ - settings (FORMAT_SK,     │
│ - type = "SK"      │               │   KEPALA_KANTOR, NIP,      │
│ - oleh             │               │   %_DIPA)                  │
│ - kegiatan         │               │ - uu / uu_tambahan         │
│ - kepada           │               │ - kegiatan + satuan        │
│ - perihal          │               │ - mitra_kepka              │
│ - tanggal          │               └────────────────────────────┘
│ - kol_lampiran     │
└────────────────────┘
```

---

### 📝 Format Nomor Surat

Format disimpan di tabel `settings` dengan key `FORMAT_SK`, contoh nilai: `"{nomor}/SK/{bln}/{tahun}"`

Komponen:
- `{nomor}` → 4-digit + opsional `.sisip` (contoh: `0001` atau `0001.2`)
- `{bln}` → Bulan Romawi dari `tanggal` (contoh: `I`, `VI`, `XII`)
- `{tahun}` → `thn` (contoh: `2024`)

**Hasil akhir**: `0001/SK/I/2024` atau `0001.2/SK/VI/2024` (sisipan)