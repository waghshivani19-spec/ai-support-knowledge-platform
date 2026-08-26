<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
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

    /*
    |--------------------------------------------------------------------------
    | Internal Microservices
    |--------------------------------------------------------------------------
    |
    | Configuration for internal services owned by this platform, such as
    | the Python/FastAPI AI microservice. These are not third party vendor
    | credentials, but they still benefit from living alongside them so
    | that all outbound service configuration is discoverable in one place.
    |
    */

    'ai_service' => [
        'url' => rtrim(env('AI_SERVICE_URL', 'http://127.0.0.1:8001'), '/'),
        'timeout' => env('AI_SERVICE_TIMEOUT', 30),
        'connect_timeout' => env('AI_SERVICE_CONNECT_TIMEOUT', 5),
        'retry' => [
            'times' => env('AI_SERVICE_RETRY_TIMES', 2),
            'sleep_ms' => env('AI_SERVICE_RETRY_SLEEP_MS', 200),
        ],
    ],

];
