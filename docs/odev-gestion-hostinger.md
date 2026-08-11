# Déploiement — ODev Gestion (Hostinger + phpMyAdmin)

## 1. Base de données

Dans **hPanel → Bases de données MySQL** :

1. Créer une base (ex. `uXXXX_odev`)
2. Créer un utilisateur avec mot de passe fort
3. Lier l’utilisateur à la base (tous les privilèges)

Note les valeurs : **hôte** (souvent `localhost`), **nom**, **user**, **mot de passe**.

## 2. Fichiers

Uploade le contenu du dossier `gestion/` vers :

```
public_html/gestion/
```

(ou un sous-domaine dédié)

## 3. Installation

1. Va sur `https://ton-domaine/gestion/install.php`
2. Remplis le formulaire MySQL + compte admin
3. Clique **Installer**
4. Connecte-toi
5. **Supprime `install.php`** (File Manager)

## 4. Alternative phpMyAdmin

1. phpMyAdmin → importer `sql/schema.sql`
2. Copier `config.example.php` → `config.php` et remplir les identifiants
3. Créer un user manuellement (hash bcrypt) ou passer par install.php uniquement pour le compte

## 5. Usage

| Module | Rôle |
|--------|------|
| Clients | Carnet d’adresses / fiches |
| Devis | Propositions commerciales |
| Factures | Facturation + paiements |
| Compta | Suivi encaissements / reste dû |

Impression devis/facture : bouton **Imprimer / PDF** (impression navigateur → enregistrer en PDF).
