# GET `/admin/users?per_page=15&page=1`

## Endpoint Info

| Property | Value |
|----------|-------|
| **Method** | `GET` |
| **URL** | `/admin/users?per_page=15&page=1` |
| **Description** | Users list |
| **Query Params Used** | `per_page=15&page=1` |
| **HTTP Status** | `200` |
| **Response Time** | 0.062s |
| **Generated** | 2026-04-18 20:50:56 |

## Response

```json
{
    "success": true,
    "message": "Data retrieved successfully",
    "data": {
        "organik_data": [
            {
                "id": 2,
                "name": "Asep Surya",
                "email": "asep.surya@bps.go.id",
                "email_verified_at": null,
                "current_team_id": null,
                "profile_photo_path": null,
                "created_at": "2024-02-25T09:27:46.000000Z",
                "updated_at": "2024-02-25T09:27:46.000000Z",
                "roles": [
                    {
                        "id": 2,
                        "name": "katim",
                        "guard_name": "sanctum",
                        "created_at": "2026-01-02T03:58:30.000000Z",
                        "updated_at": "2026-01-02T03:58:30.000000Z",
                        "pivot": {
                            "model_type": "App\\Models\\User",
                            "model_id": 2,
                            "role_id": 2
                        }
                    }
                ],
                "permissions": []
            },
            {
                "id": 3,
                "name": "Harni Dwi Prikasih",
                "email": "dhedhe@bps.go.id",
                "email_verified_at": null,
                "current_team_id": null,
                "profile_photo_path": null,
                "created_at": "2024-02-25T09:27:47.000000Z",
                "updated_at": "2024-02-25T09:27:47.000000Z",
                "roles": [
                    {
                        "id": 2,
                        "name": "katim",
                        "guard_name": "sanctum",
                        "created_at": "2026-01-02T03:58:30.000000Z",
                        "updated_at": "2026-01-02T03:58:30.000000Z",
                        "pivot": {
                            "model_type": "App\\Models\\User",
                            "model_id": 3,
                            "role_id": 2
                        }
                    }
                ],
                "permissions": []
            },
            {
                "id": 4,
                "name": "Ari Setiadi Gunawan",
                "email": "arisetia@bps.go.id",
                "email_verified_at": null,
                "current_team_id": null,
                "profile_photo_path": null,
                "created_at": "2024-02-25T09:27:47.000000Z",
                "updated_at": "2025-07-28T07:54:41.000000Z",
                "roles": [
                    {
                        "id": 3,
                        "name": "kepala",
                        "guard_name": "sanctum",
                        "created_at": "2026-01-02T03:58:30.000000Z",
                        "updated_at": "2026-01-02T03:58:30.000000Z",
                        "pivot": {
                            "model_type": "App\\Models\\User",
                            "model_id": 4,
                            "role_id": 3
                        }
                    }
                ],
                "permissions": []
            },
            {
                "id": 5,
                "name": "Budi Yunior",
                "email": "dior@bps.go.id",
                "email_verified_at": null,
                "current_team_id": null,
                "profile_photo_path": null,
                "created_at": "2024-02-25T09:27:47.000000Z",
                "updated_at": "2024-02-25T09:27:47.000000Z",
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
                "permissions": []
            },
            {
                "id": 6,
                "name": "Mina Nur Aini",
                "email": "minan@bps.go.id",
                "email_verified_at": null,
                "current_team_id": null,
                "profile_photo_path": null,
                "created_at": "2024-02-25T09:27:47.000000Z",
                "updated_at": "2024-02-25T09:27:47.000000Z",
                "roles": [
                    {
                        "id": 4,
                        "name": "madya",
                        "guard_name": "sanctum",
                        "created_at": "2026-01-02T03:58:30.000000Z",
                        "updated_at": "2026-01-02T03:58:30.000000Z",
                        "pivot": {
                            "model_type": "App\\Models\\User",
                            "model_id": 6,
                            "role_id": 4
                        }
                    }
                ],
                "permissions": []
            },
            {
                "id": 7,
                "name": "Iskandar Zulkarnain",
                "email": "iskandarzs@bps.go.id",
                "email_verified_at": null,
                "current_team_id": null,
                "profile_photo_path": null,
                "created_at": "2024-02-25T09:27:48.000000Z",
                "updated_at": "2024-02-25T09:27:48.000000Z",
                "roles": [
                    {
                        "id": 2,
                        "name": "katim",
                        "guard_name": "sanctum",
                        "created_at": "2026-01-02T03:58:30.000000Z",
                        "updated_at": "2026-01-02T03:58:30.000000Z",
                        "pivot": {
                            "model_type": "App\\Models\\User",
                            "model_id": 7,
                            "role_id": 2
                        }
                    }
                ],
                "permissions": []
            },
            {
                "id": 8,
                "name": "Dody Syafrudin",
                "email": "dodys@bps.go.id",
                "email_verified_at": null,
                "current_team_id": null,
                "profile_photo_path": null,
                "created_at": "2024-02-25T09:27:48.000000Z",
                "updated_at": "2024-02-25T09:27:48.000000Z",
                "roles": [
                    {
                        "id": 5,
                        "name": "staf",
                        "guard_name": "sanctum",
                        "created_at": "2026-01-02T03:58:30.000000Z",
                        "updated_at": "2026-01-02T03:58:30.000000Z",
                        "pivot": {
                            "model_type": "App\\Models\\User",
                            "model_id": 8,
                            "role_id": 5
                        }
                    }
                ],
                "permissions": []
            },
            {
                "id": 9,
                "name": "Ali Anwar",
                "email": "ali.anwar@bps.go.id",
                "email_verified_at": null,
                "current_team_id": null,
                "profile_photo_path": null,
                "created_at": "2024-02-25T09:27:48.000000Z",
                "updated_at": "2024-02-25T09:27:48.000000Z",
                "roles": [
                    {
                        "id": 5,
                        "name": "staf",
                        "guard_name": "sanctum",
                        "created_at": "2026-01-02T03:58:30.000000Z",
                        "updated_at": "2026-01-02T03:58:30.000000Z",
                        "pivot": {
                            "model_type": "App\\Models\\User",
                            "model_id": 9,
                            "role_id": 5
                        }
                    }
                ],
                "permissions": []
            },
            {
                "id": 10,
                "name": "Asep Suryadi",
                "email": "asep.suryadi@bps.go.id",
                "email_verified_at": null,
                "current_team_id": null,
                "profile_photo_path": null,
                "created_at": "2024-02-25T09:27:48.000000Z",
                "updated_at": "2024-02-25T09:27:48.000000Z",
                "roles": [
                    {
                        "id": 5,
                        "name": "staf",
                        "guard_name": "sanctum",
                        "created_at": "2026-01-02T03:58:30.000000Z",
                        "updated_at": "2026-01-02T03:58:30.000000Z",
                        "pivot": {
                            "model_type": "App\\Models\\User",
                            "model_id": 10,
                            "role_id": 5
                        }
                    }
                ],
                "permissions": []
            },
            {
                "id": 11,
                "name": "Agus Syaripudin",
                "email": "a.syarifudin@bps.go.id",
                "email_verified_at": null,
                "current_team_id": null,
                "profile_photo_path": null,
                "created_at": "2024-02-25T09:27:48.000000Z",
                "updated_at": "2024-02-25T09:27:48.000000Z",
                "roles": [
                    {
                        "id": 5,
                        "name": "staf",
                        "guard_name": "sanctum",
                        "created_at": "2026-01-02T03:58:30.000000Z",
                        "updated_at": "2026-01-02T03:58:30.000000Z",
                        "pivot": {
                            "model_type": "App\\Models\\User",
                            "model_id": 11,
                            "role_id": 5
                        }
                    }
                ],
                "permissions": []
            },
            {
                "id": 12,
                "name": "Wawan Kurniawan",
                "email": "wa.kurniawan@bps.go.id",
                "email_verified_at": null,
                "current_team_id": null,
                "profile_photo_path": null,
                "created_at": "2024-02-25T09:27:49.000000Z",
                "updated_at": "2024-02-25T09:27:49.000000Z",
                "roles": [],
                "permissions": []
            },
            {
                "id": 13,
                "name": "Evy Djuwita",
                "email": "evi@bps.go.id",
                "email_verified_at": null,
                "current_team_id": null,
                "profile_photo_path": null,
                "created_at": "2024-02-25T09:27:49.000000Z",
                "updated_at": "2024-02-25T09:27:49.000000Z",
                "roles": [],
                "permissions": []
            },
            {
                "id": 14,
                "name": "Novi Rinawati",
                "email": "novi_rinawati@bps.go.id",
                "email_verified_at": null,
                "current_team_id": null,
                "profile_photo_path": null,
                "created_at": "2024-02-25T09:27:49.000000Z",
                "updated_at": "2024-02-25T09:27:49.000000Z",
                "roles": [],
                "permissions": []
            },
            {
                "id": 15,
                "name": "Yedih Wahyudin",
                "email": "yedih.wahyudin@bps.go.id",
                "email_verified_at": null,
                "current_team_id": null,
                "profile_photo_path": null,
                "created_at": "2024-02-25T09:27:49.000000Z",
                "updated_at": "2024-02-25T09:27:49.000000Z",
                "roles": [],
                "permissions": []
            },
            {
                "id": 16,
                "name": "Agus Rosidi",
                "email": "agusrosidi@bps.go.id",
                "email_verified_at": null,
                "current_team_id": null,
                "profile_photo_path": null,
                "created_at": "2024-02-25T09:27:50.000000Z",
                "updated_at": "2024-02-25T09:27:50.000000Z",
                "roles": [],
                "permissions": []
            }
        ],
        "mitra_data": [
            {
                "id": 320623110637,
                "name": "Dede Supiyanto",
                "email": "dedesupiyanto12@gmail.com",
                "email_verified_at": null,
                "current_team_id": null,
                "profile_photo_path": null,
                "created_at": null,
                "updated_at": null,
                "roles": [
                    {
                        "id": 7,
                        "name": "mitra",
                        "guard_name": "sanctum",
                        "created_at": "2026-02-17T06:29:29.000000Z",
                        "updated_at": "2026-02-17T06:29:29.000000Z",
                        "pivot": {
                            "model_type": "App\\Models\\User",
                            "model_id": 320623110637,
                            "role_id": 7
                        }
                    }
                ],
                "permissions": []
            },
            {
                "id": 321422120005,
                "name": "Aulia Fitri Ghoniyah",
                "email": "auliafirighoniyah@gmail.com",
                "email_verified_at": null,
                "current_team_id": null,
                "profile_photo_path": null,
                "created_at": null,
                "updated_at": null,
                "roles": [
                    {
                        "id": 7,
                        "name": "mitra",
                        "guard_name": "sanctum",
                        "created_at": "2026-02-17T06:29:29.000000Z",
                        "updated_at": "2026-02-17T06:29:29.000000Z",
                        "pivot": {
                            "model_type": "App\\Models\\User",
                            "model_id": 321422120005,
                            "role_id": 7
                        }
                    }
                ],
                "permissions": []
            },
            {
                "id": 321522010001,
                "name": "Santosa",
                "email": "sasanto416@gmail.com",
                "email_verified_at": null,
                "current_team_id": null,
                "profile_photo_path": null,
                "created_at": null,
                "updated_at": null,
                "roles": [
                    {
                        "id": 7,
                        "name": "mitra",
                        "guard_name": "sanctum",
                        "created_at": "2026-02-17T06:29:29.000000Z",
                        "updated_at": "2026-02-17T06:29:29.000000Z",
                        "pivot": {
                            "model_type": "App\\Models\\User",
                            "model_id": 321522010001,
                            "role_id": 7
                        }
                    }
                ],
                "permissions": []
            },
            {
                "id": 321522010002,
                "name": "Suhendra",
                "email": "kabayan.dewa@gmail.com",
                "email_verified_at": null,
                "current_team_id": null,
                "profile_photo_path": null,
                "created_at": null,
                "updated_at": null,
                "roles": [
                    {
                        "id": 7,
                        "name": "mitra",
                        "guard_name": "sanctum",
                        "created_at": "2026-02-17T06:29:29.000000Z",
                        "updated_at": "2026-02-17T06:29:29.000000Z",
                        "pivot": {
                            "model_type": "App\\Models\\User",
                            "model_id": 321522010002,
                            "role_id": 7
                        }
                    }
                ],
                "permissions": []
            },
            {
                "id": 321522010003,
                "name": "Dadan Suhendar",
                "email": "dansoehendar88@gmail.com",
                "email_verified_at": null,
                "current_team_id": null,
                "profile_photo_path": null,
                "created_at": null,
                "updated_at": null,
                "roles": [
                    {
                        "id": 7,
                        "name": "mitra",
                        "guard_name": "sanctum",
                        "created_at": "2026-02-17T06:29:29.000000Z",
                        "updated_at": "2026-02-17T06:29:29.000000Z",
                        "pivot": {
                            "model_type": "App\\Models\\User",
                            "model_id": 321522010003,
                            "role_id": 7
                        }
                    }
                ],
                "permissions": []
            },
            {
                "id": 321522010004,
                "name": "Olis Nurholis",
                "email": "mangolisjuni2020@gmail.com",
                "email_verified_at": null,
                "current_team_id": null,
                "profile_photo_path": null,
                "created_at": null,
                "updated_at": null,
                "roles": [
                    {
                        "id": 7,
                        "name": "mitra",
                        "guard_name": "sanctum",
                        "created_at": "2026-02-17T06:29:29.000000Z",
                        "updated_at": "2026-02-17T06:29:29.000000Z",
                        "pivot": {
                            "model_type": "App\\Models\\User",
                            "model_id": 321522010004,
                            "role_id": 7
                        }
                    }
                ],
                "permissions": []
            },
            {
                "id": 321522010005,
                "name": "Karyono",
                "email": "binkaryono@gmail.com",
                "email_verified_at": null,
                "current_team_id": null,
                "profile_photo_path": null,
                "created_at": null,
                "updated_at": null,
                "roles": [
                    {
                        "id": 7,
                        "name": "mitra",
                        "guard_name": "sanctum",
                        "created_at": "2026-02-17T06:29:29.000000Z",
                        "updated_at": "2026-02-17T06:29:29.000000Z",
                        "pivot": {
                            "model_type": "App\\Models\\User",
                            "model_id": 321522010005,
                            "role_id": 7
                        }
                    }
                ],
                "permissions": []
            },
            {
                "id": 321522010006,
                "name": "Moch Rofei",
                "email": "rofei333@gmail.com",
                "email_verified_at": null,
                "current_team_id": null,
                "profile_photo_path": null,
                "created_at": null,
                "updated_at": null,
                "roles": [
                    {
                        "id": 7,
                        "name": "mitra",
                        "guard_name": "sanctum",
                        "created_at": "2026-02-17T06:29:29.000000Z",
                        "updated_at": "2026-02-17T06:29:29.000000Z",
                        "pivot": {
                            "model_type": "App\\Models\\User",
                            "model_id": 321522010006,
                            "role_id": 7
                        }
                    }
                ],
                "permissions": []
            },
            {
                "id": 321522010007,
                "name": "Thalia Sava Salsabila",
                "email": "thaliasavasalsabila15@gmail.com",
                "email_verified_at": null,
                "current_team_id": null,
                "profile_photo_path": null,
                "created_at": null,
                "updated_at": null,
                "roles": [
                    {
                        "id": 7,
                        "name": "mitra",
                        "guard_name": "sanctum",
                        "created_at": "2026-02-17T06:29:29.000000Z",
                        "updated_at": "2026-02-17T06:29:29.000000Z",
                        "pivot": {
                            "model_type": "App\\Models\\User",
                            "model_id": 321522010007,
                            "role_id": 7
                        }
                    }
                ],
                "permissions": []
            },
            {
                "id": 321522010008,
                "name": "Ropa'i",
                "email": "ropaipadel@gmail.com",
                "email_verified_at": null,
                "current_team_id": null,
                "profile_photo_path": null,
                "created_at": null,
                "updated_at": null,
                "roles": [
                    {
                        "id": 7,
                        "name": "mitra",
                        "guard_name": "sanctum",
                        "created_at": "2026-02-17T06:29:29.000000Z",
                        "updated_at": "2026-02-17T06:29:29.000000Z",
                        "pivot": {
                            "model_type": "App\\Models\\User",
                            "model_id": 321522010008,
                            "role_id": 7
                        }
                    }
                ],
                "permissions": []
            },
            {
                "id": 321522010009,
                "name": "Ziaul Haq",
                "email": "zhaq452@gmail.com",
                "email_verified_at": null,
                "current_team_id": null,
                "profile_photo_path": null,
                "created_at": null,
                "updated_at": null,
                "roles": [
                    {
                        "id": 7,
                        "name": "mitra",
                        "guard_name": "sanctum",
                        "created_at": "2026-02-17T06:29:29.000000Z",
                        "updated_at": "2026-02-17T06:29:29.000000Z",
                        "pivot": {
                            "model_type": "App\\Models\\User",
                            "model_id": 321522010009,
                            "role_id": 7
                        }
                    }
                ],
                "permissions": []
            },
            {
                "id": 321522010010,
                "name": "Karsih Sukarsih",
                "email": "karsihyusrina10@gmail.com",
                "email_verified_at": null,
                "current_team_id": null,
                "profile_photo_path": null,
                "created_at": null,
                "updated_at": null,
                "roles": [
                    {
                        "id": 7,
                        "name": "mitra",
                        "guard_name": "sanctum",
                        "created_at": "2026-02-17T06:29:29.000000Z",
                        "updated_at": "2026-02-17T06:29:29.000000Z",
                        "pivot": {
                            "model_type": "App\\Models\\User",
                            "model_id": 321522010010,
                            "role_id": 7
                        }
                    }
                ],
                "permissions": []
            },
            {
                "id": 321522010012,
                "name": "Karno",
                "email": "nizarsabiqajulmy@gmail.com",
                "email_verified_at": null,
                "current_team_id": null,
                "profile_photo_path": null,
                "created_at": null,
                "updated_at": null,
                "roles": [
                    {
                        "id": 7,
                        "name": "mitra",
                        "guard_name": "sanctum",
                        "created_at": "2026-02-17T06:29:29.000000Z",
                        "updated_at": "2026-02-17T06:29:29.000000Z",
                        "pivot": {
                            "model_type": "App\\Models\\User",
                            "model_id": 321522010012,
                            "role_id": 7
                        }
                    }
                ],
                "permissions": []
            },
            {
                "id": 321522010013,
                "name": "Asep Supriatna",
                "email": "asepsupriatna0121@gmail.com",
                "email_verified_at": null,
                "current_team_id": null,
                "profile_photo_path": null,
                "created_at": null,
                "updated_at": null,
                "roles": [
                    {
                        "id": 7,
                        "name": "mitra",
                        "guard_name": "sanctum",
                        "created_at": "2026-02-17T06:29:29.000000Z",
                        "updated_at": "2026-02-17T06:29:29.000000Z",
                        "pivot": {
                            "model_type": "App\\Models\\User",
                            "model_id": 321522010013,
                            "role_id": 7
                        }
                    }
                ],
                "permissions": []
            },
            {
                "id": 321522010014,
                "name": "Eko Haryanto",
                "email": "ekoharyanto063@gmail.com",
                "email_verified_at": null,
                "current_team_id": null,
                "profile_photo_path": null,
                "created_at": null,
                "updated_at": null,
                "roles": [
                    {
                        "id": 7,
                        "name": "mitra",
                        "guard_name": "sanctum",
                        "created_at": "2026-02-17T06:29:29.000000Z",
                        "updated_at": "2026-02-17T06:29:29.000000Z",
                        "pivot": {
                            "model_type": "App\\Models\\User",
                            "model_id": 321522010014,
                            "role_id": 7
                        }
                    }
                ],
                "permissions": []
            }
        ],
        "roles": [
            {
                "id": 1,
                "name": "super-admin",
                "guard_name": "sanctum",
                "created_at": "2026-01-02T03:58:30.000000Z",
                "updated_at": "2026-01-02T03:58:30.000000Z"
            },
            {
                "id": 2,
                "name": "katim",
                "guard_name": "sanctum",
                "created_at": "2026-01-02T03:58:30.000000Z",
                "updated_at": "2026-01-02T03:58:30.000000Z"
            },
            {
                "id": 3,
                "name": "kepala",
                "guard_name": "sanctum",
                "created_at": "2026-01-02T03:58:30.000000Z",
                "updated_at": "2026-01-02T03:58:30.000000Z"
            },
            {
                "id": 4,
                "name": "madya",
                "guard_name": "sanctum",
                "created_at": "2026-01-02T03:58:30.000000Z",
                "updated_at": "2026-01-02T03:58:30.000000Z"
            },
            {
                "id": 5,
                "name": "staf",
                "guard_name": "sanctum",
                "created_at": "2026-01-02T03:58:30.000000Z",
                "updated_at": "2026-01-02T03:58:30.000000Z"
            },
            {
                "id": 6,
                "name": "Kasub Umum",
                "guard_name": "sanctum",
                "created_at": "2026-01-02T03:58:30.000000Z",
                "updated_at": "2026-01-02T03:58:30.000000Z"
            },
            {
                "id": 7,
                "name": "mitra",
                "guard_name": "sanctum",
                "created_at": "2026-02-17T06:29:29.000000Z",
                "updated_at": "2026-02-17T06:29:29.000000Z"
            },
            {
                "id": 10,
                "name": "super-admin",
                "guard_name": "web",
                "created_at": null,
                "updated_at": null
            }
        ],
        "organik_meta": {
            "per_page": 15,
            "has_more": true,
            "count": 15,
            "total_records": 41,
            "total_page": 3
        },
        "organik_links": {
            "next_cursor": "eyJ1c2Vycy5pZCI6MTYsIl9wb2ludHNUb05leHRJdGVtcyI6dHJ1ZX0",
            "next_page_url": "http://127.0.0.1:9001/api/admin/users?organik_cursor=eyJ1c2Vycy5pZCI6MTYsIl9wb2ludHNUb05leHRJdGVtcyI6dHJ1ZX0",
            "prev_cursor": null,
            "prev_page_url": null,
            "path": "http://127.0.0.1:9001/api/admin/users"
        },
        "mitra_meta": {
            "per_page": 15,
            "has_more": true,
            "count": 15,
            "total_records": 1288,
            "total_page": 86
        },
        "mitra_links": {
            "next_cursor": "eyJ1c2Vycy5pZCI6MzIxNTIyMDEwMDE0LCJfcG9pbnRzVG9OZXh0SXRlbXMiOnRydWV9",
            "next_page_url": "http://127.0.0.1:9001/api/admin/users?mitra_cursor=eyJ1c2Vycy5pZCI6MzIxNTIyMDEwMDE0LCJfcG9pbnRzVG9OZXh0SXRlbXMiOnRydWV9",
            "prev_cursor": null,
            "prev_page_url": null,
            "path": "http://127.0.0.1:9001/api/admin/users"
        }
    }
}
```

---
*Generated at: 2026-04-18 20:50:56*
