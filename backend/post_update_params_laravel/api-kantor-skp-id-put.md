# `PUT` `/api/kantor/skp/{id}`

## Endpoint

| Property | Value |
|----------|-------|
| **HTTP Method** | `PUT` |
| **URL** | `/api/kantor/skp/{id}` |
| **Controller** | `App\Http\Controllers\Api\Kantor\SkpApiController` |
| **Method** | `update()` |
| **FormRequest** | `App\Http\Requests\Kantor\Skp\UpdateSkpRequest` |

## Path Parameters

| Parameter | Description |
|-----------|-------------|
| `id` | Path parameter |

## Body Parameters

| Parameter | Required | Type | Rules |
|-----------|----------|------|-------|
| `jenis` | No | `enum` | sometimes, string, in:SKP Bulanan,SKP Tahunan (Penetapan),SKP Tahunan (Penilaian),SKP Evaluasi Tahunan |
| `bulan` | No | `string` | nullable, string, max:2 |
| `tahun` | No | `string` | sometimes, string, min:4, max:4 |
| `file` | No | `file` | nullable, file, mimes:pdf, max:7168 |

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
