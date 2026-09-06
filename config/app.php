<?php

return [
    'name' => $_ENV['APP_NAME'] ?? 'Acadtrack',
    'env' => $_ENV['APP_ENV'] ?? 'production',
    'debug' => ($_ENV['APP_DEBUG'] ?? 'false') === 'true',
    'url' => $_ENV['APP_URL'] ?? 'http://localhost/acadtrack',
    'key' => $_ENV['APP_KEY'] ?? '',
];
