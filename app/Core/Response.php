<?php

declare(strict_types=1);

namespace App\Core;

class Response
{
    private int $statusCode = 200;

    public function statusCode(int $code): self
    {
        $this->statusCode = $code;
        http_response_code($code);
        return $this;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function json(mixed $data, int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    public function html(string $content, int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: text/html; charset=UTF-8');
        echo $content;
        exit;
    }

    public function redirect(string $url, int $code = 302): void
    {
        $this->statusCode = $code;
        http_response_code($code);
        redirect($url);
    }

    public function withHeaders(array $headers): self
    {
        foreach ($headers as $name => $value) {
            header("{$name}: {$value}");
        }
        return $this;
    }

    public function setCookie(string $name, string $value, int $lifetime = 0, string $path = '/'): void
    {
        setcookie($name, $value, [
            'expires' => $lifetime,
            'path' => $path,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }
}
