# `POST` `/api/miniapp/surveycraft/test-generate-ai`

## Endpoint

| Property | Value |
|----------|-------|
| **HTTP Method** | `POST` |
| **URL** | `/api/miniapp/surveycraft/test-generate-ai` |
| **Controller** | `App\Http\Controllers\Api\Miniapp\SurveycraftApiController` |
| **Method** | `generateWithAI()` |
| **FormRequest** | `App\Http\Requests\Miniapp\Surveycraft\GenerateSurveyAIRequest` |

## Body Parameters

| Parameter | Required | Type | Rules |
|-----------|----------|------|-------|
| `prompt` | **Yes** | `string` | required, string, max:1000 |
| `lang` | **Yes** | `enum` | required, string, in:en,id |

### Required Fields Summary

```
prompt (string)
lang (enum)
```

### Example Request Body

```json
{
    "prompt": "string",
    "lang": "en"
}
```

---
*Generated at: 2026-04-18 20:35:30*
