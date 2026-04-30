# `PUT` `/api/kantor/kegiatan/monitoring/detil-configurations/{id}`

## Endpoint

| Property | Value |
|----------|-------|
| **HTTP Method** | `PUT` |
| **URL** | `/api/kantor/kegiatan/monitoring/detil-configurations/{id}` |
| **Controller** | `App\Http\Controllers\Api\Kantor\DetilConfigurationApiController` |
| **Method** | `update()` |

## Path Parameters

| Parameter | Description |
|-----------|-------------|
| `id` | Path parameter |

## Body Parameters

| Parameter | Required | Type | Rules |
|-----------|----------|------|-------|
| `name` | No | `string` | string |
| `related_table` | No | `string` | nullable, string |
| `foreign_key` | No | `enum` | nullable, string, in:kegiatan_id,kec_id,desa_id |
| `field` | No | `array` | array |
| `is_active` | No | `boolean` | boolean |

### Example Request Body

```json
{
    "name": "string",
    "related_table": "string",
    "foreign_key": "kegiatan_id",
    "field": [],
    "is_active": true
}
```

---
*Generated at: 2026-04-18 20:35:30*
