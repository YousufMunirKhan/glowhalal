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

    // Google keys. .env WINS when present; the hardcoded second argument is the
    // fallback so the app works even on a server whose .env lacks these — these
    // are the owner's real, in-use keys.
    // ⚠️ The client_secret is committed here by request; rotate it in Google
    // Cloud → Credentials if this repo is ever shared publicly, and prefer .env
    // on the live server.
    // Google AI Studio (Gemini) — used for blog cover image generation.
    // Key comes ONLY from .env; free tier (~100 requests/day) is ample for
    // the store's ~2 posts/day. No key → the generator falls back to the
    // free keyless Pollinations API automatically.
    'gemini' => [
        'key' => env('GEMINI_API_KEY'),
        'image_model' => env('GEMINI_IMAGE_MODEL', 'gemini-2.5-flash-image'),
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID', '613174641872-q0sv3bbvkb6tgedgfkvfo58937rlh3hn.apps.googleusercontent.com'),
        // Secret is NEVER hard-coded — it comes from the production .env (and the
        // DB-backed SeoSettings override in AppServiceProvider). Keeping it out of
        // the repo is what stops it leaking into git history.
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI', 'https://glowhalal.com/auth/google/callback'),
        'analytics_id' => env('GOOGLE_ANALYTICS_ID', 'G-K88S432NS2'),
    ],

];
