# `PUT` `/api/kantor/laporan-perjalanan-dinas/{id}/details/{detailId}`

## Endpoint

| Property | Value |
|----------|-------|
| **HTTP Method** | `PUT` |
| **URL** | `/api/kantor/laporan-perjalanan-dinas/{id}/details/{detailId}` |
| **Controller** | `App\Http\Controllers\Api\Kantor\LaporanPerjalananDinasApiController` |
| **Method** | `updateDetail()` |
| **FormRequest** | `App\Http\Requests\Kantor\Laperdin\UpdateLaperdinDetailRequest` |

## Path Parameters

| Parameter | Description |
|-----------|-------------|
| `id` | Path parameter |
| `detailId` | Path parameter |

## Body Parameters

| Parameter | Required | Type | Rules |
|-----------|----------|------|-------|
| `tanggal` | No | `date` | sometimes, date |
| `uraian_lhp` | No | `string` | sometimes, string |
| `kendala` | No | `string` | nullable, string |
| `solusi` | No | `string` | nullable, string |

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
