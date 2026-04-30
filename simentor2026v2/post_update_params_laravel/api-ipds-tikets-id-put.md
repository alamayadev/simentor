# `PUT` `/api/ipds/tikets/{id}`

## Endpoint

| Property | Value |
|----------|-------|
| **HTTP Method** | `PUT` |
| **URL** | `/api/ipds/tikets/{id}` |
| **Controller** | `App\Http\Controllers\Api\Ipds\TiketApiController` |
| **Method** | `update()` |
| **FormRequest** | `App\Http\Requests\UpdateTiketRequest` |

## Path Parameters

| Parameter | Description |
|-----------|-------------|
| `id` | Path parameter |

## Body Parameters

| Parameter | Required | Type | Rules |
|-----------|----------|------|-------|
| `status` | No | `string` | nullable, string |
| `keterangan` | No | `string` | nullable, string |

### Example Request Body

```json
{
    "status": "string",
    "keterangan": "string"
}
```

---
*Generated at: 2026-04-18 20:35:30*
