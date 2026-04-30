# `PUT` `/api/kantor/kegiatan/monitoring/monitoring-kegiatan-config/{monitoring_kegiatan_config}`

## Endpoint

| Property | Value |
|----------|-------|
| **HTTP Method** | `PUT` |
| **URL** | `/api/kantor/kegiatan/monitoring/monitoring-kegiatan-config/{monitoring_kegiatan_config}` |
| **Controller** | `App\Http\Controllers\Api\Kantor\MonitoringKegiatanConfigApiController` |
| **Method** | `update()` |

## Path Parameters

| Parameter | Description |
|-----------|-------------|
| `monitoring_kegiatan_config` | Path parameter |

## Body Parameters

| Parameter | Required | Type | Rules |
|-----------|----------|------|-------|
| `fungsi` | No | `string` | string |
| `kegiatan_id` | No | `string` | string |
| `detil_configurations` | No | `array` | nullable, array |

### Example Request Body

```json
{
    "fungsi": "string",
    "kegiatan_id": 1,
    "detil_configurations": []
}
```

---
*Generated at: 2026-04-18 20:35:30*
