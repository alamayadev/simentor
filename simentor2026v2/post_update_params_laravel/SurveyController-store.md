# `POST` `/api/miniapp/survey`

## Endpoint

| Property | Value |
|----------|-------|
| **HTTP Method** | `POST` |
| **URL** | `/api/miniapp/survey` |
| **Controller** | `App\Http\Controllers\Api\Miniapp\SurveyController` |
| **Method** | `store()` |

## Body Parameters

| Parameter | Required | Type | Rules |
|-----------|----------|------|-------|
| `survey_name` | **Yes** | `string` | required, string, max:255 |
| `survey_description` | No | `string` | nullable, string |
| `json_file` | No | `array` | nullable, array |
| `source_json` | No | `array` | nullable, array |
| `validation_rules` | No | `array` | nullable, array |
| `start_at` | No | `date` | nullable, date |
| `end_at` | No | `date` | nullable, date, after_or_equal:start_at |

### Required Fields Summary

```
survey_name (string)
```

### Example Request Body

```json
{
    "survey_name": "string",
    "survey_description": "string",
    "json_file": [],
    "source_json": [],
    "validation_rules": [],
    "start_at": "2025-01-01",
    "end_at": "2025-01-01"
}
```

---
*Generated at: 2026-04-18 20:35:30*
