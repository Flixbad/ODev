<?php

declare(strict_types=1);

/**
 * Configuration ODev API (Hostinger).
 * - Branche MySQL + crée le compte admin
 * - Si config.php existe déjà sans admin → formulaire "créer le compte"
 */

session_start();

$error = null;
$success = null;
$configFile = __DIR__ . '/config.php';
$hasConfig = is_file($configFile);
$adminCount = null;
$config = null;

if ($hasConfig) {
    try {
        $config = require $configFile;
        $db = $config['db'];
        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            $db['host'],
            (int) ($db['port'] ?? 3306),
            $db['name'],
            $db['charset'] ?? 'utf8mb4'
        );
        $pdo = new PDO($dsn, $db['user'], $db['pass'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        // Tables absentes → traiter comme non installé côté users
        $tables = $pdo->query("SHOW TABLES LIKE 'users'")->fetchAll();
        if ($tables) {
            $adminCount = (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
        } else {
            $adminCount = 0;
        }
    } catch (Throwable $e) {
        $error = 'Config trouvée mais MySQL inaccessible : ' . $e->getMessage();
        $adminCount = null;
    }
}

$needsAdmin = $hasConfig && $adminCount === 0;
$fullyDone = $hasConfig && $adminCount !== null && $adminCount > 0;

function h(?string $v): string
{
    return htmlspecialchars((string) $v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function run_schema(PDO $pdo): void
{
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
}

function write_config(array $data): void
{
    $export = var_export($data, true);
    if (file_put_contents(__DIR__ . '/config.php', "<?php\n\nreturn " . $export . ";\n") === false) {
        throw new RuntimeException('Impossible d’écrire config.php (vérifiez les droits du dossier api/).');
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string) ($_POST['action'] ?? 'full');

    try {
        // --- Cas A : config OK, créer uniquement l’admin ---
        if ($action === 'create_admin') {
            if (!$hasConfig || !is_array($config)) {
                throw new RuntimeException('Aucune config MySQL. Relancez la configuration complète.');
            }
            $adminName = trim((string) ($_POST['admin_name'] ?? "Darren O'Sullivan"));
            $adminEmail = trim((string) ($_POST['admin_email'] ?? ''));
            $adminPass = (string) ($_POST['admin_pass'] ?? '');
            $company = trim((string) ($_POST['company_name'] ?? ($config['company']['name'] ?? 'ODev')));

            if ($adminEmail === '' || strlen($adminPass) < 6) {
                throw new RuntimeException('Email et mot de passe (≥ 6) obligatoires.');
            }
            if (!filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
                throw new RuntimeException('Email invalide.');
            }

            $db = $config['db'];
            $dsn = sprintf(
                'mysql:host=%s;port=%d;dbname=%s;charset=%s',
                $db['host'],
                (int) ($db['port'] ?? 3306),
                $db['name'],
                $db['charset'] ?? 'utf8mb4'
            );
            $pdo = new PDO($dsn, $db['user'], $db['pass'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
            run_schema($pdo);

            $count = (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
            if ($count > 0) {
                throw new RuntimeException('Un compte existe déjà. Utilisez la page Connexion du site.');
            }

            $hash = password_hash($adminPass, PASSWORD_DEFAULT);
            $pdo->prepare('INSERT INTO users (name, email, password_hash) VALUES (?,?,?)')
                ->execute([$adminName, $adminEmail, $hash]);

            $config['company']['name'] = $company;
            $config['company']['email'] = $adminEmail;
            write_config($config);

            $fullyDone = true;
            $needsAdmin = false;
            $success = 'Compte admin créé. Tu peux te connecter sur /connexion/ puis supprimer setup.php.';
        }

        // --- Cas B : installation complète ---
        if ($action === 'full' && !$fullyDone) {
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

            if ($name === '' || $user === '' || $adminEmail === '' || strlen($adminPass) < 6) {
                throw new RuntimeException('Champs obligatoires manquants (mot de passe admin ≥ 6 caractères).');
            }
            if (!filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
                throw new RuntimeException('Email admin invalide.');
            }

            $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', $host, $port, $name);
            $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
            run_schema($pdo);

            $count = (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
            if ($count === 0) {
                $hash = password_hash($adminPass, PASSWORD_DEFAULT);
                $pdo->prepare('INSERT INTO users (name, email, password_hash) VALUES (?,?,?)')
                    ->execute([$adminName, $adminEmail, $hash]);
            }

            $pdo->prepare(
                'INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)
                 ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)'
            )->execute(['api_installed_at', date('c')]);

            write_config([
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
            ]);

            $hasConfig = true;
            $fullyDone = true;
            $needsAdmin = false;
            $adminCount = max(1, $count);
            $success = 'Configuration terminée. Connecte-toi sur /connexion/ puis supprime setup.php.';
        }
    } catch (Throwable $e) {
        $error = $e->getMessage();
        $success = null;
        // Recalculer l’état
        $hasConfig = is_file($configFile);
        $fullyDone = false;
        $needsAdmin = $hasConfig;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Configuration — ODev API</title>
  <style>
    :root { --bg:#e9ecf1; --card:#fff; --text:#12151c; --muted:#2a303c; --accent:#e63312; --err:#e63312; --ok:#1a7a72; --line:rgba(18,21,28,.12); }
    * { box-sizing: border-box; }
    body { margin:0; font-family: "Segoe UI", system-ui, sans-serif; background: var(--bg); color: var(--text); min-height:100vh; display:flex; align-items:center; justify-content:center; padding:1.5rem; }
    main { width:100%; max-width:440px; background:var(--card); border:1px solid var(--line); padding:1.75rem; }
    h1 { margin:0 0 .35rem; font-size:1.5rem; letter-spacing:-.02em; }
    .eyebrow { color:var(--accent); text-transform:uppercase; letter-spacing:.18em; font-size:.7rem; font-weight:700; margin:0 0 .5rem; }
    .muted { color:var(--muted); font-size:.92rem; line-height:1.45; }
    label { display:block; margin:.7rem 0; font-size:.72rem; text-transform:uppercase; letter-spacing:.12em; color:var(--muted); }
    input { width:100%; margin-top:.35rem; padding:.7rem .75rem; border:0; border-bottom:1px solid var(--line); background:#f7f8fa; color:var(--text); font:inherit; text-transform:none; letter-spacing:normal; }
    button, .btn { display:inline-block; margin-top:1rem; padding:.7rem 1.1rem; border:1px solid var(--accent); background:var(--accent); color:#fff; font-weight:600; cursor:pointer; text-decoration:none; font:inherit; }
    .btn-ghost { background:transparent; color:var(--text); border-color:var(--line); }
    .flash { padding:.75rem .9rem; margin:1rem 0; font-size:.9rem; border:1px solid var(--line); }
    .flash-error { background:rgba(230,51,18,.08); border-color:rgba(230,51,18,.25); color:var(--err); }
    .flash-success { background:rgba(26,122,114,.1); border-color:rgba(26,122,114,.3); color:var(--ok); }
    .flash-warn { background:rgba(196,92,0,.1); border-color:rgba(196,92,0,.3); }
    h2 { font-size:.8rem; margin:1.2rem 0 .4rem; text-transform:uppercase; letter-spacing:.14em; color:var(--muted); }
  </style>
</head>
<body>
  <main>
    <p class="eyebrow">ODev API</p>
    <h1>Configuration</h1>
    <p class="muted">MySQL Hostinger + compte admin unique. Pas d’inscription publique.</p>

    <?php if ($error): ?><div class="flash flash-error"><?= h($error) ?></div><?php endif; ?>
    <?php if ($success): ?>
      <div class="flash flash-success"><?= h($success) ?></div>
      <p>
        <a class="btn" href="/connexion/">Aller à Connexion</a>
        <a class="btn btn-ghost" href="index.php">Tester l’API</a>
      </p>
    <?php elseif ($fullyDone): ?>
      <div class="flash flash-success">
        Déjà configuré (<?= (int) $adminCount ?> compte admin).
        Connecte-toi sur le site, puis <strong>supprime setup.php</strong>.
      </div>
      <p>
        <a class="btn" href="/connexion/">Connexion</a>
      </p>
      <p class="muted" style="margin-top:1rem;font-size:.8rem">
        Mot de passe oublié ? Dans phpMyAdmin, vide la table <code>users</code>,
        puis rouvre cette page pour recréer le compte.
      </p>
    <?php elseif ($needsAdmin): ?>
      <div class="flash flash-warn">
        MySQL est déjà branché (<code>config.php</code> présent),
        mais <strong>aucun compte admin</strong> n’existe encore.
      </div>
      <form method="post">
        <input type="hidden" name="action" value="create_admin">
        <h2>Créer le compte admin</h2>
        <label>Entreprise <input name="company_name" value="<?= h($config['company']['name'] ?? "ODev — Darren O'Sullivan") ?>"></label>
        <label>Nom <input name="admin_name" value="Darren O'Sullivan" required></label>
        <label>Email <input name="admin_email" type="email" required placeholder="toi@email.com"></label>
        <label>Mot de passe <input name="admin_pass" type="password" minlength="6" required></label>
        <button type="submit">Créer mon compte</button>
      </form>
    <?php else: ?>
      <form method="post">
        <input type="hidden" name="action" value="full">
        <h2>Base MySQL</h2>
        <label>Hôte <input name="db_host" value="localhost" required></label>
        <label>Port <input name="db_port" type="number" value="3306" required></label>
        <label>Nom de la base <input name="db_name" required placeholder="uXXXXXX_odev"></label>
        <label>Utilisateur <input name="db_user" required></label>
        <label>Mot de passe <input name="db_pass" type="password" required></label>
        <h2>Compte admin</h2>
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
