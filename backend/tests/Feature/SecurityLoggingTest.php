<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityLoggingTest extends TestCase
{
    use RefreshDatabase;

    public function test_failed_login_response()
    {
        // SECURITY FIX: Verify failed login attempts return correct response
        $response = $this->postJson('/api/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
                 ->assertJson([
                     'success' => false,
                     'message' => 'Invalid credentials',
                 ]);
    }

    public function test_successful_login_response()
    {
        // SECURITY FIX: Verify successful login returns correct response
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
        $user->password = 'Test@123'; // Will be hashed by setPasswordAttribute mutator
        $user->save();

        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'Test@123',
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'message',
                     'data' => [
                         'user',
                         'token',
                     ],
                 ]);
    }

    public function test_logout_works_correctly()
    {
        // SECURITY FIX: Verify logout functionality works
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'testlogout@example.com',
        ]);
        $user->password = 'Test@123'; // Will be hashed by setPasswordAttribute mutator
        $user->save();

        // Login first
        $loginResponse = $this->postJson('/api/login', [
            'email' => 'testlogout@example.com',
            'password' => 'Test@123',
        ]);

        $token = $loginResponse->json('data.token');

        // Logout
        $logoutResponse = $this->withToken($token)->postJson('/api/logout');

        $logoutResponse->assertStatus(200)
                      ->assertJson([
                          'success' => true,
                          'message' => 'Logged out',
                      ]);
    }

    public function test_security_log_channel_is_configured()
    {
        // SECURITY FIX: Verify security log channel is properly configured
        $loggingConfig = include config_path('logging.php');

        $this->assertArrayHasKey('security', $loggingConfig['channels']);
        $this->assertEquals('daily', $loggingConfig['channels']['security']['driver']);
        $this->assertEquals(storage_path('logs/security.log'), $loggingConfig['channels']['security']['path']);
        $this->assertEquals('info', $loggingConfig['channels']['security']['level']);
        $this->assertEquals(30, $loggingConfig['channels']['security']['days']);
    }

    public function test_security_log_channel_has_correct_configuration()
    {
        // SECURITY FIX: Verify security log has proper retention and level
        $loggingConfig = include config_path('logging.php');

        // Security logs should be kept for 30 days
        $this->assertEquals(30, $loggingConfig['channels']['security']['days']);

        // Should log at info level or higher
        $this->assertEquals('info', $loggingConfig['channels']['security']['level']);

        // Should use daily rotation
        $this->assertEquals('daily', $loggingConfig['channels']['security']['driver']);
    }
}
