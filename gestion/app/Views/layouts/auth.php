<?php /** @var string $viewFile */ ?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($title ?? 'Connexion') ?></title>
  <link rel="stylesheet" href="assets/css/app.css">
</head>
<body class="auth-body">
  <?php require $viewFile; ?>
</body>
</html>
