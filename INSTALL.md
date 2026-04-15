# Installation — CineMap

Ce guide détaille toutes les étapes pour installer et lancer le projet en local.

---

## Prérequis

- PHP >= 8.x
- Composer
- Node.js & npm
- MySQL ou SQLite

---

## Étapes d'installation

### 1. Cloner le projet

```bash
git clone git@github.com:RustyRory/B3dev-TP_framework_php.git
cd B3dev-TP_framework_php
```

### 2. Installer les dépendances PHP

```bash
composer create-project laravel/laravel cinemap-app
```

### 3. Installer les dépendances Node

```bash
cd cinemap-app/
npm install
```

### 4. Copier et configurer le fichier d'environnement

```bash
cp .env.example .env
```

Renseignez ensuite dans `.env` :

```env
# Base de données
DB_CONNECTION=sqlite
# ou mysql, pgsql...
DB_DATABASE=/chemin/vers/database.sqlite

# OAuth (ex. GitHub)
GITHUB_CLIENT_ID=xxx
GITHUB_CLIENT_SECRET=xxx
GITHUB_REDIRECT_URI=http://localhost:8000/auth/github/callback

# Stripe
STRIPE_KEY=pk_test_xxx
STRIPE_SECRET=sk_test_xxx

# JWT
JWT_SECRET=xxx
```

### 5. Générer la clé de l'application

```bash
php artisan key:generate
```

### 6. Lancer les migrations

```bash
php artisan migrate
```

> Si Laravel vous demande de créer un fichier de BDD (SQLite), répondez `oui`.

### 7. (Optionnel) Lancer les seeders

```bash
php artisan migrate:fresh --seed
```

### 8. Lancer le serveur de développement

```bash
composer run dev
```

L'application est accessible sur [http://localhost:8000](http://localhost:8000).

> **En cas de bug avec `composer run dev`**, modifiez la commande `dev` dans `composer.json` :
>
> ```bash
> npx concurrently "php artisan serve" "php artisan queue:listen --tries=1 --timeout=0" "npm run dev" --names=server,queue,vite --kill-others
> ```
>
> Si vous avez une erreur `pail`, supprimez la partie `"php artisan pail --timeout=0"`.  
> Si vous avez une erreur dans `resources/js/app.js`, supprimez la ligne `import './bootstrap';`.

---

## Worker de queue

Le worker est inclus dans `composer run dev`. Pour le lancer séparément :

```bash
php artisan queue:listen --tries=1 --timeout=0
```

---

## Commande planifiée

Supprime les emplacements créés depuis plus de 14 jours et qui ont moins de 2 upvotes.

Pour tester manuellement :

```bash
php artisan app:clean-old-locations
```

---

## Stripe (abonnement API)

Pour tester un paiement ou un abonnement, utilisez la carte de test Stripe : `4242 4242 4242 4242`.

---

## API JSON — JWT + Abonnement Stripe

La route `/api/films/{film}/locations` nécessite **à la fois** :
- un abonnement Stripe actif
- un token JWT valide

### Générer la clé JWT

```bash
php artisan jwt:secret
```

Copiez la valeur générée dans `.env` sous `JWT_SECRET`.

### Obtenir un token et appeler l'API

```bash
# 1. S'authentifier pour obtenir un token
POST /api/auth/login
{ "email": "...", "password": "..." }

# 2. Appeler l'API avec le token
GET /api/films/{film}/locations
Authorization: Bearer <token>
```

---

## Formatage du code (Laravel Pint)

```bash
./vendor/bin/pint
```

---

## MCP (Model Context Protocol)

Le serveur MCP expose deux outils en lecture seule :

- `list_films` — liste tous les films
- `get_locations_for_film` — retourne les emplacements d'un film

Consultez [docs/](docs/) pour la configuration et le lancement du serveur MCP.
