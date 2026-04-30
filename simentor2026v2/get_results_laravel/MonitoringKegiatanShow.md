# GET `/kantor/kegiatan/monitoring/1`

## Endpoint Info

| Property | Value |
|----------|-------|
| **Method** | `GET` |
| **URL** | `/kantor/kegiatan/monitoring/1` |
| **Description** | Monitoring kegiatan detail |
| **HTTP Status** | `200` |
| **Response Time** | 0.027s |
| **Generated** | 2026-04-18 20:51:06 |

## Response

```json
{
    "success": true,
    "message": "Monitoring kegiatan retrieved successfully",
    "data": {
        "id": 1,
        "fungsi": "Sosial",
        "kegiatan_id": 88,
        "kec_id": "3215112",
        "desa_id": "3215112001",
        "monitoring_kegiatan_config_id": 4,
        "kode_sampel": "SMP-001",
        "created_at": "2026-01-02T04:00:48.000000Z",
        "updated_at": "2026-01-02T04:00:48.000000Z",
        "detil_configurations": null,
        "detil_data": {
            "mitra": "Santosa"
        },
        "kegiatan": {
            "id": 88,
            "tahun": "2025",
            "fungsi": "Sosial",
            "kode_kelompok_kegiatan": null,
            "kode_kegiatan": "2905.521213",
            "nama": "Pendataan Lapangan Updating Listing Bs (Sakernas Februari)",
            "tgl_mulai": "2025-01-01",
            "tgl_selesai": "2025-12-31",
            "jenis_kegiatan": "PENGUMPULAN DATA",
            "jml_ptgs": 1,
            "volume": 20,
            "satuan": "BS",
            "rate_pcl": 187000,
            "rate_pml": null,
            "rate_entri": null,
            "status": "aktif",
            "created_at": "2025-01-01T12:18:54.000000Z",
            "updated_at": "2025-02-28T02:59:46.000000Z"
        },
        "kecamatan": {
            "id": "3215112",
            "kdkec": "112",
            "nmkec": "KARAWANG TIMUR",
            "created_at": "2026-01-02T04:01:25.000000Z",
            "updated_at": "2026-01-02T04:01:25.000000Z"
        },
        "desa": {
            "id": "3215112001",
            "kecamatan_id": "3215112",
            "kdkec": "112",
            "kddesa": "001",
            "nmdesa": "ADIARSA TIMUR",
            "created_at": "2026-01-02T04:01:26.000000Z",
            "updated_at": "2026-01-02T04:01:26.000000Z"
        },
        "monitoring_config": {
            "id": 4,
            "fungsi": "Sosial",
            "kegiatan_id": "88",
            "detil_configurations": [
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
                    "updated_at": "2026-01-02T04:00:48.000000Z",
                    "pivot": {
                        "monitoring_kegiatan_config_id": 4,
                        "detil_configuration_id": 1
                    }
                }
            ],
            "created_at": "2026-01-02T04:00:48.000000Z",
            "updated_at": "2026-01-02T04:00:48.000000Z"
        }
    }
}
```

---
*Generated at: 2026-04-18 20:51:06*
