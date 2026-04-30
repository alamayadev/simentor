# `POST` `/api/kantor/kegiatan`

## Endpoint

| Property | Value |
|----------|-------|
| **HTTP Method** | `POST` |
| **URL** | `/api/kantor/kegiatan` |
| **Controller** | `App\Http\Controllers\Api\Kantor\KegiatanApiController` |
| **Method** | `store()` |
| **FormRequest** | `App\Http\Requests\Kantor\Kegiatan\StoreKegiatanRequest` |

## Body Parameters

| Parameter | Required | Type | Rules |
|-----------|----------|------|-------|
| `tahun` | **Yes** | `string` | required, string, size:4 |
| `fungsi` | **Yes** | `enum` | required, string, in:Umum,Distribusi,Produksi,Sosial,Nerwilis,IPDS |
| `kode_kegiatan` | **Yes** | `string` | required, string, size:11 |
| `nama` | **Yes** | `string` | required, string, min:10, max:255 |
| `tgl_mulai` | **Yes** | `string` | required, date_format:Y-m-d |
| `tgl_selesai` | **Yes** | `string` | required, date_format:Y-m-d |
| `jenis_kegiatan` | **Yes** | `enum` | required, string, in:PERSIAPAN,PENGUMPULAN DATA,PENGOLAHAN,DISEMINASI,PENGAWASAN/SUPERVISI |
| `jml_ptgs` | **Yes** | `integer` | required, integer, min:1 |
| `volume` | **Yes** | `integer` | required, integer, min:1 |
| `satuan` | **Yes** | `string` | required, string, max:255 |
| `rate_pcl` | No | `integer` | nullable, integer, min:0 |
| `rate_pml` | No | `integer` | nullable, integer, min:0 |
| `rate_entri` | No | `integer` | nullable, integer, min:0 |
| `status` | No | `enum` | nullable, string, in:aktif,tidak dicairkan,dibatalkan |

### Required Fields Summary

```
tahun (string)
fungsi (enum)
kode_kegiatan (string)
nama (string)
tgl_mulai (string)
tgl_selesai (string)
jenis_kegiatan (enum)
jml_ptgs (integer)
volume (integer)
satuan (string)
```

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
