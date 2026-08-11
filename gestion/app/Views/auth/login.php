<?php
$flashes = consume_flashes();
?>
<main class="auth-card">
  <p class="eyebrow">ODev Gestion</p>
  <h1>Connexion</h1>
  <p class="muted">Accédez à votre carnet clients, devis et factures.</p>
  <?php foreach ($flashes as $flash): ?>
    <div class="flash flash-<?= e($flash['type'] === 'error' ? 'error' : 'success') ?>"><?= e($flash['message']) ?></div>
  <?php endforeach; ?>
  <form method="post" action="<?= e(url('login')) ?>" class="stack">
    <?= csrf_field() ?>
    <label>Email <input type="email" name="email" required autofocus></label>
    <label>Mot de passe <input type="password" name="password" required></label>
    <button class="btn btn-primary" type="submit">Se connecter</button>
  </form>
</main>
