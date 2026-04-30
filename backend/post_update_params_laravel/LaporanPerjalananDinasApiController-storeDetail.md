# `POST` `/api/kantor/laporan-perjalanan-dinas/{id}/details`

## Endpoint

| Property | Value |
|----------|-------|
| **HTTP Method** | `POST` |
| **URL** | `/api/kantor/laporan-perjalanan-dinas/{id}/details` |
| **Controller** | `App\Http\Controllers\Api\Kantor\LaporanPerjalananDinasApiController` |
| **Method** | `storeDetail()` |
| **FormRequest** | `App\Http\Requests\Kantor\Laperdin\StoreLaperdinDetailRequest` |

## Path Parameters

| Parameter | Description |
|-----------|-------------|
| `id` | Path parameter |

## Body Parameters

| Parameter | Required | Type | Rules |
|-----------|----------|------|-------|
| `tanggal` | **Yes** | `date` | required, date |
| `uraian_lhp` | **Yes** | `string` | required, string |
| `kendala` | No | `string` | nullable, string |
| `solusi` | No | `string` | nullable, string |

### Required Fields Summary

```
tanggal (date)
uraian_lhp (string)
```

### Example Request Body

```json
{
    "tanggal": "2025-01-01",
    "uraian_lhp": "string",
    "kendala": "string",
    "solusi": "string"
}
```

---
*Generated at: 2026-04-18 20:35:30*
