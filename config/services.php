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
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'claveunica' => [
        'client_id' => env('CLAVEUNICA_CLIENT_ID'),
        'client_secret' => env('CLAVEUNICA_CLIENT_SECRET'),
        'redirect' => env('CLAVEUNICA_REDIRECT_URI'),
    ],

    'recaptcha' => [
        'site_key' => env('RECAPTCHA_SITE_KEY', '6Le48cwrAAAAABedSDcI682mOcNawqjCKT6BNGr9'),
        'secret_key' => env('RECAPTCHA_SECRET_KEY'),
        'enabled' => env('RECAPTCHA_ENABLED', true),
        'min_score' => env('RECAPTCHA_MIN_SCORE', 0.5),
        'timeout' => env('RECAPTCHA_TIMEOUT', 10),
        'verify_url' => 'https://www.google.com/recaptcha/api/siteverify',
    ],

];
