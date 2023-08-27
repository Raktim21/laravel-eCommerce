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

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'google' => [
        'client_id'     => '575772310587-9m2ld53f9f7nadtkcn6ina1j51ru6dom.apps.googleusercontent.com',
        'client_secret' => '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',
        'redirect'      => 'https://testmerchnaf.selopia.com/auth/google/callback'
    ],

    'facebook' => [
        'client_id'     => '950332889409684',
        'client_secret' => '4080b82c1b9e815bfe96f5e92bee53fe',
        'redirect'      => 'https://testmerchnaf.selopia.com/auth/facebook/callback'
    ]
];
