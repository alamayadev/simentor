# `POST` `/api/kantor/kegiatan/monitoring/detil-configurations`

## Endpoint

| Property | Value |
|----------|-------|
| **HTTP Method** | `POST` |
| **URL** | `/api/kantor/kegiatan/monitoring/detil-configurations` |
| **Controller** | `App\Http\Controllers\Api\Kantor\DetilConfigurationApiController` |
| **Method** | `storeConfiguration()` |

## Body Parameters

| Parameter | Required | Type | Rules |
|-----------|----------|------|-------|
| `name` | **Yes** | `string` | required, string |
| `related_table` | No | `string` | nullable, string |
| `foreign_key` | No | `enum` | nullable, string, in:kegiatan_id,kec_id,desa_id |
| `field` | **Yes** | `array` | required, array |
| `is_active` | No | `boolean` | boolean |

### Required Fields Summary

```
name (string)
field (array)
```

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
