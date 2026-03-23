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

    "postmark" => [
        "key" => env("POSTMARK_API_KEY"),
    ],

    "resend" => [
        "key" => env("RESEND_API_KEY"),
    ],

    "ses" => [
        "key" => env("AWS_ACCESS_KEY_ID"),
        "secret" => env("AWS_SECRET_ACCESS_KEY"),
        "region" => env("AWS_DEFAULT_REGION", "us-east-1"),
    ],

    "slack" => [
        "notifications" => [
            "bot_user_oauth_token" => env("SLACK_BOT_USER_OAUTH_TOKEN"),
            "channel" => env("SLACK_BOT_USER_DEFAULT_CHANNEL"),
        ],
    ],

    "leekpay" => [
        "public_key" => env("LEEKPAY_PUBLIC_KEY"),
        "secret_key" => env("LEEKPAY_SECRET_KEY"),
        "base_url" => env("LEEKPAY_BASE_URL", "https://leekpay.fr/api/v1"),
    ],

    "groq" => [
        "api_key" => env("GROQ_API_KEY"),
        "model" => env("GROQ_MODEL", "llama-3.3-70b-versatile"),
        "fallback_model" => env("GROQ_FALLBACK_MODEL", "llama-3.1-8b-instant"),
        "base_url" => "https://api.groq.com/openai/v1",
        "max_tokens" => (int) env("AI_MAX_TOKENS", 1024),
        "temperature" => (float) env("AI_TEMPERATURE", 0.7),
    ],

    "gemini" => [
        "api_key" => env("GEMINI_API_KEY"),
        "model" => env("GEMINI_MODEL", "gemini-1.5-flash"),
        "base_url" => "https://generativelanguage.googleapis.com/v1beta",
        "max_tokens" => (int) env("AI_MAX_TOKENS", 1024),
        "temperature" => (float) env("AI_TEMPERATURE", 0.7),
    ],

    "google" => [
        "client_id" => env("GOOGLE_CLIENT_ID"),
        "client_secret" => env("GOOGLE_CLIENT_SECRET"),
        "redirect" => env("GOOGLE_REDIRECT_URI"),
    ],

    "facebook" => [
        "client_id" => env("FACEBOOK_CLIENT_ID"),
        "client_secret" => env("FACEBOOK_CLIENT_SECRET"),
        "redirect" => env("FACEBOOK_REDIRECT_URI"),
    ],
];
