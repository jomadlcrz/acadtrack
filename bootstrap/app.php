<?php

declare(strict_types=1);

use Dotenv\Dotenv;
use App\Core\Application;
use App\Core\Router;
use App\Core\Database;

$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();
$dotenv->required(['DB_HOST', 'DB_DATABASE', 'DB_USERNAME', 'APP_URL']);

session_start();

$database = new Database(
    env('DB_HOST'),
    env('DB_DATABASE'),
    env('DB_USERNAME'),
    (string) env('DB_PASSWORD', '')
);
$router = new Router();

require_once __DIR__ . '/../routes/web.php';
require_once __DIR__ . '/../routes/api.php';

$app = new Application($router, $database);
$GLOBALS['app'] = $app;

return $app;
