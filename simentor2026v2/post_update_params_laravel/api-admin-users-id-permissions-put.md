# `PUT` `/api/admin/users/{id}/permissions`

## Endpoint

| Property | Value |
|----------|-------|
| **HTTP Method** | `PUT` |
| **URL** | `/api/admin/users/{id}/permissions` |
| **Controller** | `App\Http\Controllers\Api\Admin\UserApiController` |
| **Method** | `updatePermissions()` |
| **FormRequest** | `App\Http\Requests\Admin\User\UpdateUserPermissionsRequest` |

## Path Parameters

| Parameter | Description |
|-----------|-------------|
| `id` | Path parameter |

## Body Parameters

| Parameter | Required | Type | Rules |
|-----------|----------|------|-------|
| `permissions` | **Yes** | `array` | required, array |
| `permissions.*` | No | `foreign_key` | string, exists:permissions,name |

### Required Fields Summary

```
permissions (array)
```

### Example Request Body

```json
{
    "permissions": [],
    "permissions.*": 1
}
```

---
*Generated at: 2026-04-18 20:35:30*
