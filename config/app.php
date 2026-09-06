<?php

return [
    'name' => $_ENV['APP_NAME'] ?? 'GWC Grading System',
    'env' => $_ENV['APP_ENV'] ?? 'production',
    'debug' => ($_ENV['APP_DEBUG'] ?? 'false') === 'true',
    'url' => $_ENV['APP_URL'] ?? 'http://localhost/grading-system',
    'key' => $_ENV['APP_KEY'] ?? '',
];
