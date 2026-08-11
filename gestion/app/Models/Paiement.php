<?php

declare(strict_types=1);

namespace App\Models;

final class Paiement
{
    public static function forFacture(int $factureId): array
    {
        $stmt = db()->prepare('SELECT * FROM paiements WHERE facture_id = ? ORDER BY paid_at DESC, id DESC');
        $stmt->execute([$factureId]);
        return $stmt->fetchAll();
    }

    public static function create(array $data): int
    {
        $stmt = db()->prepare(
            'INSERT INTO paiements (facture_id, amount, paid_at, method, reference, notes) VALUES (?,?,?,?,?,?)'
        );
        $stmt->execute([
            $data['facture_id'], $data['amount'], $data['paid_at'], $data['method'], $data['reference'], $data['notes'],
        ]);
        $id = (int) db()->lastInsertId();
        Facture::syncPaymentState((int) $data['facture_id']);
        return $id;
    }

    public static function delete(int $id): void
    {
        $stmt = db()->prepare('SELECT facture_id FROM paiements WHERE id = ?');
        $stmt->execute([$id]);
        $factureId = (int) $stmt->fetchColumn();
        db()->prepare('DELETE FROM paiements WHERE id = ?')->execute([$id]);
        if ($factureId) {
            Facture::syncPaymentState($factureId);
        }
    }

    public static function between(string $from, string $to): array
    {
        $stmt = db()->prepare(
            'SELECT p.*, f.number AS facture_number, f.title, c.first_name, c.last_name, c.company
             FROM paiements p
             JOIN factures f ON f.id = p.facture_id
             JOIN clients c ON c.id = f.client_id
             WHERE p.paid_at BETWEEN ? AND ?
             ORDER BY p.paid_at DESC, p.id DESC'
        );
        $stmt->execute([$from, $to]);
        return $stmt->fetchAll();
    }

    public static function sumBetween(string $from, string $to): float
    {
        $stmt = db()->prepare('SELECT COALESCE(SUM(amount),0) FROM paiements WHERE paid_at BETWEEN ? AND ?');
        $stmt->execute([$from, $to]);
        return (float) $stmt->fetchColumn();
    }

    public static function monthlyTotals(int $year): array
    {
        $stmt = db()->prepare(
            'SELECT MONTH(paid_at) AS m, COALESCE(SUM(amount),0) AS total
             FROM paiements WHERE YEAR(paid_at) = ? GROUP BY MONTH(paid_at) ORDER BY m'
        );
        $stmt->execute([$year]);
        $map = array_fill(1, 12, 0.0);
        foreach ($stmt->fetchAll() as $row) {
            $map[(int) $row['m']] = (float) $row['total'];
        }
        return $map;
    }
}
