# `PUT` `/api/kantor/pengaduan/{id}`

## Endpoint

| Property | Value |
|----------|-------|
| **HTTP Method** | `PUT` |
| **URL** | `/api/kantor/pengaduan/{id}` |
| **Controller** | `App\Http\Controllers\Api\Kantor\PengaduanApiController` |
| **Method** | `update()` |

## Path Parameters

| Parameter | Description |
|-----------|-------------|
| `id` | Path parameter |

## Body Parameters

| Parameter | Required | Type | Rules |
|-----------|----------|------|-------|
| `jenis_pelangaran` | No | `string` | sometimes, string, max:255 |
| `lainnya` | No | `string` | sometimes, string, max:255 |
| `pelaku` | No | `string` | sometimes, string, max:255 |
| `waktu_kejadian` | No | `date` | sometimes, date |
| `kronologi` | No | `string` | sometimes, string |
| `bukti` | No | `string` | sometimes, string, max:255 |

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
