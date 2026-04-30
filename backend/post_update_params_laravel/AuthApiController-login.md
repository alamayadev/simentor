# `POST` `/api/login`

## Endpoint

| Property | Value |
|----------|-------|
| **HTTP Method** | `POST` |
| **URL** | `/api/login` |
| **Controller** | `App\Http\Controllers\Api\AuthApiController` |
| **Method** | `login()` |

## Body Parameters

| Parameter | Required | Type | Rules |
|-----------|----------|------|-------|
| `email` | **Yes** | `string` | required, email |
| `password` | **Yes** | `string` | required, string |

### Required Fields Summary

```
email (string)
password (string)
```

### Example Request Body

```json
{
    "email": "user@example.com",
    "password": "string"
}
```

---
*Generated at: 2026-04-18 20:35:30*
