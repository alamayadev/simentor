# GET `/kantor/kegiatan/monitoring/detil-configurations/1`

## Endpoint Info

| Property | Value |
|----------|-------|
| **Method** | `GET` |
| **URL** | `/kantor/kegiatan/monitoring/detil-configurations/1` |
| **Description** | Detil config detail |
| **HTTP Status** | `200` |
| **Response Time** | 0.022s |
| **Generated** | 2026-04-18 20:51:06 |

## Response

```json
{
    "success": true,
    "message": "Data retrieved successfully",
    "data": {
        "id": 1,
        "name": "Petugas",
        "related_table": "penugasan",
        "foreign_key": "kegiatan_id",
        "field": {
            "name": "mitra",
            "label": "Petugas",
            "source": "database",
            "required": true
        },
        "is_active": 1,
        "created_at": "2026-01-02T04:00:48.000000Z",
        "updated_at": "2026-01-02T04:00:48.000000Z"
    }
}
```

---
*Generated at: 2026-04-18 20:51:06*
