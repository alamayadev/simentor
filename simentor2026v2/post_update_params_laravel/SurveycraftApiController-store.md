# `POST` `/api/miniapp/surveycraft`

## Endpoint

| Property | Value |
|----------|-------|
| **HTTP Method** | `POST` |
| **URL** | `/api/miniapp/surveycraft` |
| **Controller** | `App\Http\Controllers\Api\Miniapp\SurveycraftApiController` |
| **Method** | `store()` |
| **FormRequest** | `App\Http\Requests\Miniapp\Surveycraft\StoreSurveycraftRequest` |

## Body Parameters

| Parameter | Required | Type | Rules |
|-----------|----------|------|-------|
| `survey_id` | **Yes** | `string` | required, string, max:255 |
| `title` | **Yes** | `string` | required, string, max:255 |
| `questions` | No | `integer` | sometimes, nullable, integer |
| `description` | No | `string` | nullable, string |
| `json_file` | **Yes** | `file` | required, file, mimes:json, max:2048 |
| `published_link` | No | `string` | nullable, string, max:255 |
| `owner_id` | **Yes** | `integer` | required, integer, Illuminate\Validation\Rules\Exists |
| `timestamp` | No | `date` | nullable, date |

### Required Fields Summary

```
survey_id (string)
title (string)
json_file (file)
owner_id (integer)
```

### Example Request Body

```json
{
    "survey_id": 1,
    "title": "string",
    "questions": 1,
    "description": "string",
    "json_file": "(binary file)",
    "published_link": "string",
    "owner_id": 1,
    "timestamp": "2025-01-01"
}
```

---
*Generated at: 2026-04-18 20:35:30*
