<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityHeadersTest extends TestCase
{
    public function test_api_response_has_content_security_policy_header()
    {
        // SECURITY FIX: Verify API responses include CSP header
        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $this->assertNotNull($response->headers->get('Content-Security-Policy'));
        $csp = $response->headers->get('Content-Security-Policy');

        // Verify CSP includes important directives
        $this->assertStringContainsString('default-src', $csp);
        $this->assertStringContainsString('script-src', $csp);
        $this->assertStringContainsString('connect-src', $csp);
    }

    public function test_api_response_has_x_content_type_options_header()
    {
        // SECURITY FIX: Verify responses include X-Content-Type-Options header
        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $this->assertEquals('nosniff', $response->headers->get('X-Content-Type-Options'));
    }

    public function test_api_response_has_x_frame_options_header()
    {
        // SECURITY FIX: Verify responses include X-Frame-Options header
        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $this->assertEquals('DENY', $response->headers->get('X-Frame-Options'));
    }

    public function test_api_response_has_x_xss_protection_header()
    {
        // SECURITY FIX: Verify responses include X-XSS-Protection header
        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $this->assertEquals('1; mode=block', $response->headers->get('X-XSS-Protection'));
    }

    public function test_api_response_has_referrer_policy_header()
    {
        // SECURITY FIX: Verify responses include Referrer-Policy header
        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $this->assertEquals('strict-origin-when-cross-origin', $response->headers->get('Referrer-Policy'));
    }

    public function test_api_response_has_permissions_policy_header()
    {
        // SECURITY FIX: Verify responses include Permissions-Policy header
        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $this->assertNotNull($response->headers->get('Permissions-Policy'));
    }

    public function test_csp_policy_prevents_external_scripts()
    {
        // SECURITY FIX: Verify CSP policy blocks external scripts
        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $csp = $response->headers->get('Content-Security-Policy');

        // Should only allow scripts from same origin
        $this->assertStringContainsString("script-src 'self'", $csp);
        $this->assertStringNotContainsString('script-src *', $csp);
    }

    public function test_csp_policy_blocks_frames()
    {
        // SECURITY FIX: Verify CSP policy blocks framing
        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $csp = $response->headers->get('Content-Security-Policy');

        // Should deny framing
        $this->assertStringContainsString("frame-ancestors 'none'", $csp);
    }

    public function test_csp_policy_blocks_mixed_content()
    {
        // SECURITY FIX: Verify CSP policy blocks mixed content
        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $csp = $response->headers->get('Content-Security-Policy');

        // Should block mixed content
        $this->assertStringContainsString('block-all-mixed-content', $csp);
    }

    public function test_csp_default_src_is_restrictive()
    {
        // SECURITY FIX: Verify CSP default-src is restrictive
        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $csp = $response->headers->get('Content-Security-Policy');

        // Default should be restrictive (deny all by default)
        $this->assertStringContainsString("default-src 'none'", $csp);
    }
}
