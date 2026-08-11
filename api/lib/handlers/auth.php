<?php

declare(strict_types=1);

function handle_auth(string $action, string $method): void
{
    if ($action === 'login' && $method === 'POST') {
        $body = request_json();
        $email = body_string($body, 'email');
        $password = (string) ($body['password'] ?? '');

        if ($email === '' || $password === '') {
            json_error('Email et mot de passe obligatoires.');
        }

        $stmt = db()->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            json_error('Identifiants incorrects.', 401);
        }

        auth_login($user);
        json_ok(['user' => public_user($user)]);
    }

    if ($action === 'logout' && $method === 'POST') {
        auth_logout();
        json_ok(['message' => 'Déconnecté.']);
    }

    if ($action === 'me' && $method === 'GET') {
        $user = auth_user();
        if ($user === null) {
            json_error('Non authentifié.', 401);
        }
        json_ok(['user' => $user]);
    }

    json_error('Route auth introuvable.', 404);
}
