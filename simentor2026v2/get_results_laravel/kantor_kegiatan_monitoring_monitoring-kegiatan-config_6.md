# GET /api/kantor/kegiatan/monitoring/monitoring-kegiatan-config/6

- **Endpoint:** `GET /api/kantor/kegiatan/monitoring/monitoring-kegiatan-config/6`
- **HTTP Status:** 200
- **Token Used:** `297|h4dulrfFT8G6WHuqGRO1iILMWs1VdEHc0TgE7bjqfcfc3110`

## Response

```json
{
    "success": true,
    "message": "Data retrieved successfully",
    "data": {
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
    }
}
```
