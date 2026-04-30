# `POST` `/api/kantor/pegawai`

## Endpoint

| Property | Value |
|----------|-------|
| **HTTP Method** | `POST` |
| **URL** | `/api/kantor/pegawai` |
| **Controller** | `App\Http\Controllers\Api\Kantor\PegawaiApiController` |
| **Method** | `store()` |
| **FormRequest** | `App\Http\Requests\Kantor\Pegawai\StorePegawaiRequest` |

## Body Parameters

| Parameter | Required | Type | Rules |
|-----------|----------|------|-------|
| `nama` | **Yes** | `string` | required, string, max:255 |
| `pangkat` | **Yes** | `string` | required, string, max:255 |
| `gol` | **Yes** | `string` | required, string, max:255 |
| `nip` | **Yes** | `string` | required, string, max:255, unique:profil_pegawai,nip |
| `jabatan` | **Yes** | `string` | required, string, max:255 |
| `kelas` | **Yes** | `string` | required, string, max:255 |
| `user_id` | **Yes** | `foreign_key` | required, integer, exists:users,id |
| `status` | No | `string` | nullable, string, max:255 |

### Required Fields Summary

```
nama (string)
pangkat (string)
gol (string)
nip (string)
jabatan (string)
kelas (string)
user_id (foreign_key)
```

### Example Request Body

```json
{
    "nama": "string",
    "pangkat": "string",
    "gol": "string",
    "nip": "string",
    "jabatan": "string",
    "kelas": "string",
    "user_id": 1,
    "status": "string"
}
```

---
*Generated at: 2026-04-18 20:35:30*
