# GET `/kantor/kegiatan/monitoring/monitoring-kegiatan-config`

## Endpoint Info

| Property | Value |
|----------|-------|
| **Method** | `GET` |
| **URL** | `/kantor/kegiatan/monitoring/monitoring-kegiatan-config` |
| **Description** | Monitoring kegiatan config list |
| **HTTP Status** | `200` |
| **Response Time** | 0.029s |
| **Generated** | 2026-04-18 20:51:06 |

## Response

```json
{
    "success": true,
    "message": "Data retrieved successfully",
    "data": [
        {
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
            "updated_at": "2026-01-02T04:00:48.000000Z",
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
            }
        },
        {
            "id": 5,
            "fungsi": "Produksi",
            "kegiatan_id": "68",
            "detil_configurations": [
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
                    "updated_at": "2026-01-02T04:00:48.000000Z",
                    "pivot": {
                        "monitoring_kegiatan_config_id": 5,
                        "detil_configuration_id": 2
                    }
                }
            ],
            "created_at": "2026-01-02T04:00:48.000000Z",
            "updated_at": "2026-01-02T04:00:48.000000Z",
            "kegiatan": {
                "id": 68,
                "tahun": "2025",
                "fungsi": "Produksi",
                "kode_kelompok_kegiatan": null,
                "kode_kegiatan": "2904.521213",
                "nama": "Pendataan Lapangan Survei Perusahaan Pertambangan Migas",
                "tgl_mulai": "2025-01-01",
                "tgl_selesai": "2025-12-31",
                "jenis_kegiatan": "PENGUMPULAN DATA",
                "jml_ptgs": 1,
                "volume": 1,
                "satuan": "Dok",
                "rate_pcl": 98000,
                "rate_pml": null,
                "rate_entri": null,
                "status": "aktif",
                "created_at": "2025-01-01T12:18:54.000000Z",
                "updated_at": null
            }
        },
        {
            "id": 6,
            "fungsi": "Distribusi",
            "kegiatan_id": "33",
            "detil_configurations": [
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
                    "updated_at": "2026-01-02T09:07:09.000000Z",
                    "pivot": {
                        "monitoring_kegiatan_config_id": 6,
                        "detil_configuration_id": 3
                    }
                }
            ],
            "created_at": "2026-01-02T04:00:48.000000Z",
            "updated_at": "2026-01-02T04:00:48.000000Z",
            "kegiatan": {
                "id": 33,
                "tahun": "2025",
                "fungsi": "Distribusi",
                "kode_kelompok_kegiatan": null,
                "kode_kegiatan": "2903.521213",
                "nama": "Pendataan Lapangan Survei Harga Perdagangan Besar",
                "tgl_mulai": "2025-01-01",
                "tgl_selesai": "2025-12-31",
                "jenis_kegiatan": "PENGUMPULAN DATA",
                "jml_ptgs": 1,
                "volume": 108,
                "satuan": "Dok",
                "rate_pcl": 52000,
                "rate_pml": null,
                "rate_entri": null,
                "status": "aktif",
                "created_at": "2025-01-01T12:18:54.000000Z",
                "updated_at": null
            }
        },
        {
            "id": 7,
            "fungsi": "Nerwilis",
            "kegiatan_id": "12",
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
                        "monitoring_kegiatan_config_id": 7,
                        "detil_configuration_id": 1
                    }
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
                    "updated_at": "2026-01-02T09:07:09.000000Z",
                    "pivot": {
                        "monitoring_kegiatan_config_id": 7,
                        "detil_configuration_id": 3
                    }
                }
            ],
            "created_at": "2026-01-02T04:00:48.000000Z",
            "updated_at": "2026-01-02T09:04:45.000000Z",
            "kegiatan": {
                "id": 12,
                "tahun": "2025",
                "fungsi": "Nerwilis",
                "kode_kelompok_kegiatan": null,
                "kode_kegiatan": "2899.521213",
                "nama": "Pendataan Lapangan Survei Sktnp Barang",
                "tgl_mulai": "2025-01-01",
                "tgl_selesai": "2025-12-31",
                "jenis_kegiatan": "PENGUMPULAN DATA",
                "jml_ptgs": 1,
                "volume": 80,
                "satuan": "Dok",
                "rate_pcl": 71000,
                "rate_pml": null,
                "rate_entri": null,
                "status": "aktif",
                "created_at": "2025-01-01T12:18:54.000000Z",
                "updated_at": "2025-04-28T05:00:48.000000Z"
            }
        },
        {
            "id": 9,
            "fungsi": "IPDS",
            "kegiatan_id": "1169",
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
                        "monitoring_kegiatan_config_id": 9,
                        "detil_configuration_id": 1
                    }
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
                    "updated_at": "2026-01-02T09:07:09.000000Z",
                    "pivot": {
                        "monitoring_kegiatan_config_id": 9,
                        "detil_configuration_id": 3
                    }
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
                    "updated_at": "2026-01-02T04:00:48.000000Z",
                    "pivot": {
                        "monitoring_kegiatan_config_id": 9,
                        "detil_configuration_id": 5
                    }
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
                    "updated_at": "2026-01-02T04:00:48.000000Z",
                    "pivot": {
                        "monitoring_kegiatan_config_id": 9,
                        "detil_configuration_id": 7
                    }
                }
            ],
            "created_at": "2026-02-09T08:47:53.000000Z",
            "updated_at": "2026-02-09T08:47:53.000000Z",
            "kegiatan": {
                "id": 1169,
                "tahun": "2026",
                "fungsi": "IPDS",
                "kode_kelompok_kegiatan": null,
                "kode_kegiatan": "2906.521213",
                "nama": "Pengolahan Updating Listing (Susenas Maret)",
                "tgl_mulai": "2026-01-01",
                "tgl_selesai": "2026-01-01",
                "jenis_kegiatan": "PENGOLAHAN",
                "jml_ptgs": 1,
                "volume": 102,
                "satuan": "Dok",
                "rate_pcl": 0,
                "rate_pml": 0,
                "rate_entri": 37000,
                "status": "aktif",
                "created_at": "2026-01-01T12:18:54.000000Z",
                "updated_at": "2026-02-02T02:03:32.000000Z"
            }
        },
        {
            "id": 10,
            "fungsi": "Sosial",
            "kegiatan_id": "1170",
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
                        "monitoring_kegiatan_config_id": 10,
                        "detil_configuration_id": 1
                    }
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
                    "updated_at": "2026-01-02T09:07:09.000000Z",
                    "pivot": {
                        "monitoring_kegiatan_config_id": 10,
                        "detil_configuration_id": 3
                    }
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
                    "updated_at": "2026-01-02T04:00:48.000000Z",
                    "pivot": {
                        "monitoring_kegiatan_config_id": 10,
                        "detil_configuration_id": 5
                    }
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
                    "updated_at": "2026-01-02T04:00:48.000000Z",
                    "pivot": {
                        "monitoring_kegiatan_config_id": 10,
                        "detil_configuration_id": 6
                    }
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
                    "updated_at": "2026-01-02T09:08:30.000000Z",
                    "pivot": {
                        "monitoring_kegiatan_config_id": 10,
                        "detil_configuration_id": 8
                    }
                }
            ],
            "created_at": "2026-02-09T10:03:14.000000Z",
            "updated_at": "2026-02-09T10:04:30.000000Z",
            "kegiatan": {
                "id": 1170,
                "tahun": "2026",
                "fungsi": "Sosial",
                "kode_kelompok_kegiatan": null,
                "kode_kegiatan": "2906.521213",
                "nama": "Pendataan Lapangan Survei Sosial Ekonomi Nasional (Susenas Maret)",
                "tgl_mulai": "2026-01-01",
                "tgl_selesai": "2026-01-01",
                "jenis_kegiatan": "PENGUMPULAN DATA",
                "jml_ptgs": 1,
                "volume": 1020,
                "satuan": "Ruta",
                "rate_pcl": 138000,
                "rate_pml": 0,
                "rate_entri": 0,
                "status": "aktif",
                "created_at": "2026-01-01T12:18:54.000000Z",
                "updated_at": "2026-01-22T05:32:29.000000Z"
            }
        }
    ],
    "meta": {
        "per_page": 10,
        "has_more": false,
        "count": 6
    },
    "links": {
        "next_cursor": null,
        "next_page_url": null,
        "prev_cursor": null,
        "prev_page_url": null,
        "path": "http://127.0.0.1:9001/api/kantor/kegiatan/monitoring/monitoring-kegiatan-config"
    },
    "per_page": 10,
    "pagination_info": {
        "total_page": 1,
        "total_records": 6
    }
}
```

---
*Generated at: 2026-04-18 20:51:06*
