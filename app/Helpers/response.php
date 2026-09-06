<?php

declare(strict_types=1);

if (!function_exists('response')) {
    function response(): \App\Core\Response
    {
        return new \App\Core\Response();
    }
}

if (!function_exists('view')) {
    function view(string $view, array $data = []): void
    {
        $app = $GLOBALS['app'] ?? null;
        if ($app && $app->view) {
            $html = $app->view->render($view, $data);
            echo $html;
        }
    }
}

if (!function_exists('json_response')) {
    function json_response(mixed $data, int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
