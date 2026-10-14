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

    'marzpay' => [
        'base_url' => env('MARZPAY_API_BASE', 'https://wallet.wearemarz.com/api/v1'),
        'key' => env('MARZPAY_API_KEY'),
        'secret' => env('MARZPAY_API_SECRET'),
        'webhook_secret' => env('MARZPAY_WEBHOOK_SECRET'),
        'callback_url' => env('MARZPAY_CALLBACK_URL'),
        'country' => env('MARZPAY_COUNTRY', 'UG'),
        'currency' => env('MARZPAY_CURRENCY', 'UGX'),
        'mock' => env('MARZPAY_MOCK'),
    ],

];
