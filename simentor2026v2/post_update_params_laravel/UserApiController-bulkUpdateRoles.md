# `POST` `/api/admin/users/bulk-update-roles`

## Endpoint

| Property | Value |
|----------|-------|
| **HTTP Method** | `POST` |
| **URL** | `/api/admin/users/bulk-update-roles` |
| **Controller** | `App\Http\Controllers\Api\Admin\UserApiController` |
| **Method** | `bulkUpdateRoles()` |
| **FormRequest** | `App\Http\Requests\Admin\User\BulkUpdateUserRolesRequest` |

## Body Parameters

| Parameter | Required | Type | Rules |
|-----------|----------|------|-------|
| `user_ids` | **Yes** | `array` | required, array |
| `user_ids.*` | **Yes** | `foreign_key` | required, integer, exists:users,id |
| `roles` | **Yes** | `array` | required, array |
| `roles.*` | **Yes** | `foreign_key` | required, string, exists:roles,name |

### Required Fields Summary

```
user_ids (array)
user_ids.* (foreign_key)
roles (array)
roles.* (foreign_key)
```

### Example Request Body

```json
{
    "user_ids": [],
    "user_ids.*": 1,
    "roles": [],
    "roles.*": 1
}
```

---
*Generated at: 2026-04-18 20:35:30*
