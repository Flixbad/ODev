<?php

declare(strict_types=1);

namespace App\Models;

final class Client
{
    public static function all(?string $q = null, ?string $status = null): array
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

    public static function find(int $id): ?array
    {
        $stmt = db()->prepare('SELECT * FROM clients WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function create(array $data): int
    {
        $stmt = db()->prepare(
            'INSERT INTO clients (company, first_name, last_name, email, phone, address, city, postal_code, notes, status)
             VALUES (?,?,?,?,?,?,?,?,?,?)'
        );
        $stmt->execute([
            $data['company'], $data['first_name'], $data['last_name'], $data['email'], $data['phone'],
            $data['address'], $data['city'], $data['postal_code'], $data['notes'], $data['status'],
        ]);
        return (int) db()->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        $stmt = db()->prepare(
            'UPDATE clients SET company=?, first_name=?, last_name=?, email=?, phone=?, address=?, city=?, postal_code=?, notes=?, status=? WHERE id=?'
        );
        $stmt->execute([
            $data['company'], $data['first_name'], $data['last_name'], $data['email'], $data['phone'],
            $data['address'], $data['city'], $data['postal_code'], $data['notes'], $data['status'], $id,
        ]);
    }

    public static function delete(int $id): void
    {
        $stmt = db()->prepare('DELETE FROM clients WHERE id = ?');
        $stmt->execute([$id]);
    }

    public static function count(): int
    {
        return (int) db()->query('SELECT COUNT(*) FROM clients')->fetchColumn();
    }
}
