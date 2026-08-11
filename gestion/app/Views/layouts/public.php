<?php
/** @var string $viewFile */
$flashes = consume_flashes();
$cfgName = 'ODev Gestion';
try {
    if (is_file(base_path('config.php'))) {
        $cfgName = app_config()['app_name'] ?? $cfgName;
    }
} catch (Throwable $e) {
    // config absente
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($title ?? 'Accueil') ?> — <?= e($cfgName) ?></title>
  <link rel="stylesheet" href="assets/css/app.css">
</head>
<body>
  <header class="public-nav">
    <a class="brand" href="<?= e(url('home')) ?>">O<span>Dev</span> <small>Gestion</small></a>
    <nav>
      <a class="btn btn-ink" href="<?= e(url('login')) ?>">Connexion</a>
    </nav>
  </header>

  <main class="public-main">
    <?php foreach ($flashes as $flash): ?>
      <div class="flash flash-<?= e($flash['type'] === 'error' ? 'error' : 'success') ?>" style="max-width:1100px;margin:1rem auto 0;"><?= e($flash['message']) ?></div>
    <?php endforeach; ?>
    <?php require $viewFile; ?>
  </main>

  <footer class="public-foot">
    <span>ODev — accès réservé</span>
    <a href="<?= e(url('login')) ?>">Connexion</a>
  </footer>
</body>
</html>
