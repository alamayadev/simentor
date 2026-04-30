<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    // Paths that should be (CORS) accessible
    'paths' => explode(',', env('CORS_PATHS', 'api/*,sanctum/csrf-cookie,surveycraft/*,storage/*')),

    'allowed_methods' => explode(',', env('CORS_ALLOWED_METHODS', '*')),

    // Allow origins are set via environment variable for production safety.
    // We always keep common local origins (localhost / local IPs) and merge any
    // env-provided origins so local development remains allowed even if the
    // environment variable is set.
    'allowed_origins' => (function () {
        $default_local = [
            'http://localhost:5173',
            'http://localhost:4173',
            'http://localhost:54283',
            'http://127.0.0.1:5173',
            'http://127.0.0.1:4173',
            'http://localhost:6006',
            'http://127.0.0.1:6006',
            'http://localhost:6007',
            'http://127.0.0.1:6007',
            'http://localhost:3000',
            'http://127.0.0.1:3000',
            'http://localhost:3000',
            'http://localhost:5500',
            'http://127.0.0.1:5500',
            'http://localhost:5174',
            'http://127.0.0.1:5174',
            'http://localhost:5000',
            'http://127.0.0.1:5000',
            'http://10.200.8.206:3000',
            'http://10.132.15.26:8600',
            'http://172.27.188.191:9998',
        ];
        $default_remote = [
            'https://olah.bps3215.id',
            'https://puslah.bps3215.id',
            'https://simentor-dev.bps3215.id',
            'https://simentor.bps3215.id',
            'https://dev.simentor.id',
        ];

        $env = env('CORS_ALLOWED_ORIGINS', null);
        if ($env === null || trim($env) === '') {
            return array_values(array_unique(array_merge($default_local, $default_remote)));
        }

        $env_arr = array_filter(array_map('trim', explode(',', $env)));
        return array_values(array_unique(array_merge($default_local, $env_arr)));
    })(),

    // Allow wildcard-style subdomains for bps3215.id via a regex pattern.
    // You can override with CORS_ALLOWED_ORIGINS_PATTERNS in .env (comma-separated patterns).
    // Default pattern allows any subdomain depth for bps3215.id (like *.bps3215.id)
    // SECURITY FIX: Disabled wildcard patterns - use only explicit origins
    'allowed_origins_patterns' => [],

    'allowed_headers' => explode(',', env('CORS_ALLOWED_HEADERS', '*')),

    'exposed_headers' => array_filter(array_map('trim', explode(',', env('CORS_EXPOSED_HEADERS', 'Authorization,X-Requested-With,Content-Type,Accept,X-CSRF-TOKEN')))),

    'max_age' => (int) env('CORS_MAX_AGE', 86400), // 24 hours in seconds

    'supports_credentials' => (bool) env('CORS_CREDENTIALS', false),
    // SECURITY FIX: Ensure credentials are never supported with wildcard origins
    // This prevents CSRF bypass and credential leakage
];
