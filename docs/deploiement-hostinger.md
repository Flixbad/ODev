# Déploiement Hostinger (hébergement mutualisé)

ODev est exporté en **site statique** : pas besoin de Node.js sur le serveur Hostinger.

## Préparer les fichiers

Sur ta machine :

```bash
npm install
npm run build:hostinger
```

Cela génère :

- le dossier **`out/`** (à uploader)
- éventuellement **`odev-hostinger.zip`** (même contenu, pratique pour File Manager)

## Uploader sur Hostinger

1. Connecte-toi au **hPanel** Hostinger
2. Ouvre **Fichiers** → **Gestionnaire de fichiers**
3. Entre dans **`public_html`** (domaine principal)  
   ou le sous-dossier du domaine / sous-domaine concerné
4. Supprime le contenu par défaut Hostinger si besoin (`default.php`, page d’accueil temporaire…)
5. Uploade **tout le contenu** de `out/` (pas le dossier `out` lui-même)  
   - soit en glissant les fichiers  
   - soit en uploadant `odev-hostinger.zip` puis **Extraire**
6. Vérifie que tu as bien à la racine :
   - `index.html`
   - `.htaccess`
   - dossier `_next/`

## Après upload

- Ouvre ton domaine dans le navigateur
- Active le **SSL gratuit** (Let’s Encrypt) dans hPanel si ce n’est pas déjà fait
- Dans `public_html/.htaccess`, tu peux décommenter les lignes HTTPS pour forcer le HTTPS

## Mise à jour plus tard

```bash
npm run build:hostinger
```

Puis remplace les fichiers dans `public_html` (ou ré-extrais le nouveau zip).

## Important

| Oui | Non |
|-----|-----|
| Hébergement mutualisé classique | `npm start` / serveur Node sur le mutualisé |
| Upload de `out/` | Upload du projet source (`src/`, `node_modules/`) |
| Domaine ou sous-domaine | — |

Le formulaire de contact est une démo front-end (pas d’envoi email serveur). Pour un vrai envoi, brancher plus tard un service type Formspree / EmailJS / webhook.

## Dépannage

- **Page blanche** : vérifie que `index.html` est bien dans `public_html`, pas dans `public_html/out/`
- **CSS / JS cassés** : le dossier `_next` doit être présent à côté de `index.html`
- **404** : le `.htaccess` doit être uploadé (fichiers cachés visibles dans le File Manager)
