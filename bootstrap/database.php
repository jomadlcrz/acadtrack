<?php

declare(strict_types=1);

use App\Core\Database;

$database = new Database(
    $_ENV['DB_HOST'],
    $_ENV['DB_DATABASE'],
    $_ENV['DB_USERNAME'],
    $_ENV['DB_PASSWORD'] ?? ''
);

return $database;
