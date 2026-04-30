# `POST` `/api/kantor/penugasan`

## Endpoint

| Property | Value |
|----------|-------|
| **HTTP Method** | `POST` |
| **URL** | `/api/kantor/penugasan` |
| **Controller** | `App\Http\Controllers\Api\Kantor\PenugasanApiController` |
| **Method** | `store()` |
| **FormRequest** | `App\Http\Requests\Kantor\Penugasan\StorePenugasanRequest` |

## Body Parameters

| Parameter | Required | Type | Rules |
|-----------|----------|------|-------|
| `kegiatan_id` | **Yes** | `foreign_key` | required, integer, exists:kegiatan,id |
| `jabatan_tugas` | **Yes** | `string` | required, string, max:255 |
| `mitra_id` | No | `foreign_key` | nullable, integer, exists:mitra_kepka,id |
| `pegawai_id` | No | `foreign_key` | nullable, integer, exists:profil_pegawai,id |
| `volume` | **Yes** | `integer` | required, integer, min:1 |
| `bln_bayar` | No | `string` | nullable, date_format:Y-m-d |

### Required Fields Summary

```
kegiatan_id (foreign_key)
jabatan_tugas (string)
volume (integer)
```

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
