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
        'enabled' => env('WHATSAPP_ENABLED', true),
        'active_provider' => env('WHATSAPP_PROVIDER', 'selfhosted'),
        'sender' => env('WHATSAPP_SENDER', '082373642864'),
        'timeout' => env('WHATSAPP_TIMEOUT', 15),

        // Provider A: Fonnte (Berbayar, cocok untuk production/shared hosting)
        'providers' => [
            'fonnte' => [
                'label' => 'Fonnte (Cloud API)',
                'api_url' => env('FONNTE_API_URL', 'https://api.fonnte.com'),
                'api_token' => env('FONNTE_API_TOKEN', env('WHATSAPP_API_TOKEN')),
            ],

            // Provider B: Self-hosted Baileys ($0, butuh Node.js berjalan)
            'selfhosted' => [
                'label' => 'Self-Hosted Baileys (Gratis)',
                'api_url' => env('BAILEYS_API_URL', 'http://localhost:3002'),
                'api_token' => env('BAILEYS_API_TOKEN', 'y7xhSUrJ37wpRykg15kc'),
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Kiosk (RFID Hardware) Configuration
    |--------------------------------------------------------------------------
    */
    'kiosk' => [
        'api_key' => env('KIOSK_API_KEY', 'RAHASIA-PEMBDAHUB-12345'),
        'cooldown_seconds' => env('KIOSK_COOLDOWN_SECONDS', 300),
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
    | Agora.io WebRTC Configuration
    |--------------------------------------------------------------------------
    | Digunakan untuk Live Kelas (Video Conference) di fitur LMS PembdaHUB.
    | Daftarkan akun di https://console.agora.io dan buat project baru.
    | Salin App ID dan App Certificate ke dalam file .env production.
    */
    'agora' => [
        'app_id'          => env('AGORA_APP_ID', ''),
        'app_certificate' => env('AGORA_APP_CERTIFICATE', ''),
    ],

    /*
    |--------------------------------------------------------------------------
    | Firebase Configuration
    |--------------------------------------------------------------------------
    */
    'firebase' => [
        'server_key' => env('FIREBASE_SERVER_KEY', ''),
        'project_id' => env('FIREBASE_PROJECT_ID', ''),
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

    /*
    |--------------------------------------------------------------------------
    | Agora.io WebRTC Live Video Configuration
    |--------------------------------------------------------------------------
    */
    'agora' => [
        'app_id' => env('AGORA_APP_ID', 'aeca99d8bea14861ad0a38d2082a3814'),
        'app_certificate' => env('AGORA_APP_CERTIFICATE', '57595929b52b4aa98df72aca79186983'),
    ],

];
