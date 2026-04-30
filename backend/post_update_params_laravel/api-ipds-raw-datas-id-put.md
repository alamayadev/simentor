# `PUT` `/api/ipds/raw-datas/{id}`

## Endpoint

| Property | Value |
|----------|-------|
| **HTTP Method** | `PUT` |
| **URL** | `/api/ipds/raw-datas/{id}` |
| **Controller** | `App\Http\Controllers\Api\Ipds\RawDataApiController` |
| **Method** | `update()` |
| **FormRequest** | `App\Http\Requests\UpdateRawDataRequest` |

## Path Parameters

| Parameter | Description |
|-----------|-------------|
| `id` | Path parameter |

## Body Parameters

| Parameter | Required | Type | Rules |
|-----------|----------|------|-------|
| `fungsi` | **Yes** | `string` | sometimes, required, string, max:255 |
| `nama` | **Yes** | `string` | sometimes, required, string, max:255 |
| `keterangan` | No | `string` | sometimes, nullable, string |
| `file` | **Yes** | `file` | sometimes, required, file, mimetypes:text/csv,text/plain,application/csv,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/zip,application/x-rar-compressed, max:51200 |

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
