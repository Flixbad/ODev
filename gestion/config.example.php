<?php
/**
 * Copiez ce fichier en config.php et renseignez vos identifiants MySQL Hostinger.
 * (hPanel → Bases de données MySQL)
 *
 * En pratique, préférez setup.php qui génère config.php automatiquement.
 */
return [
    'app_name' => 'ODev Gestion',
    'app_url' => '', // ex: https://votredomaine.fr/gestion  (sans slash final)
    'timezone' => 'Europe/Paris',
    'currency' => 'USD',
    'currency_symbol' => '$',
    'default_tax_rate' => 0,
    'company' => [
        'name' => "ODev — Darren O'Sullivan",
        'email' => 'darren@odev.studio',
        'phone' => '',
        'address' => '',
    ],
    'db' => [
        'host' => 'localhost',
        'port' => 3306,
        'name' => 'uXXXXXX_odev',
        'user' => 'uXXXXXX_odev',
        'pass' => 'VOTRE_MOT_DE_PASSE',
        'charset' => 'utf8mb4',
    ],
];
