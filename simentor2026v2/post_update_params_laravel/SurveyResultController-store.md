# `POST` `/api/miniapp/survey-results`

## Endpoint

| Property | Value |
|----------|-------|
| **HTTP Method** | `POST` |
| **URL** | `/api/miniapp/survey-results` |
| **Controller** | `App\Http\Controllers\Api\Miniapp\Surveys\Result\SurveyResultController` |
| **Method** | `store()` |

## Body Parameters

| Parameter | Required | Type | Rules |
|-----------|----------|------|-------|
| `survey_id` | **Yes** | `foreign_key` | required, exists:surveys,id |
| `survey_result` | **Yes** | `array` | required, array |

### Required Fields Summary

```
survey_id (foreign_key)
survey_result (array)
```

### Example Request Body

```json
{
    "survey_id": 1,
    "survey_result": []
}
```

---
*Generated at: 2026-04-18 20:35:30*
