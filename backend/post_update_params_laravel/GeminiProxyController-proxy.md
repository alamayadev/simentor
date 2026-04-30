# `POST` `/api/ai/generate`

## Endpoint

| Property | Value |
|----------|-------|
| **HTTP Method** | `POST` |
| **URL** | `/api/ai/generate` |
| **Controller** | `App\Http\Controllers\Api\GeminiProxyController` |
| **Method** | `proxy()` |

## Body Parameters

| Parameter | Required | Type | Rules |
|-----------|----------|------|-------|
| `prompt` | **Yes** | `string` | required, string |
| `systemInstruction` | No | `string` | nullable, string |
| `model` | No | `string` | nullable, string |
| `images` | No | `array` | nullable, array |
| `imageConfig` | No | `array` | nullable, array |
| `generationConfig` | No | `mixed` | from input() |

### Required Fields Summary

```
prompt (string)
```

### Example Request Body

```json
{
    "prompt": "string",
    "systemInstruction": "string",
    "model": "string",
    "images": [],
    "imageConfig": [],
    "generationConfig": "string"
}
```

---
*Generated at: 2026-04-18 20:35:30*
