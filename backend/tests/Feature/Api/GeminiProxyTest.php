<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GeminiProxyTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_returns_error_if_api_key_is_not_configured()
    {
        $user = User::factory()->create();
        
        Config::set('services.gemini.key', 'your_actual_key_here');

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/ai/generate', [
                'prompt' => 'Hello',
            ]);

        $response->assertStatus(500)
            ->assertJson([
                'error' => 'Gemini API key is not configured in the backend.'
            ]);
    }

    /** @test */
    public function it_proxies_request_to_google_correctly()
    {
        $user = User::factory()->create();
        $mockApiKey = 'test_api_key';
        Config::set('services.gemini.key', $mockApiKey);
        Config::set('services.gemini.chat_model', 'gemini-1.5-flash');

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    ['content' => ['parts' => [['text' => 'AI Response']]]]
                ]
            ], 200)
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/ai/generate', [
                'prompt' => 'Test Prompt',
                'systemInstruction' => 'Test Instruction',
            ]);

        $response->assertStatus(200)
            ->assertJsonFragment(['text' => 'AI Response']);

        Http::assertSent(function ($request) use ($mockApiKey) {
            return str_contains($request->url(), 'key=' . $mockApiKey) &&
                   $request['contents'][0]['parts'][0]['text'] === 'Test Prompt' &&
                   $request['systemInstruction']['parts'][0]['text'] === 'Test Instruction';
        });
    }

    /** @test */
    public function it_requires_authentication()
    {
        $response = $this->postJson('/api/ai/generate', [
            'prompt' => 'Hello',
        ]);

        $response->assertStatus(401);
    }
}
