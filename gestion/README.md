# ODev Gestion

CRM / back-office PHP + MySQL pour **ODev** (Darren O'Sullivan).

Compatible **Hostinger mutualisé** + **phpMyAdmin**.

## Fonctionnalités

- Carnet clients (recherche, fiches, notes, statuts)
- Devis (lignes, TVA, statuts, impression / PDF navigateur)
- Factures (création manuelle ou depuis un devis)
- Paiements & suivi d’encaissement
- Comptabilité (période, CA, reste dû, graphique mensuel)
- Auth admin + CSRF

## Déploiement Hostinger

1. Crée une base MySQL dans hPanel (+ utilisateur)
2. Uploade le dossier `gestion/` dans `public_html/gestion/`
3. Ouvre **`https://votredomaine.fr/gestion/setup.php`**  
   (évite `install.php`, souvent en 404 / bloqué chez Hostinger)
4. Configure MySQL + compte admin unique
5. Accueil public : `/gestion/` → bouton **Connexion** seulement
6. Supprime `setup.php` et `install.php`


## Local (optionnel)

- PHP 8.1+
- MySQL / MariaDB
- `php -S localhost:8080 -t gestion` depuis la racine du repo, ou pointe le vhost sur `gestion/`

## Sécurité

- Ne commit jamais `config.php`
- Supprime `install.php` après installation
- Les dossiers `app/`, `sql/`, `storage/` sont protégés par `.htaccess`
