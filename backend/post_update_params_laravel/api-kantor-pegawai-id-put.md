# `PUT` `/api/kantor/pegawai/{id}`

## Endpoint

| Property | Value |
|----------|-------|
| **HTTP Method** | `PUT` |
| **URL** | `/api/kantor/pegawai/{id}` |
| **Controller** | `App\Http\Controllers\Api\Kantor\PegawaiApiController` |
| **Method** | `update()` |
| **FormRequest** | `App\Http\Requests\Kantor\Pegawai\UpdatePegawaiRequest` |

## Path Parameters

| Parameter | Description |
|-----------|-------------|
| `id` | Path parameter |

## Body Parameters

| Parameter | Required | Type | Rules |
|-----------|----------|------|-------|
| `nama` | **Yes** | `string` | sometimes, required, string, max:255 |
| `pangkat` | **Yes** | `string` | sometimes, required, string, max:255 |
| `gol` | **Yes** | `string` | sometimes, required, string, max:255 |
| `nip` | **Yes** | `string` | sometimes, required, string, max:255, unique:profil_pegawai,nip, |
| `jabatan` | **Yes** | `string` | sometimes, required, string, max:255 |
| `kelas` | **Yes** | `string` | sometimes, required, string, max:255 |
| `user_id` | **Yes** | `foreign_key` | sometimes, required, integer, exists:users,id |
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
