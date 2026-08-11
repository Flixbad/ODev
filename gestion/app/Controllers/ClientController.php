<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Models\Client;

final class ClientController
{
    public static function index(): void
    {
        Auth::requireLogin();
        $q = isset($_GET['q']) ? trim((string) $_GET['q']) : null;
        $status = isset($_GET['status']) && $_GET['status'] !== '' ? (string) $_GET['status'] : null;
        view('clients/index', [
            'title' => 'Clients',
            'clients' => Client::all($q ?: null, $status),
            'q' => $q ?? '',
            'status' => $status ?? '',
        ]);
    }

    public static function createForm(): void
    {
        Auth::requireLogin();
        view('clients/form', [
            'title' => 'Nouveau client',
            'client' => null,
        ]);
    }

    public static function editForm(): void
    {
        Auth::requireLogin();
        $id = (int) ($_GET['id'] ?? 0);
        $client = Client::find($id);
        if (!$client) {
            flash('error', 'Client introuvable.');
            redirect('clients');
        }
        view('clients/form', [
            'title' => 'Modifier client',
            'client' => $client,
        ]);
    }

    public static function save(): void
    {
        Auth::requireLogin();
        verify_csrf();
        $id = (int) ($_POST['id'] ?? 0);
        $data = [
            'company' => post_string('company') ?: null,
            'first_name' => post_string('first_name'),
            'last_name' => post_string('last_name'),
            'email' => post_string('email') ?: null,
            'phone' => post_string('phone') ?: null,
            'address' => post_string('address') ?: null,
            'city' => post_string('city') ?: null,
            'postal_code' => post_string('postal_code') ?: null,
            'notes' => post_string('notes') ?: null,
            'status' => post_string('status', 'actif'),
        ];

        if ($data['first_name'] === '' || $data['last_name'] === '') {
            flash('error', 'Prénom et nom obligatoires.');
            if ($id > 0) {
                redirect('clients/edit', ['id' => $id]);
            }
            redirect('clients/create');
        }

        if (!in_array($data['status'], ['actif', 'prospect', 'archive'], true)) {
            $data['status'] = 'actif';
        }

        if ($id > 0) {
            Client::update($id, $data);
            flash('success', 'Client mis à jour.');
            redirect('clients/show', ['id' => $id]);
        }

        $newId = Client::create($data);
        flash('success', 'Client créé.');
        redirect('clients/show', ['id' => $newId]);
    }

    public static function show(): void
    {
        Auth::requireLogin();
        $id = (int) ($_GET['id'] ?? 0);
        $client = Client::find($id);
        if (!$client) {
            flash('error', 'Client introuvable.');
            redirect('clients');
        }

        $devis = db()->prepare('SELECT * FROM devis WHERE client_id = ? ORDER BY issue_date DESC');
        $devis->execute([$id]);
        $factures = db()->prepare('SELECT * FROM factures WHERE client_id = ? ORDER BY issue_date DESC');
        $factures->execute([$id]);

        view('clients/show', [
            'title' => client_display_name($client),
            'client' => $client,
            'devis' => $devis->fetchAll(),
            'factures' => $factures->fetchAll(),
        ]);
    }

    public static function delete(): void
    {
        Auth::requireLogin();
        verify_csrf();
        $id = (int) ($_POST['id'] ?? 0);
        try {
            Client::delete($id);
            flash('success', 'Client supprimé.');
        } catch (\Throwable $e) {
            flash('error', 'Impossible de supprimer : des devis/factures sont liés.');
        }
        redirect('clients');
    }
}
