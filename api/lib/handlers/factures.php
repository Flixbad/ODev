<?php

declare(strict_types=1);

function factures_all(?string $status): array
{
    $sql = 'SELECT f.*, c.first_name, c.last_name, c.company
            FROM factures f JOIN clients c ON c.id = f.client_id WHERE 1=1';
    $params = [];
    if ($status) {
        $sql .= ' AND f.status = ?';
        $params[] = $status;
    }
    $sql .= ' ORDER BY f.issue_date DESC, f.id DESC';
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function factures_find(int $id): ?array
{
    $stmt = db()->prepare(
        'SELECT f.*, c.first_name, c.last_name, c.company, c.email, c.phone, c.address, c.city, c.postal_code
         FROM factures f JOIN clients c ON c.id = f.client_id WHERE f.id = ?'
    );
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function factures_items(int $factureId): array
{
    $stmt = db()->prepare('SELECT * FROM facture_items WHERE facture_id = ? ORDER BY sort_order, id');
    $stmt->execute([$factureId]);
    return $stmt->fetchAll();
}

function factures_replace_items(PDO $pdo, int $factureId, array $items): void
{
    $stmt = $pdo->prepare(
        'INSERT INTO facture_items (facture_id, description, quantity, unit_price, line_total, sort_order) VALUES (?,?,?,?,?,?)'
    );
    foreach ($items as $i => $item) {
        $stmt->execute([
            $factureId, $item['description'], $item['quantity'], $item['unit_price'], $item['line_total'], $i,
        ]);
    }
}

function factures_create(array $header, array $items): int
{
    $pdo = db();
    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare(
            'INSERT INTO factures (number, client_id, devis_id, title, status, issue_date, due_date, notes, subtotal, tax_rate, tax_amount, total, amount_paid)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)'
        );
        $stmt->execute([
            $header['number'], $header['client_id'], $header['devis_id'], $header['title'], $header['status'],
            $header['issue_date'], $header['due_date'], $header['notes'],
            $header['subtotal'], $header['tax_rate'], $header['tax_amount'], $header['total'], $header['amount_paid'] ?? 0,
        ]);
        $id = (int) $pdo->lastInsertId();
        factures_replace_items($pdo, $id, $items);
        $pdo->commit();
        return $id;
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }
}

function factures_sync_payment(int $factureId): void
{
    $facture = factures_find($factureId);
    if (!$facture) {
        return;
    }

    $stmt = db()->prepare('SELECT COALESCE(SUM(amount),0) FROM paiements WHERE facture_id = ?');
    $stmt->execute([$factureId]);
    $paid = (float) $stmt->fetchColumn();
    $total = (float) $facture['total'];
    $status = $facture['status'];

    if ($status !== 'annulee') {
        if ($total > 0 && $paid >= $total) {
            $status = 'payee';
        } elseif (!empty($facture['due_date']) && $facture['due_date'] < date('Y-m-d') && $paid < $total) {
            $status = 'en_retard';
        } elseif ($paid > 0 && $paid < $total) {
            $status = 'envoyee';
        }
    }

    $paidAt = null;
    if ($status === 'payee') {
        $paidAtStmt = db()->prepare('SELECT MAX(paid_at) FROM paiements WHERE facture_id = ?');
        $paidAtStmt->execute([$factureId]);
        $paidAt = $paidAtStmt->fetchColumn() ?: date('Y-m-d');
    }

    db()->prepare('UPDATE factures SET amount_paid = ?, status = ?, paid_at = ? WHERE id = ?')
        ->execute([$paid, $status, $paidAt, $factureId]);
}

function paiements_for_facture(int $factureId): array
{
    $stmt = db()->prepare('SELECT * FROM paiements WHERE facture_id = ? ORDER BY paid_at DESC, id DESC');
    $stmt->execute([$factureId]);
    return $stmt->fetchAll();
}

function factures_parse(array $body): array
{
    $taxRate = body_float($body, 'tax_rate', (float) (app_config()['default_tax_rate'] ?? 0));
    $rawItems = $body['items'] ?? [];
    if (!is_array($rawItems)) {
        $rawItems = [];
    }
    [$items, $subtotal, $tax, $total] = parse_items($rawItems, $taxRate);

    $status = body_string($body, 'status', 'brouillon');
    if (!in_array($status, ['brouillon', 'envoyee', 'payee', 'en_retard', 'annulee'], true)) {
        $status = 'brouillon';
    }

    $devisId = body_int($body, 'devis_id');
    $header = [
        'client_id' => body_int($body, 'client_id'),
        'devis_id' => $devisId > 0 ? $devisId : null,
        'title' => body_string($body, 'title'),
        'status' => $status,
        'issue_date' => body_string($body, 'issue_date', date('Y-m-d')),
        'due_date' => body_string($body, 'due_date') ?: null,
        'notes' => body_string($body, 'notes') ?: null,
        'subtotal' => $subtotal,
        'tax_rate' => $taxRate,
        'tax_amount' => $tax,
        'total' => $total,
    ];

    return [$header, $items];
}

function handle_factures(string $method, ?int $id, ?string $sub): void
{
    require_auth();

    if ($id !== null && $sub === 'paiements' && $method === 'POST') {
        if (!factures_find($id)) {
            json_error('Facture introuvable.', 404);
        }
        $body = request_json();
        $amount = body_float($body, 'amount');
        if ($amount <= 0) {
            json_error('Montant de paiement invalide.');
        }
        $methodPay = body_string($body, 'method', 'virement');
        if (!in_array($methodPay, ['virement', 'especes', 'cheque', 'carte', 'autre'], true)) {
            $methodPay = 'virement';
        }
        db()->prepare(
            'INSERT INTO paiements (facture_id, amount, paid_at, method, reference, notes) VALUES (?,?,?,?,?,?)'
        )->execute([
            $id,
            $amount,
            body_string($body, 'paid_at', date('Y-m-d')),
            $methodPay,
            body_string($body, 'reference') ?: null,
            body_string($body, 'notes') ?: null,
        ]);
        $payId = (int) db()->lastInsertId();
        factures_sync_payment($id);
        $stmt = db()->prepare('SELECT * FROM paiements WHERE id = ?');
        $stmt->execute([$payId]);
        json_ok([
            'paiement' => $stmt->fetch(),
            'facture' => factures_find($id),
        ], 201);
    }

    if ($id === null && $method === 'GET') {
        $status = isset($_GET['status']) && $_GET['status'] !== '' ? (string) $_GET['status'] : null;
        json_ok(['factures' => factures_all($status)]);
    }

    if ($id === null && $method === 'POST') {
        [$header, $items] = factures_parse(request_json());
        if ($header['client_id'] <= 0 || $header['title'] === '') {
            json_error('Client et titre obligatoires.');
        }
        if ($items === []) {
            json_error('Ajoutez au moins une ligne.');
        }
        $header['number'] = next_document_number('FAC', 'factures');
        $header['amount_paid'] = 0;
        $newId = factures_create($header, $items);
        json_ok([
            'facture' => factures_find($newId),
            'items' => factures_items($newId),
            'paiements' => [],
        ], 201);
    }

    if ($id !== null && $method === 'GET' && $sub === null) {
        $facture = factures_find($id);
        if (!$facture) {
            json_error('Facture introuvable.', 404);
        }
        json_ok([
            'facture' => $facture,
            'items' => factures_items($id),
            'paiements' => paiements_for_facture($id),
        ]);
    }

    if ($id !== null && $method === 'PUT' && $sub === null) {
        if (!factures_find($id)) {
            json_error('Facture introuvable.', 404);
        }
        [$header, $items] = factures_parse(request_json());
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
                'UPDATE factures SET client_id=?, devis_id=?, title=?, status=?, issue_date=?, due_date=?, notes=?, subtotal=?, tax_rate=?, tax_amount=?, total=? WHERE id=?'
            )->execute([
                $header['client_id'], $header['devis_id'], $header['title'], $header['status'],
                $header['issue_date'], $header['due_date'], $header['notes'],
                $header['subtotal'], $header['tax_rate'], $header['tax_amount'], $header['total'], $id,
            ]);
            $pdo->prepare('DELETE FROM facture_items WHERE facture_id = ?')->execute([$id]);
            factures_replace_items($pdo, $id, $items);
            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
        factures_sync_payment($id);
        json_ok([
            'facture' => factures_find($id),
            'items' => factures_items($id),
            'paiements' => paiements_for_facture($id),
        ]);
    }

    if ($id !== null && $method === 'DELETE' && $sub === null) {
        db()->prepare('DELETE FROM factures WHERE id = ?')->execute([$id]);
        json_ok(['message' => 'Facture supprimée.']);
    }

    json_error('Méthode non autorisée.', 405);
}

function handle_paiements_delete(int $id): void
{
    require_auth();
    $stmt = db()->prepare('SELECT facture_id FROM paiements WHERE id = ?');
    $stmt->execute([$id]);
    $factureId = (int) $stmt->fetchColumn();
    if ($factureId <= 0) {
        json_error('Paiement introuvable.', 404);
    }
    db()->prepare('DELETE FROM paiements WHERE id = ?')->execute([$id]);
    factures_sync_payment($factureId);
    json_ok(['message' => 'Paiement supprimé.', 'facture' => factures_find($factureId)]);
}
