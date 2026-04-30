# `PUT` `/api/admin/permissions/{id}`

## Endpoint

| Property | Value |
|----------|-------|
| **HTTP Method** | `PUT` |
| **URL** | `/api/admin/permissions/{id}` |
| **Controller** | `App\Http\Controllers\Api\Admin\PermissionsApiController` |
| **Method** | `update()` |

## Path Parameters

| Parameter | Description |
|-----------|-------------|
| `id` | Path parameter |

## Body Parameters

| Parameter | Required | Type | Rules |
|-----------|----------|------|-------|
| `name` | **Yes** | `string` | required, string, unique:permissions,name, |

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
