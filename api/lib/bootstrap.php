<?php

declare(strict_types=1);

/**
 * Bootstrap API — session, config, CORS same-origin, autoload lib.
 */

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$host = $_SERVER['HTTP_HOST'] ?? '';
if ($origin !== '' && $host !== '') {
    $originHost = parse_url($origin, PHP_URL_HOST);
    $originPort = parse_url($origin, PHP_URL_PORT);
    $reqHost = $host;
    if ($originHost && strcasecmp((string) $originHost, explode(':', $reqHost)[0]) === 0) {
        header('Access-Control-Allow-Origin: ' . $origin);
        header('Access-Control-Allow-Credentials: true');
        header('Vary: Origin');
    }
}
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'OPTIONS') {
    http_response_code(204);
    exit;
}

ini_set('session.use_strict_mode', '1');
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Lax');
if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
    ini_set('session.cookie_secure', '1');
}

session_start();

require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';

$configFile = dirname(__DIR__) . '/config.php';
if (!is_file($configFile)) {
    json_error('API non configurée. Ouvrez setup.php.', 503);
}

/** @var array $CONFIG */
$CONFIG = require $configFile;
date_default_timezone_set($CONFIG['timezone'] ?? 'Europe/Paris');

function app_config(): array
{
    global $CONFIG;
    return $CONFIG;
}
