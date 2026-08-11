<?php

declare(strict_types=1);

function devis_all(?string $status): array
{
    $sql = 'SELECT d.*, c.first_name, c.last_name, c.company
            FROM devis d JOIN clients c ON c.id = d.client_id WHERE 1=1';
    $params = [];
    if ($status) {
        $sql .= ' AND d.status = ?';
        $params[] = $status;
    }
    $sql .= ' ORDER BY d.issue_date DESC, d.id DESC';
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function devis_find(int $id): ?array
{
    $stmt = db()->prepare(
        'SELECT d.*, c.first_name, c.last_name, c.company, c.email, c.phone, c.address, c.city, c.postal_code
         FROM devis d JOIN clients c ON c.id = d.client_id WHERE d.id = ?'
    );
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function devis_items(int $devisId): array
{
    $stmt = db()->prepare('SELECT * FROM devis_items WHERE devis_id = ? ORDER BY sort_order, id');
    $stmt->execute([$devisId]);
    return $stmt->fetchAll();
}

function devis_replace_items(PDO $pdo, int $devisId, array $items): void
{
    $stmt = $pdo->prepare(
        'INSERT INTO devis_items (devis_id, description, quantity, unit_price, line_total, sort_order) VALUES (?,?,?,?,?,?)'
    );
    foreach ($items as $i => $item) {
        $stmt->execute([
            $devisId, $item['description'], $item['quantity'], $item['unit_price'], $item['line_total'], $i,
        ]);
    }
}

function devis_parse(array $body): array
{
    $taxRate = body_float($body, 'tax_rate', (float) (app_config()['default_tax_rate'] ?? 0));
    $rawItems = $body['items'] ?? [];
    if (!is_array($rawItems)) {
        $rawItems = [];
    }
    [$items, $subtotal, $tax, $total] = parse_items($rawItems, $taxRate);

    $status = body_string($body, 'status', 'brouillon');
    if (!in_array($status, ['brouillon', 'envoye', 'accepte', 'refuse', 'expire'], true)) {
        $status = 'brouillon';
    }

    $header = [
        'client_id' => body_int($body, 'client_id'),
        'title' => body_string($body, 'title'),
        'status' => $status,
        'issue_date' => body_string($body, 'issue_date', date('Y-m-d')),
        'valid_until' => body_string($body, 'valid_until') ?: null,
        'notes' => body_string($body, 'notes') ?: null,
        'subtotal' => $subtotal,
        'tax_rate' => $taxRate,
        'tax_amount' => $tax,
        'total' => $total,
    ];

    return [$header, $items];
}

function handle_devis(string $method, ?int $id, ?string $sub): void
{
    require_auth();

    if ($id !== null && $sub === 'to-facture' && $method === 'POST') {
        $devis = devis_find($id);
        if (!$devis) {
            json_error('Devis introuvable.', 404);
        }
        $items = devis_items($id);
        $mapped = array_map(static fn ($i) => [
            'description' => $i['description'],
            'quantity' => (float) $i['quantity'],
            'unit_price' => (float) $i['unit_price'],
            'line_total' => (float) $i['line_total'],
        ], $items);

        $factureId = factures_create([
            'number' => next_document_number('FAC', 'factures'),
            'client_id' => (int) $devis['client_id'],
            'devis_id' => $id,
            'title' => $devis['title'],
            'status' => 'brouillon',
            'issue_date' => date('Y-m-d'),
            'due_date' => date('Y-m-d', strtotime('+30 days')),
            'notes' => 'Générée depuis le devis ' . $devis['number'],
            'subtotal' => (float) $devis['subtotal'],
            'tax_rate' => (float) $devis['tax_rate'],
            'tax_amount' => (float) $devis['tax_amount'],
            'total' => (float) $devis['total'],
            'amount_paid' => 0,
        ], $mapped);

        json_ok(['facture' => factures_find($factureId), 'items' => factures_items($factureId)], 201);
    }

    if ($id === null && $method === 'GET') {
        $status = isset($_GET['status']) && $_GET['status'] !== '' ? (string) $_GET['status'] : null;
        json_ok(['devis' => devis_all($status)]);
    }

    if ($id === null && $method === 'POST') {
        [$header, $items] = devis_parse(request_json());
        if ($header['client_id'] <= 0 || $header['title'] === '') {
            json_error('Client et titre obligatoires.');
        }
        if ($items === []) {
            json_error('Ajoutez au moins une ligne.');
        }
        $header['number'] = next_document_number('DEV', 'devis');
        $pdo = db();
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare(
                'INSERT INTO devis (number, client_id, title, status, issue_date, valid_until, notes, subtotal, tax_rate, tax_amount, total)
                 VALUES (?,?,?,?,?,?,?,?,?,?,?)'
            );
            $stmt->execute([
                $header['number'], $header['client_id'], $header['title'], $header['status'],
                $header['issue_date'], $header['valid_until'], $header['notes'],
                $header['subtotal'], $header['tax_rate'], $header['tax_amount'], $header['total'],
            ]);
            $newId = (int) $pdo->lastInsertId();
            devis_replace_items($pdo, $newId, $items);
            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
        json_ok(['devis' => devis_find($newId), 'items' => devis_items($newId)], 201);
    }

    if ($id !== null && $method === 'GET' && $sub === null) {
        $devis = devis_find($id);
        if (!$devis) {
            json_error('Devis introuvable.', 404);
        }
        json_ok(['devis' => $devis, 'items' => devis_items($id)]);
    }

    if ($id !== null && $method === 'PUT' && $sub === null) {
        if (!devis_find($id)) {
            json_error('Devis introuvable.', 404);
        }
        [$header, $items] = devis_parse(request_json());
        if ($header['client_id'] <= 0 || $header['title'] === '') {
            json_error('Client et titre obligatoires.');
        }
        if ($items === []) {
            json_error('Ajoutez au moins une ligne.');
        }
        $pdo = db();
        $pdo->beginTransaction();
        try {
            $pdo->prepare(
                'UPDATE devis SET client_id=?, title=?, status=?, issue_date=?, valid_until=?, notes=?, subtotal=?, tax_rate=?, tax_amount=?, total=? WHERE id=?'
            )->execute([
                $header['client_id'], $header['title'], $header['status'],
                $header['issue_date'], $header['valid_until'], $header['notes'],
                $header['subtotal'], $header['tax_rate'], $header['tax_amount'], $header['total'], $id,
            ]);
            $pdo->prepare('DELETE FROM devis_items WHERE devis_id = ?')->execute([$id]);
            devis_replace_items($pdo, $id, $items);
            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
        json_ok(['devis' => devis_find($id), 'items' => devis_items($id)]);
    }

    if ($id !== null && $method === 'DELETE' && $sub === null) {
        db()->prepare('DELETE FROM devis WHERE id = ?')->execute([$id]);
        json_ok(['message' => 'Devis supprimé.']);
    }

    json_error('Méthode non autorisée.', 405);
}
