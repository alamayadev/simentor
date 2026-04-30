# `PUT` `/api/kantor/kegiatan/{id}`

## Endpoint

| Property | Value |
|----------|-------|
| **HTTP Method** | `PUT` |
| **URL** | `/api/kantor/kegiatan/{id}` |
| **Controller** | `App\Http\Controllers\Api\Kantor\KegiatanApiController` |
| **Method** | `update()` |
| **FormRequest** | `App\Http\Requests\Kantor\Kegiatan\UpdateKegiatanRequest` |

## Path Parameters

| Parameter | Description |
|-----------|-------------|
| `id` | Path parameter |

## Body Parameters

| Parameter | Required | Type | Rules |
|-----------|----------|------|-------|
| `tahun` | No | `string` | sometimes, string, size:4 |
| `fungsi` | No | `enum` | sometimes, string, in:Umum,Distribusi,Produksi,Sosial,Nerwilis,IPDS |
| `kode_kegiatan` | No | `string` | sometimes, string, size:11 |
| `nama` | No | `string` | sometimes, string, min:10, max:255 |
| `tgl_mulai` | No | `string` | sometimes, date_format:Y-m-d |
| `tgl_selesai` | No | `string` | sometimes, date_format:Y-m-d |
| `jenis_kegiatan` | No | `enum` | sometimes, string, in:PERSIAPAN,PENGUMPULAN DATA,PENGOLAHAN,DISEMINASI,PENGAWASAN/SUPERVISI |
| `jml_ptgs` | No | `integer` | sometimes, integer, min:1 |
| `volume` | No | `integer` | sometimes, integer, min:1 |
| `satuan` | No | `string` | sometimes, string, max:255 |
| `rate_pcl` | No | `integer` | nullable, integer, min:0 |
| `rate_pml` | No | `integer` | nullable, integer, min:0 |
| `rate_entri` | No | `integer` | nullable, integer, min:0 |
| `status` | No | `enum` | nullable, string, in:aktif,tidak dicairkan,dibatalkan |

### Example Request Body

```json
{
    "tahun": "string",
    "fungsi": "Umum",
    "kode_kegiatan": "string",
    "nama": "string",
    "tgl_mulai": "string",
    "tgl_selesai": "string",
    "jenis_kegiatan": "PERSIAPAN",
    "jml_ptgs": 1,
    "volume": 1,
    "satuan": "string",
    "rate_pcl": 1,
    "rate_pml": 1,
    "rate_entri": 1,
    "status": "aktif"
}
```

---
*Generated at: 2026-04-18 20:35:30*
