# `PUT` `/api/kantor/spk/bulk-update`

## Endpoint

| Property | Value |
|----------|-------|
| **HTTP Method** | `PUT` |
| **URL** | `/api/kantor/spk/bulk-update` |
| **Controller** | `App\Http\Controllers\Api\Kantor\SpkApiController` |
| **Method** | `bulkUpdateSpk()` |
| **FormRequest** | `App\Http\Requests\Kantor\Spk\BulkUpdateSpkRequest` |

## Body Parameters

| Parameter | Required | Type | Rules |
|-----------|----------|------|-------|
| `mitra_ids` | **Yes** | `array` | required, array |
| `mitra_ids.*` | No | `foreign_key` | integer, exists:mitra_kepka,id |
| `tgl_sk` | **Yes** | `date` | required, date |
| `bln_bayar` | **Yes** | `string` | required, date_format:Y-m |

### Required Fields Summary

```
mitra_ids (array)
tgl_sk (date)
bln_bayar (string)
```

### Example Request Body

```json
{
    "mitra_ids": [],
    "mitra_ids.*": 1,
    "tgl_sk": "2025-01-01",
    "bln_bayar": "string"
}
```

---
*Generated at: 2026-04-18 20:35:30*
