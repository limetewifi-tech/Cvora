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
        'driver' => env('AI_DRIVER', 'anthropic'),
        'fallback' => env('AI_FALLBACK_DRIVER'),
        'timeout' => (int) env('AI_TIMEOUT', 45),
        'providers' => [
            'anthropic' => [
                'api_key' => env('ANTHROPIC_API_KEY'),
                'model' => env('ANTHROPIC_MODEL'),
                'base_url' => env('ANTHROPIC_BASE_URL', 'https://api.anthropic.com'),
            ],
            'gemini' => [
                'api_key' => env('GEMINI_API_KEY'),
                'model' => env('GEMINI_MODEL'),
                'base_url' => env('GEMINI_BASE_URL', 'https://generativelanguage.googleapis.com'),
            ],
            'groq' => [
                'api_key' => env('GROQ_API_KEY'),
                'model' => env('GROQ_MODEL'),
                'base_url' => env('GROQ_BASE_URL', 'https://api.groq.com/openai/v1'),
            ],
        ],
    ],

    'payments' => [
        'driver' => env('PAYMENT_DRIVER', 'sandbox'),
        'webhook_secret' => env('PAYMENT_WEBHOOK_SECRET'),
        'ikeepay' => [
            'api_url' => env('IKEEPAY_API_URL', 'https://api.ikeepay.com'),
            'api_key' => env('IKEEPAY_API_KEY'),
        ],
    ],

];
