# `POST` `/api/kantor/links`

## Endpoint

| Property | Value |
|----------|-------|
| **HTTP Method** | `POST` |
| **URL** | `/api/kantor/links` |
| **Controller** | `App\Http\Controllers\Api\Kantor\LinkApiController` |
| **Method** | `store()` |
| **FormRequest** | `App\Http\Requests\LinkRequest` |

## Body Parameters

| Parameter | Required | Type | Rules |
|-----------|----------|------|-------|
| `nama` | **Yes** | `string` | required, string, max:255 |
| `link` | No | `string` | nullable, string, max:2048 |
| `parent_id` | No | `foreign_key` | nullable, integer, exists:links,id |

### Required Fields Summary

```
nama (string)
```

### Example Request Body

```json
{
    "nama": "string",
    "link": "string",
    "parent_id": 1
}
```

---
*Generated at: 2026-04-18 20:35:30*
