# Architecture

## Stack

- **Next.js 16** (App Router)
- **React 19**
- **TypeScript**
- **Tailwind CSS 4**
- **Framer Motion**

## Structure

```
src/
  app/                 # Routes & layout
  components/          # Sections UI
    ui/                # Primitives (Reveal, MagneticButton)
  lib/                 # Utilitaires
assets/                # Médias statiques du projet
docs/                  # Documentation
```

## Déploiement

Export statique (`output: "export"`) → dossier `out/` compatible Apache / Hostinger mutualisé.
Voir `docs/deploiement-hostinger.md`.

## Design system

Direction visuelle **Signal Forge** :

| Token   | Rôle                          |
|---------|-------------------------------|
| Ink     | Texte & surfaces sombres      |
| Paper   | Fond principal                |
| Mist    | Surfaces secondaires          |
| Signal  | Accent vermillon (CTA, focus) |
| Teal    | Accent secondaire technique   |

Typographie : **Syne** (display) + **Manrope** (corps).
