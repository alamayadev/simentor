# GET `/miniapp/survey-results`

## Endpoint Info

| Property | Value |
|----------|-------|
| **Method** | `GET` |
| **URL** | `/miniapp/survey-results` |
| **Description** | Survey results list |
| **HTTP Status** | `200` |
| **Response Time** | 0.024s |
| **Generated** | 2026-04-18 20:50:55 |

## Response

```json
{
    "success": true,
    "message": "Survey results retrieved successfully",
    "data": [
        {
            "id": 1,
            "survey_id": 1,
            "survey_result": {
                "anggota_dasar": [
                    {
                        "nama": "Korong",
                        "Hubungaan_KRT": "Kepala RT"
                    },
                    {
                        "nama": "Jablay",
                        "Hubungaan_KRT": "Istri/Suami"
                    },
                    {
                        "nama": "Ariel",
                        "Hubungaan_KRT": "Anak"
                    }
                ],
                "rincian_status": [
                    {
                        "pilih_nama": "Korong",
                        "status_perkawinan": "Menikah"
                    },
                    {
                        "pilih_nama": "Jablay",
                        "status_perkawinan": "Menikah"
                    },
                    {
                        "pilih_nama": "Ariel",
                        "status_perkawinan": "Belum Menikah"
                    }
                ]
            },
            "created_at": "2026-04-12T07:55:00.000000Z",
            "updated_at": "2026-04-12T07:55:00.000000Z",
            "survey": {
                "id": 1,
                "survey_name": "Contoh Validasi",
                "survey_description": "Contoh validasi antar rincian"
            }
        }
    ]
}
```

---
*Generated at: 2026-04-18 20:50:55*
