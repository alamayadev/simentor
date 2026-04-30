<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Security Headers Middleware
 *
 * Adds security headers to all HTTP responses to protect against
 * common web vulnerabilities and attacks.
 */
class SecurityHeadersMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // SECURITY FIX: Add Content-Security-Policy header
        // Prevents XSS attacks by controlling which resources can be loaded
        $response->headers->set('Content-Security-Policy', $this->getContentSecurityPolicy());

        // SECURITY FIX: Add X-Content-Type-Options header
        // Prevents MIME-sniffing
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // SECURITY FIX: Add X-Frame-Options header
        // Prevents clickjacking attacks
        $response->headers->set('X-Frame-Options', 'DENY');

        // SECURITY FIX: Add X-XSS-Protection header
        // Enables XSS filtering in browsers
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // SECURITY FIX: Add Strict-Transport-Security header (only for HTTPS)
        // Ensures HTTPS is always used
        if ($request->secure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        // SECURITY FIX: Add Referrer-Policy header
        // Controls how much referrer information is sent
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // SECURITY FIX: Add Permissions-Policy header
        // Controls which browser features can be used
        $response->headers->set('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');

        return $response;
    }

    /**
     * Get Content Security Policy
     *
     * For an API, we use a restrictive policy that only allows necessary resources.
     * This prevents XSS attacks by controlling which scripts, styles, and other
     * resources can be loaded.
     *
     * @return string
     */
    protected function getContentSecurityPolicy(): string
    {
        // Build CSP directives
        $directives = [
            // Default policy: deny everything by default
            "default-src 'none'",

            // Allow Livewire/Alpine/Flowbite scripts plus needed CDNs
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net",

            // Allow inline styles and Google Fonts
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com",

            // Allow images from same origin, data URIs, and required external hosts
            "img-src 'self' data: https://ui-avatars.com https://webapps.bps.go.id",

            // Allow Google Fonts
            "font-src 'self' https://fonts.gstatic.com",

            // Only allow connections (AJAX, WebSockets) to same origin
            "connect-src 'self'",

            // Allow form submissions to same origin
            "form-action 'self'",

            // Only allow frames from same origin
            "frame-ancestors 'none'",

            // Block mixed content
            "block-all-mixed-content",

            // Allow base URLs for relative URLs
            "base-uri 'self'",

            // Allow workers from same origin
            "worker-src 'self'",
        ];

        return implode('; ', $directives);
    }
}
