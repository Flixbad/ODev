<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Models\Client;
use App\Models\Devis;
use App\Models\Facture;
use App\Models\Paiement;

final class AuthController
{
    public static function loginForm(): void
    {
        if (Auth::check()) {
            redirect('dashboard');
        }
        view('auth/login', ['title' => 'Connexion', 'layout' => 'auth']);
    }

    public static function login(): void
    {
        verify_csrf();
        $email = post_string('email');
        $password = (string) ($_POST['password'] ?? '');

        $stmt = db()->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            flash('error', 'Identifiants incorrects.');
            redirect('login');
        }

        Auth::login($user);
        flash('success', 'Bienvenue ' . $user['name'] . '.');
        redirect('dashboard');
    }

    public static function logout(): void
    {
        Auth::logout();
        redirect_raw('index.php?r=login');
    }
}

final class DashboardController
{
    public static function index(): void
    {
        Auth::requireLogin();
        $from = date('Y-m-01');
        $to = date('Y-m-t');

        $unpaid = (float) db()->query(
            "SELECT COALESCE(SUM(total - amount_paid),0) FROM factures WHERE status IN ('envoyee','en_retard','brouillon')"
        )->fetchColumn();

        $stats = [
            'clients' => Client::count(),
            'ca_month' => Paiement::sumBetween($from, $to),
            'unpaid' => $unpaid,
            'devis_open' => (int) db()->query("SELECT COUNT(*) FROM devis WHERE status IN ('brouillon','envoye')")->fetchColumn(),
            'factures_late' => (int) db()->query("SELECT COUNT(*) FROM factures WHERE status = 'en_retard'")->fetchColumn(),
        ];

        $recentClients = array_slice(Client::all(), 0, 5);
        $recentFactures = array_slice(Facture::all(), 0, 5);
        $monthly = Paiement::monthlyTotals((int) date('Y'));

        view('dashboard/index', [
            'title' => 'Tableau de bord',
            'stats' => $stats,
            'recentClients' => $recentClients,
            'recentFactures' => $recentFactures,
            'monthly' => $monthly,
        ]);
    }
}
