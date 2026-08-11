<?php

declare(strict_types=1);

function base_path(string $path = ''): string
{
    return dirname(__DIR__, 2) . ($path ? DIRECTORY_SEPARATOR . ltrim($path, '/\\') : '');
}

function app_config(): array
{
    static $config;
    if ($config === null) {
        $file = base_path('config.php');
        if (!is_file($file)) {
            redirect_raw('install.php');
        }
        $config = require $file;
    }
    return $config;
}

function db(): PDO
{
    return \App\Core\Database::connection(app_config());
}

function view(string $name, array $data = []): void
{
    extract($data, EXTR_SKIP);
    $viewFile = base_path('app/Views/' . $name . '.php');
    if (!is_file($viewFile)) {
        http_response_code(500);
        echo 'Vue manquante : ' . htmlspecialchars($name);
        return;
    }

    $layout = $data['layout'] ?? 'main';
    if (!empty($data['print'])) {
        $layout = 'print';
    }

    $layoutFile = base_path('app/Views/layouts/' . $layout . '.php');
    if (!is_file($layoutFile)) {
        $layoutFile = base_path('app/Views/layouts/main.php');
    }
    require $layoutFile;
}

function view_partial(string $name, array $data = []): void
{
    extract($data, EXTR_SKIP);
    require base_path('app/Views/' . $name . '.php');
}

function redirect(string $path, array $query = []): never
{
    $path = ltrim($path, '/');
    $q = array_merge(['r' => $path], $query);
    header('Location: index.php?' . http_build_query($q));
    exit;
}

function redirect_raw(string $url): never
{
    header('Location: ' . $url);
    exit;
}

function url(string $path = '', array $query = []): string
{
    $path = ltrim($path, '/');
    $q = array_merge(['r' => $path], $query);
    return 'index.php?' . http_build_query($q);
}

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function flash(string $type, string $message): void
{
    $_SESSION['_flash'][] = ['type' => $type, 'message' => $message];
}

function consume_flashes(): array
{
    $flashes = $_SESSION['_flash'] ?? [];
    unset($_SESSION['_flash']);
    return $flashes;
}

function csrf_token(): string
{
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): void
{
    $token = $_POST['_csrf'] ?? '';
    if (!is_string($token) || !hash_equals(csrf_token(), $token)) {
        http_response_code(419);
        exit('Jeton CSRF invalide.');
    }
}

function money(float|int|string $amount): string
{
    $cfg = app_config();
    $symbol = $cfg['currency_symbol'] ?? '$';
    return $symbol . number_format((float) $amount, 2, ',', ' ');
}

function post_string(string $key, string $default = ''): string
{
    return trim((string) ($_POST[$key] ?? $default));
}

function post_float(string $key, float $default = 0.0): float
{
    $raw = str_replace([' ', ','], ['', '.'], (string) ($_POST[$key] ?? $default));
    return is_numeric($raw) ? (float) $raw : $default;
}

function request_route(): string
{
    $r = $_GET['r'] ?? 'dashboard';
    $r = is_string($r) ? trim($r, '/') : 'dashboard';
    return $r === '' ? 'dashboard' : $r;
}

function next_document_number(string $prefix, string $table): string
{
    $year = date('Y');
    $like = $prefix . '-' . $year . '-%';
    $stmt = db()->prepare("SELECT number FROM {$table} WHERE number LIKE ? ORDER BY id DESC LIMIT 1");
    $stmt->execute([$like]);
    $last = $stmt->fetchColumn();
    $seq = 1;
    if ($last && preg_match('/-(\d+)$/', (string) $last, $m)) {
        $seq = (int) $m[1] + 1;
    }
    return sprintf('%s-%s-%04d', $prefix, $year, $seq);
}

function recalc_totals(float $subtotal, float $taxRate): array
{
    $tax = round($subtotal * ($taxRate / 100), 2);
    $total = round($subtotal + $tax, 2);
    return [$subtotal, $tax, $total];
}

function client_display_name(array $client): string
{
    $name = trim(($client['first_name'] ?? '') . ' ' . ($client['last_name'] ?? ''));
    if (!empty($client['company'])) {
        return $client['company'] . ' — ' . $name;
    }
    return $name;
}

function status_label(string $status): string
{
    return match ($status) {
        'actif' => 'Actif',
        'prospect' => 'Prospect',
        'archive' => 'Archivé',
        'brouillon' => 'Brouillon',
        'envoye', 'envoyee' => 'Envoyé',
        'accepte' => 'Accepté',
        'refuse' => 'Refusé',
        'expire' => 'Expiré',
        'payee' => 'Payée',
        'en_retard' => 'En retard',
        'annulee' => 'Annulée',
        default => ucfirst($status),
    };
}
