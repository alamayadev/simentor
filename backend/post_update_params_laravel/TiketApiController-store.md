# `POST` `/api/ipds/tikets`

## Endpoint

| Property | Value |
|----------|-------|
| **HTTP Method** | `POST` |
| **URL** | `/api/ipds/tikets` |
| **Controller** | `App\Http\Controllers\Api\Ipds\TiketApiController` |
| **Method** | `store()` |
| **FormRequest** | `App\Http\Requests\StoreTiketRequest` |

## Body Parameters

| Parameter | Required | Type | Rules |
|-----------|----------|------|-------|
| `jenis_keluhan` | **Yes** | `enum` | required, string, in:Sistem,Software,Printer,Hardware PC/Laptop,Jaringan,Akun BPS,Lainnya |
| `deskripsi` | **Yes** | `string` | required, string |

### Required Fields Summary

```
jenis_keluhan (enum)
deskripsi (string)
```

### Example Request Body

```json
{
    "jenis_keluhan": "Sistem",
    "deskripsi": "string"
}
```

---
*Generated at: 2026-04-18 20:35:30*
