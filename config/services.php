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

    'google_maps' => [
    'api_key' => env('GOOGLE_MAPS_API_KEY'),
],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    // ---------------------------------------------------------------------------
    // Étape 4.1 — Passerelle WhatsApp (API officielle Meta Cloud).
    //   driver = 'meta' → WHATSAPP_TOKEN + WHATSAPP_PHONE_NUMBER_ID requis
    //   driver = 'log'  → mode simulation (défaut) : l'envoi est journalisé
    // Le serveur envoie lui-même le PDF (upload media → message document).
    // ---------------------------------------------------------------------------
    'whatsapp' => [
        'driver' => env('WHATSAPP_DRIVER', 'log'),
        'meta'   => [
            'token'            => env('WHATSAPP_TOKEN'),
            'phone_number_id'  => env('WHATSAPP_PHONE_NUMBER_ID'),
            'version'          => env('WHATSAPP_GRAPH_VERSION', 'v21.0'),
        ],
    ],
];
