# Déploiement — ODev Gestion (Hostinger + phpMyAdmin)

## 1. Base de données

Dans **hPanel → Bases de données MySQL** :

1. Créer une base (ex. `uXXXX_odev`)
2. Créer un utilisateur avec mot de passe fort
3. Lier l’utilisateur à la base (tous les privilèges)

Note les valeurs : **hôte** (souvent `localhost`), **nom**, **user**, **mot de passe**.

## 2. Fichiers

Uploade le **contenu** du dossier `gestion/` vers :

```
public_html/gestion/
```

Tu dois voir à cet endroit : `index.php`, `setup.php`, `assets/`, `app/`, `sql/`, etc.

## 3. Configuration (important)

Hostinger bloque souvent `install.php` → utilise **`setup.php`** :

1. Ouvre `https://ton-domaine/gestion/setup.php`
2. Remplis MySQL + compte admin (ce sera le **seul** compte)
3. Valide
4. Va sur `https://ton-domaine/gestion/` → page d’accueil publique
5. Clique **Connexion** (pas d’inscription)
6. **Supprime `setup.php` et `install.php`**

Si tu as un 404 sur `install.php`, c’est normal : utilise `setup.php`.

## 4. Accès public vs privé

| URL | Comportement |
|-----|----------------|
| `/gestion/` | Accueil public + bouton Connexion |
| `/gestion/index.php?r=login` | Formulaire de connexion |
| `/gestion/index.php?r=dashboard` | Back-office (login requis) |

Aucune inscription publique.

## 5. Alternative phpMyAdmin

1. Importer `sql/schema.sql`
2. Copier `config.example.php` → `config.php` et remplir les identifiants
3. Créer l’admin via `setup.php` (ou hash bcrypt manuel)

## 6. Modules après connexion

| Module | Rôle |
|--------|------|
| Clients | Carnet d’adresses / fiches |
| Devis | Propositions commerciales |
| Factures | Facturation + paiements |
| Compta | Suivi encaissements / reste dû |
