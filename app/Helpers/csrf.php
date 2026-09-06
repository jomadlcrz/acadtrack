<?php

declare(strict_types=1);

if (!function_exists('csrf_token')) {
    function csrf_token(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string
    {
        return '<input type="hidden" name="_token" value="' . csrf_token() . '">';
    }
}

if (!function_exists('csrf_verify')) {
    function csrf_verify(?string $token = null): bool
    {
        $token = $token ?? ($_POST['_token'] ?? '');
        return hash_equals(csrf_token(), $token);
    }
}
