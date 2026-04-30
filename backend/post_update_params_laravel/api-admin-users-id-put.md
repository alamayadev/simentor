# `PUT` `/api/admin/users/{id}`

## Endpoint

| Property | Value |
|----------|-------|
| **HTTP Method** | `PUT` |
| **URL** | `/api/admin/users/{id}` |
| **Controller** | `App\Http\Controllers\Api\Admin\UserApiController` |
| **Method** | `update()` |
| **FormRequest** | `App\Http\Requests\Admin\User\UpdateUserRequest` |

## Path Parameters

| Parameter | Description |
|-----------|-------------|
| `id` | Path parameter |

## Body Parameters

| Parameter | Required | Type | Rules |
|-----------|----------|------|-------|
| `name` | No | `string` | sometimes, string, max:255 |
| `email` | No | `string` | sometimes, email, unique:users,email, |
| `password` | No | `string` | sometimes, string, min:8, regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/ |
| `roles` | No | `array` | sometimes, array |
| `roles.*` | No | `foreign_key` | string, exists:roles,name |
| `permissions` | No | `array` | sometimes, array |
| `permissions.*` | No | `foreign_key` | string, exists:permissions,name |

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
