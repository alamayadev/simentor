# `PATCH` `/api/ipds/asset-it-maintenance-schedule/{asset_it_maintenance_schedule}`

## Endpoint

| Property | Value |
|----------|-------|
| **HTTP Method** | `PATCH` |
| **URL** | `/api/ipds/asset-it-maintenance-schedule/{asset_it_maintenance_schedule}` |
| **Controller** | `App\Http\Controllers\Api\Ipds\AssetITMaintenanceScheduleApiController` |
| **Method** | `update()` |

## Path Parameters

| Parameter | Description |
|-----------|-------------|
| `asset_it_maintenance_schedule` | Path parameter |

## Body Parameters

| Parameter | Required | Type | Rules |
|-----------|----------|------|-------|
| `asset_id` | **Yes** | `integer` | sometimes, required, integer, exists:asset_it,id |
| `next_maintenance` | **Yes** | `date` | sometimes, required, date |
| `responsible_team` | **Yes** | `string` | sometimes, required, string, max:255 |

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
