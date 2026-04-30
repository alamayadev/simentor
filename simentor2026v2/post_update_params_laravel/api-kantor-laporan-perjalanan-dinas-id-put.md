# `PUT` `/api/kantor/laporan-perjalanan-dinas/{id}`

## Endpoint

| Property | Value |
|----------|-------|
| **HTTP Method** | `PUT` |
| **URL** | `/api/kantor/laporan-perjalanan-dinas/{id}` |
| **Controller** | `App\Http\Controllers\Api\Kantor\LaporanPerjalananDinasApiController` |
| **Method** | `update()` |
| **FormRequest** | `App\Http\Requests\Kantor\Laperdin\UpdateLaperdinRequest` |

## Path Parameters

| Parameter | Description |
|-----------|-------------|
| `id` | Path parameter |

## Body Parameters

| Parameter | Required | Type | Rules |
|-----------|----------|------|-------|
| `nama_traveler` | No | `string` | sometimes, string, max:255 |
| `tujuan` | No | `string` | sometimes, string, max:255 |
| `lama_tanggal` | No | `string` | sometimes, string, max:255 |
| `dalam_rangka` | No | `string` | sometimes, string, max:255 |
| `pembebanan` | No | `string` | nullable, string, max:255 |
| `kode_keg` | No | `foreign_key` | nullable, exists:kegiatans,id |
| `status` | No | `enum` | sometimes, in:draft,submitted,final |

### Example Request Body

```json
{
    "nama_traveler": "string",
    "tujuan": "string",
    "lama_tanggal": "2025-01-01",
    "dalam_rangka": "string",
    "pembebanan": "string",
    "kode_keg": 1,
    "status": "draft"
}
```

---
*Generated at: 2026-04-18 20:35:30*
