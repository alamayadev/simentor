# `POST` `/api/kantor/pengaduan`

## Endpoint

| Property | Value |
|----------|-------|
| **HTTP Method** | `POST` |
| **URL** | `/api/kantor/pengaduan` |
| **Controller** | `App\Http\Controllers\Api\Kantor\PengaduanApiController` |
| **Method** | `store()` |

## Body Parameters

| Parameter | Required | Type | Rules |
|-----------|----------|------|-------|
| `jenis_pelangaran` | **Yes** | `string` | required, string, max:255 |
| `lainnya` | **Yes** | `string` | required, string, max:255 |
| `pelaku` | **Yes** | `string` | required, string, max:255 |
| `waktu_kejadian` | **Yes** | `date` | required, date |
| `kronologi` | **Yes** | `string` | required, string |
| `bukti` | **Yes** | `string` | required, string, max:255 |

### Required Fields Summary

```
jenis_pelangaran (string)
lainnya (string)
pelaku (string)
waktu_kejadian (date)
kronologi (string)
bukti (string)
```

### Example Request Body

```json
{
    "jenis_pelangaran": "string",
    "lainnya": "string",
    "pelaku": "string",
    "waktu_kejadian": "2025-01-01",
    "kronologi": "string",
    "bukti": "string"
}
```

---
*Generated at: 2026-04-18 20:35:30*
