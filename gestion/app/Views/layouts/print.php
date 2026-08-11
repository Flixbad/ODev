<?php /** @var string $viewFile */ $cfg = app_config(); ?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <title><?= e($title ?? 'Document') ?></title>
  <link rel="stylesheet" href="assets/css/app.css">
</head>
<body style="background:#fff;padding:1.5rem">
  <?php require $viewFile; ?>
  <script>window.print();</script>
</body>
</html>
