# `PUT` `/api/kantor/surat/surat-tugas/{id}`

## Endpoint

| Property | Value |
|----------|-------|
| **HTTP Method** | `PUT` |
| **URL** | `/api/kantor/surat/surat-tugas/{id}` |
| **Controller** | `App\Http\Controllers\Api\Kantor\NomorSurat\SuratTugasApiController` |
| **Method** | `update()` |

## Path Parameters

| Parameter | Description |
|-----------|-------------|
| `id` | Path parameter |

## Body Parameters

| Parameter | Required | Type | Rules |
|-----------|----------|------|-------|
| `file` | No | `file` | from file() |

### Example Request Body

```json
{
    "file": "(binary file)"
}
```

---
*Generated at: 2026-04-18 20:35:30*
