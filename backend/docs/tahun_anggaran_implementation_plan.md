# Implementation Plan: Adding `tahun_anggaran` to Budget Ingestion

This document outlines the steps to add the `tahun_anggaran` (Fiscal Year) field to the Simentor budget monitoring system. This field will be provided by the client during upload and will be integrated into the `composite_key` generation to ensure uniqueness across different fiscal years.

## User Review Required

> [!IMPORTANT]
> Including `tahun_anggaran` in the `composite_key` will change the hash for all future imports. Existing data in `budget_items` will have a different hash structure than new data. We should decide if we need to migrate old data or if the different years will naturally keep them separate.

## Proposed Changes

### 1. Database Schema
#### [MODIFY] [schema_mysql.sql](file:///e:/Github/Simentor3215/backend/scripts/python/schema_mysql.sql)
- Add `tahun_anggaran` column to `budget_items` (INT).
- Add `tahun_anggaran` column to `imported_files` (INT).
- Update `UNIQUE` constraint in `budget_items` to `(revision_name, composite_key, tahun_anggaran)` if needed, although `composite_key` itself will now contain the year.

### 2. Laravel Backend
#### [MODIFY] [BudgetItem.php](file:///e:/Github/Simentor3215/backend/app/Models/Dipa/BudgetItem.php)
- Add `tahun_anggaran` to the `$fillable` array.

#### [MODIFY] [DipaBudgetApiController.php](file:///e:/Github/Simentor3215/backend/app/Http/Controllers/Api/Kantor/Dipa/DipaBudgetApiController.php)
- Update `importDipaFile` validation to require `tahun_anggaran` (numeric, e.g., 2026).
- Modify the `Process::run` call to pass the year as an argument:
  ```php
  $result = Process::path(base_path())
      ->run("python " . escapeshellarg($pythonScript) . " --year " . escapeshellarg($request->tahun_anggaran));
  ```

### 3. Python Scripts
#### [MODIFY] [auto_import.py](file:///e:/Github/Simentor3215/backend/scripts/python/auto_import.py)
- Use `argparse` to accept the `--year` parameter.
- Pass the `year` value to the `convert_rkk()` function.
- Update the `imported_files` insertion to include the `tahun_anggaran`.

#### [MODIFY] [converter.py](file:///e:/Github/Simentor3215/backend/scripts/python/converter.py)
- Update `convert_rkk` to accept `tahun_anggaran` as a parameter.
- Modify `key_src` generation to include the year at the beginning:
  ```python
  key_src = "|".join([
      str(tahun_anggaran), # New field included in hash
      str(state['program']['code']), 
      # ... other fields
  ])
  ```
- Update the `INSERT` query for `budget_items` to include the `tahun_anggaran` column.

## Verification Plan
### Automated Tests
- N/A (Manual verification of database and API logs is preferred for this integration task).

### Manual Verification
1. **Database Update**: Execute the `ALTER TABLE` commands to add the column.
2. **API Test**: Use an API client (like Postman or Insomnia) to upload an Excel file with the added `tahun_anggaran` field.
3. **Persistence Check**: Query the `budget_items` table to ensure:
   - The `tahun_anggaran` column is correctly filled.
   - The `composite_key` is generated successfully (should be a 32-character MD5 hash).
4. **Log Check**: Verify `imported_files` table also captures the year.

## Client-Side Data Format

To accommodate this change, the client app (Frontend) must update its `POST` request to the `/import-dipa` endpoint using `multipart/form-data`.

### Required Fields

| Field Name | Data Type | Example | Description |
| :--- | :--- | :--- | :--- |
| **`revisi`** | `numeric` | `3` | The revision number of the DIPA. |
| **`tgl_revisi`** | `string` | `2026-04-18` | The date of the revision (YYYY-MM-DD). |
| **`excel_file`** | `file` | `Budget.xlsx` | The actual Excel file (.xlsx or .xls). |
| **`tahun_anggaran`** | `numeric` | `2026` | **(NEW)** The 4-digit fiscal year for this data. |

### Axios Example
```javascript
const formData = new FormData();
formData.append('revisi', 4);
formData.append('tgl_revisi', '2026-03-12');
formData.append('tahun_anggaran', 2026);
formData.append('excel_file', fileInput.files[0]);

axios.post('/api/kantor/dipa/import', formData);
```

