# `POST` `/api/admin/permissions/bulk-create`

## Endpoint

| Property | Value |
|----------|-------|
| **HTTP Method** | `POST` |
| **URL** | `/api/admin/permissions/bulk-create` |
| **Controller** | `App\Http\Controllers\Api\Admin\PermissionsApiController` |
| **Method** | `bulkCreate()` |

## Body Parameters

| Parameter | Required | Type | Rules |
|-----------|----------|------|-------|
| `prefix` | **Yes** | `string` | required, string, max:255 |

### Required Fields Summary

```
prefix (string)
```

### Example Request Body

```json
{
    "prefix": "string"
}
```

---
*Generated at: 2026-04-18 20:35:30*
