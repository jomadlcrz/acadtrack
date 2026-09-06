<?php

declare(strict_types=1);

if (!function_exists('base_path_url')) {
    function base_path_url(): string
    {
        $reqUri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';

        if (preg_match('#^/(acadtrack|grading-system)\b#i', $reqUri, $m)) {
            return '/' . strtolower($m[1]);
        }

        $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));

        // If accessed through root rewrite, SCRIPT_NAME has /public but REQUEST_URI does not
        if (str_ends_with($scriptDir, '/public') && !str_contains($reqUri, '/public')) {
            $scriptDir = substr($scriptDir, 0, -7);
        }

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
        $clean = ltrim($path, '/');
        if (str_starts_with($clean, 'assets/')) {
            $clean = substr($clean, 7);
        }
        return url('assets/' . $clean);
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
        if (str_starts_with($uri, '/public')) {
            $uri = substr($uri, 7);
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
        if (php_sapi_name() !== 'cli' && !defined('PHPUNIT_RUNNING')) {
            exit;
        }
    }
}

if (!function_exists('back')) {
    function back(): void
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? '/';
        redirect($referer);
    }
}
