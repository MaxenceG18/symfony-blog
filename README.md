# Symfony Blog

Un blog complet développé avec Symfony 7, incluant la gestion des utilisateurs, articles, commentaires, catégories et un système de collaboration.

## Prérequis

- PHP 8.2 ou supérieur
- Composer
- MySQL/MariaDB
- Symfony CLI (optionnel mais recommandé)

## Installation

### 1. Cloner le projet

```bash
git clone https://github.com/MaxenceG18/symfony-blog.git
cd symfony-blog
```

### 2. Installer les dépendances

```bash
composer install
```

### 3. Configurer la base de données

Modifier la ligne `DATABASE_URL` dans le fichier `.env` :

```env
DATABASE_URL="mysql://root:@127.0.0.1:3306/symfony_blog?serverVersion=10.4.32-MariaDB&charset=utf8mb4"
```

> Adapter `root`, le mot de passe et `127.0.0.1:3306` selon votre configuration MySQL.

### 4. Créer la base de données

```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

### 5. Charger les données de test

```bash
php bin/console doctrine:fixtures:load
```

Répondre `yes` à la confirmation.

### 6. Lancer le serveur

Avec Symfony CLI :
```bash
symfony serve
```

## Comptes de test

| Email | Mot de passe | Rôle |
|-------|--------------|------|
| admin@blog.com | admin123 | Admin |
| marie@blog.com | admin123 | Admin |
| pierre@example.com | user123 | Utilisateur |
| sophie@example.com | user123 | Utilisateur |

## Fonctionnalités

- **Utilisateurs** : Inscription, connexion, profil, activation par admin
- **Articles** : CRUD, catégories, images, recherche, pagination
- **Commentaires** : Modération par admin
- **Collaboration** : Système de co-auteurs
- **Rôles** : Admin, Auteur, Utilisateur
- **Likes & Vues** : Système de favoris et compteur de vues
