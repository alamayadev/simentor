# `POST` `/api/kantor/surat/permintaan/bulk-update-status`

## Endpoint

| Property | Value |
|----------|-------|
| **HTTP Method** | `POST` |
| **URL** | `/api/kantor/surat/permintaan/bulk-update-status` |
| **Controller** | `App\Http\Controllers\Api\Kantor\NomorSurat\PermintaanApiController` |
| **Method** | `bulkUpdateStatus()` |

## Body Parameters

| Parameter | Required | Type | Rules |
|-----------|----------|------|-------|
| `ids` | No | `mixed` | from input() |
| `status` | No | `mixed` | from input() |

### Example Request Body

```json
{
    "ids": "string",
    "status": "string"
}
```

---
*Generated at: 2026-04-18 20:35:30*
