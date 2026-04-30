# `PUT` `/api/kantor/tamu/{id}`

## Endpoint

| Property | Value |
|----------|-------|
| **HTTP Method** | `PUT` |
| **URL** | `/api/kantor/tamu/{id}` |
| **Controller** | `App\Http\Controllers\Api\Kantor\TamuApiController` |
| **Method** | `update()` |

## Path Parameters

| Parameter | Description |
|-----------|-------------|
| `id` | Path parameter |

## Body Parameters

| Parameter | Required | Type | Rules |
|-----------|----------|------|-------|
| `nama` | No | `string` | sometimes, string, max:255 |
| `email` | No | `string` | sometimes, email, max:255 |
| `no_hp` | No | `string` | sometimes, string, max:20 |
| `asal_instansi` | No | `string` | sometimes, string, max:255 |
| `tgl_kunjungan` | No | `date` | sometimes, date |
| `tujuan_kunjungan` | No | `string` | sometimes, string, max:255 |
| `jenis_layanan` | No | `string` | sometimes, string, max:255 |
| `detil_layanan` | No | `string` | sometimes, string |

### Example Request Body

```json
{
    "nama": "string",
    "email": "user@example.com",
    "no_hp": "081234567890",
    "asal_instansi": "string",
    "tgl_kunjungan": "2025-01-01",
    "tujuan_kunjungan": "string",
    "jenis_layanan": "string",
    "detil_layanan": "string"
}
```

---
*Generated at: 2026-04-18 20:35:30*
