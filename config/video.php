<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Video Render Provider
    |--------------------------------------------------------------------------
    |
    | Supported: "creatomate"
    |
    */
    'provider' => env('VIDEO_RENDER_PROVIDER', 'creatomate'),

    /*
    |--------------------------------------------------------------------------
    | Creatomate Settings
    |--------------------------------------------------------------------------
    */
    'creatomate' => [
        'api_key' => env('CREATOMATE_API_KEY'),
        'endpoint' => env('CREATOMATE_ENDPOINT', 'https://api.creatomate.com/v2'),
        'timeout' => (int) env('CREATOMATE_TIMEOUT', 30),
    ],

    /*
    |--------------------------------------------------------------------------
    | Webhook Configuration
    |--------------------------------------------------------------------------
    |
    | The absolute URL where the video provider will post the rendering result.
    | Optionally secure this with a custom token checked in the query string.
    |
    */
    'webhook_url' => env('VIDEO_RENDER_WEBHOOK_URL'),
    'webhook_token' => env('VIDEO_RENDER_WEBHOOK_TOKEN'),
];
