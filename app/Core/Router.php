<?php

declare(strict_types=1);

namespace App\Core;

class Router
{
    private array $routes = [];
    private array $groupMiddleware = [];
    private string $groupPrefix = '';

    public function get(string $path, array $action, array $middleware = []): void
    {
        $this->addRoute('GET', $path, $action, $middleware);
    }

    public function post(string $path, array $action, array $middleware = []): void
    {
        $this->addRoute('POST', $path, $action, $middleware);
    }

    public function put(string $path, array $action, array $middleware = []): void
    {
        $this->addRoute('PUT', $path, $action, $middleware);
    }

    public function delete(string $path, array $action, array $middleware = []): void
    {
        $this->addRoute('DELETE', $path, $action, $middleware);
    }

    public function group(string $prefix, array $middleware, callable $callback): void
    {
        $previousGroup = $this->groupMiddleware;
        $previousPrefix = $this->groupPrefix;

        $this->groupMiddleware = array_merge($this->groupMiddleware, $middleware);
        $cleanPrefix = '/' . trim($prefix, '/');
        $this->groupPrefix = $previousPrefix !== '' ? $previousPrefix . $cleanPrefix : $cleanPrefix;

        $callback($this);

        $this->groupMiddleware = $previousGroup;
        $this->groupPrefix = $previousPrefix;
    }

    public function getRoutes(): array
    {
        return $this->routes;
    }

    private function addRoute(string $method, string $path, array $action, array $middleware): void
    {
        $cleanPath = '/' . ltrim($path, '/');
        $fullPath = $this->groupPrefix !== '' ? $this->groupPrefix . ($path === '/' ? '' : $cleanPath) : $path;

        $this->routes[] = [
            'method' => $method,
            'path' => $fullPath,
            'action' => $action,
            'middleware' => array_merge($this->groupMiddleware, $middleware),
        ];
    }

    public function resolve(string $method, string $path, Request $request, Response $response, Session $session): void
    {
        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            $params = $this->matchRoute($route['path'], $path);
            if ($params === false) {
                continue;
            }

            foreach ($route['middleware'] as $middleware) {
                $middlewareInstance = is_string($middleware) ? new $middleware() : $middleware;
                $result = $middlewareInstance->handle($request, $response, $session);
                if ($result === false) {
                    return;
                }
            }

            [$controllerClass, $action] = $route['action'];
            $controller = new $controllerClass();
            $controller->$action($request, $response, $session, ...array_values($params));
            return;
        }

        $response->statusCode(404)->html('404 - Page Not Found');
    }

    private function matchRoute(string $routePath, string $requestPath): array|false
    {
        $routeParts = explode('/', trim($routePath, '/'));
        $requestParts = explode('/', trim($requestPath, '/'));

        if (count($routeParts) !== count($requestParts)) {
            return false;
        }

        $params = [];
        foreach ($routeParts as $index => $part) {
            if (str_starts_with($part, '{') && str_ends_with($part, '}')) {
                $paramName = trim($part, '{}');
                $params[$paramName] = $requestParts[$index];
            } elseif ($part !== $requestParts[$index]) {
                return false;
            }
        }

        return $params;
    }
}
