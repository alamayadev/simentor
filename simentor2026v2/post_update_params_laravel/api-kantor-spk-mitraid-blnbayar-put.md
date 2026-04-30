# `PUT` `/api/kantor/spk/{mitra_id}/{bln_bayar}`

## Endpoint

| Property | Value |
|----------|-------|
| **HTTP Method** | `PUT` |
| **URL** | `/api/kantor/spk/{mitra_id}/{bln_bayar}` |
| **Controller** | `App\Http\Controllers\Api\Kantor\SpkApiController` |
| **Method** | `update()` |
| **FormRequest** | `App\Http\Requests\Kantor\Spk\UpdateSpkRequest` |

## Path Parameters

| Parameter | Description |
|-----------|-------------|
| `mitra_id` | Path parameter |
| `bln_bayar` | Path parameter |

## Body Parameters

| Parameter | Required | Type | Rules |
|-----------|----------|------|-------|
| `no_sk` | No | `string` | sometimes, nullable, string, max:255 |
| `tgl_sk` | No | `string` | sometimes, nullable, date_format:Y-m-d |
| `no_bast` | No | `string` | sometimes, nullable, string, max:255 |
| `tgl_bast` | No | `string` | sometimes, nullable, date_format:Y-m-d |

### Example Request Body

```json
{
    "no_sk": "string",
    "tgl_sk": "string",
    "no_bast": "string",
    "tgl_bast": "string"
}
```

---
*Generated at: 2026-04-18 20:35:30*
