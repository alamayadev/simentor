# GET `/kantor/kegiatan/monitoring?per_page=15&page=1`

## Endpoint Info

| Property | Value |
|----------|-------|
| **Method** | `GET` |
| **URL** | `/kantor/kegiatan/monitoring?per_page=15&page=1` |
| **Description** | Monitoring kegiatan list |
| **Query Params Used** | `per_page=15&page=1` |
| **HTTP Status** | `200` |
| **Response Time** | 0.032s |
| **Generated** | 2026-04-18 20:51:06 |

## Response

```json
{
    "success": true,
    "message": "Monitoring kegiatan retrieved successfully",
    "data": [
        {
            "id": 1,
            "fungsi": "Sosial",
            "kegiatan_id": 88,
            "kec_id": "3215112",
            "desa_id": "3215112001",
            "kode_sampel": "SMP-001",
            "monitoring_kegiatan_config_id": 4,
            "created_at": "2026-01-02T04:00:48.000000Z",
            "updated_at": "2026-01-02T04:00:48.000000Z",
            "kegiatan": "Pendataan Lapangan Updating Listing Bs (Sakernas Februari)",
            "nmkec": "KARAWANG TIMUR",
            "nmdesa": "ADIARSA TIMUR",
            "mitra": "Santosa"
        },
        {
            "id": 2,
            "fungsi": "Produksi",
            "kegiatan_id": 68,
            "kec_id": "3215112",
            "desa_id": "3215112001",
            "kode_sampel": "SMP-002",
            "monitoring_kegiatan_config_id": 5,
            "created_at": "2026-01-02T04:00:48.000000Z",
            "updated_at": "2026-01-02T04:00:48.000000Z",
            "kegiatan": "Pendataan Lapangan Survei Perusahaan Pertambangan Migas",
            "nmkec": "KARAWANG TIMUR",
            "nmdesa": "ADIARSA TIMUR",
            "status_pelaksanaan": "sedang berjalan"
        },
        {
            "id": 3,
            "fungsi": "Distribusi",
            "kegiatan_id": 33,
            "kec_id": "3215113",
            "desa_id": "3215113008",
            "kode_sampel": "SMP-003",
            "monitoring_kegiatan_config_id": 6,
            "created_at": "2026-01-02T04:00:48.000000Z",
            "updated_at": "2026-01-02T09:08:51.000000Z",
            "kegiatan": "Pendataan Lapangan Survei Harga Perdagangan Besar",
            "nmkec": "KARAWANG BARAT",
            "nmdesa": "TUNGGAKJATI",
            "tanggal_survei": "2025-12-24"
        },
        {
            "id": 4,
            "fungsi": "Nerwilis",
            "kegiatan_id": 12,
            "kec_id": "3215090",
            "desa_id": "3215090001",
            "kode_sampel": "SMP-004",
            "monitoring_kegiatan_config_id": 7,
            "created_at": "2026-01-02T04:00:48.000000Z",
            "updated_at": "2026-01-02T04:00:48.000000Z",
            "kegiatan": "Pendataan Lapangan Survei Sktnp Barang",
            "nmkec": "LEMAHABANG",
            "nmdesa": "CIWARINGIN",
            "mitra": "Amrini",
            "status_pelaksanaan": "selesai"
        }
    ],
    "meta": {
        "per_page": 15,
        "has_more": false,
        "count": 4
    },
    "links": {
        "next_cursor": null,
        "next_page_url": null,
        "prev_cursor": null,
        "prev_page_url": null,
        "path": "http://127.0.0.1:9001/api/kantor/kegiatan/monitoring"
    },
    "per_page": 15
}
```

---
*Generated at: 2026-04-18 20:51:06*
