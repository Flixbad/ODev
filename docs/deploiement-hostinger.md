# Déploiement unifié ODev (site + CRM) — Hostinger

Un **seul site** : vitrine Next.js + espace CRM (`/connexion`, `/espace`) + API PHP MySQL (`/api`).

## Build local

```bash
npm install
npm run build:hostinger
```

Génère :
- `deploy-hostinger/` (à uploader)
- `odev-hostinger.zip`

## Upload Hostinger

1. Uploade le **contenu** de `deploy-hostinger/` dans **`public_html/`**
2. Vérifie la présence de :
   - `index.html` (accueil)
   - `connexion/`
   - `espace/`
   - `api/` (PHP)

## Base MySQL (phpMyAdmin / hPanel)

1. Crée une base + utilisateur MySQL
2. Ouvre **`https://ton-domaine/api/setup.php`**
3. Remplis les identifiants + crée **ton seul compte admin**
4. **Supprime `api/setup.php`**

> N’utilise pas `install.php` — souvent bloqué / 404 chez Hostinger.

## Utilisation

| URL | Rôle |
|-----|------|
| `/` | Site vitrine public |
| `/connexion/` | Connexion (pas d’inscription) |
| `/espace/` | CRM (clients, devis, factures, compta) |

Le bouton **Connexion** de la barre de navigation mène à `/connexion/`.

## Important

- Pas de création de compte publique
- L’ancien dossier `gestion/` (UI PHP séparée) n’est plus nécessaire pour le site
- Ne commit jamais `api/config.php`
