<?php

declare(strict_types=1);

namespace App\Core;

class Request
{
    private ?string $rawBody = null;
    private ?array $jsonCache = null;

    /** Lets tests and CLI callers supply a body that php://input cannot provide. */
    public function setRawBody(?string $body): void
    {
        $this->rawBody = $body;
        $this->jsonCache = null;
    }

    public function rawBody(): string
    {
        return $this->rawBody ?? (string) file_get_contents('php://input');
    }

    public function isJson(): bool
    {
        // PHP exposes Content-Type unprefixed in $_SERVER, unlike every other header.
        $contentType = (string) ($_SERVER['CONTENT_TYPE'] ?? $this->header('Content-Type', ''));
        return str_contains($contentType, 'application/json');
    }

    /** Decoded JSON body; pass null for the whole array or a key for one field. */
    public function json(?string $key = null, mixed $default = null): mixed
    {
        if ($this->jsonCache === null) {
            $decoded = json_decode($this->rawBody(), true);
            $this->jsonCache = is_array($decoded) ? $decoded : [];
        }

        if ($key === null) {
            return $this->jsonCache;
        }

        return $this->jsonCache[$key] ?? $default;
    }

    public function method(): string
    {
        return strtoupper($_SERVER['REQUEST_METHOD']);
    }

    public function path(): string
    {
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        if ($uri === false || $uri === '') {
            return '/';
        }

        $base = function_exists('base_path_url') ? base_path_url() : '';
        if ($base === '') {
            $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
            if (str_ends_with($scriptDir, '/public') && !str_contains($uri, '/public')) {
                $scriptDir = substr($scriptDir, 0, -7);
            }
            $base = ($scriptDir === '/' || $scriptDir === '.') ? '' : rtrim($scriptDir, '/');
        }

        $path = rtrim($uri, '/');

        if ($base !== '' && str_starts_with($path, $base)) {
            $path = substr($path, strlen($base));
        }

        if (str_starts_with($path, '/public')) {
            $path = substr($path, 7);
        }

        return $path === '' ? '/' : $path;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $_GET[$key] ?? $default;
    }

    public function post(string $key, mixed $default = null): mixed
    {
        return $_POST[$key] ?? $default;
    }

    public function all(): array
    {
        return array_merge($_GET, $_POST);
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return $this->post($key, $this->get($key, $default));
    }

    public function has(string $key): bool
    {
        return isset($_POST[$key]) || isset($_GET[$key]);
    }

    public function isPost(): bool
    {
        return $this->method() === 'POST';
    }

    public function isGet(): bool
    {
        return $this->method() === 'GET';
    }

    public function header(string $name, mixed $default = null): mixed
    {
        $key = 'HTTP_' . strtoupper(str_replace('-', '_', $name));
        return $_SERVER[$key] ?? $default;
    }

    public function bearerToken(): ?string
    {
        $header = $this->header('Authorization', '');
        if (str_starts_with($header, 'Bearer ')) {
            return substr($header, 7);
        }
        return null;
    }
}
