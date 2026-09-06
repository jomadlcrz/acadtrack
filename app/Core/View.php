<?php

declare(strict_types=1);

namespace App\Core;

class View
{
    private array $data = [];

    public function share(string $key, mixed $value): void
    {
        $this->data[$key] = $value;
    }

    public function render(string $view, array $data = []): string
    {
        $data = array_merge($this->data, $data);
        $viewPath = dirname(__DIR__) . '/Views/' . str_replace('.', '/', $view) . '.php';

        if (!file_exists($viewPath)) {
            throw new \RuntimeException("View [{$view}] not found at {$viewPath}");
        }

        extract($data, EXTR_SKIP);
        ob_start();
        include $viewPath;
        $html = (string) ob_get_clean();

        $base = function_exists('base_path_url') ? base_path_url() : '';
        if ($base !== '') {
            $pattern = '#\b(href|action|src)=(["\'])/(?!/|' . preg_quote(ltrim($base, '/'), '#') . ')#i';
            $html = (string) preg_replace($pattern, '$1=$2' . $base . '/', $html);
        }

        return $html;
    }

    public function renderComponent(string $component, array $data = []): string
    {
        return $this->render("components.{$component}", $data);
    }
}
