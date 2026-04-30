# GET `/kantor/notulensi?per_page=15&page=1`

## Endpoint Info

| Property | Value |
|----------|-------|
| **Method** | `GET` |
| **URL** | `/kantor/notulensi?per_page=15&page=1` |
| **Description** | Notulensi list |
| **Query Params Used** | `per_page=15&page=1` |
| **HTTP Status** | `200` |
| **Response Time** | 0.024s |
| **Generated** | 2026-04-18 20:51:09 |

## Response

```json
{
    "success": true,
    "message": "Notulensi list retrieved successfully",
    "data": [],
    "meta": {
        "per_page": 15,
        "has_more": false,
        "count": 0
    },
    "links": {
        "next_cursor": null,
        "next_page_url": null,
        "prev_cursor": null,
        "prev_page_url": null,
        "path": "http://127.0.0.1:9001/api/kantor/notulensi"
    },
    "per_page": 15
}
```

---
*Generated at: 2026-04-18 20:51:09*
