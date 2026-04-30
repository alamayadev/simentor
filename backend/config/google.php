<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Google Drive Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Google Drive integration.
    |
    */

    'drive' => [
        // Enable or disable Google Drive upload
        'enabled' => env('GOOGLE_DRIVE_ENABLED', false),

        // Authentication type: 'oauth' or 'service_account'
        'auth_type' => env('GOOGLE_DRIVE_AUTH_TYPE', 'oauth'),

        // Path to OAuth credentials JSON file (client_id, client_secret)
        'oauth_credentials_path' => env('GOOGLE_DRIVE_OAUTH_CREDENTIALS_PATH', storage_path('app/google-oauth-credentials.json')),

        // Path to store OAuth tokens (access_token, refresh_token)
        'oauth_token_path' => env('GOOGLE_DRIVE_OAUTH_TOKEN_PATH', storage_path('app/google-oauth-token.json')),

        // Path to service account credentials JSON file (legacy)
        'credentials_path' => env('GOOGLE_DRIVE_CREDENTIALS_PATH', storage_path('app/google-credentials.json')),

        // The shared folder ID where SKP files will be uploaded
        'folder_id' => env('GOOGLE_DRIVE_FOLDER_ID', ''),
    ],
];
