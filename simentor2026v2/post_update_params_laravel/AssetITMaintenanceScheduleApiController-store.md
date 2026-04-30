# `POST` `/api/ipds/asset-it-maintenance-schedule`

## Endpoint

| Property | Value |
|----------|-------|
| **HTTP Method** | `POST` |
| **URL** | `/api/ipds/asset-it-maintenance-schedule` |
| **Controller** | `App\Http\Controllers\Api\Ipds\AssetITMaintenanceScheduleApiController` |
| **Method** | `store()` |

## Body Parameters

| Parameter | Required | Type | Rules |
|-----------|----------|------|-------|
| `asset_id` | **Yes** | `integer` | required, integer, exists:asset_it,id |
| `next_maintenance` | **Yes** | `date` | required, date |
| `responsible_team` | **Yes** | `string` | required, string, max:255 |

### Required Fields Summary

```
asset_id (integer)
next_maintenance (date)
responsible_team (string)
```

### Example Request Body

```json
{
    "asset_id": 1,
    "next_maintenance": "2025-01-01",
    "responsible_team": "string"
}
```

---
*Generated at: 2026-04-18 20:35:30*
