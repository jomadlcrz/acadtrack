<?php

declare(strict_types=1);

if (!function_exists('auth')) {
    function auth(): ?array
    {
        return $_SESSION['user'] ?? null;
    }
}

if (!function_exists('isLoggedIn')) {
    function isLoggedIn(): bool
    {
        return isset($_SESSION['user']['id']);
    }
}

if (!function_exists('hasRole')) {
    function hasRole(string ...$roles): bool
    {
        $user = auth();
        return $user && in_array($user['role'] ?? '', $roles, true);
    }
}

if (!function_exists('userId')) {
    function userId(): ?int
    {
        return auth()['id'] ?? null;
    }
}

if (!function_exists('userRole')) {
    function userRole(): ?string
    {
        return auth()['role'] ?? null;
    }
}
