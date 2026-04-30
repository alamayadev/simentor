# `POST` `/api/admin/roles`

## Endpoint

| Property | Value |
|----------|-------|
| **HTTP Method** | `POST` |
| **URL** | `/api/admin/roles` |
| **Controller** | `App\Http\Controllers\Api\Admin\RolesApiController` |
| **Method** | `store()` |

## Body Parameters

| Parameter | Required | Type | Rules |
|-----------|----------|------|-------|
| `name` | **Yes** | `string` | required, string, unique:roles,name |

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
