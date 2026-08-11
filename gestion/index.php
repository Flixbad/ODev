<?php

declare(strict_types=1);

session_start();

require __DIR__ . '/app/Core/helpers.php';
require __DIR__ . '/app/Core/Database.php';
require __DIR__ . '/app/Core/Auth.php';
require __DIR__ . '/app/Core/Router.php';
require __DIR__ . '/app/Models/Client.php';
require __DIR__ . '/app/Models/Devis.php';
require __DIR__ . '/app/Models/Facture.php';
require __DIR__ . '/app/Models/Paiement.php';
require __DIR__ . '/app/Controllers/HomeController.php';
require __DIR__ . '/app/Controllers/AuthController.php';
require __DIR__ . '/app/Controllers/ClientController.php';
require __DIR__ . '/app/Controllers/DevisController.php';
require __DIR__ . '/app/Controllers/FactureController.php';

use App\Controllers\AuthController;
use App\Controllers\ClientController;
use App\Controllers\ComptaController;
use App\Controllers\DashboardController;
use App\Controllers\DevisController;
use App\Controllers\FactureController;
use App\Controllers\HomeController;
use App\Core\Router;

$configFile = __DIR__ . '/config.php';
$route = request_route();

// Pas encore configuré → setup (évite install.php souvent bloqué chez Hostinger)
if (!is_file($configFile) && !in_array($route, ['setup', 'login'], true)) {
    header('Location: setup.php');
    exit;
}

if (is_file($configFile)) {
    $config = require $configFile;
    date_default_timezone_set($config['timezone'] ?? 'Europe/Paris');
}

$router = new Router();

$router->get('/', [HomeController::class, 'index']);
$router->get('/home', [HomeController::class, 'index']);

$router->get('/login', [AuthController::class, 'loginForm']);
$router->post('/login', [AuthController::class, 'login']);
$router->get('/logout', [AuthController::class, 'logout']);

$router->get('/dashboard', [DashboardController::class, 'index']);

$router->get('/clients', [ClientController::class, 'index']);
$router->get('/clients/create', [ClientController::class, 'createForm']);
$router->get('/clients/edit', [ClientController::class, 'editForm']);
$router->get('/clients/show', [ClientController::class, 'show']);
$router->post('/clients/save', [ClientController::class, 'save']);
$router->post('/clients/delete', [ClientController::class, 'delete']);

$router->get('/devis', [DevisController::class, 'index']);
$router->get('/devis/create', [DevisController::class, 'createForm']);
$router->get('/devis/edit', [DevisController::class, 'editForm']);
$router->get('/devis/show', [DevisController::class, 'show']);
$router->post('/devis/save', [DevisController::class, 'save']);
$router->post('/devis/delete', [DevisController::class, 'delete']);
$router->post('/devis/to-invoice', [DevisController::class, 'toInvoice']);

$router->get('/factures', [FactureController::class, 'index']);
$router->get('/factures/create', [FactureController::class, 'createForm']);
$router->get('/factures/edit', [FactureController::class, 'editForm']);
$router->get('/factures/show', [FactureController::class, 'show']);
$router->post('/factures/save', [FactureController::class, 'save']);
$router->post('/factures/delete', [FactureController::class, 'delete']);
$router->post('/factures/payment', [FactureController::class, 'addPayment']);
$router->post('/factures/payment-delete', [FactureController::class, 'deletePayment']);

$router->get('/compta', [ComptaController::class, 'index']);

$router->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', '/' . $route);
