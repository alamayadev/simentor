# `PUT` `/api/admin/roles/{id}/permissions`

## Endpoint

| Property | Value |
|----------|-------|
| **HTTP Method** | `PUT` |
| **URL** | `/api/admin/roles/{id}/permissions` |
| **Controller** | `App\Http\Controllers\Api\Admin\RolesApiController` |
| **Method** | `updatePermissions()` |

## Path Parameters

| Parameter | Description |
|-----------|-------------|
| `id` | Path parameter |

## Body Parameters

| Parameter | Required | Type | Rules |
|-----------|----------|------|-------|
| `permission_ids` | **Yes** | `array` | required, array |

### Required Fields Summary

```
permission_ids (array)
```

### Example Request Body

```json
{
    "permission_ids": []
}
```

---
*Generated at: 2026-04-18 20:35:30*
