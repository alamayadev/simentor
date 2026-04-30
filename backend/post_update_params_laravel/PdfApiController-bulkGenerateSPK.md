# `POST` `/api/spk/pdf/bulk-spks`

## Endpoint

| Property | Value |
|----------|-------|
| **HTTP Method** | `POST` |
| **URL** | `/api/spk/pdf/bulk-spks` |
| **Controller** | `App\Http\Controllers\Api\PdfApiController` |
| **Method** | `bulkGenerateSPK()` |

## Body Parameters

| Parameter | Required | Type | Rules |
|-----------|----------|------|-------|
| `ids` | No | `mixed` | from input() |

### Example Request Body

```json
{
    "ids": "string"
}
```

---
*Generated at: 2026-04-18 20:35:30*
