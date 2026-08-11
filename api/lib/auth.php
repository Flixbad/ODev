<?php

declare(strict_types=1);

function auth_check(): bool
{
    return isset($_SESSION['user_id']);
}

function auth_user(): ?array
{
    if (!auth_check()) {
        return null;
    }
    return [
        'id' => (int) $_SESSION['user_id'],
        'name' => (string) ($_SESSION['user_name'] ?? ''),
        'email' => (string) ($_SESSION['user_email'] ?? ''),
    ];
}

function auth_login(array $user): void
{
    session_regenerate_id(true);
    $_SESSION['user_id'] = (int) $user['id'];
    $_SESSION['user_name'] = (string) $user['name'];
    $_SESSION['user_email'] = (string) $user['email'];
}

function auth_logout(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], (bool) $p['secure'], (bool) $p['httponly']);
    }
    session_destroy();
}

function require_auth(): array
{
    $user = auth_user();
    if ($user === null) {
        json_error('Authentification requise.', 401);
    }
    return $user;
}
