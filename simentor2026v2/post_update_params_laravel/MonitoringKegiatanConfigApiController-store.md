# `POST` `/api/kantor/kegiatan/monitoring/monitoring-kegiatan-config`

## Endpoint

| Property | Value |
|----------|-------|
| **HTTP Method** | `POST` |
| **URL** | `/api/kantor/kegiatan/monitoring/monitoring-kegiatan-config` |
| **Controller** | `App\Http\Controllers\Api\Kantor\MonitoringKegiatanConfigApiController` |
| **Method** | `store()` |

## Body Parameters

| Parameter | Required | Type | Rules |
|-----------|----------|------|-------|
| `fungsi` | **Yes** | `string` | required, string |
| `kegiatan_id` | **Yes** | `string` | required, string |
| `detil_configurations` | No | `array` | nullable, array |

### Required Fields Summary

```
fungsi (string)
kegiatan_id (string)
```

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
