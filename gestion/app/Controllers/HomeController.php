<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;

final class HomeController
{
    public static function index(): void
    {
        if (Auth::check()) {
            redirect('dashboard');
        }

        view('home/index', [
            'title' => 'Accueil',
            'layout' => 'public',
        ]);
    }
}
