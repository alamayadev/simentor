# GET `/kantor/settings?per_page=15&page=1`

## Endpoint Info

| Property | Value |
|----------|-------|
| **Method** | `GET` |
| **URL** | `/kantor/settings?per_page=15&page=1` |
| **Description** | Settings list |
| **Query Params Used** | `per_page=15&page=1` |
| **HTTP Status** | `200` |
| **Response Time** | 0.025s |
| **Generated** | 2026-04-18 20:50:56 |

## Response

```json
{
    "success": true,
    "message": "Settings retrieved successfully",
    "data": {
        "1": [
            {
                "id": 3,
                "tahun": "2025",
                "key": "NAMA_KANTOR",
                "value": "Badan Pusat Statistik Kabupaten Karawang",
                "grup": 1,
                "created_at": "2023-12-31T17:00:00.000000Z",
                "updated_at": "2026-01-02T03:59:13.000000Z"
            },
            {
                "id": 4,
                "tahun": "2025",
                "key": "ALAMAT_KANTOR",
                "value": "Jl. Cakradireja No 36 Nagasari Karawang",
                "grup": 1,
                "created_at": "2023-12-31T17:00:00.000000Z",
                "updated_at": "2026-01-02T03:59:13.000000Z"
            },
            {
                "id": 6,
                "tahun": "2025",
                "key": "KODE_RING",
                "value": "054.01.GG.",
                "grup": 1,
                "created_at": "2023-12-31T17:00:00.000000Z",
                "updated_at": "2026-01-02T03:59:13.000000Z"
            }
        ],
        "2": [
            {
                "id": 1,
                "tahun": "2025",
                "key": "PPK",
                "value": "Asep Surya, S.ST",
                "grup": 2,
                "created_at": "2023-12-31T17:00:00.000000Z",
                "updated_at": "2026-01-02T03:59:13.000000Z"
            },
            {
                "id": 5,
                "tahun": "2025",
                "key": "KEPALA_KANTOR",
                "value": "Ari Setiadi Gunawan S.H.",
                "grup": 2,
                "created_at": "2023-12-31T17:00:00.000000Z",
                "updated_at": "2026-01-02T03:59:13.000000Z"
            },
            {
                "id": 13,
                "tahun": "2025",
                "key": "NIP_PPK",
                "value": "19690930 198903 1 001",
                "grup": 2,
                "created_at": "2023-12-31T17:00:00.000000Z",
                "updated_at": "2026-01-02T03:59:13.000000Z"
            },
            {
                "id": 16,
                "tahun": "2025",
                "key": "NIP_KEPALA",
                "value": "19710101 199211 1001",
                "grup": 2,
                "created_at": "2023-12-31T17:00:00.000000Z",
                "updated_at": "2026-01-02T03:59:13.000000Z"
            }
        ],
        "3": [
            {
                "id": 8,
                "tahun": "2025",
                "key": "TAHUN_KEGIATAN",
                "value": "2025",
                "grup": 3,
                "created_at": "2023-12-31T17:00:00.000000Z",
                "updated_at": "2026-01-02T03:59:13.000000Z"
            },
            {
                "id": 9,
                "tahun": "2025",
                "key": "TAHUN_SPK",
                "value": "2025",
                "grup": 3,
                "created_at": "2023-12-31T17:00:00.000000Z",
                "updated_at": "2026-01-02T03:59:13.000000Z"
            },
            {
                "id": 10,
                "tahun": "2025",
                "key": "TAHUN_BAST",
                "value": "2025",
                "grup": 3,
                "created_at": "2023-12-31T17:00:00.000000Z",
                "updated_at": "2026-01-02T03:59:13.000000Z"
            },
            {
                "id": 11,
                "tahun": "2025",
                "key": "NILAI_MAX_SPK",
                "value": "4000000",
                "grup": 3,
                "created_at": "2023-12-31T17:00:00.000000Z",
                "updated_at": "2026-01-02T03:59:13.000000Z"
            },
            {
                "id": 19,
                "tahun": "2025",
                "key": "NOMOR_DIPA",
                "value": "SP DIPA-054.01.2.018686/2026",
                "grup": 3,
                "created_at": "2024-12-26T17:00:00.000000Z",
                "updated_at": "2026-01-22T02:03:34.000000Z"
            },
            {
                "id": 20,
                "tahun": "2025",
                "key": "TANGGAL_DIPA",
                "value": "01 Desember 2025",
                "grup": 3,
                "created_at": "2024-12-26T17:00:00.000000Z",
                "updated_at": "2026-01-22T02:03:57.000000Z"
            }
        ],
        "4": [
            {
                "id": 2,
                "tahun": "2025",
                "key": "FORMAT_NO_SPK",
                "value": "/3215/PPK/SPK/",
                "grup": 4,
                "created_at": "2023-12-31T17:00:00.000000Z",
                "updated_at": "2026-01-02T03:59:13.000000Z"
            },
            {
                "id": 12,
                "tahun": "2025",
                "key": "FORMAT_NO_BAST",
                "value": "/3215/PPK/BAST/",
                "grup": 4,
                "created_at": "2023-12-31T17:00:00.000000Z",
                "updated_at": "2026-01-02T03:59:13.000000Z"
            },
            {
                "id": 14,
                "tahun": "2025",
                "key": "FORMAT_SURTUG",
                "value": "B-{nomor}/32150/{klasifikasi}/{tahun}",
                "grup": 4,
                "created_at": "2023-12-31T17:00:00.000000Z",
                "updated_at": "2026-01-02T03:59:13.000000Z"
            },
            {
                "id": 15,
                "tahun": "2025",
                "key": "FORMAT_SURAT_KELUAR",
                "value": "B-{nomor}/32150/KA.220/{tahun}",
                "grup": 4,
                "created_at": "2023-12-31T17:00:00.000000Z",
                "updated_at": "2026-01-02T03:59:13.000000Z"
            },
            {
                "id": 17,
                "tahun": "2025",
                "key": "FORMAT_FORM_PERMINTAAN",
                "value": "B-{nomor}/32150/{klas}/{tahun}",
                "grup": 4,
                "created_at": "2023-12-31T17:00:00.000000Z",
                "updated_at": "2026-01-02T03:59:13.000000Z"
            },
            {
                "id": 18,
                "tahun": "2025",
                "key": "FORMAT_SK",
                "value": "32150.{nomor}/{bln}/{tahun}",
                "grup": 4,
                "created_at": "2023-12-31T17:00:00.000000Z",
                "updated_at": "2026-01-02T03:59:13.000000Z"
            }
        ]
    }
}
```

---
*Generated at: 2026-04-18 20:50:56*
