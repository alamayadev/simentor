# `POST` `/api/kantor/kegiatan/monitoring`

## Endpoint

| Property | Value |
|----------|-------|
| **HTTP Method** | `POST` |
| **URL** | `/api/kantor/kegiatan/monitoring` |
| **Controller** | `App\Http\Controllers\Api\Kantor\MonitoringKegiatanApiController` |
| **Method** | `store()` |
| **FormRequest** | `App\Http\Requests\Kantor\MonitoringKegiatan\StoreMonitoringRequest` |

## Body Parameters

| Parameter | Required | Type | Rules |
|-----------|----------|------|-------|
| `fungsi` | **Yes** | `string` | required, string, max:255 |
| `kegiatan_id` | **Yes** | `foreign_key` | required, integer, exists:kegiatan,id |
| `kec_id` | **Yes** | `string` | required, string, max:255 |
| `desa_id` | **Yes** | `string` | required, string, max:255 |
| `kode_sampel` | **Yes** | `string` | required, string, max:255 |
| `monitoring_kegiatan_config_id` | **Yes** | `foreign_key` | required, exists:monitoring_kegiatan_config,id |
| `detil_configurations` | No | `array` | nullable, array |
| `detil_configurations.*` | No | `foreign_key` | exists:detil_configurations,id |
| `detil_data` | No | `array` | nullable, array |

### Required Fields Summary

```
fungsi (string)
kegiatan_id (foreign_key)
kec_id (string)
desa_id (string)
kode_sampel (string)
monitoring_kegiatan_config_id (foreign_key)
```

### Example Request Body

```json
{
    "fungsi": "string",
    "kegiatan_id": 1,
    "kec_id": 1,
    "desa_id": 1,
    "kode_sampel": "string",
    "monitoring_kegiatan_config_id": 1,
    "detil_configurations": [],
    "detil_configurations.*": 1,
    "detil_data": []
}
```

---
*Generated at: 2026-04-18 20:35:30*
