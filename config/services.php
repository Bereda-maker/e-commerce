<?php

return [
    'stripe' => [
        // Raw card numbers never touch this server. Stripe Elements
        // tokenizes card details entirely client-side; Laravel only ever
        // sees a payment-method token. See README "Security".
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],
];
