# `POST` `/api/kantor/laporan-perjalanan-dinas`

## Endpoint

| Property | Value |
|----------|-------|
| **HTTP Method** | `POST` |
| **URL** | `/api/kantor/laporan-perjalanan-dinas` |
| **Controller** | `App\Http\Controllers\Api\Kantor\LaporanPerjalananDinasApiController` |
| **Method** | `store()` |
| **FormRequest** | `App\Http\Requests\Kantor\Laperdin\StoreLaperdinRequest` |

## Body Parameters

| Parameter | Required | Type | Rules |
|-----------|----------|------|-------|
| `nama_traveler` | **Yes** | `string` | required, string, max:255 |
| `tujuan` | **Yes** | `string` | required, string, max:255 |
| `lama_tanggal` | **Yes** | `string` | required, string, max:255 |
| `dalam_rangka` | **Yes** | `string` | required, string, max:255 |
| `pembebanan` | No | `string` | nullable, string, max:255 |
| `kode_keg` | No | `foreign_key` | nullable, exists:kegiatans,id |
| `status` | No | `enum` | nullable, in:draft,submitted,final |

### Required Fields Summary

```
nama_traveler (string)
tujuan (string)
lama_tanggal (string)
dalam_rangka (string)
```

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
