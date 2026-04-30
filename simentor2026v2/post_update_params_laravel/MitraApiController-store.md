# `POST` `/api/kantor/mitra/penugasan`

## Endpoint

| Property | Value |
|----------|-------|
| **HTTP Method** | `POST` |
| **URL** | `/api/kantor/mitra/penugasan` |
| **Controller** | `App\Http\Controllers\Api\Kantor\MitraApiController` |
| **Method** | `store()` |
| **FormRequest** | `App\Http\Requests\Kantor\Mitra\StoreMitraPenugasanRequest` |

## Body Parameters

| Parameter | Required | Type | Rules |
|-----------|----------|------|-------|
| `kegiatan_id` | **Yes** | `foreign_key` | required, exists:kegiatan,id |
| `jabatan_tugas` | **Yes** | `enum` | required, in:PCL,PML,OPERATOR |
| `mitra_id` | **Yes** | `foreign_key` | required, exists:mitra_kepka,id |
| `volume` | **Yes** | `numeric` | required, numeric, min:1 |
| `bln_bayar` | **Yes** | `date` | required, date |

### Required Fields Summary

```
kegiatan_id (foreign_key)
jabatan_tugas (enum)
mitra_id (foreign_key)
volume (numeric)
bln_bayar (date)
```

### Example Request Body

```json
{
    "kegiatan_id": 1,
    "jabatan_tugas": "PCL",
    "mitra_id": 1,
    "volume": 1,
    "bln_bayar": "2025-01-01"
}
```

---
*Generated at: 2026-04-18 20:35:30*
