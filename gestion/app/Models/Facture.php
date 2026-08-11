<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

final class Facture
{
    public static function all(?string $status = null): array
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

    public static function find(int $id): ?array
    {
        $stmt = db()->prepare(
            'SELECT f.*, c.first_name, c.last_name, c.company, c.email, c.phone, c.address, c.city, c.postal_code
             FROM factures f JOIN clients c ON c.id = f.client_id WHERE f.id = ?'
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function items(int $factureId): array
    {
        $stmt = db()->prepare('SELECT * FROM facture_items WHERE facture_id = ? ORDER BY sort_order, id');
        $stmt->execute([$factureId]);
        return $stmt->fetchAll();
    }

    public static function create(array $header, array $items): int
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
                'UPDATE factures SET client_id=?, devis_id=?, title=?, status=?, issue_date=?, due_date=?, notes=?, subtotal=?, tax_rate=?, tax_amount=?, total=? WHERE id=?'
            );
            $stmt->execute([
                $header['client_id'], $header['devis_id'], $header['title'], $header['status'],
                $header['issue_date'], $header['due_date'], $header['notes'],
                $header['subtotal'], $header['tax_rate'], $header['tax_amount'], $header['total'], $id,
            ]);
            $pdo->prepare('DELETE FROM facture_items WHERE facture_id = ?')->execute([$id]);
            self::replaceItems($pdo, $id, $items);
            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    private static function replaceItems(PDO $pdo, int $factureId, array $items): void
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

    public static function delete(int $id): void
    {
        db()->prepare('DELETE FROM factures WHERE id = ?')->execute([$id]);
    }

    public static function syncPaymentState(int $factureId): void
    {
        $facture = self::find($factureId);
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

    public static function fromDevis(int $devisId): int
    {
        $devis = Devis::find($devisId);
        if (!$devis) {
            throw new \RuntimeException('Devis introuvable');
        }
        $items = Devis::items($devisId);
        $mapped = array_map(static fn ($i) => [
            'description' => $i['description'],
            'quantity' => (float) $i['quantity'],
            'unit_price' => (float) $i['unit_price'],
            'line_total' => (float) $i['line_total'],
        ], $items);

        return self::create([
            'number' => next_document_number('FAC', 'factures'),
            'client_id' => (int) $devis['client_id'],
            'devis_id' => $devisId,
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
    }
}
