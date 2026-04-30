# GET `/profile`

## Endpoint Info

| Property | Value |
|----------|-------|
| **Method** | `GET` |
| **URL** | `/profile` |
| **Description** | User profile |
| **HTTP Status** | `200` |
| **Response Time** | 0.036s |
| **Generated** | 2026-04-18 20:50:55 |

## Response

```json
{
    "success": true,
    "message": "User profile retrieved",
    "data": {
        "id": 5,
        "name": "Budi Yunior",
        "email": "dior@bps.go.id",
        "email_verified_at": null,
        "current_team_id": null,
        "profile_photo_path": null,
        "created_at": "2024-02-25T09:27:47.000000Z",
        "updated_at": "2024-02-25T09:27:47.000000Z",
        "two_factor_confirmed_at": null,
        "roles": [
            {
                "id": 5,
                "name": "staf",
                "guard_name": "sanctum",
                "created_at": "2026-01-02T03:58:30.000000Z",
                "updated_at": "2026-01-02T03:58:30.000000Z",
                "pivot": {
                    "model_type": "App\\Models\\User",
                    "model_id": 5,
                    "role_id": 5
                }
            }
        ],
        "permissions": [],
        "pegawai": {
            "id": 8,
            "nama": "Budi Yunior, S.ST",
            "pangkat": "Penata Tk. I",
            "gol": "IIId",
            "nip": "19740609 199302 1 001",
            "gelar_depan": null,
            "gelar_belakang": "S.St",
            "tempat_lahir": null,
            "tanggal_lahir": null,
            "alamat": "Jalan Margasari RT 017/005, Margasari,Karawang Timur 41371",
            "no_hp": "087777979677",
            "jabatan": "Pranata Komputer Ahli Muda",
            "kelas": 9,
            "user_id": 5,
            "status": null,
            "created_at": "2026-01-02T03:58:56.000000Z",
            "updated_at": "2026-01-13T08:56:27.000000Z"
        }
    }
}
```

---
*Generated at: 2026-04-18 20:50:55*
