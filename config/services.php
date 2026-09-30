<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'gemini' => [
        'key' => env('GEMINI_API_KEY'),
        'model' => env('GEMINI_MODEL', 'gemini-2.5-flash'),
        'base_url' => env('GEMINI_BASE_URL', 'https://generativelanguage.googleapis.com/v1beta'),
        'timeout' => (int) env('GEMINI_TIMEOUT', 60),
        'retry_times' => (int) env('GEMINI_RETRY_TIMES', 3),
        'retry_delay_ms' => (int) env('GEMINI_RETRY_DELAY_MS', 2000),
        'tts_model' => env('GEMINI_TTS_MODEL', 'gemini-2.5-flash-preview-tts'),
        'tts_default_voice' => env('GEMINI_TTS_DEFAULT_VOICE', 'Kore'),
    ],

    'ai' => [
        'demo_mode' => (bool) env('AI_DEMO_MODE', false),
    ],

    'falai' => [
        'enabled' => (bool) env('FAL_ENABLED', false),
        'key' => env('FAL_KEY'),
        'model' => env('FAL_MODEL', 'fal-ai/flux/schnell'),
    ],

    'pollinations' => [
        'width' => (int) env('POLLINATIONS_WIDTH', 768),
        'height' => (int) env('POLLINATIONS_HEIGHT', 432),
    ],

    'elevenlabs' => [
        'key' => env('ELEVENLABS_API_KEY'),
        'default_voice' => env('ELEVENLABS_DEFAULT_VOICE', '21m00Tcm4TlvDq8ikWAM'),
        'model' => env('ELEVENLABS_MODEL', 'eleven_multilingual_v2'),
    ],

    'creatomate' => [
        'key' => env('CREATOMATE_API_KEY'),
        'template_id' => env('CREATOMATE_TEMPLATE_ID'),
        'endpoint' => env('CREATOMATE_ENDPOINT', 'https://api.creatomate.com/v1'),
        'timeout' => (int) env('CREATOMATE_TIMEOUT', 30),
        'webhook_url' => env('VIDEO_RENDER_WEBHOOK_URL', '/api/webhooks/creatomate'),
        'webhook_token' => env('VIDEO_RENDER_WEBHOOK_TOKEN'),
    ],
];
