# `POST` `/api/admin/users`

## Endpoint

| Property | Value |
|----------|-------|
| **HTTP Method** | `POST` |
| **URL** | `/api/admin/users` |
| **Controller** | `App\Http\Controllers\Api\Admin\UserApiController` |
| **Method** | `store()` |
| **FormRequest** | `App\Http\Requests\Admin\User\StoreUserRequest` |

## Body Parameters

| Parameter | Required | Type | Rules |
|-----------|----------|------|-------|
| `name` | **Yes** | `string` | required, string, max:255 |
| `email` | **Yes** | `string` | required, email, unique:users,email |
| `password` | **Yes** | `string` | required, string, min:8 |
| `roles` | No | `array` | sometimes, array |
| `roles.*` | No | `foreign_key` | string, exists:roles,name |
| `permissions` | No | `array` | sometimes, array |
| `permissions.*` | No | `foreign_key` | string, exists:permissions,name |

### Required Fields Summary

```
name (string)
email (string)
password (string)
```

### Example Request Body

```json
{
    "name": "string",
    "email": "user@example.com",
    "password": "string",
    "roles": [],
    "roles.*": 1,
    "permissions": [],
    "permissions.*": 1
}
```

---
*Generated at: 2026-04-18 20:35:30*
