<?php

declare(strict_types=1);

function paiements_between(string $from, string $to): array
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

function paiements_sum_between(string $from, string $to): float
{
    $stmt = db()->prepare('SELECT COALESCE(SUM(amount),0) FROM paiements WHERE paid_at BETWEEN ? AND ?');
    $stmt->execute([$from, $to]);
    return (float) $stmt->fetchColumn();
}

function paiements_monthly(int $year): array
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

function handle_compta(): void
{
    require_auth();
    $year = (int) ($_GET['year'] ?? date('Y'));
    $from = isset($_GET['from']) ? (string) $_GET['from'] : date('Y-m-01');
    $to = isset($_GET['to']) ? (string) $_GET['to'] : date('Y-m-t');

    $paiements = paiements_between($from, $to);
    $encaisse = paiements_sum_between($from, $to);
    $monthly = paiements_monthly($year);

    $due = (float) db()->query(
        "SELECT COALESCE(SUM(total - amount_paid),0) FROM factures WHERE status IN ('envoyee','en_retard','brouillon')"
    )->fetchColumn();

    $invoicedStmt = db()->prepare(
        'SELECT COALESCE(SUM(total),0) FROM factures WHERE issue_date BETWEEN ? AND ? AND status != ?'
    );
    $invoicedStmt->execute([$from, $to, 'annulee']);
    $facture = (float) $invoicedStmt->fetchColumn();

    json_ok([
        'from' => $from,
        'to' => $to,
        'year' => $year,
        'stats' => [
            'encaisse' => $encaisse,
            'facture' => $facture,
            'due' => $due,
        ],
        'paiements' => $paiements,
        'monthly' => $monthly,
    ]);
}

function handle_dashboard(): void
{
    require_auth();
    $from = date('Y-m-01');
    $to = date('Y-m-t');

    $unpaid = (float) db()->query(
        "SELECT COALESCE(SUM(total - amount_paid),0) FROM factures WHERE status IN ('envoyee','en_retard','brouillon')"
    )->fetchColumn();

    $clientsCount = (int) db()->query('SELECT COUNT(*) FROM clients')->fetchColumn();
    $devisOpen = (int) db()->query("SELECT COUNT(*) FROM devis WHERE status IN ('brouillon','envoye')")->fetchColumn();
    $facturesLate = (int) db()->query("SELECT COUNT(*) FROM factures WHERE status = 'en_retard'")->fetchColumn();

    json_ok([
        'stats' => [
            'clients' => $clientsCount,
            'ca_month' => paiements_sum_between($from, $to),
            'unpaid' => $unpaid,
            'devis_open' => $devisOpen,
            'factures_late' => $facturesLate,
        ],
        'recent_clients' => array_slice(clients_all(null, null), 0, 5),
        'recent_factures' => array_slice(factures_all(null), 0, 5),
        'monthly' => paiements_monthly((int) date('Y')),
    ]);
}
