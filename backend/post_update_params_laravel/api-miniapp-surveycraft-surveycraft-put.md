# `PUT` `/api/miniapp/surveycraft/{surveycraft}`

## Endpoint

| Property | Value |
|----------|-------|
| **HTTP Method** | `PUT` |
| **URL** | `/api/miniapp/surveycraft/{surveycraft}` |
| **Controller** | `App\Http\Controllers\Api\Miniapp\SurveycraftApiController` |
| **Method** | `update()` |
| **FormRequest** | `App\Http\Requests\Miniapp\Surveycraft\UpdateSurveycraftRequest` |

## Path Parameters

| Parameter | Description |
|-----------|-------------|
| `surveycraft` | Path parameter |

## Body Parameters

| Parameter | Required | Type | Rules |
|-----------|----------|------|-------|
| `survey_id` | No | `string` | sometimes, string, max:255 |
| `title` | No | `string` | sometimes, string, max:255 |
| `description` | No | `string` | sometimes, nullable, string |
| `json_file` | No | `file` | sometimes, file, mimes:json, max:10240 |
| `published_link` | No | `string` | sometimes, nullable, string, max:255 |
| `owner_id` | No | `integer` | sometimes, integer, Illuminate\Validation\Rules\Exists |
| `timestamp` | No | `date` | sometimes, nullable, date |
| `questions` | No | `integer` | sometimes, nullable, integer |

### Example Request Body

```json
{
    "survey_id": 1,
    "title": "string",
    "description": "string",
    "json_file": "(binary file)",
    "published_link": "string",
    "owner_id": 1,
    "timestamp": "2025-01-01",
    "questions": 1
}
```

---
*Generated at: 2026-04-18 20:35:30*
