# `PUT` `/api/profile`

## Endpoint

| Property | Value |
|----------|-------|
| **HTTP Method** | `PUT` |
| **URL** | `/api/profile` |
| **Controller** | `App\Http\Controllers\Api\ProfileApiController` |
| **Method** | `update()` |

## Body Parameters

| Parameter | Required | Type | Rules |
|-----------|----------|------|-------|
| `name` | **Yes** | `string` | required, string, max:255 |
| `email` | **Yes** | `string` | required, string, email, max:255, unique:users,email, |

### Required Fields Summary

```
name (string)
email (string)
```

### Example Request Body

```json
{
    "name": "string",
    "email": "user@example.com"
}
```

---
*Generated at: 2026-04-18 20:35:30*
