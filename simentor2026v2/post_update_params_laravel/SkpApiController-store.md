# `POST` `/api/kantor/skp`

## Endpoint

| Property | Value |
|----------|-------|
| **HTTP Method** | `POST` |
| **URL** | `/api/kantor/skp` |
| **Controller** | `App\Http\Controllers\Api\Kantor\SkpApiController` |
| **Method** | `store()` |
| **FormRequest** | `App\Http\Requests\Kantor\Skp\StoreSkpRequest` |

## Body Parameters

| Parameter | Required | Type | Rules |
|-----------|----------|------|-------|
| `jenis` | **Yes** | `enum` | required, string, in:SKP Bulanan,SKP Tahunan (Penetapan),SKP Tahunan (Penilaian),SKP Evaluasi Tahunan |
| `bulan` | No | `string` | nullable, string, max:2 |
| `tahun` | **Yes** | `string` | required, string, min:4, max:4 |
| `file` | **Yes** | `file` | required, file, mimes:pdf, max:7168 |

### Required Fields Summary

```
jenis (enum)
tahun (string)
file (file)
```

### Example Request Body

```json
{
    "jenis": "SKP Bulanan",
    "bulan": "string",
    "tahun": "string",
    "file": "(binary file)"
}
```

---
*Generated at: 2026-04-18 20:35:30*
