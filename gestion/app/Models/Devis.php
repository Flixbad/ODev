<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

final class Devis
{
    public static function all(?string $status = null): array
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

    public static function find(int $id): ?array
    {
        $stmt = db()->prepare(
            'SELECT d.*, c.first_name, c.last_name, c.company, c.email, c.phone, c.address, c.city, c.postal_code
             FROM devis d JOIN clients c ON c.id = d.client_id WHERE d.id = ?'
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function items(int $devisId): array
    {
        $stmt = db()->prepare('SELECT * FROM devis_items WHERE devis_id = ? ORDER BY sort_order, id');
        $stmt->execute([$devisId]);
        return $stmt->fetchAll();
    }

    public static function create(array $header, array $items): int
    {
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
            $id = (int) $pdo->lastInsertId();
            self::replaceItems($pdo, $id, $items);
            $pdo->commit();
            return $id;
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public static function update(int $id, array $header, array $items): void
    {
        $pdo = db();
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare(
                'UPDATE devis SET client_id=?, title=?, status=?, issue_date=?, valid_until=?, notes=?, subtotal=?, tax_rate=?, tax_amount=?, total=? WHERE id=?'
            );
            $stmt->execute([
                $header['client_id'], $header['title'], $header['status'],
                $header['issue_date'], $header['valid_until'], $header['notes'],
                $header['subtotal'], $header['tax_rate'], $header['tax_amount'], $header['total'], $id,
            ]);
            $pdo->prepare('DELETE FROM devis_items WHERE devis_id = ?')->execute([$id]);
            self::replaceItems($pdo, $id, $items);
            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    private static function replaceItems(PDO $pdo, int $devisId, array $items): void
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

    public static function delete(int $id): void
    {
        db()->prepare('DELETE FROM devis WHERE id = ?')->execute([$id]);
    }

    public static function countByStatus(): array
    {
        return db()->query('SELECT status, COUNT(*) AS c FROM devis GROUP BY status')->fetchAll();
    }
}
