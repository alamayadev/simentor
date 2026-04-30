# GET `/kantor/kegiatan/filter-list`

## Endpoint Info

| Property | Value |
|----------|-------|
| **Method** | `GET` |
| **URL** | `/kantor/kegiatan/filter-list` |
| **Description** | Filter list |
| **HTTP Status** | `200` |
| **Response Time** | 0.028s |
| **Generated** | 2026-04-18 20:51:07 |

## Response

```json
{
    "success": true,
    "message": "Filters retrieved successfully",
    "data": {
        "fungsiList": [
            null,
            "Distribusi",
            "IPDS",
            "Nerwilis",
            "Produksi",
            "Sosial"
        ],
        "tahunList": [
            "2025",
            "2026"
        ],
        "jenisList": [
            null,
            "PENGOLAHAN",
            "PENGUMPULAN DATA",
            "PERSIAPAN"
        ],
        "statusList": [
            "aktif",
            "tidak dicairkan",
            "dibatalkan"
        ]
    }
}
```

---
*Generated at: 2026-04-18 20:51:07*
