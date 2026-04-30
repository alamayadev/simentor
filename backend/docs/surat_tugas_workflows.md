Here's the complete SuratTugas workflow:

---

## End-to-End Workflow

```
Step 1: CREATE HEADER (Nomor)        Step 2: ADD DETAIL RECORDS         Step 3: GENERATE DOCX
┌──────────────────────────┐     ┌───────────────────────────┐     ┌──────────────────────────┐
│ POST /surat-tugas        │     │ POST /surtug-detil         │     │ GET /generate-docx/.../{id}
│ SuratTugasApiController  │────>│ SurtugDetilApiController  │────>│ SuratTugas*Controller    │
│ ::store()                │     │ ::store() / bulkMitra()   │     │ ::index/generate()       │
└──────────────────────────┘     └───────────────────────────┘     └──────────────────────────┘
```

---

### Step 1: Create Nomor (Letter Number)

**Controller:** `SuratTugasApiController::store()` via `POST /api/kantor/surat/surat-tugas`

1. Reads `FORMAT_SURTUG` setting from `settings` table (e.g. `{nomor}/ST-{klasifikasi}/{tahun}`)
2. Auto-increments `nomor`: `MAX(CAST(nomor AS UNSIGNED)) + 1` for the given year, padded to 4 digits → `"0001"`
3. Substitutes placeholders → final letter number e.g. `0001/ST-100/2024`
4. Auto-generates `tanggal_indo` via Carbon Indonesian locale
5. Inserts into `surat_tugas` table (header record)

**Insert (Sisip):** `POST /surat-tugas/sisip` — auto-generates `no_sisip` = `MAX(no_sisip)+1`, producing number like `0001.1/ST-100/2024`

---

### Step 2: Add Detail Records (Personnel Assignments)

**Controller:** `SurtugDetilApiController` via `POST /api/kantor/surat/surtug-detil`

Each detail links a person to the surat tugas header:

| Field | Purpose |
|---|---|
| `surtug_id` | FK → `surat_tugas.id` |
| `pegawai_id` or `mitra_id` | The person assigned (organik or mitra) |
| `isOrganik` | `true` = civil servant, `false` = mitra |
| `nama_kegiatan` | Activity name |
| `hari` | Duration in days |
| `tgl_mulai` | Start date |
| `wilayah_kerja` | Work area |
| `no_dipa` | Budget number |
| `sppd` | Whether travel warrant applies |
| `dasar` | Legal basis (optional) |

**Bulk mitra:** `POST /surtug-detil/bulk-mitra` accepts `mitra_ids[]` + `kegiatan_id`, auto-looks up `penugasan_id` for each mitra.

---

### Step 3: Generate DOCX

There are **two approaches**, selected by which route/controller is called:

#### Approach A: Template-based (`SuratTugasOrganikController`)
Uses `.docx` template files in `public/templates/` with `TemplateProcessor` to replace `${placeholders}`.

**Template selection logic** (by `isOrganik`, `jml_petugas`, `sppd`, `dasar`):

| Condition | Template |
|---|---|
| Organik + 1 person + SPPD | `surat_tugas_organik.docx` |
| Organik + 1 person + lokal | `format_surat_tugas_organik_lokal.docx` |
| Mitra + 1 person + no dasar | `surat_tugas_mitra.docx` |
| Mitra + 1 person + dasar | `surat_tugas_mitra_with_menimbang.docx` |
| Mitra + multiple + dasar | `surat_tugas_mitra_with_table_and_menimbang.docx` |
| Organik + multiple + SPPD | `surat_tugas_organik_with_table.docx` |
| Organik + multiple + lokal | `format_surat_tugas_organik_lokal_with_table.docx` |

For ≥2 personnel, a dynamic `Table` is injected via `setComplexBlock('table', $table)` with columns: No, Nama, NIP, Jabatan/Gol (organik) or No, Nama, Sobat ID, Kecamatan (mitra).

#### Approach B: Programmatic (`SuratTugasMitraController`, `*BladeController`)
Builds the DOCX entirely from code. The **Blade** variants render a Blade view to HTML then inject via `Html::addHtml()` for the header portion.

**Document structure:**
1. Header — BPS logo + office name + "SURAT Tugas" + letter number
2. Menimbang — numbered list of considerations (+ optional `menimbang` from header)
3. Mengingat — legal references from `Uu` model (`jenis='KPA'` for mitra, `jenis='Kepala'` for organik)
4. Memberi Perintah — centered
5. Detail table — Kepada / NIP or Sobat ID / Jabatan / Untuk
6. Signature block — date + Kepala BPS name + NIP
7. Lampiran — personnel table (for multi-person docs)

---

### Data Relationships

