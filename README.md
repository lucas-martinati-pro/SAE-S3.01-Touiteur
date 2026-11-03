# SAE-S3.01 - Touiteur

Touiteur.app, publication et partage de Touites. Elle est codée en PHP. Elle utilise Bootstrap 5 et MySQL.

## Groupe C

- MARTINATI Lucas
- CHARLES Samuel
- LE MADEC Jean

---

## 👥 Comptes utilisateurs de test

La base de données est dans [`sql/Touiteur_db.sql`](sql/Touiteur_db.sql). Les comptes sont prêts pour tester.

| Email | Mot de passe |
|:---|:---:|
| `users1@mail.com` | `users1` |
| `users2@mail.com` | `users2` |
| `users3@mail.com` | `users3` |
| `users4@mail.com` | `users4` |
| `admin@mail.com` | `admin` |

Vous pouvez créer un compte sur la page d'inscription (`?action=register`). Le mot de passe doit faire 10 caractères minimum. Il faut une majuscule, une minuscule, un chiffre et un caractère spécial.

---

## Installation des dépendances

Le fichier `composer.json` est dans `src/`. Pour installer le dossier `vendor/` :

```bash
cd src
composer install
```

Le dossier `vendor/` est créé seul.

## Mise à jour des dépendances

Pour mettre à jour `composer.lock` :

```bash
cd src
composer update
```