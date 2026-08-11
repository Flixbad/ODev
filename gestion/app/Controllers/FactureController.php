<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Models\Client;
use App\Models\Facture;
use App\Models\Paiement;

final class FactureController
{
    public static function index(): void
    {
        Auth::requireLogin();
        $status = isset($_GET['status']) && $_GET['status'] !== '' ? (string) $_GET['status'] : null;
        view('factures/index', [
            'title' => 'Factures',
            'factures' => Facture::all($status),
            'status' => $status ?? '',
        ]);
    }

    public static function createForm(): void
    {
        Auth::requireLogin();
        view('factures/form', [
            'title' => 'Nouvelle facture',
            'facture' => null,
            'items' => [['description' => '', 'quantity' => 1, 'unit_price' => 0]],
            'clients' => Client::all(null, 'actif'),
            'paiements' => [],
        ]);
    }

    public static function editForm(): void
    {
        Auth::requireLogin();
        $id = (int) ($_GET['id'] ?? 0);
        $facture = Facture::find($id);
        if (!$facture) {
            flash('error', 'Facture introuvable.');
            redirect('factures');
        }
        view('factures/form', [
            'title' => 'Modifier facture',
            'facture' => $facture,
            'items' => Facture::items($id) ?: [['description' => '', 'quantity' => 1, 'unit_price' => 0]],
            'clients' => Client::all(),
            'paiements' => Paiement::forFacture($id),
        ]);
    }

    public static function show(): void
    {
        Auth::requireLogin();
        $id = (int) ($_GET['id'] ?? 0);
        $facture = Facture::find($id);
        if (!$facture) {
            flash('error', 'Facture introuvable.');
            redirect('factures');
        }
        view('factures/show', [
            'title' => $facture['number'],
            'facture' => $facture,
            'items' => Facture::items($id),
            'paiements' => Paiement::forFacture($id),
            'print' => isset($_GET['print']),
        ]);
    }

    public static function save(): void
    {
        Auth::requireLogin();
        verify_csrf();
        $id = (int) ($_POST['id'] ?? 0);
        [$header, $items] = self::parsePayload();

        if ($header['client_id'] <= 0 || $header['title'] === '') {
            flash('error', 'Client et titre obligatoires.');
            redirect($id ? 'factures/edit' : 'factures/create', $id ? ['id' => $id] : []);
        }
        if ($items === []) {
            flash('error', 'Ajoutez au moins une ligne.');
            redirect($id ? 'factures/edit' : 'factures/create', $id ? ['id' => $id] : []);
        }

        if ($id > 0) {
            Facture::update($id, $header, $items);
            Facture::syncPaymentState($id);
            flash('success', 'Facture mise à jour.');
            redirect('factures/show', ['id' => $id]);
        }

        $header['number'] = next_document_number('FAC', 'factures');
        $header['amount_paid'] = 0;
        $newId = Facture::create($header, $items);
        flash('success', 'Facture créée.');
        redirect('factures/show', ['id' => $newId]);
    }

    public static function delete(): void
    {
        Auth::requireLogin();
        verify_csrf();
        Facture::delete((int) ($_POST['id'] ?? 0));
        flash('success', 'Facture supprimée.');
        redirect('factures');
    }

    public static function addPayment(): void
    {
        Auth::requireLogin();
        verify_csrf();
        $factureId = (int) ($_POST['facture_id'] ?? 0);
        $amount = post_float('amount');
        if ($factureId <= 0 || $amount <= 0) {
            flash('error', 'Paiement invalide.');
            redirect('factures/show', ['id' => $factureId]);
        }

        Paiement::create([
            'facture_id' => $factureId,
            'amount' => $amount,
            'paid_at' => post_string('paid_at', date('Y-m-d')),
            'method' => post_string('method', 'virement'),
            'reference' => post_string('reference') ?: null,
            'notes' => post_string('notes') ?: null,
        ]);
        flash('success', 'Paiement enregistré.');
        redirect('factures/show', ['id' => $factureId]);
    }

    public static function deletePayment(): void
    {
        Auth::requireLogin();
        verify_csrf();
        $factureId = (int) ($_POST['facture_id'] ?? 0);
        Paiement::delete((int) ($_POST['id'] ?? 0));
        flash('success', 'Paiement supprimé.');
        redirect('factures/show', ['id' => $factureId]);
    }

    /** @return array{0: array, 1: array} */
    private static function parsePayload(): array
    {
        $taxRate = post_float('tax_rate', (float) (app_config()['default_tax_rate'] ?? 0));
        $descriptions = $_POST['item_description'] ?? [];
        $quantities = $_POST['item_quantity'] ?? [];
        $prices = $_POST['item_unit_price'] ?? [];
        if (!is_array($descriptions)) {
            $descriptions = [];
        }

        $items = [];
        $subtotal = 0.0;
        foreach ($descriptions as $i => $desc) {
            $desc = trim((string) $desc);
            if ($desc === '') {
                continue;
            }
            $qty = (float) str_replace(',', '.', (string) ($quantities[$i] ?? 1));
            $price = (float) str_replace(',', '.', (string) ($prices[$i] ?? 0));
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

        $header = [
            'client_id' => (int) ($_POST['client_id'] ?? 0),
            'devis_id' => (($d = (int) ($_POST['devis_id'] ?? 0)) > 0) ? $d : null,
            'title' => post_string('title'),
            'status' => post_string('status', 'brouillon'),
            'issue_date' => post_string('issue_date', date('Y-m-d')),
            'due_date' => post_string('due_date') ?: null,
            'notes' => post_string('notes') ?: null,
            'subtotal' => $subtotal,
            'tax_rate' => $taxRate,
            'tax_amount' => $tax,
            'total' => $total,
        ];

        if (!in_array($header['status'], ['brouillon', 'envoyee', 'payee', 'en_retard', 'annulee'], true)) {
            $header['status'] = 'brouillon';
        }

        return [$header, $items];
    }
}

final class ComptaController
{
    public static function index(): void
    {
        Auth::requireLogin();
        $year = (int) ($_GET['year'] ?? date('Y'));
        $from = isset($_GET['from']) ? (string) $_GET['from'] : date('Y-m-01');
        $to = isset($_GET['to']) ? (string) $_GET['to'] : date('Y-m-t');

        $paiements = Paiement::between($from, $to);
        $encaissé = Paiement::sumBetween($from, $to);
        $monthly = Paiement::monthlyTotals($year);

        $due = (float) db()->query(
            "SELECT COALESCE(SUM(total - amount_paid),0) FROM factures WHERE status IN ('envoyee','en_retard','brouillon')"
        )->fetchColumn();

        $invoiced = (float) db()->prepare(
            'SELECT COALESCE(SUM(total),0) FROM factures WHERE issue_date BETWEEN ? AND ? AND status != ?'
        );
        $invoiced->execute([$from, $to, 'annulee']);

        view('compta/index', [
            'title' => 'Comptabilité',
            'from' => $from,
            'to' => $to,
            'year' => $year,
            'paiements' => $paiements,
            'encaisse' => $encaissé,
            'facture' => (float) $invoiced->fetchColumn(),
            'due' => $due,
            'monthly' => $monthly,
        ]);
    }
}
