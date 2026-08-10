# ODev

Site vitrine de **ODev**, micro-entreprise fictive de développement web dirigée par **Darren O'Sullivan**.

Stack : Next.js · React · Node-ready mindset · animations Framer Motion.

## Aperçu

ODev propose :

- Applications **React** / Next.js
- Back-ends **Node.js**
- **Sites vitrine**
- Outils de **gestion d'entreprise**
- Bases de données & UX/UI

## Démarrage rapide

```bash
npm install
npm run dev
```

→ [http://localhost:3000](http://localhost:3000)

## Scripts

| Commande                  | Description                                      |
|---------------------------|--------------------------------------------------|
| `npm run dev`             | Serveur de développement                         |
| `npm run build`           | Build statique → dossier `out/`                  |
| `npm run build:hostinger` | Build + zip prêt pour Hostinger mutualisé        |
| `npm start`               | Non utilisé pour Hostinger (export statique)       |
| `npm run lint`            | ESLint                                           |

## Déploiement Hostinger (mutualisé)

```bash
npm run build:hostinger
```

Uploade le contenu de **`out/`** (ou `odev-hostinger.zip`) dans **`public_html`**.

Guide détaillé : [docs/deploiement-hostinger.md](docs/deploiement-hostinger.md)

## Arborescence

```
ODev/
├── assets/
├── docs/
│   ├── architecture.md
│   └── installation.md
├── src/
│   ├── app/
│   ├── components/
│   └── lib/
├── .env.example
├── CHANGELOG.md
├── CONTRIBUTING.md
├── LICENSE
└── README.md
```

## Documentation

- [Installation](docs/installation.md)
- [Architecture](docs/architecture.md)
- [Contribuer](CONTRIBUTING.md)

## Licence

MIT — voir [LICENSE](LICENSE).

> Entreprise fictive — projet de démonstration.
