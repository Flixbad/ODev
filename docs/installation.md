# Guide d'installation

## Prérequis

- Node.js ≥ 20
- npm ≥ 10

## Installation

```bash
git clone <url-du-repo>
cd ODev
npm install
```

## Lancement

```bash
npm run dev
```

Ouvrir [http://localhost:3000](http://localhost:3000).

## Production (Hostinger mutualisé)

```bash
npm run build:hostinger
```

Uploade le contenu de `out/` dans `public_html`.  
Voir [deploiement-hostinger.md](./deploiement-hostinger.md).

## Variables d'environnement

Aucune clé obligatoire pour la démo. Voir `.env.example` pour les futures intégrations (email, analytics).
