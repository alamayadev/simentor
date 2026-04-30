# GET `/ipds/asset-it-maintenance-schedule?per_page=15&page=1`

## Endpoint Info

| Property | Value |
|----------|-------|
| **Method** | `GET` |
| **URL** | `/ipds/asset-it-maintenance-schedule?per_page=15&page=1` |
| **Description** | Maintenance schedule list |
| **Query Params Used** | `per_page=15&page=1` |
| **HTTP Status** | `200` |
| **Response Time** | 0.026s |
| **Generated** | 2026-04-18 20:51:09 |

## Response

```json
{
    "success": true,
    "message": "Data retrieved successfully",
    "data": [
        {
            "id": 1,
            "asset_id": 2,
            "next_maintenance": "2026-02-01",
            "responsible_team": "Infrastructure Team",
            "created_at": "2026-01-02T04:00:48.000000Z",
            "updated_at": "2026-01-02T04:00:48.000000Z",
            "asset": {
                "id": 2,
                "kode_asset": "AS-002",
                "type": "hardware",
                "category": "Laptop",
                "brand": "Lenovo",
                "model": "ThinkPad X1 Carbon",
                "serial_number": "SN987654321",
                "name": null,
                "license_key": null,
                "device": null,
                "ip_address": null,
                "location": "Head Office",
                "status": "in-use",
                "assigned_to": "Budi",
                "purchase_date": "2024-03-10",
                "warranty_expiry": "2027-03-10",
                "expiry_date": null,
                "delivery_date": "2024-03-05",
                "created_at": "2026-01-02T04:00:48.000000Z",
                "updated_at": "2026-01-02T04:00:48.000000Z"
            }
        },
        {
            "id": 2,
            "asset_id": 5,
            "next_maintenance": "2026-01-15",
            "responsible_team": "Network Team",
            "created_at": "2026-01-02T04:00:48.000000Z",
            "updated_at": "2026-01-02T04:00:48.000000Z",
            "asset": {
                "id": 5,
                "kode_asset": "AS-005",
                "type": "network",
                "category": "Router",
                "brand": "Cisco",
                "model": "ISR 4451",
                "serial_number": null,
                "name": null,
                "license_key": null,
                "device": "Router",
                "ip_address": "192.168.1.1",
                "location": "Data Center Jakarta",
                "status": "active",
                "assigned_to": null,
                "purchase_date": null,
                "warranty_expiry": null,
                "expiry_date": null,
                "delivery_date": "2023-12-01",
                "created_at": "2026-01-02T04:00:48.000000Z",
                "updated_at": "2026-01-02T04:00:48.000000Z"
            }
        }
    ],
    "meta": {
        "per_page": 15,
        "has_more": false,
        "count": 2
    },
    "links": {
        "next_cursor": null,
        "next_page_url": null,
        "prev_cursor": null,
        "prev_page_url": null,
        "path": "http://127.0.0.1:9001/api/ipds/asset-it-maintenance-schedule"
    },
    "per_page": 15,
    "pagination_info": {
        "total_page": 1,
        "total_records": 2
    }
}
```

---
*Generated at: 2026-04-18 20:51:09*
