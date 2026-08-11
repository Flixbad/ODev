<div class="panel">
  <h1><?= e($title ?? 'Erreur') ?></h1>
  <p class="muted"><?= e($message ?? 'Une erreur est survenue.') ?></p>
  <a class="btn" href="<?= e(url('dashboard')) ?>">Retour</a>
</div>
