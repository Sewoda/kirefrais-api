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

    'cinetpay' => [
        'site_id' => env('CINETPAY_SITE_ID'),
        'apikey'  => env('CINETPAY_API_KEY'),
        'secret'  => env('CINETPAY_SECRET'),
    ],

    'groq' => [
        'api_key'        => env('GROQ_API_KEY'),
        'model'          => env('GROQ_MODEL', 'llama-3.3-70b-versatile'),
        'fallback_model' => env('GROQ_FALLBACK_MODEL', 'llama-3.1-8b-instant'),
        'base_url'       => 'https://api.groq.com/openai/v1',
        'max_tokens'     => (int) env('AI_MAX_TOKENS', 1024),
        'temperature'    => (float) env('AI_TEMPERATURE', 0.7),
    ],

    'gemini' => [
        'api_key'     => env('GEMINI_API_KEY'),
        'model'       => env('GEMINI_MODEL', 'gemini-1.5-flash'),
        'base_url'    => 'https://generativelanguage.googleapis.com/v1beta',
        'max_tokens'  => (int) env('AI_MAX_TOKENS', 1024),
        'temperature' => (float) env('AI_TEMPERATURE', 0.7),
    ],

];
