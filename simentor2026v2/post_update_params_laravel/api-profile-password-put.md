# `PUT` `/api/profile/password`

## Endpoint

| Property | Value |
|----------|-------|
| **HTTP Method** | `PUT` |
| **URL** | `/api/profile/password` |
| **Controller** | `App\Http\Controllers\Api\ProfileApiController` |
| **Method** | `updatePassword()` |

## Body Parameters

| Parameter | Required | Type | Rules |
|-----------|----------|------|-------|
| `current_password` | **Yes** | `string` | required, string |
| `password` | **Yes** | `string` | required, string, min:8, confirmed |

### Required Fields Summary

```
current_password (string)
password (string)
```

### Example Request Body

```json
{
    "current_password": "string",
    "password": "string"
}
```

---
*Generated at: 2026-04-18 20:35:30*
