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
| `user1@mail.com` | `user1` |
| `user2@mail.com` | `user2` |
| `user3@mail.com` | `user3` |
| `user4@mail.com` | `user4` |
| `user5@mail.com` | `user5` |
| `user6@mail.com` | `user6` |
| `user7@mail.com` | `user7` |
| `user8@mail.com` | `user8` |
| `user9@mail.com` | `user9` |
| `user10@mail.com` | `user10` |
| `user11@mail.com` | `user11` |
| `user12@mail.com` | `user12` |
| `user13@mail.com` | `user13` |
| `user14@mail.com` | `user14` |
| `user15@mail.com` | `user15` |
| `user16@mail.com` | `user16` |
| `user17@mail.com` | `user17` |
| `user18@mail.com` | `user18` |
| `user19@mail.com` | `user19` |
| `user20@mail.com` | `user20` |
| `admin@mail.com` | `admin` |

Pour les comptes `userN`, le mot de passe est `userN` (par exemple, `user12@mail.com` utilise le mot de passe `user12`).

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