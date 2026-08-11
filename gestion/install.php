<?php

declare(strict_types=1);

/**
 * Ancien nom souvent bloqué par Hostinger (filtre "install").
 * Redirige vers setup.php
 */
header('Location: setup.php', true, 302);
exit;
