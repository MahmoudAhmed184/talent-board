<?php

$allowedOrigins = array_values(array_filter(explode(',', (string) env(
    'CORS_ALLOWED_ORIGINS',
    'http://127.0.0.1:5173,http://127.0.0.1:5174,http://localhost:5173,http://localhost:5174',
))));

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie', 'broadcasting/auth'],

    'allowed_methods' => ['*'],

    'allowed_origins' => $allowedOrigins,

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => (bool) env('CORS_SUPPORTS_CREDENTIALS', true),
];
