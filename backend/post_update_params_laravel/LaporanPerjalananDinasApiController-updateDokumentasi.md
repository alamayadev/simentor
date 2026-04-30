# `POST` `/api/kantor/laporan-perjalanan-dinas/{id}/dokumentasi/{docId}`

## Endpoint

| Property | Value |
|----------|-------|
| **HTTP Method** | `POST` |
| **URL** | `/api/kantor/laporan-perjalanan-dinas/{id}/dokumentasi/{docId}` |
| **Controller** | `App\Http\Controllers\Api\Kantor\LaporanPerjalananDinasApiController` |
| **Method** | `updateDokumentasi()` |
| **FormRequest** | `App\Http\Requests\Kantor\Laperdin\UpdateLaperdinDokumentasiRequest` |

## Path Parameters

| Parameter | Description |
|-----------|-------------|
| `id` | Path parameter |
| `docId` | Path parameter |

## Body Parameters

| Parameter | Required | Type | Rules |
|-----------|----------|------|-------|
| `file` | No | `file` | nullable, file, image, max:10240 |
| `deskripsi` | No | `string` | nullable, string, max:255 |

### Example Request Body

```json
{
    "file": "(binary file)",
    "deskripsi": "string"
}
```

---
*Generated at: 2026-04-18 20:35:30*
