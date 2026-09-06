<?php

return [
    'lifetime' => (int) ($_ENV['SESSION_LIFETIME'] ?? 120),
    'path' => $_ENV['SESSION_PATH'] ?? '/',
    'domain' => $_ENV['SESSION_DOMAIN'] ?? null,
    'secure' => ($_ENV['APP_ENV'] ?? 'production') === 'production',
    'httponly' => true,
    'samesite' => 'lax',
];
