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

    'xendit' => [
        'secret_key' => env('XENDIT_SECRET_KEY'),
        'callback_token' => env('XENDIT_CALLBACK_TOKEN'),
        'session_expiry_hours' => (int) env('XENDIT_SESSION_EXPIRY_HOURS', 24),
        'country' => env('XENDIT_COUNTRY', 'PH'),
        'currency' => env('XENDIT_CURRENCY', 'PHP'),
        'allowed_channels' => array_values(array_filter(array_map(
            'trim',
            explode(',', (string) env(
                'XENDIT_ALLOWED_CHANNELS',
                'CARDS,GCASH,PAYMAYA,GRABPAY,SHOPEEPAY,BPI_DIRECT_DEBIT,RCBC_DIRECT_DEBIT,UBP_DIRECT_DEBIT'
            ))
        ))),
    ],

];
