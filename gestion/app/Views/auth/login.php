<?php
$flashes = consume_flashes();
?>
<main class="auth-card">
  <p class="eyebrow">ODev Gestion</p>
  <h1>Connexion</h1>
  <p class="muted">Accès réservé. Aucune création de compte en ligne.</p>
  <?php foreach ($flashes as $flash): ?>
    <div class="flash flash-<?= e($flash['type'] === 'error' ? 'error' : 'success') ?>"><?= e($flash['message']) ?></div>
  <?php endforeach; ?>
  <form method="post" action="<?= e(url('login')) ?>" class="stack" autocomplete="on">
    <?= csrf_field() ?>
    <label>Email <input type="email" name="email" required autofocus autocomplete="username"></label>
    <label>Mot de passe <input type="password" name="password" required autocomplete="current-password"></label>
    <button class="btn btn-primary" type="submit">Se connecter</button>
  </form>
  <p class="muted" style="margin-top:1.25rem;font-size:.85rem">
    <a href="<?= e(url('home')) ?>">← Retour à l’accueil</a>
  </p>
</main>
