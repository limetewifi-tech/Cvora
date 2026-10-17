<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Vérification d'e-mail
    |--------------------------------------------------------------------------
    |
    | Désactivée par défaut pour qu'une installation sans serveur mail reste
    | utilisable. Activez MUST_VERIFY_EMAIL=true pour exiger la vérification.
    |
    */

    'must_verify_email' => (bool) env('MUST_VERIFY_EMAIL', false),

    'seed_demo' => (bool) env('SEED_DEMO', false),

    'import' => [
        'max_kb' => (int) env('CVORA_IMPORT_MAX_KB', 2048),
        'min_text_length' => 40,
    ],

    'ai' => [
        'driver' => env('AI_DRIVER', 'openai'),
        'api_key' => env('AI_API_KEY'),
        'base_url' => env('AI_BASE_URL', 'https://api.openai.com/v1'),
        'model' => env('AI_MODEL', 'gpt-4o-mini'),
        'timeout' => (int) env('AI_TIMEOUT', 25),
    ],

    'payments' => [
        'driver' => env('PAYMENT_DRIVER', 'sandbox'),
        'webhook_secret' => env('PAYMENT_WEBHOOK_SECRET'),
        'unipay' => [
            'api_url' => env('UNIPAY_API_URL'),
            'api_key' => env('UNIPAY_API_KEY'),
            'merchant_id' => env('UNIPAY_MERCHANT_ID'),
        ],
    ],

];
