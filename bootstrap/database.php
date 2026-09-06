<?php

declare(strict_types=1);

use App\Core\Database;

$database = new Database(
    env('DB_HOST'),
    env('DB_DATABASE'),
    env('DB_USERNAME'),
    (string) env('DB_PASSWORD', '')
);

return $database;
