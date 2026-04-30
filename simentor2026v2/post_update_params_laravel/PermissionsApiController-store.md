# `POST` `/api/admin/permissions`

## Endpoint

| Property | Value |
|----------|-------|
| **HTTP Method** | `POST` |
| **URL** | `/api/admin/permissions` |
| **Controller** | `App\Http\Controllers\Api\Admin\PermissionsApiController` |
| **Method** | `store()` |

## Body Parameters

| Parameter | Required | Type | Rules |
|-----------|----------|------|-------|
| `name` | **Yes** | `string` | required, string, unique:permissions,name |

### Required Fields Summary

```
name (string)
```

### Example Request Body

```json
{
    "name": "string"
}
```

---
*Generated at: 2026-04-18 20:35:30*
