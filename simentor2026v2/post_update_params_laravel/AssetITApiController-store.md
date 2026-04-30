# `POST` `/api/ipds/assets`

## Endpoint

| Property | Value |
|----------|-------|
| **HTTP Method** | `POST` |
| **URL** | `/api/ipds/assets` |
| **Controller** | `App\Http\Controllers\Api\Ipds\AssetITApiController` |
| **Method** | `store()` |

## Body Parameters

| Parameter | Required | Type | Rules |
|-----------|----------|------|-------|
| `kode_asset` | **Yes** | `string` | required, string, unique:asset_it,kode_asset |
| `type` | **Yes** | `enum` | required, in:hardware,software,network |
| `category` | **Yes** | `string` | required, string |
| `brand` | No | `string` | nullable, string |
| `model` | No | `string` | nullable, string |
| `serial_number` | No | `string` | nullable, string |
| `name` | No | `string` | nullable, string |
| `license_key` | No | `string` | nullable, string |
| `device` | No | `string` | nullable, string |
| `ip_address` | No | `string` | nullable, string |
| `location` | No | `string` | nullable, string |
| `status` | **Yes** | `enum` | required, in:active,in-use,maintenance,inactive |
| `assigned_to` | No | `string` | nullable, string |
| `purchase_date` | No | `date` | nullable, date |
| `warranty_expiry` | No | `date` | nullable, date |
| `expiry_date` | No | `date` | nullable, date |
| `delivery_date` | No | `date` | nullable, date |

### Required Fields Summary

```
kode_asset (string)
type (enum)
category (string)
status (enum)
```

### Example Request Body

```json
{
    "kode_asset": "string",
    "type": "hardware",
    "category": "string",
    "brand": "string",
    "model": "string",
    "serial_number": "string",
    "name": "string",
    "license_key": "string",
    "device": "string",
    "ip_address": "string",
    "location": "string",
    "status": "active",
    "assigned_to": "string",
    "purchase_date": "2025-01-01",
    "warranty_expiry": "2025-01-01",
    "expiry_date": "2025-01-01",
    "delivery_date": "2025-01-01"
}
```

---
*Generated at: 2026-04-18 20:35:30*
