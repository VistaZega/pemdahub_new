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

    /*
    |--------------------------------------------------------------------------
    | WhatsApp Service Configuration
    |--------------------------------------------------------------------------
    |
    | Configure WhatsApp API integration for sending notifications
    | Supports: Fonnte, Wablas, Twilio, or custom API
    |
    */
    'whatsapp' => [
        'enabled' => env('WHATSAPP_ENABLED', false),
        'provider' => env('WHATSAPP_PROVIDER', 'fonnte'), // fontte, selfhosted (Baileys), wablas, twilio
        'api_url' => env('WHATSAPP_API_URL', 'https://api.fonnte.com'),
        'api_token' => env('WHATSAPP_API_TOKEN'),
        'sender' => env('WHATSAPP_SENDER', '088991144184'), // Nomor pengirim
        'timeout' => env('WHATSAPP_TIMEOUT', 15),
    ],

    /*
    |--------------------------------------------------------------------------
    | Kiosk (RFID Hardware) Configuration
    |--------------------------------------------------------------------------
    */
    'kiosk' => [
        'api_key' => env('KIOSK_API_KEY', 'RAHASIA-PEMBDAHUB-12345'),
        'cooldown_seconds' => env('KIOSK_COOLDOWN_SECONDS', 10),
    ],

    /*
    |--------------------------------------------------------------------------
    | Jitsi Meet Configuration
    |--------------------------------------------------------------------------
    */
    'jitsi' => [
        'domain' => env('JITSI_DOMAIN', 'meet.jit.si'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Gemini API Configuration
    |--------------------------------------------------------------------------
    */
    'gemini' => [
        'key' => env('GEMINI_API_KEY'),
    ],

    /*
    |--------------------------------------------------------------------------
    | System Error Alert Notifications (Telegram & WhatsApp)
    |--------------------------------------------------------------------------
    */
    'alerts' => [
        'enabled' => env('ERROR_ALERTS_ENABLED', true),
        'cooldown_minutes' => env('ERROR_ALERT_COOLDOWN_MINUTES', 5),
        'telegram' => [
            'enabled' => env('TELEGRAM_ALERT_ENABLED', false),
            'bot_token' => env('TELEGRAM_ALERT_BOT_TOKEN'),
            'chat_id' => env('TELEGRAM_ALERT_CHAT_ID'),
        ],
        'whatsapp' => [
            'enabled' => env('WHATSAPP_ALERT_ENABLED', false),
            'admin_phone' => env('WHATSAPP_ALERT_PHONE', env('WHATSAPP_ADMIN_PHONE')),
        ],
    ],

];
