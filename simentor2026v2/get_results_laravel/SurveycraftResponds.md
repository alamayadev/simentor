# GET `/miniapp/surveycraft/responds?survey_id=1`

## Endpoint Info

| Property | Value |
|----------|-------|
| **Method** | `GET` |
| **URL** | `/miniapp/surveycraft/responds?survey_id=1` |
| **Description** | Responds |
| **Query Params Used** | `survey_id` |
| **HTTP Status** | `200` |
| **Response Time** | 0.05s |
| **Generated** | 2026-04-18 20:51:09 |

## Response

```json
{
    "success": true,
    "message": null,
    "data": {
        "survey_id": "1",
        "responses": [
            {
                "id": "11f13d08-d9ba-40c2-9663-2e05b982a881_resp-1766259366012",
                "survey_id": "1",
                "submitted_at": "2026-01-08T14:26:57Z",
                "answers": {
                    "id": "c7f136f7-a79f-48e0-81e6-2cce0eac1013",
                    "surveyId": "11f13d08-d9ba-40c2-9663-2e05b982a881",
                    "submittedAt": "2025-12-20T19:36:06.011Z",
                    "answers": {
                        "q1": [
                            "opt1"
                        ],
                        "q2": 5,
                        "q3": "mantap"
                    }
                },
                "file_url": "http://127.0.0.1:9001/api/surveycraft/file/respond/11f13d08-d9ba-40c2-9663-2e05b982a881_resp-1766259366012"
            }
        ]
    }
}
```

---
*Generated at: 2026-04-18 20:51:09*
