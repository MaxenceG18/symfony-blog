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

## Pages d'erreur personnalisées

Les pages d'erreur personnalisées (404, 500, etc.) ne s'affichent qu'en **mode production**.

### Tester en mode développement

En mode `dev`, Symfony affiche le Profiler pour faciliter le débogage. Pour prévisualiser vos pages d'erreur sans changer d'environnement, utilisez les routes spéciales :

- `http://localhost:8000/_error/404` → Page 404
- `http://localhost:8000/_error/500` → Page 500
- `http://localhost:8000/_error/403` → Page 403

### Passer en mode production

1. **Modifier le fichier `.env`** :
   ```env
   APP_ENV=prod
   APP_DEBUG=0
   ```

2. **S'assurer que `APP_SECRET` est défini** :
   ```env
   APP_SECRET=votre_clé_secrète_ici
   ```
   > ⚠️ Utilisez une clé aléatoire et sécurisée. Vous pouvez en générer une avec : `php -r "echo bin2hex(random_bytes(16));"`

3. **Vider le cache** :
   ```bash
   php bin/console cache:clear --env=prod
   ```

4. **Pour revenir en mode dev** :
   ```env
   APP_ENV=dev
   APP_DEBUG=1
   ```

## Fonctionnalités

- **Utilisateurs** : Inscription, connexion, profil, activation par admin
- **Articles** : CRUD, catégories, images, recherche, pagination
- **Commentaires** : Modération par admin
- **Collaboration** : Système de co-auteurs
- **Rôles** : Admin, Auteur, Utilisateur
- **Likes & Vues** : Système de favoris et compteur de vues
