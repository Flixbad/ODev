<?php
/** @var string $viewFile */
$user = \App\Core\Auth::user();
$route = request_route();
$cfg = app_config();
$flashes = consume_flashes();
$isActive = static fn(string $prefix): string => str_starts_with($route, $prefix) ? 'active' : '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($title ?? 'ODev Gestion') ?> — <?= e($cfg['app_name']) ?></title>
  <link rel="stylesheet" href="assets/css/app.css">
</head>
<body>
<div class="app-shell">
  <aside class="sidebar no-print">
    <div>
      <div class="brand">O<span>Dev</span></div>
      <div class="muted" style="color:rgba(255,255,255,.45);font-size:.8rem;margin-top:.25rem">Gestion</div>
    </div>
    <nav class="nav">
      <a class="<?= $isActive('dashboard') || $route === '' ? 'active' : '' ?>" href="<?= e(url('dashboard')) ?>">Tableau de bord</a>
      <a class="<?= $isActive('clients') ?>" href="<?= e(url('clients')) ?>">Clients</a>
      <a class="<?= $isActive('devis') ?>" href="<?= e(url('devis')) ?>">Devis</a>
      <a class="<?= $isActive('factures') ?>" href="<?= e(url('factures')) ?>">Factures</a>
      <a class="<?= $isActive('compta') ?>" href="<?= e(url('compta')) ?>">Compta</a>
      <a href="<?= e(url('logout')) ?>">Déconnexion</a>
    </nav>
    <div class="sidebar-foot">
      <?= e($user['name'] ?? '') ?><br>
      <?= e($cfg['company']['name'] ?? 'ODev') ?>
    </div>
  </aside>
  <main class="content">
    <?php foreach ($flashes as $flash): ?>
      <div class="flash flash-<?= e($flash['type'] === 'error' ? 'error' : 'success') ?> no-print"><?= e($flash['message']) ?></div>
    <?php endforeach; ?>
    <?php require $viewFile; ?>
  </main>
</div>
<script src="assets/js/app.js"></script>
</body>
</html>
