# `POST` `/api/ipds/raw-datas`

## Endpoint

| Property | Value |
|----------|-------|
| **HTTP Method** | `POST` |
| **URL** | `/api/ipds/raw-datas` |
| **Controller** | `App\Http\Controllers\Api\Ipds\RawDataApiController` |
| **Method** | `store()` |
| **FormRequest** | `App\Http\Requests\StoreRawDataRequest` |

## Body Parameters

| Parameter | Required | Type | Rules |
|-----------|----------|------|-------|
| `fungsi` | **Yes** | `string` | required, string, max:255 |
| `nama` | **Yes** | `string` | required, string, max:255 |
| `keterangan` | No | `string` | nullable, string |
| `file` | **Yes** | `file` | required, file, mimetypes:text/csv,text/plain,application/csv,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/zip,application/x-rar-compressed, max:51200 |

### Required Fields Summary

```
fungsi (string)
nama (string)
file (file)
```

### Example Request Body

```json
{
    "fungsi": "string",
    "nama": "string",
    "keterangan": "string",
    "file": "(binary file)"
}
```

---
*Generated at: 2026-04-18 20:35:30*
