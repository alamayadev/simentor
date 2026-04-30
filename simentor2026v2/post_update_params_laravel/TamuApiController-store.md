# `POST` `/api/kantor/tamu`

## Endpoint

| Property | Value |
|----------|-------|
| **HTTP Method** | `POST` |
| **URL** | `/api/kantor/tamu` |
| **Controller** | `App\Http\Controllers\Api\Kantor\TamuApiController` |
| **Method** | `store()` |

## Body Parameters

| Parameter | Required | Type | Rules |
|-----------|----------|------|-------|
| `nama` | **Yes** | `string` | required, string, max:255 |
| `email` | **Yes** | `string` | required, email, max:255 |
| `no_hp` | **Yes** | `string` | required, string, max:20 |
| `asal_instansi` | **Yes** | `string` | required, string, max:255 |
| `tgl_kunjungan` | **Yes** | `date` | required, date |
| `tujuan_kunjungan` | **Yes** | `string` | required, string, max:255 |
| `jenis_layanan` | **Yes** | `string` | required, string, max:255 |
| `detil_layanan` | **Yes** | `string` | required, string |

### Required Fields Summary

```
nama (string)
email (string)
no_hp (string)
asal_instansi (string)
tgl_kunjungan (date)
tujuan_kunjungan (string)
jenis_layanan (string)
detil_layanan (string)
```

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
