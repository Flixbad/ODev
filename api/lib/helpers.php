<?php

declare(strict_types=1);

function json_ok(mixed $data = null, int $code = 200): never
{
    http_response_code($code);
    $payload = ['ok' => true];
    if (is_array($data)) {
        $payload = array_merge($payload, $data);
    } elseif ($data !== null) {
        $payload['data'] = $data;
    }
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function json_error(string $message, int $code = 400, array $extra = []): never
{
    http_response_code($code);
    echo json_encode(array_merge([
        'ok' => false,
        'error' => $message,
    ], $extra), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function request_json(): array
{
    static $cached = null;
    if ($cached !== null) {
        return $cached;
    }

    $raw = file_get_contents('php://input');
    if ($raw === false || trim($raw) === '') {
        $cached = $_POST ?: [];
        return $cached;
    }

    $decoded = json_decode($raw, true);
    if (!is_array($decoded)) {
        json_error('Corps JSON invalide.', 400);
    }
    $cached = $decoded;
    return $cached;
}

function body_string(array $body, string $key, string $default = ''): string
{
    return trim((string) ($body[$key] ?? $default));
}

function body_float(array $body, string $key, float $default = 0.0): float
{
    $raw = $body[$key] ?? $default;
    if (is_string($raw)) {
        $raw = str_replace([' ', ','], ['', '.'], $raw);
    }
    return is_numeric($raw) ? (float) $raw : $default;
}

function body_int(array $body, string $key, int $default = 0): int
{
    return (int) ($body[$key] ?? $default);
}

function next_document_number(string $prefix, string $table): string
{
    $allowed = ['devis', 'factures'];
    if (!in_array($table, $allowed, true)) {
        throw new RuntimeException('Table invalide.');
    }
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

/**
 * Parse lignes {description, quantity, unit_price} et recalcule les totaux.
 *
 * @return array{0: list<array>, 1: float, 2: float, 3: float} items, subtotal, tax, total
 */
function parse_items(array $rawItems, float $taxRate): array
{
    $items = [];
    $subtotal = 0.0;
    foreach ($rawItems as $row) {
        if (!is_array($row)) {
            continue;
        }
        $desc = trim((string) ($row['description'] ?? ''));
        if ($desc === '') {
            continue;
        }
        $qty = (float) ($row['quantity'] ?? 1);
        $price = (float) ($row['unit_price'] ?? 0);
        $line = round($qty * $price, 2);
        $subtotal += $line;
        $items[] = [
            'description' => $desc,
            'quantity' => $qty,
            'unit_price' => $price,
            'line_total' => $line,
        ];
    }
    [$subtotal, $tax, $total] = recalc_totals($subtotal, $taxRate);
    return [$items, $subtotal, $tax, $total];
}

function public_user(array $row): array
{
    return [
        'id' => (int) $row['id'],
        'name' => (string) $row['name'],
        'email' => (string) $row['email'],
    ];
}
