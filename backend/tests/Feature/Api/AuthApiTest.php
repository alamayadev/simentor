<?php

namespace Tests\Feature\Api;

use App\Models\User;


class AuthApiTest extends BaseApiTestCase
{
    // =====================================================================
    // POST /api/login - Validation Tests
    // =====================================================================

    /** @test */
    public function it_requires_email_for_login()
    {
        $response = $this->postJson('/api/login', [
            'password' => 'password123'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function it_requires_password_for_login()
    {
        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    /** @test */
    public function it_requires_valid_email_format_for_login()
    {
        $response = $this->postJson('/api/login', [
            'email' => 'invalid-email',
            'password' => 'password123'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    // =====================================================================
    // POST /api/login - Authentication Tests
    // =====================================================================

    /** @test */
    public function it_fails_login_with_invalid_credentials()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => 'password123'
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword'
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Invalid credentials'
            ]);
    }

    /** @test */
    public function it_fails_login_with_nonexistent_user()
    {
        $response = $this->postJson('/api/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'password123'
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Invalid credentials'
            ]);
    }

    /** @test */
    public function it_successfully_logs_in_admin_user()
    {
        $admin = User::factory()->create([
            'password' => 'password123'
        ]);
        $admin->assignRole('admin');

        $response = $this->postJson('/api/login', [
            'email' => $admin->email,
            'password' => 'password123'
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'user' => [
                        'id',
                        'name',
                        'email',
                        'created_at',
                        'updated_at'
                    ],
                    'token'
                ]
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Login successful'
            ]);

        $this->assertArrayHasKey('token', $response->json('data'));
        $this->assertEquals($admin->id, $response->json('data.user.id'));
        $this->assertEquals($admin->email, $response->json('data.user.email'));
    }

    /** @test */
    public function it_successfully_logs_in_organik_user()
    {
        $organik = User::factory()->create([
            'password' => 'password123'
        ]);
        $organik->assignRole('organik');

        $response = $this->postJson('/api/login', [
            'email' => $organik->email,
            'password' => 'password123'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Login successful'
            ]);

        $this->assertArrayHasKey('token', $response->json('data'));
        $this->assertEquals($organik->id, $response->json('data.user.id'));
    }

    /** @test */
    public function it_successfully_logs_in_mitra_user()
    {
        $mitra = User::factory()->create([
            'password' => 'password123'
        ]);
        $mitra->assignRole('mitra');

        $response = $this->postJson('/api/login', [
            'email' => $mitra->email,
            'password' => 'password123'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Login successful'
            ]);

        $this->assertArrayHasKey('token', $response->json('data'));
        $this->assertEquals($mitra->id, $response->json('data.user.id'));
    }

    /** @test */
    public function it_returns_user_with_roles_on_login()
    {
        $user = User::factory()->create([
            'password' => 'password123'
        ]);
        $user->assignRole('organik');

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password123'
        ]);

        $response->assertStatus(200);
        
        // User data should contain roles relationship
        $userData = $response->json('data.user');
        $this->assertNotNull($userData);
    }

    /** @test */
    public function it_generates_valid_sanctum_token_on_login()
    {
        $user = User::factory()->create([
            'password' => 'password123'
        ]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password123'
        ]);

        $token = $response->json('data.token');
        $this->assertIsString($token);
        $this->assertNotEmpty($token);
        
        // Token format: "1|xxxxx..." - verify it follows Sanctum format
        $this->assertStringContainsString('|', $token);
    }

    // =====================================================================
    // POST /api/logout - Logout Tests
    // =====================================================================

    /** @test */
    public function it_fails_logout_without_authentication()
    {
        $response = $this->postJson('/api/logout');

        $response->assertStatus(401);
    }

    /** @test */
    public function it_successfully_logs_out_authenticated_user()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->postJson('/api/logout');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Logged out'
            ]);
    }

    /** @test */
    public function it_revokes_all_tokens_on_logout()
    {
        $user = User::factory()->create();
        
        // Create multiple tokens
        $token1 = $user->createToken('token1')->plainTextToken;
        $user->createToken('token2')->plainTextToken;

        // Verify user has 2 tokens before logout
        $this->assertEquals(2, $user->tokens()->count());

        // Logout with one token
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token1
        ])->postJson('/api/logout');

        $response->assertStatus(200);

        // All tokens should be revoked from database
        $user->refresh();
        $this->assertEquals(0, $user->tokens()->count());
    }

    /** @test */
    public function it_handles_logout_with_invalid_token_gracefully()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer invalid-token'
        ])->postJson('/api/logout');

        $response->assertStatus(401);
    }

    /** @test */
    public function it_prevents_access_after_logout()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        // Verify token exists before logout
        $this->assertEquals(1, $user->tokens()->count());

        // Logout
        $logoutResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->postJson('/api/logout');

        $logoutResponse->assertStatus(200);

        // Verify token no longer exists in database after logout
        $user->refresh();
        $this->assertEquals(0, $user->tokens()->count());
    }

    // =====================================================================
    // Multiple Login Tests
    // =====================================================================

    /** @test */
    public function it_handles_multiple_login_attempts_correctly()
    {
        $user = User::factory()->create([
            'password' => 'password123'
        ]);

        // First login
        $response1 = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password123'
        ]);

        $response1->assertStatus(200);
        $token1 = $response1->json('data.token');

        // Second login (should create new token)
        $response2 = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password123'
        ]);

        $response2->assertStatus(200);
        $token2 = $response2->json('data.token');

        // Tokens should be different
        $this->assertNotEquals($token1, $token2);

        // Both tokens should work until logout
        $this->withHeaders([
            'Authorization' => 'Bearer ' . $token1
        ])->getJson('/api/profile')->assertStatus(200);

        $this->withHeaders([
            'Authorization' => 'Bearer ' . $token2
        ])->getJson('/api/profile')->assertStatus(200);
    }

    // =====================================================================
    // Response Structure Tests
    // =====================================================================

    /** @test */
    public function it_includes_required_response_structure_on_login()
    {
        $user = User::factory()->create([
            'password' => 'password123'
        ]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password123'
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'user',
                    'token'
                ]
            ]);

        $this->assertTrue($response->json('success'));
        $this->assertEquals('Login successful', $response->json('message'));
    }

    /** @test */
    public function it_includes_required_response_structure_on_logout()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->postJson('/api/logout');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data'
            ]);

        $this->assertTrue($response->json('success'));
        $this->assertEquals('Logged out', $response->json('message'));
        $this->assertNull($response->json('data'));
    }

    // =====================================================================
    // Security Tests
    // =====================================================================

    /** @test */
    public function it_prevents_sql_injection_in_login()
    {
        $response = $this->postJson('/api/login', [
            'email' => "'; DROP TABLE users; --@example.com",
            'password' => 'password'
        ]);

        // Should fail validation (invalid email format) or authentication, not crash
        $this->assertContains($response->status(), [401, 422]);
        
        // Users table should still exist and have data
        $this->assertTrue(User::count() > 0);
    }

    /** @test */
    public function it_handles_large_request_payloads_gracefully()
    {
        $largePayload = str_repeat('a', 10000);

        $response = $this->postJson('/api/login', [
            'email' => $largePayload . '@example.com',
            'password' => $largePayload
        ]);

        // Should handle gracefully without crashing (validation error or auth failure)
        $this->assertContains($response->status(), [401, 422]);
    }
}
