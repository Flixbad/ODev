<?php

declare(strict_types=1);

/**
 * Installateur ODev API (Hostinger).
 * Ne pas nommer install.php — souvent bloqué chez Hostinger.
 * Crée config.php, importe le schéma, crée UN admin. Pas d’inscription publique.
 */

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
    $appUrl = rtrim(trim((string) ($_POST['app_url'] ?? '')), '/');

    try {
        if ($name === '' || $user === '' || $adminEmail === '' || strlen($adminPass) < 6) {
            throw new RuntimeException('Champs obligatoires manquants (mot de passe admin ≥ 6 caractères).');
        }
        if (!filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException('Email admin invalide.');
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

        $count = (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
        if ($count === 0) {
            $hash = password_hash($adminPass, PASSWORD_DEFAULT);
            $pdo->prepare('INSERT INTO users (name, email, password_hash) VALUES (?,?,?)')
                ->execute([$adminName, $adminEmail, $hash]);
        }
        // Si un admin existe déjà (ex. même base que gestion/), on le réutilise — jamais d’inscription publique.

        $pdo->prepare(
            'INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)
             ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)'
        )->execute(['api_installed_at', date('c')]);
        $export = var_export([
            'app_name' => 'ODev API',
            'app_url' => $appUrl,
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
        $success = 'Installation terminée. Supprimez setup.php pour plus de sécurité.';
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}

function h(?string $v): string
{
    return htmlspecialchars((string) $v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Configuration — ODev API</title>
  <style>
    :root { --bg:#0f1419; --card:#1a222c; --text:#e8eef4; --muted:#8b9aab; --accent:#3d8bfd; --err:#f07178; --ok:#7fd99a; }
    * { box-sizing: border-box; }
    body { margin:0; font-family: "Segoe UI", system-ui, sans-serif; background: radial-gradient(1200px 600px at 10% -10%, #1c2a3a, var(--bg)); color: var(--text); min-height:100vh; display:flex; align-items:center; justify-content:center; padding:1.5rem; }
    main { width:100%; max-width:420px; background:var(--card); border:1px solid #2a3644; border-radius:12px; padding:1.75rem; }
    h1 { margin:0 0 .35rem; font-size:1.4rem; }
    .eyebrow { color:var(--accent); text-transform:uppercase; letter-spacing:.08em; font-size:.72rem; margin:0 0 .5rem; }
    .muted { color:var(--muted); font-size:.92rem; line-height:1.45; }
    label { display:block; margin:.65rem 0; font-size:.85rem; color:var(--muted); }
    input { width:100%; margin-top:.3rem; padding:.55rem .65rem; border-radius:8px; border:1px solid #334155; background:#10161e; color:var(--text); }
    button, .btn { display:inline-block; margin-top:1rem; padding:.65rem 1rem; border:0; border-radius:8px; background:var(--accent); color:#fff; font-weight:600; cursor:pointer; text-decoration:none; }
    .flash { padding:.65rem .8rem; border-radius:8px; margin:1rem 0; font-size:.9rem; }
    .flash-error { background:rgba(240,113,120,.15); color:var(--err); }
    .flash-success { background:rgba(127,217,154,.15); color:var(--ok); }
    h2 { font-size:.95rem; margin:1.2rem 0 .4rem; color:var(--text); }
  </style>
</head>
<body>
  <main>
    <p class="eyebrow">ODev API</p>
    <h1>Configuration</h1>
    <p class="muted">Connectez MySQL Hostinger et créez le compte admin unique. Aucune inscription publique ensuite.</p>

    <?php if ($error): ?><div class="flash flash-error"><?= h($error) ?></div><?php endif; ?>
    <?php if ($success): ?>
      <div class="flash flash-success"><?= h($success) ?></div>
      <p><a class="btn" href="index.php">Tester l’API</a></p>
    <?php elseif ($done): ?>
      <div class="flash flash-success">Déjà configuré. Supprimez setup.php si ce n’est pas fait.</div>
      <p><a class="btn" href="index.php">API</a></p>
    <?php else: ?>
      <form method="post">
        <h2>Base MySQL</h2>
        <label>Hôte <input name="db_host" value="localhost" required></label>
        <label>Port <input name="db_port" type="number" value="3306" required></label>
        <label>Nom de la base <input name="db_name" required placeholder="uXXXXXX_odev"></label>
        <label>Utilisateur <input name="db_user" required></label>
        <label>Mot de passe <input name="db_pass" type="password" required></label>
        <h2>Compte admin (unique)</h2>
        <label>URL API (optionnel) <input name="app_url" placeholder="https://domaine.fr/api"></label>
        <label>Entreprise <input name="company_name" value="ODev — Darren O'Sullivan"></label>
        <label>Nom <input name="admin_name" value="Darren O'Sullivan" required></label>
        <label>Email <input name="admin_email" type="email" required></label>
        <label>Mot de passe <input name="admin_pass" type="password" minlength="6" required></label>
        <button type="submit">Configurer</button>
      </form>
    <?php endif; ?>
  </main>
</body>
</html>
