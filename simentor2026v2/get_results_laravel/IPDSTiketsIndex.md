# GET `/ipds/tikets?per_page=15&page=1`

## Endpoint Info

| Property | Value |
|----------|-------|
| **Method** | `GET` |
| **URL** | `/ipds/tikets?per_page=15&page=1` |
| **Description** | Tikets list |
| **Query Params Used** | `per_page=15&page=1` |
| **HTTP Status** | `200` |
| **Response Time** | 0.03s |
| **Generated** | 2026-04-18 20:51:09 |

## Response

```json
{
    "success": true,
    "message": "Data retrieved successfully",
    "data": [
        {
            "id": 7,
            "user_id": 29,
            "jenis_keluhan": "Hardware PC/Laptop",
            "deskripsi": "PC sering freeze atau heng berkali. minta dicek karena menghambat pekerjaan..",
            "status": "Pending",
            "keterangan": null,
            "ditangani_oleh": null,
            "created_at": "2025-12-11T00:22:06.000000Z",
            "updated_at": "2025-12-11T00:22:06.000000Z",
            "user": {
                "id": 29,
                "name": "Prima Rudiansah",
                "email": "prima.rudiansah@bps.go.id",
                "email_verified_at": null,
                "current_team_id": null,
                "profile_photo_path": null,
                "created_at": "2024-02-25T09:27:52.000000Z",
                "updated_at": "2024-02-25T09:27:52.000000Z",
                "two_factor_confirmed_at": null,
                "pegawai": {
                    "id": 11,
                    "nama": "Prima Rudiansah, S.Si",
                    "pangkat": "Penata",
                    "gol": "IIIc",
                    "nip": "19851013 2011011 014",
                    "gelar_depan": null,
                    "gelar_belakang": null,
                    "tempat_lahir": null,
                    "tanggal_lahir": null,
                    "alamat": null,
                    "no_hp": null,
                    "jabatan": "Statistisi Ahli Muda",
                    "kelas": 9,
                    "user_id": 29,
                    "status": null,
                    "created_at": "2026-01-02T03:58:56.000000Z",
                    "updated_at": "2026-01-02T03:58:56.000000Z"
                }
            }
        },
        {
            "id": 6,
            "user_id": 33,
            "jenis_keluhan": "Jaringan",
            "deskripsi": "Tidak bisa akses jaringan lokal IPDS-1 untuk mengambil template KCDA",
            "status": "closed",
            "keterangan": "Jaringan sudah kembali normal",
            "ditangani_oleh": 5,
            "created_at": "2025-09-12T06:44:21.000000Z",
            "updated_at": "2026-01-14T00:06:17.000000Z",
            "user": {
                "id": 33,
                "name": "Pramadya Yuyu Ananda",
                "email": "pramadya.ananda@bps.go.id",
                "email_verified_at": null,
                "current_team_id": null,
                "profile_photo_path": null,
                "created_at": "2024-02-25T09:27:53.000000Z",
                "updated_at": "2024-02-25T09:27:53.000000Z",
                "two_factor_confirmed_at": null,
                "pegawai": {
                    "id": 15,
                    "nama": "Pramadya Yuyu Ananda, SST",
                    "pangkat": "Penata Muda Tk. I",
                    "gol": "IIIb",
                    "nip": "19940316 201701 1 001",
                    "gelar_depan": null,
                    "gelar_belakang": null,
                    "tempat_lahir": null,
                    "tanggal_lahir": null,
                    "alamat": null,
                    "no_hp": null,
                    "jabatan": "Statistisi Ahli Pertama",
                    "kelas": 8,
                    "user_id": 33,
                    "status": null,
                    "created_at": "2026-01-02T03:58:56.000000Z",
                    "updated_at": "2026-01-02T03:58:56.000000Z"
                }
            }
        },
        {
            "id": 5,
            "user_id": 3,
            "jenis_keluhan": "Hardware PC/Laptop",
            "deskripsi": "Memori tidak terbaca",
            "status": "Pending",
            "keterangan": null,
            "ditangani_oleh": null,
            "created_at": "2025-09-12T06:39:49.000000Z",
            "updated_at": "2025-09-12T06:39:49.000000Z",
            "user": {
                "id": 3,
                "name": "Harni Dwi Prikasih",
                "email": "dhedhe@bps.go.id",
                "email_verified_at": null,
                "current_team_id": null,
                "profile_photo_path": null,
                "created_at": "2024-02-25T09:27:47.000000Z",
                "updated_at": "2024-02-25T09:27:47.000000Z",
                "two_factor_confirmed_at": null,
                "pegawai": {
                    "id": 4,
                    "nama": "Harni Dwi Prikasih, S.ST",
                    "pangkat": "Penata Tk. I",
                    "gol": "IIId",
                    "nip": "19700911 199003 2 001",
                    "gelar_depan": null,
                    "gelar_belakang": null,
                    "tempat_lahir": null,
                    "tanggal_lahir": null,
                    "alamat": null,
                    "no_hp": null,
                    "jabatan": "Statistisi Ahli Muda",
                    "kelas": 9,
                    "user_id": 3,
                    "status": null,
                    "created_at": "2026-01-02T03:58:56.000000Z",
                    "updated_at": "2026-01-02T03:58:56.000000Z"
                }
            }
        },
        {
            "id": 4,
            "user_id": 21,
            "jenis_keluhan": "Software",
            "deskripsi": "pvn ngadat boz",
            "status": "Selesai Ditangani",
            "keterangan": "Pengguna Sudah bisa terkoneksi dengan VPN",
            "ditangani_oleh": 5,
            "created_at": "2025-04-29T02:44:05.000000Z",
            "updated_at": "2025-05-26T03:30:42.000000Z",
            "user": {
                "id": 21,
                "name": "Suwirno Atma Atmaja",
                "email": "suwirno.aa@bps.go.id",
                "email_verified_at": null,
                "current_team_id": null,
                "profile_photo_path": null,
                "created_at": "2024-02-25T09:27:51.000000Z",
                "updated_at": "2024-02-25T09:27:51.000000Z",
                "two_factor_confirmed_at": null,
                "pegawai": {
                    "id": 29,
                    "nama": "Suwirno Atma Atmaja",
                    "pangkat": "Penata Muda",
                    "gol": "IIIa",
                    "nip": "19680407 200701 1 007",
                    "gelar_depan": null,
                    "gelar_belakang": null,
                    "tempat_lahir": null,
                    "tanggal_lahir": null,
                    "alamat": null,
                    "no_hp": null,
                    "jabatan": "Pengolah Data",
                    "kelas": 6,
                    "user_id": 21,
                    "status": null,
                    "created_at": "2026-01-02T03:58:56.000000Z",
                    "updated_at": "2026-01-02T03:58:56.000000Z"
                }
            }
        },
        {
            "id": 3,
            "user_id": 29,
            "jenis_keluhan": "Printer",
            "deskripsi": "tidak bisa print pada printer yang terhubung dari PC sebelah (Pak Suwirno)",
            "status": "Selesai Ditangani",
            "keterangan": "printer sudah bisa di jalankan dengan normal",
            "ditangani_oleh": 5,
            "created_at": "2025-04-28T23:50:01.000000Z",
            "updated_at": "2025-07-28T08:23:18.000000Z",
            "user": {
                "id": 29,
                "name": "Prima Rudiansah",
                "email": "prima.rudiansah@bps.go.id",
                "email_verified_at": null,
                "current_team_id": null,
                "profile_photo_path": null,
                "created_at": "2024-02-25T09:27:52.000000Z",
                "updated_at": "2024-02-25T09:27:52.000000Z",
                "two_factor_confirmed_at": null,
                "pegawai": {
                    "id": 11,
                    "nama": "Prima Rudiansah, S.Si",
                    "pangkat": "Penata",
                    "gol": "IIIc",
                    "nip": "19851013 2011011 014",
                    "gelar_depan": null,
                    "gelar_belakang": null,
                    "tempat_lahir": null,
                    "tanggal_lahir": null,
                    "alamat": null,
                    "no_hp": null,
                    "jabatan": "Statistisi Ahli Muda",
                    "kelas": 9,
                    "user_id": 29,
                    "status": null,
                    "created_at": "2026-01-02T03:58:56.000000Z",
                    "updated_at": "2026-01-02T03:58:56.000000Z"
                }
            }
        }
    ],
    "meta": {
        "per_page": 15,
        "has_more": false,
        "count": 5
    },
    "links": {
        "next_cursor": null,
        "next_page_url": null,
        "prev_cursor": null,
        "prev_page_url": null,
        "path": "http://127.0.0.1:9001/api/ipds/tikets"
    },
    "per_page": 15,
    "pagination_info": {
        "total_page": 1,
        "total_records": 5
    }
}
```

---
*Generated at: 2026-04-18 20:51:09*
