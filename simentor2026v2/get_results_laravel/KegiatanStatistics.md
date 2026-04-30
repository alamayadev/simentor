# GET `/kantor/kegiatan/statistics`

## Endpoint Info

| Property | Value |
|----------|-------|
| **Method** | `GET` |
| **URL** | `/kantor/kegiatan/statistics` |
| **Description** | Statistics |
| **HTTP Status** | `200` |
| **Response Time** | 0.047s |
| **Generated** | 2026-04-18 20:51:07 |

## Response

```json
{
    "success": true,
    "message": "Statistics retrieved successfully",
    "data": {
        "kegiatan_by_fungsi": [
            {
                "tahun": 2026,
                "fungsi": "Distribusi",
                "total_anggaran": 8059615564,
                "total_penyerapan": 11401000,
                "persen": 0.14
            },
            {
                "tahun": 2026,
                "fungsi": "IPDS",
                "total_anggaran": 127654041,
                "total_penyerapan": 3774000,
                "persen": 2.96
            },
            {
                "tahun": 2026,
                "fungsi": "Nerwilis",
                "total_anggaran": 30588084,
                "total_penyerapan": 2556000,
                "persen": 8.36
            },
            {
                "tahun": 2026,
                "fungsi": "Produksi",
                "total_anggaran": 1120061868,
                "total_penyerapan": 27059000,
                "persen": 2.42
            },
            {
                "tahun": 2026,
                "fungsi": "Sosial",
                "total_anggaran": 575319000,
                "total_penyerapan": 197958000,
                "persen": 34.41
            }
        ],
        "nilai_penugasan_by_month": [
            {
                "month": "2026-Feb",
                "total_nilai": 65848000
            },
            {
                "month": "2026-Mar",
                "total_nilai": 176900000
            }
        ],
        "top_mitra_honor": [
            {
                "nama_lengkap": "Ardi Wardiat",
                "keca": "KARAWANG BARAT",
                "tahun": 2026,
                "total_nilai": 4961000
            },
            {
                "nama_lengkap": "Eja Herdian",
                "keca": "BATUJAYA",
                "tahun": 2026,
                "total_nilai": 4281000
            },
            {
                "nama_lengkap": "Masrudin",
                "keca": "KARAWANG BARAT",
                "tahun": 2026,
                "total_nilai": 4156000
            },
            {
                "nama_lengkap": "Wasdi",
                "keca": "PEDES",
                "tahun": 2026,
                "total_nilai": 4127000
            },
            {
                "nama_lengkap": "Vivi Dwi Haviani",
                "keca": "KLARI",
                "tahun": 2026,
                "total_nilai": 4120000
            },
            {
                "nama_lengkap": "Sai Gunawan",
                "keca": "LEMAHABANG",
                "tahun": 2026,
                "total_nilai": 4074000
            },
            {
                "nama_lengkap": "Ukim Kurniawan",
                "keca": "TIRTAJAYA",
                "tahun": 2026,
                "total_nilai": 3842000
            },
            {
                "nama_lengkap": "Iwan Wirawan Abdul Kadir",
                "keca": "CIAMPEL",
                "tahun": 2026,
                "total_nilai": 3842000
            },
            {
                "nama_lengkap": "Euis Widha Widiawati",
                "keca": "TELUKJAMBE BARAT",
                "tahun": 2026,
                "total_nilai": 3724000
            },
            {
                "nama_lengkap": "Zaenal Abidin",
                "keca": "KOTABARU",
                "tahun": 2026,
                "total_nilai": 3476000
            }
        ],
        "kegiatan_penyerapan_100": [
            {
                "id": 1169,
                "tahun": "2026",
                "fungsi": "IPDS",
                "nama": "Pengolahan Updating Listing (Susenas Maret)",
                "total_nilai": 3774000
            },
            {
                "id": 1131,
                "tahun": "2026",
                "fungsi": "Produksi",
                "nama": "Pendataan Lapangan Survei Imk Triwulanan (Vimk25 Triwulan 4)",
                "total_nilai": 3363000
            },
            {
                "id": 1162,
                "tahun": "2026",
                "fungsi": "Sosial",
                "nama": "Pendataan Lapangan Updating Listing (Susenas Maret)",
                "total_nilai": 19074000
            }
        ]
    }
}
```

---
*Generated at: 2026-04-18 20:51:07*
