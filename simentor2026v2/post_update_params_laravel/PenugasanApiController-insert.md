# `POST` `/api/kantor/penugasan/{id}/insert`

## Endpoint

| Property | Value |
|----------|-------|
| **HTTP Method** | `POST` |
| **URL** | `/api/kantor/penugasan/{id}/insert` |
| **Controller** | `App\Http\Controllers\Api\Kantor\PenugasanApiController` |
| **Method** | `insert()` |
| **FormRequest** | `App\Http\Requests\Kantor\Penugasan\InsertPenugasanRequest` |

## Path Parameters

| Parameter | Description |
|-----------|-------------|
| `id` | Path parameter |

## Body Parameters

| Parameter | Required | Type | Rules |
|-----------|----------|------|-------|
| `kegiatan_id` | No | `foreign_key` | sometimes, integer, exists:kegiatan,id |
| `jabatan_tugas` | No | `string` | sometimes, string, max:255 |
| `mitra_id` | No | `foreign_key` | nullable, integer, exists:mitra_kepka,id |
| `pegawai_id` | No | `foreign_key` | nullable, integer, exists:profil_pegawai,id |
| `volume` | No | `integer` | sometimes, integer, min:1 |
| `bln_bayar` | No | `string` | nullable, date_format:Y-m-d |

### Example Request Body

```json
{
    "kegiatan_id": 1,
    "jabatan_tugas": "string",
    "mitra_id": 1,
    "pegawai_id": 1,
    "volume": 1,
    "bln_bayar": "string"
}
```

---
*Generated at: 2026-04-18 20:35:30*
