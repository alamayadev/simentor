# GET `/ipds/assets?per_page=15&page=1`

## Endpoint Info

| Property | Value |
|----------|-------|
| **Method** | `GET` |
| **URL** | `/ipds/assets?per_page=15&page=1` |
| **Description** | Assets list |
| **Query Params Used** | `per_page=15&page=1` |
| **HTTP Status** | `200` |
| **Response Time** | 0.033s |
| **Generated** | 2026-04-18 20:51:09 |

## Response

```json
{
    "success": true,
    "message": "Data retrieved successfully",
    "data": [
        {
            "id": 1,
            "kode_asset": "AS-001",
            "type": "hardware",
            "category": "Server",
            "brand": "Dell",
            "model": "PowerEdge R740",
            "serial_number": "SN123456789",
            "name": null,
            "license_key": null,
            "device": null,
            "ip_address": null,
            "location": "Data Center Jakarta",
            "status": "active",
            "assigned_to": "Infrastructure Team",
            "purchase_date": "2023-01-15",
            "warranty_expiry": "2026-01-15",
            "expiry_date": null,
            "delivery_date": "2023-01-10",
            "created_at": "2026-01-02T04:00:48.000000Z",
            "updated_at": "2026-01-02T04:00:48.000000Z",
            "maintenance_schedules": []
        },
        {
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
            "updated_at": "2026-01-02T04:00:48.000000Z",
            "maintenance_schedules": [
                {
                    "id": 1,
                    "asset_id": 2,
                    "next_maintenance": "2026-02-01",
                    "responsible_team": "Infrastructure Team",
                    "created_at": "2026-01-02T04:00:48.000000Z",
                    "updated_at": "2026-01-02T04:00:48.000000Z"
                }
            ]
        },
        {
            "id": 3,
            "kode_asset": "AS-003",
            "type": "software",
            "category": "Subscription",
            "brand": null,
            "model": null,
            "serial_number": null,
            "name": "Microsoft 365",
            "license_key": "XXXX-YYYY-ZZZZ",
            "device": null,
            "ip_address": null,
            "location": null,
            "status": "active",
            "assigned_to": "All Employees",
            "purchase_date": null,
            "warranty_expiry": null,
            "expiry_date": "2026-12-31",
            "delivery_date": "2024-01-15",
            "created_at": "2026-01-02T04:00:48.000000Z",
            "updated_at": "2026-01-02T04:00:48.000000Z",
            "maintenance_schedules": []
        },
        {
            "id": 4,
            "kode_asset": "AS-004",
            "type": "software",
            "category": "License",
            "brand": null,
            "model": null,
            "serial_number": null,
            "name": "Docker Enterprise",
            "license_key": "DOCKER-1234-5678",
            "device": null,
            "ip_address": null,
            "location": null,
            "status": "active",
            "assigned_to": "DevOps Team",
            "purchase_date": null,
            "warranty_expiry": null,
            "expiry_date": "2025-08-01",
            "delivery_date": "2024-05-20",
            "created_at": "2026-01-02T04:00:48.000000Z",
            "updated_at": "2026-01-02T04:00:48.000000Z",
            "maintenance_schedules": []
        },
        {
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
            "updated_at": "2026-01-02T04:00:48.000000Z",
            "maintenance_schedules": [
                {
                    "id": 2,
                    "asset_id": 5,
                    "next_maintenance": "2026-01-15",
                    "responsible_team": "Network Team",
                    "created_at": "2026-01-02T04:00:48.000000Z",
                    "updated_at": "2026-01-02T04:00:48.000000Z"
                }
            ]
        },
        {
            "id": 6,
            "kode_asset": "AS-006",
            "type": "network",
            "category": "Switch",
            "brand": "HP",
            "model": "Aruba 2930F",
            "serial_number": null,
            "name": null,
            "license_key": null,
            "device": "Switch",
            "ip_address": "192.168.1.10",
            "location": "Head Office",
            "status": "active",
            "assigned_to": null,
            "purchase_date": null,
            "warranty_expiry": null,
            "expiry_date": null,
            "delivery_date": "2024-02-15",
            "created_at": "2026-01-02T04:00:48.000000Z",
            "updated_at": "2026-01-02T04:00:48.000000Z",
            "maintenance_schedules": []
        }
    ],
    "meta": {
        "per_page": 15,
        "has_more": false,
        "count": 6
    },
    "links": {
        "next_cursor": null,
        "next_page_url": null,
        "prev_cursor": null,
        "prev_page_url": null,
        "path": "http://127.0.0.1:9001/api/ipds/assets"
    },
    "per_page": 15,
    "pagination_info": {
        "total_page": 1,
        "total_records": 6
    }
}
```

---
*Generated at: 2026-04-18 20:51:09*
