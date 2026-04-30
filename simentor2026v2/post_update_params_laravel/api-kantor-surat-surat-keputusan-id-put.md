# `PUT` `/api/kantor/surat/surat-keputusan/{id}`

## Endpoint

| Property | Value |
|----------|-------|
| **HTTP Method** | `PUT` |
| **URL** | `/api/kantor/surat/surat-keputusan/{id}` |
| **Controller** | `App\Http\Controllers\Api\Kantor\NomorSurat\SuratKeputusanApiController` |
| **Method** | `update()` |

## Path Parameters

| Parameter | Description |
|-----------|-------------|
| `id` | Path parameter |

## Body Parameters

| Parameter | Required | Type | Rules |
|-----------|----------|------|-------|
| `tanggal` | No | `mixed` | from only() |
| `oleh` | No | `mixed` | from only() |
| `kegiatan` | No | `mixed` | from only() |
| `kepada` | No | `mixed` | from only() |
| `perihal` | No | `mixed` | from only() |
| `kode_klas` | No | `mixed` | from only() |
| `kol_lampiran` | No | `mixed` | from only() |

### Example Request Body

```json
{
    "tanggal": "2025-01-01",
    "oleh": "string",
    "kegiatan": "string",
    "kepada": "string",
    "perihal": "string",
    "kode_klas": "string",
    "kol_lampiran": "string"
}
```

---
*Generated at: 2026-04-18 20:35:30*
