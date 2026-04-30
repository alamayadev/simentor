# Simentor Reconciliation Logic Documentation

This document explains how the Simentor system synchronizes and reconciles internal DIPA budget data with official SAKTI realization reports.

---

## 1. RKK DIPA Ingestion (Internal Budget)

**Script**: `backend/scripts/python/converter.py`  
**Target Table**: `budget_items`

### Parsing Workflow
1.  **Hierarchical State**: The script identifies the budget hierarchy (Program -> Activity -> Output -> Component -> Sub-Component -> Account) by matching regex patterns in the first column of the Excel file.
2.  **Item Extraction**: Individual rows containing volume and price below an Account block are captured as "Budget Items".
3.  **Key Generation**: A unique `composite_key` (MD5 hash) is generated.

### Hashing Strategy (Harmonized)
The `composite_key` in `budget_items` is generated from:
- `tahun_anggaran`
- Segment codes (Program...Account)
- **Normalized Description**: All non-alphanumeric characters are removed, and the text is converted to lowercase.
- *Note: Unit and Funding Source are EXCLUDED from the key to maximize match rates with SAKTI.*

---

## 2. SAKTI FA Detail Ingestion (Official Realization)

**Script**: `backend/scripts/python/sakti_import.py`  
**Target Table**: `budget_sakti_realizations`

### Ingestion Workflow
1.  **GLP039 Report Hierarchy**: Similar to the RKK parser, this script tracks the hierarchy level of the SAKTI GLP039 report.
2.  **Detail Item Identification**: The script looks for rows within an Account block that have a 6-digit prefix in the description column.
3.  **Realization Capture**: Extracts `sd_periode` (Total realization to date).
4.  **Key Generation**: Generates a `composite_key` using the exact same segments and normalization logic as the RKK parser.

---

## 3. Reconciliation Match Logic (1:1 Key Join)

Both the RKK and SAKTI parsers now generate identical `composite_key` values for the same items.

### Description Normalization Rules
1.  **SAKTI Side**: Strips the first 8 characters (the numeric prefix), then applies AlphaNumeric normalization.
2.  **RKK Side**: Applies AlphaNumeric normalization directly to the item name.

**Result**: `"000380. Honorarium Gaji"` (SAKTI) and `"Honorarium Gaji"` (RKK) both resolve to the normalized string `"honorariumgaji"`.

---

## 4. Composite Key Comparison Table

| Field Component | `budget_items` (Internal) | `sakti_realizations` (Official) | Used in Match Key? |
| :--- | :--- | :--- | :---: |
| **Year** | `tahun_anggaran` | `tahun_anggaran` | ✅ |
| **Program** | `program_code` | `program_code` | ✅ |
| **Activity** | `activity_code` | `activity_code` | ✅ |
| **Output** | `output_code` | `output_code` | ✅ |
| **Component** | `component_code` | `component_code` | ✅ |
| **Sub-Component** | `sub_component_code` | `sub_component_code` | ✅ |
| **Account** | `account_code` | `account_code` | ✅ |
| **Description** | `description` (Normalized) | `description` (8-char trim + Normalized) | ✅ |
| **Unit** | `unit` | *N/A* | ❌ |
| **Funding Source** | `funding_source` | *N/A* | ❌ |

---

## 5. Sample Data Matching Example

### Scenario: Office Supplies (ATK)

#### Internal RKK Row (`budget_items`)
- **Segments**: `2025 | WA | 1234.ABC | 001 | 001 | A | 521111`
- **Description**: `Pengadaan ATK, Kertas, & Bahan Cetak`
- **Normalization**: `pengadaanatkkertasbahancetak`
- **Composite Key**: `MD5("2025|WA|1234.ABC|001|001|A|521111|pengadaanatkkertasbahancetak")`

#### SAKTI GLP039 Row (`budget_sakti_realizations`)
- **Segments**: `2025 | WA | 1234.ABC | 001 | 001 | A | 521111`
- **Raw Desc**: `000121. Pengadaan ATK, Kertas, & Bahan Cetak`
- **Trimmed & Normalized**: `pengadaanatkkertasbahancetak`
- **Composite Key**: `MD5("2025|WA|1234.ABC|001|001|A|521111|pengadaanatkkertasbahancetak")`

**Conclusion**: The keys are identical, allowing a direct database JOIN.

---

## 6. Reconciliation Table View

**View**: `v_sakti_reconciliation`

Since the keys are already perfectly aligned in the tables, the view performs a simple direct join:
```sql
JOIN budget_sakti_realizations sr ON bi.composite_key = sr.composite_key
```

---

## 7. Update Behavior (New Uploads)

- **Cumulative Replacement**: Uploading a new SAKTI file for a year deletes OLD SAKTI records for that year and inserts fresh data.
- **RKK Re-import**: To apply these new matching rules to existing data, the internal RKK DIPA file must be re-uploaded once.

---

## 8. Directory Structure & Files

- `backend/scripts/python/converter.py`: RKK DIPA Ingester.
- `backend/scripts/python/sakti_import.py`: SAKTI Realization Ingester.
- `backend/database/migrations/2026_04_20_134905_create_sakti_realizations_table.php`: View definition.
- `backend/public/sakti_excel/`: Upload folder for SAKTI reports (Named: `LAP_FA_SAKTI_[internal_creation_date].xlsx`).
