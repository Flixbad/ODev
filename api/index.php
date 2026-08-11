<?php

declare(strict_types=1);

/**
 * ODev CRM — API JSON
 *
 * Entrée : index.php?r=clients   ou   /api/clients (via .htaccess)
 */

require __DIR__ . '/lib/bootstrap.php';
require __DIR__ . '/lib/handlers/auth.php';
require __DIR__ . '/lib/handlers/clients.php';
require __DIR__ . '/lib/handlers/factures.php';
require __DIR__ . '/lib/handlers/devis.php';
require __DIR__ . '/lib/handlers/compta.php';

$method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
$route = isset($_GET['r']) ? trim((string) $_GET['r'], '/') : '';

if ($route === '') {
    json_ok([
        'name' => 'ODev API',
        'version' => '1.0',
        'hint' => 'Utilisez ?r=auth/login ou les chemins /auth/login',
    ]);
}

$parts = array_values(array_filter(explode('/', $route), static fn ($p) => $p !== ''));
$resource = $parts[0] ?? '';
$id = null;
$sub = null;

if (isset($parts[1]) && ctype_digit($parts[1])) {
    $id = (int) $parts[1];
    $sub = $parts[2] ?? null;
} elseif (isset($parts[1])) {
    $sub = $parts[1];
}

try {
    match ($resource) {
        'auth' => handle_auth((string) ($sub ?? $parts[1] ?? ''), $method),
        'clients' => handle_clients($method, $id),
        'devis' => handle_devis($method, $id, $sub),
        'factures' => handle_factures($method, $id, $sub),
        'paiements' => ($method === 'DELETE' && $id !== null)
            ? handle_paiements_delete($id)
            : json_error('Utilisez DELETE paiements/{id}.', 405),
        'compta' => $method === 'GET' ? handle_compta() : json_error('Méthode non autorisée.', 405),
        'dashboard' => $method === 'GET' ? handle_dashboard() : json_error('Méthode non autorisée.', 405),
        default => json_error('Route introuvable.', 404),
    };
} catch (Throwable $e) {
    json_error('Erreur serveur : ' . $e->getMessage(), 500);
}
