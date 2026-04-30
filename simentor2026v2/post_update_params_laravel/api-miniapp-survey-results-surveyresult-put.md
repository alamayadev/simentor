# `PUT` `/api/miniapp/survey-results/{survey_result}`

## Endpoint

| Property | Value |
|----------|-------|
| **HTTP Method** | `PUT` |
| **URL** | `/api/miniapp/survey-results/{survey_result}` |
| **Controller** | `App\Http\Controllers\Api\Miniapp\Surveys\Result\SurveyResultController` |
| **Method** | `update()` |

## Path Parameters

| Parameter | Description |
|-----------|-------------|
| `survey_result` | Path parameter |

## Body Parameters

| Parameter | Required | Type | Rules |
|-----------|----------|------|-------|
| `survey_id` | **Yes** | `foreign_key` | sometimes, required, exists:surveys,id |
| `survey_result` | **Yes** | `array` | sometimes, required, array |

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
