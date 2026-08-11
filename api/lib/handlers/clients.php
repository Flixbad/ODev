<?php

declare(strict_types=1);

function clients_all(?string $q, ?string $status): array
{
    $sql = 'SELECT * FROM clients WHERE 1=1';
    $params = [];
    if ($q) {
        $sql .= ' AND (first_name LIKE ? OR last_name LIKE ? OR company LIKE ? OR email LIKE ? OR phone LIKE ?)';
        $like = '%' . $q . '%';
        $params = [$like, $like, $like, $like, $like];
    }
    if ($status) {
        $sql .= ' AND status = ?';
        $params[] = $status;
    }
    $sql .= ' ORDER BY updated_at DESC, id DESC';
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function clients_find(int $id): ?array
{
    $stmt = db()->prepare('SELECT * FROM clients WHERE id = ?');
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function clients_parse(array $body): array
{
    $data = [
        'company' => body_string($body, 'company') ?: null,
        'first_name' => body_string($body, 'first_name'),
        'last_name' => body_string($body, 'last_name'),
        'email' => body_string($body, 'email') ?: null,
        'phone' => body_string($body, 'phone') ?: null,
        'address' => body_string($body, 'address') ?: null,
        'city' => body_string($body, 'city') ?: null,
        'postal_code' => body_string($body, 'postal_code') ?: null,
        'notes' => body_string($body, 'notes') ?: null,
        'status' => body_string($body, 'status', 'actif'),
    ];
    if (!in_array($data['status'], ['actif', 'prospect', 'archive'], true)) {
        $data['status'] = 'actif';
    }
    return $data;
}

function handle_clients(string $method, ?int $id): void
{
    require_auth();

    if ($id === null && $method === 'GET') {
        $q = isset($_GET['q']) ? trim((string) $_GET['q']) : null;
        $status = isset($_GET['status']) && $_GET['status'] !== '' ? (string) $_GET['status'] : null;
        json_ok(['clients' => clients_all($q ?: null, $status)]);
    }

    if ($id === null && $method === 'POST') {
        $data = clients_parse(request_json());
        if ($data['first_name'] === '' || $data['last_name'] === '') {
            json_error('Prénom et nom obligatoires.');
        }
        $stmt = db()->prepare(
            'INSERT INTO clients (company, first_name, last_name, email, phone, address, city, postal_code, notes, status)
             VALUES (?,?,?,?,?,?,?,?,?,?)'
        );
        $stmt->execute([
            $data['company'], $data['first_name'], $data['last_name'], $data['email'], $data['phone'],
            $data['address'], $data['city'], $data['postal_code'], $data['notes'], $data['status'],
        ]);
        $newId = (int) db()->lastInsertId();
        json_ok(['client' => clients_find($newId)], 201);
    }

    if ($id !== null && $method === 'GET') {
        $client = clients_find($id);
        if (!$client) {
            json_error('Client introuvable.', 404);
        }
        $devis = db()->prepare('SELECT * FROM devis WHERE client_id = ? ORDER BY issue_date DESC');
        $devis->execute([$id]);
        $factures = db()->prepare('SELECT * FROM factures WHERE client_id = ? ORDER BY issue_date DESC');
        $factures->execute([$id]);
        json_ok([
            'client' => $client,
            'devis' => $devis->fetchAll(),
            'factures' => $factures->fetchAll(),
        ]);
    }

    if ($id !== null && $method === 'PUT') {
        if (!clients_find($id)) {
            json_error('Client introuvable.', 404);
        }
        $data = clients_parse(request_json());
        if ($data['first_name'] === '' || $data['last_name'] === '') {
            json_error('Prénom et nom obligatoires.');
        }
        db()->prepare(
            'UPDATE clients SET company=?, first_name=?, last_name=?, email=?, phone=?, address=?, city=?, postal_code=?, notes=?, status=? WHERE id=?'
        )->execute([
            $data['company'], $data['first_name'], $data['last_name'], $data['email'], $data['phone'],
            $data['address'], $data['city'], $data['postal_code'], $data['notes'], $data['status'], $id,
        ]);
        json_ok(['client' => clients_find($id)]);
    }

    if ($id !== null && $method === 'DELETE') {
        try {
            db()->prepare('DELETE FROM clients WHERE id = ?')->execute([$id]);
            json_ok(['message' => 'Client supprimé.']);
        } catch (Throwable $e) {
            json_error('Impossible de supprimer : des devis/factures sont liés.', 409);
        }
    }

    json_error('Méthode non autorisée.', 405);
}
