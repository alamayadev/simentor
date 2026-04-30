# `POST` `/api/kantor/laporan-perjalanan-dinas/{id}/dokumentasi`

## Endpoint

| Property | Value |
|----------|-------|
| **HTTP Method** | `POST` |
| **URL** | `/api/kantor/laporan-perjalanan-dinas/{id}/dokumentasi` |
| **Controller** | `App\Http\Controllers\Api\Kantor\LaporanPerjalananDinasApiController` |
| **Method** | `uploadDokumentasi()` |
| **FormRequest** | `App\Http\Requests\Kantor\Laperdin\StoreLaperdinDokumentasiRequest` |

## Path Parameters

| Parameter | Description |
|-----------|-------------|
| `id` | Path parameter |

## Body Parameters

| Parameter | Required | Type | Rules |
|-----------|----------|------|-------|
| `file` | **Yes** | `file` | required, file, image, max:10240 |
| `deskripsi` | No | `string` | nullable, string, max:255 |

### Required Fields Summary

```
file (file)
```

### Example Request Body

```json
{
    "file": "(binary file)",
    "deskripsi": "string"
}
```

---
*Generated at: 2026-04-18 20:35:30*