```
surat_tugas (header: no_surat, tanggal, menimbang)
    │
    ├── 1:N ──> surat_tugas_detil (surtug_id)
    │               ├── N:1 ──> profil_pegawai (pegawai_id)   [organik]
    │               ├── N:1 ──> mitra_kepka (mitra_id)        [mitra]
    │               └── N:1 ──> penugasan (penugasan_id)
    │                            └── N:1 ──> kegiatan
    │
settings ──> FORMAT_SURTUG, KEPALA_KANTOR, PPK, NIP_KEPALA, NIP_PPK
uu ──> legal references (jenis='KPA' or 'Kepala')
```

### Settings Used

| Key | Used By | Purpose |
|---|---|---|
| `FORMAT_SURTUG` | `SuratTugasApiController` | Letter number format template |
| `KEPALA_KANTOR` | All DOCX generators | Signatory name |
| `PPK` | `SuratTugasOrganikController` | Budget officer name |
| `NIP_KEPALA` / `NIP%` | All DOCX generators | Signatory NIP |
| `NIP_PPK` | `SuratTugasOrganikController` | PPK NIP |


Here's how the four controllers work:

## 1. `SuratTugasOrganikController` — Template-based DOCX generator (Organik + Mitra)

- **`index($id)`**: Generates a combined DOCX for all personnel in a surat tugas. Selects a `.docx` template based on conditions (organik vs mitra, single vs multiple personnel, SPPD vs lokal). Uses `TemplateProcessor` to replace placeholders like `${nama_petugas}`, `${nip}`, etc. For multiple personnel (≥2), it builds a dynamic `Table` via `setComplexBlock('table', ...)` for the Lampiran appendix. (`app/Http/Controllers/SuratTugasOrganikController.php:92`)

- **`satu($id)`**: Same logic as `index()` but generates a DOCX for a **single detail record** (by `surtug_detail.id` instead of `surtug_id`). No table/Lampiran is generated. (`app/Http/Controllers/SuratTugasOrganikController.php:279`)

**Template selection logic** (line ~118-131): Picks one of 7 templates depending on `isOrganik`, `jml_petugas`, `sppd`, and `dasar` values.

## 2. `SuratTugasMitraController` — Pure PhpWord DOCX builder (no templates)

- **`generateDocx($id)`**: Builds a DOCX entirely from code for **Mitra** personnel. Loops through each `SurtugDetil` record, manually creating sections with the BPS logo, header, Menimbang/Mengingat tables, detail tables (Kepada/Sobat ID/Jabatan/Untuk), and signature block. Uses `IOFactory::createWriter` to save to `public_path()`. (`app/Http/Controllers/SuratTugasMitraController.php:56`)

- **`generateOrganikDocx($id)`**: Identical structure to `generateDocx()` but for **Organik/Pegawai** personnel — uses `$item->pegawai->nama/nip/jabatan` instead of mitra fields. (`app/Http/Controllers/SuratTugasMitraController.php:169`)

Both methods produce a page per person with page breaks between them, and save to `public_path()` (not temp).

## 3. `SuratTugasOrganikBladeController` — Blade + PhpWord hybrid (Organik/Mitra)

- **`generate(int $id)`**: Fetches all records for a surat tugas, determines if pegawai or mitra. Renders a Blade view (`surat_tugas_organik.header`) to HTML, then feeds it into `Html::addHtml()` for the document header. The rest (Menimbang/Mengingat as numbered lists, detail table, signature, Lampiran table) is built programmatically with PhpWord tables. The Lampiran supports both pegawai columns (NIP, Jabatan/Gol) and mitra columns (Sobat ID, Kecamatan). Saves to `storage/app/temp/`. (`app/Http/Controllers/SuratTugasOrganikBladeController.php:17`)

## 4. `SuratTugasMitraBladeController` — Blade + PhpWord hybrid (Mitra only)

- **`generate(int $id)`**: Same hybrid approach as OrganikBlade but specifically for **Mitra** personnel. Renders `surat_tugas_mitra.header` Blade view. The Lampiran table always shows mitra-specific columns (Nama, Sobat ID, Kecamatan). Fetches `Uu` laws with `jenis = 'KPA'` for Mengingat items. Saves to `storage/app/temp/`. (`app/Http/Controllers/SuratTugasMitraBladeController.php:24`)

## Summary comparison

| Controller | Method | Template | Personnel | Output |
|---|---|---|---|---|
| `SuratTugasOrganikController` | `index`/`satu` | `.docx` template files | Both | `storage/app/temp/` |
| `SuratTugasMitraController` | `generateDocx`/`generateOrganikDocx` | None (pure code) | Both | `public_path/` |
| `SuratTugasOrganikBladeController` | `generate` | Blade view for header | Both | `storage/app/temp/` |
| `SuratTugasMitraBladeController` | `generate` | Blade view for header | Mitra only | `storage/app/temp/` |

The **Blade** versions are the newer/refactored approach — they use Blade templates for the header HTML and build tables programmatically with proper numbered lists and fixed-width columns. The **non-Blade** versions are the older approach using either `.docx` template files (OrganikController) or fully inline PhpWord code (MitraController).