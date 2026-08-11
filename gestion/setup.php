<?php

declare(strict_types=1);

session_start();

$error = null;
$success = null;
$done = is_file(__DIR__ . '/config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$done) {
    $host = trim((string) ($_POST['db_host'] ?? 'localhost'));
    $port = (int) ($_POST['db_port'] ?? 3306);
    $name = trim((string) ($_POST['db_name'] ?? ''));
    $user = trim((string) ($_POST['db_user'] ?? ''));
    $pass = (string) ($_POST['db_pass'] ?? '');
    $adminName = trim((string) ($_POST['admin_name'] ?? "Darren O'Sullivan"));
    $adminEmail = trim((string) ($_POST['admin_email'] ?? ''));
    $adminPass = (string) ($_POST['admin_pass'] ?? '');
    $company = trim((string) ($_POST['company_name'] ?? 'ODev'));

    try {
        if ($name === '' || $user === '' || $adminEmail === '' || strlen($adminPass) < 6) {
            throw new RuntimeException('Champs obligatoires manquants (mot de passe admin ≥ 6 caractères).');
        }

        $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', $host, $port, $name);
        $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

        $schema = file_get_contents(__DIR__ . '/sql/schema.sql');
        if ($schema === false) {
            throw new RuntimeException('Fichier sql/schema.sql introuvable.');
        }

        $statements = array_filter(array_map('trim', preg_split('/;\s*\n/', $schema) ?: []));
        foreach ($statements as $statement) {
            if ($statement === '' || str_starts_with($statement, '--')) {
                continue;
            }
            $pdo->exec($statement);
        }

        $hash = password_hash($adminPass, PASSWORD_DEFAULT);
        $pdo->prepare('INSERT INTO users (name, email, password_hash) VALUES (?,?,?)')
            ->execute([$adminName, $adminEmail, $hash]);

        $pdo->prepare('INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)')
            ->execute(['installed_at', date('c')]);

        $export = var_export([
            'app_name' => 'ODev Gestion',
            'app_url' => '',
            'timezone' => 'Europe/Paris',
            'currency' => 'USD',
            'currency_symbol' => '$',
            'default_tax_rate' => 0,
            'company' => [
                'name' => $company,
                'email' => $adminEmail,
                'phone' => '',
                'address' => '',
            ],
            'db' => [
                'host' => $host,
                'port' => $port,
                'name' => $name,
                'user' => $user,
                'pass' => $pass,
                'charset' => 'utf8mb4',
            ],
        ], true);

        if (file_put_contents(__DIR__ . '/config.php', "<?php\n\nreturn " . $export . ";\n") === false) {
            throw new RuntimeException('Impossible d’écrire config.php (vérifiez les droits).');
        }

        $done = true;
        $success = 'Installation terminée. Supprimez setup.php (et install.php) pour plus de sécurité.';
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Configuration — ODev Gestion</title>
  <link rel="stylesheet" href="assets/css/app.css">
</head>
<body class="auth-body">
  <main class="auth-card">
    <p class="eyebrow">ODev Gestion</p>
    <h1>Configuration</h1>
    <p class="muted">Connectez MySQL Hostinger (phpMyAdmin) et créez votre compte admin. Pas d’inscription publique ensuite.</p>

    <?php if ($error): ?><div class="flash flash-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <?php if ($success): ?>
      <div class="flash flash-success"><?= htmlspecialchars($success) ?></div>
      <p><a class="btn btn-primary" href="index.php">Aller à l’accueil</a></p>
    <?php elseif ($done): ?>
      <div class="flash flash-success">Déjà configuré.</div>
      <p><a class="btn btn-primary" href="index.php">Accueil</a> <a class="btn" href="index.php?r=login">Connexion</a></p>
    <?php else: ?>
      <form method="post" class="stack">
        <h2 class="h-section">Base MySQL</h2>
        <label>Hôte <input name="db_host" value="localhost" required></label>
        <label>Port <input name="db_port" type="number" value="3306" required></label>
        <label>Nom de la base <input name="db_name" required placeholder="uXXXXXX_odev"></label>
        <label>Utilisateur <input name="db_user" required></label>
        <label>Mot de passe <input name="db_pass" type="password" required></label>
        <h2 class="h-section">Compte admin (unique)</h2>
        <label>Entreprise <input name="company_name" value="ODev — Darren O'Sullivan"></label>
        <label>Nom <input name="admin_name" value="Darren O'Sullivan" required></label>
        <label>Email <input name="admin_email" type="email" required></label>
        <label>Mot de passe <input name="admin_pass" type="password" minlength="6" required></label>
        <button class="btn btn-primary" type="submit">Configurer</button>
      </form>
    <?php endif; ?>
  </main>
</body>
</html>
