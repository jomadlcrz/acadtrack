<?php

declare(strict_types=1);

return [
    'lifetime' => (int) env('SESSION_LIFETIME', 120),
    'path' => (string) env('SESSION_PATH', '/'),
    'domain' => env('SESSION_DOMAIN'),
    'secure' => env('APP_ENV') === 'production',
    'httponly' => true,
    'samesite' => 'lax',
];
