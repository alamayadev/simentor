# GET /api/kantor/spk/monitoring?type=tanpa_spk

- **Endpoint:** `GET /api/kantor/spk/monitoring?type=tanpa_spk`
- **HTTP Status:** 200
- **Token Used:** `297|h4dulrfFT8G6WHuqGRO1iILMWs1VdEHc0TgE7bjqfcfc3110`

## Response

```json
{
    "success": true,
    "message": "Monitoring data retrieved",
    "data": [
        {
            "mitra_id": 1710,
            "bln_bayar": "2025-03-01",
            "no_sk": null,
            "tgl_sk": null,
            "no_bast": null,
            "tgl_bast": null,
            "total": "1500000",
            "mitra": null
        }
    ],
    "meta": {
        "per_page": 10,
        "has_more": false,
        "count": 1
    },
    "links": {
        "next_cursor": null,
        "next_page_url": null,
        "prev_cursor": null,
        "prev_page_url": null,
        "path": "http:\/\/127.0.0.1:9001\/api\/kantor\/spk\/monitoring"
    },
    "per_page": 10,
    "listbln": [
        "2026-03-01",
        "2026-02-01",
        "2025-12-01",
        "2025-11-01",
        "2025-10-01",
        "2025-09-01",
        "2025-08-01",
        "2025-07-01",
        "2025-06-01",
        "2025-05-01",
        "2025-04-01",
        "2025-03-01",
        "2025-02-01",
        "2023-03-01"
    ],
    "pagination_info": {
        "total_page": 1,
        "total_records": 1
    }
}
```
