# `POST` `/api/miniapp/surveycraft/respond`

## Endpoint

| Property | Value |
|----------|-------|
| **HTTP Method** | `POST` |
| **URL** | `/api/miniapp/surveycraft/respond` |
| **Controller** | `App\Http\Controllers\Api\Miniapp\SurveycraftApiController` |
| **Method** | `respond_store()` |
| **FormRequest** | `App\Http\Requests\Miniapp\Surveycraft\StoreSurveyResponseRequest` |

## Body Parameters

| Parameter | Required | Type | Rules |
|-----------|----------|------|-------|
| `respond_id` | **Yes** | `string` | required, string, max:255, regex:/^[A-Za-z0-9._-]+$/ |
| `json_file` | **Yes** | `file` | required, file, mimes:json, max:2048 |

### Required Fields Summary

```
respond_id (string)
json_file (file)
```

### Example Request Body

```json
{
    "respond_id": 1,
    "json_file": "(binary file)"
}
```

---
*Generated at: 2026-04-18 20:35:30*
