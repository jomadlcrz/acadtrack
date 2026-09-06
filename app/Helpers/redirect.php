<?php

declare(strict_types=1);

if (!function_exists('base_path_url')) {
    function base_path_url(): string
    {
        $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
        return ($scriptDir === '/' || $scriptDir === '.') ? '' : rtrim($scriptDir, '/');
    }
}

if (!function_exists('url')) {
    function url(string $path = ''): string
    {
        $base = base_path_url();
        $path = '/' . ltrim($path, '/');
        return $base . $path;
    }
}

if (!function_exists('asset')) {
    function asset(string $path): string
    {
        return url('assets/' . ltrim($path, '/'));
    }
}

if (!function_exists('current_route_path')) {
    function current_route_path(): string
    {
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
        $base = base_path_url();
        if ($base !== '' && str_starts_with($uri, $base)) {
            $uri = substr($uri, strlen($base));
        }
        return $uri === '' ? '/' : $uri;
    }
}

if (!function_exists('redirect')) {
    function redirect(string $url): void
    {
        $base = base_path_url();
        if ($base !== '' && str_starts_with($url, '/') && !str_starts_with($url, $base . '/') && $url !== $base) {
            $url = $base . $url;
        }
        header("Location: {$url}");
        exit;
    }
}

if (!function_exists('back')) {
    function back(): void
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? '/';
        redirect($referer);
    }
}
