<?php

declare(strict_types=1);

namespace App\Core;

final class Router
{
    /** @var array<string, callable> */
    private array $routes = [];

    public function get(string $path, callable $handler): void
    {
        $this->routes['GET:' . $path] = $handler;
    }

    public function post(string $path, callable $handler): void
    {
        $this->routes['POST:' . $path] = $handler;
    }

    public function dispatch(string $method, string $path): void
    {
        $path = '/' . trim($path, '/');
        if ($path !== '/') {
            $path = rtrim($path, '/');
        }

        $key = $method . ':' . $path;
        if (!isset($this->routes[$key])) {
            http_response_code(404);
            echo '<!DOCTYPE html><html lang="fr"><head><meta charset="utf-8"><title>404</title><link rel="stylesheet" href="assets/css/app.css"></head><body class="auth-body"><main class="auth-card"><h1>404</h1><p class="muted">Page introuvable.</p><a class="btn" href="index.php?r=dashboard">Retour</a></main></body></html>';
            return;
        }

        ($this->routes[$key])();
    }
}
