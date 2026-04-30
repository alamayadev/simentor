# GET `/kantor/kegiatan/monitoring/detil-configurations`

## Endpoint Info

| Property | Value |
|----------|-------|
| **Method** | `GET` |
| **URL** | `/kantor/kegiatan/monitoring/detil-configurations` |
| **Description** | Detil config list |
| **HTTP Status** | `200` |
| **Response Time** | 0.026s |
| **Generated** | 2026-04-18 20:51:06 |

## Response

```json
{
    "success": true,
    "message": "Data retrieved successfully",
    "data": [
        {
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
        },
        {
            "id": 2,
            "name": "Status Pelaksanaan",
            "related_table": null,
            "foreign_key": null,
            "field": {
                "name": "status_pelaksanaan",
                "type": "enum",
                "label": "Status Pelaksanaan",
                "source": "custom",
                "options": [
                    "belum_dimulai",
                    "sedang_berjalan",
                    "selesai",
                    "ditunda"
                ],
                "required": true
            },
            "is_active": 1,
            "created_at": "2026-01-02T04:00:48.000000Z",
            "updated_at": "2026-01-02T04:00:48.000000Z"
        },
        {
            "id": 3,
            "name": "Tanggal Survei",
            "related_table": null,
            "foreign_key": null,
            "field": {
                "name": "tanggal_survei",
                "type": "date",
                "label": "Tanggal Survei",
                "source": "custom",
                "options": [],
                "required": true
            },
            "is_active": 1,
            "created_at": "2026-01-02T04:00:48.000000Z",
            "updated_at": "2026-01-02T09:07:09.000000Z"
        },
        {
            "id": 4,
            "name": "Blok Sensus",
            "related_table": "sls2025",
            "foreign_key": "desa_id",
            "field": {
                "name": "blok_sensus",
                "label": "Blok Sensus",
                "source": "database",
                "required": true
            },
            "is_active": 1,
            "created_at": "2026-01-02T04:00:48.000000Z",
            "updated_at": "2026-01-02T04:00:48.000000Z"
        },
        {
            "id": 5,
            "name": "SLS",
            "related_table": "sls2025",
            "foreign_key": "desa_id",
            "field": {
                "name": "sls",
                "label": "SLS",
                "source": "database",
                "required": false
            },
            "is_active": 1,
            "created_at": "2026-01-02T04:00:48.000000Z",
            "updated_at": "2026-01-02T04:00:48.000000Z"
        },
        {
            "id": 6,
            "name": "Pengawas",
            "related_table": "penugasan",
            "foreign_key": "kegiatan_id",
            "field": {
                "name": "pml",
                "label": "Pengawas",
                "source": "database",
                "required": true
            },
            "is_active": 1,
            "created_at": "2026-01-02T04:00:48.000000Z",
            "updated_at": "2026-01-02T04:00:48.000000Z"
        },
        {
            "id": 7,
            "name": "Supervisor",
            "related_table": "supervisor",
            "foreign_key": "kegiatan_id",
            "field": {
                "name": "supervisor",
                "label": "Supervisor",
                "source": "database",
                "required": true
            },
            "is_active": 1,
            "created_at": "2026-01-02T04:00:48.000000Z",
            "updated_at": "2026-01-02T04:00:48.000000Z"
        },
        {
            "id": 8,
            "name": "Target",
            "related_table": null,
            "foreign_key": null,
            "field": {
                "name": "target",
                "type": "number",
                "label": "Target",
                "source": "custom",
                "options": [],
                "required": true
            },
            "is_active": 1,
            "created_at": "2026-01-02T09:08:30.000000Z",
            "updated_at": "2026-01-02T09:08:30.000000Z"
        }
    ]
}
```

---
*Generated at: 2026-04-18 20:51:06*
