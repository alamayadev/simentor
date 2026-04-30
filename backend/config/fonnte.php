<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Fonnte API Configuration
    |--------------------------------------------------------------------------
    |
    | This option controls the Fonnte API settings used for sending notifications.
    |
    */

    'token' => env('FONNTE_DEV_TOKEN'),

    'target_group' => env('FONNTE_TARGET_GROUP', '6285781781754-1459328832@g.us'),

    'api_url' => env('FONNTE_API_URL', 'https://api.fonnte.com/send'),
];
