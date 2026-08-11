<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Models\Client;
use App\Models\Devis;
use App\Models\Facture;

final class DevisController
{
    public static function index(): void
    {
        Auth::requireLogin();
        $status = isset($_GET['status']) && $_GET['status'] !== '' ? (string) $_GET['status'] : null;
        view('devis/index', [
            'title' => 'Devis',
            'devisList' => Devis::all($status),
            'status' => $status ?? '',
        ]);
    }

    public static function createForm(): void
    {
        Auth::requireLogin();
        view('devis/form', [
            'title' => 'Nouveau devis',
            'devis' => null,
            'items' => [['description' => '', 'quantity' => 1, 'unit_price' => 0]],
            'clients' => Client::all(null, 'actif'),
        ]);
    }

    public static function editForm(): void
    {
        Auth::requireLogin();
        $id = (int) ($_GET['id'] ?? 0);
        $devis = Devis::find($id);
        if (!$devis) {
            flash('error', 'Devis introuvable.');
            redirect('devis');
        }
        view('devis/form', [
            'title' => 'Modifier devis',
            'devis' => $devis,
            'items' => Devis::items($id) ?: [['description' => '', 'quantity' => 1, 'unit_price' => 0]],
            'clients' => Client::all(),
        ]);
    }

    public static function show(): void
    {
        Auth::requireLogin();
        $id = (int) ($_GET['id'] ?? 0);
        $devis = Devis::find($id);
        if (!$devis) {
            flash('error', 'Devis introuvable.');
            redirect('devis');
        }
        view('devis/show', [
            'title' => $devis['number'],
            'devis' => $devis,
            'items' => Devis::items($id),
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
            redirect($id ? 'devis/edit' : 'devis/create', $id ? ['id' => $id] : []);
        }
        if ($items === []) {
            flash('error', 'Ajoutez au moins une ligne.');
            redirect($id ? 'devis/edit' : 'devis/create', $id ? ['id' => $id] : []);
        }

        if ($id > 0) {
            Devis::update($id, $header, $items);
            flash('success', 'Devis mis à jour.');
            redirect('devis/show', ['id' => $id]);
        }

        $header['number'] = next_document_number('DEV', 'devis');
        $newId = Devis::create($header, $items);
        flash('success', 'Devis créé.');
        redirect('devis/show', ['id' => $newId]);
    }

    public static function delete(): void
    {
        Auth::requireLogin();
        verify_csrf();
        Devis::delete((int) ($_POST['id'] ?? 0));
        flash('success', 'Devis supprimé.');
        redirect('devis');
    }

    public static function toInvoice(): void
    {
        Auth::requireLogin();
        verify_csrf();
        $id = (int) ($_POST['id'] ?? 0);
        try {
            $factureId = Facture::fromDevis($id);
            flash('success', 'Facture générée depuis le devis.');
            redirect('factures/show', ['id' => $factureId]);
        } catch (\Throwable $e) {
            flash('error', 'Conversion impossible.');
            redirect('devis/show', ['id' => $id]);
        }
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
            'title' => post_string('title'),
            'status' => post_string('status', 'brouillon'),
            'issue_date' => post_string('issue_date', date('Y-m-d')),
            'valid_until' => post_string('valid_until') ?: null,
            'notes' => post_string('notes') ?: null,
            'subtotal' => $subtotal,
            'tax_rate' => $taxRate,
            'tax_amount' => $tax,
            'total' => $total,
        ];

        if (!in_array($header['status'], ['brouillon', 'envoye', 'accepte', 'refuse', 'expire'], true)) {
            $header['status'] = 'brouillon';
        }

        return [$header, $items];
    }
}
