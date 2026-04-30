<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * @group AI Service
 * 
 * Controller for proxying requests to Google's Generative Language API (Gemini).
 * Provides a backend relay for frontend applications to securely access AI features.
 */
class GeminiProxyController extends Controller
{
    /**
     * Proxy request to Google's Generative Language API.
     * 
     * Relays prompts and configurations to the Gemini API and returns the AI's response.
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 
     * @example Request: POST /api/ai/generate with {"prompt": "Hello", "model": "gemini-3-pro-preview"}
     * @example Response: {"candidates": [{"content": {"parts": [{"text": "Hello! How can I help you today?"}]}}], ...}
     */
    public function proxy(Request $request)
    {
        // 1. Validate the request
        $request->validate([
            'prompt' => 'required|string',
            'systemInstruction' => 'nullable|string',
            'model' => 'nullable|string',
            'images' => 'nullable|array',
            'imageConfig' => 'nullable|array',
        ]);

        $apiKey = config('services.gemini.key');
        
        if (!$apiKey || $apiKey === 'your_actual_key_here') {
            return response()->json([
                'error' => 'Gemini API key is not configured in the backend.'
            ], 500);
        }

        // Support dynamic model selection from frontend
        $model = $request->input('model', config('services.gemini.chat_model', 'gemini-3-flash-preview'));
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key=" . $apiKey;

        // 2. Prepare contents
        $contents = [['parts' => [['text' => $request->prompt]]]];
        
        if ($request->has('images')) {
            foreach ($request->images as $image) {
                // If it's a data URL, strip the prefix
                $base64Data = $image;
                if (preg_match('/^data:image\/(\w+);base64,/', $image, $match)) {
                    $base64Data = substr($image, strpos($image, ',') + 1);
                }

                $contents[0]['parts'][] = [
                    'inlineData' => [
                        'mimeType' => 'image/jpeg', // Defaulting to jpeg, can be more robust
                        'data' => $base64Data
                    ]
                ];
            }
        }

        // 3. Prepare payload
        $payload = [
            'contents' => $contents,
        ];

        // Add system instruction if provided (for chat models)
        if ($request->has('systemInstruction')) {
            $payload['systemInstruction'] = [
                'parts' => [['text' => $request->systemInstruction]]
            ];
        }

        // Add generationConfig for generation models
        if ($request->has('imageConfig') || $request->has('generationConfig')) {
            $payload['generationConfig'] = $request->input('generationConfig', []);
            if ($request->has('imageConfig')) {
                $payload['generationConfig']['imageConfig'] = $request->imageConfig;
            }
        }

        try {
            // 4. Forward the request to Google
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post($url, $payload);

            if ($response->failed()) {
                Log::error('Gemini Proxy Error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'url' => $url,
                ]);
            }

            return response()->json($response->json(), $response->status());
        } catch (\Exception $e) {
            Log::error('Gemini Proxy Exception', [
                'message' => $e->getMessage(),
            ]);
            return response()->json([
                'error' => 'Failed to connect to Gemini API',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
