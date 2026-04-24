# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [Unreleased]

### Ajouté

- 

### Changement

- 

### Corrigé

- 

### Supprimé

-
---

## [0.11.0] - 24/04/2026

### Ajouté

- Service `cinemap` ajouté dans `/var/www/docker-compose.yml` sur le VPS : image `www-cinemap`, port `3012:80`, volume SQLite persistant (`./data/cinemap/database.sqlite`), `env_file` pointant vers le `.env` de production
- `cinemap-app/deployment/Dockerfile` : image `php:8.3-fpm-alpine` avec nginx, supervisord, extensions PHP (`pdo_sqlite`, `bcmath`, `zip`, `mbstring`, `intl`), Composer et Node/Vite — build en layers séparés pour optimiser le cache Docker
- `cinemap-app/deployment/nginx.conf` : configuration nginx interne au container (FastCGI PHP-FPM sur `127.0.0.1:9000`, `try_files` pour le routing Laravel)
- `cinemap-app/deployment/supervisord.conf` : supervision de `php-fpm` et `nginx` dans le même container
- `cinemap-app/deployment/entrypoint.sh` : création du fichier SQLite, permissions, `migrate --force`, `config:cache` et `view:cache` au démarrage
- Bloc nginx `/cinemap/` ajouté dans `/etc/nginx/sites-available/vps` sur le VPS : reverse proxy vers `127.0.0.1:3012` avec `rewrite` pour strip du préfixe
- `.env` de production créé sur le VPS avec `APP_URL=http://78.138.58.95/cinemap`, `ASSET_URL`, `SESSION_PATH=/cinemap` et secrets Stripe/Discord/JWT
- `.github/workflows/pest.yml` : pipeline CI exécutant Pest sur push/PR vers `main`, `staging` et `dev` — setup PHP 8.3, SQLite, génération clé app + JWT secret, migrations, `withoutVite()` pour les tests de vues
- `.github/workflows/pint.yml` : pipeline CI vérifiant le style de code avec Pint (`--test`) sur push/PR vers `main`, `staging` et `dev`
- `.github/workflows/deploy-staging.yml` : pipeline CD déclenchée sur push `staging` — lint + tests en parallèle, puis déploiement SSH sur le VPS via `git pull` + `docker-compose build/up`
- `cinemap-app/.env.example` complété avec les variables `DISCORD_*`, `STRIPE_*`, `JWT_SECRET`, `USER_TEST_EMAIL/PASSWORD`, `USER_ADMIN_EMAIL/PASSWORD`
- `config/services.php` : valeurs par défaut ajoutées sur toutes les clés pour éviter les `null` en CI

---

## [0.10.0] - 23/04/2026

### Ajouté

- Suite de tests Feature Pest pour l'API JSON (`tests/Feature/Api/`) :
  - `FilmApiTest` : 3 tests sur `GET /api/films` — statut 200, tri par nom, structure des champs
  - `ApiAuthTest` : 3 tests sur `POST /api/auth/login` — 401 mauvais identifiants, 403 non abonné, 200 + token si abonné (abonnement créé directement en DB, sans appel Stripe)
  - `FilmLocalisationsApiTest` : 2 tests sur `GET /api/films/{film}/localisations` — 401 sans token, 200 avec `actingAs($user, 'api')`

### Corrigé

- Route `GET /api/films/{film}/locations` renommée en `GET /api/films/{film}/localisations` pour cohérence avec le modèle — mise à jour dans `routes/api.php`, `FilmApiController`, `cinemap-mcp/index.js` et la vue `subscription/index.blade.php`
- Tests Breeze `AuthenticationTest` et `RegistrationTest` : redirect attendu corrigé de `route('dashboard')` vers `route('home')` (la redirection post-login avait été changée en étape 1)
- `ExampleTest` : route `/` remplacée par `/home` (la racine redirige en 302)

---

## [0.9.0] - 23/04/2026

### Ajouté

- Projet `cinemap-mcp/` (Node.js, `type: module`) avec `package.json` et dépendance `@modelcontextprotocol/sdk`
- Serveur MCP `index.js` exposant deux outils via `StdioServerTransport` (transport local pour Claude Code / Claude Desktop) :
  - `list_films` : appelle `GET /api/films` (route publique) et retourne la liste complète des films
  - `get_locations_for_film` : appelle `GET /api/films/{film}/locations` avec le token JWT Bearer et retourne les emplacements de tournage du film donné
- Variable d'environnement `CINEMAP_JWT_TOKEN` dans le `.env` du projet MCP — token JWT d'un utilisateur abonné obtenu via `POST /api/auth/login`
- Route publique `GET /api/films` dans `routes/api.php` (hors groupe `auth:api`) pour alimenter l'outil `list_films`
- Méthode `FilmApiController@index` : retourne tous les films triés par nom avec les champs `id`, `name`, `producer`, `release_year` en JSON
- Configuration du serveur MCP dans `~/.config/Claude/settings.json` : entrée `cinemap` avec chemin absolu vers `index.js` et `CINEMAP_JWT_TOKEN` en variable d'environnement

---

## [0.8.0] - 17/04/2026

### Ajouté

- Package `laravel/cashier` installé (abonnements Stripe récurrents)
- Trait `Billable` ajouté au modèle `User`
- Migrations Cashier publiées (`vendor:publish --tag="cashier-migrations"`) et appliquées : colonnes Stripe sur `users` + table `subscriptions`
- Variables `.env` : `STRIPE_KEY`, `STRIPE_SECRET`, `STRIPE_PRICE_ID`
- Produit et prix récurrent créé dans le dashboard Stripe (`5 €/mois`)
- `SubscriptionController` avec `index` (page abonnement) et `store` (souscription via `newSubscription`)
- Vue `subscription/index.blade.php` : card tarif + formulaire Stripe Elements (SetupIntent)
- Si l'utilisateur est déjà abonné : formulaire grisé (`opacity-50 pointer-events-none`), bouton désactivé, JS Stripe non chargé
- Routes `GET /subscription` et `POST /subscription` dans le groupe `middleware('auth')`
- Package `php-open-source-saver/jwt-auth` installé
- Config JWT publiée (`vendor:publish --provider="PHPOpenSourceSaver\JWTAuth\..."`) et secret généré (`php artisan jwt:secret` → `JWT_SECRET` dans `.env`)
- Guard `api` avec driver `jwt` dans `config/auth.php`
- Interface `JWTSubject` implémentée sur `User` (`getJWTIdentifier`, `getJWTCustomClaims`)
- `ApiAuthController@login` : vérifie les credentials ET l'abonnement avant de délivrer le token JWT (401 si mauvais credentials, 403 si non abonné)
- `FilmApiController@locations` : retourne le film et ses localisations en JSON (champs sélectifs)
- `routes/api.php` déclaré avec `Route::post('/auth/login')` public et `GET /films/{film}/locations` protégé par `middleware('auth:api')`
- `routes/api.php` enregistré dans `bootstrap/app.php` (obligatoire en Laravel 11 — non chargé automatiquement)

### Corrigé

- `ext-bcmath` manquant : `sudo apt install php8.3-bcmath` requis par Cashier v16
- Migrations Cashier en double (double `vendor:publish`) : suppression du second jeu de fichiers + `migrate:fresh --seed`
- `STRIPE_PRICE_ID` non renseigné dans `.env` → `newSubscription()` recevait `null` — corrigé en ajoutant la variable
- `GET /api/films/1/locations` retournait 404 en Laravel 11 : `routes/api.php` n'est pas auto-chargé — résolu en ajoutant `api: __DIR__.'/../routes/api.php'` dans `bootstrap/app.php`
- `Target class [subscribed] does not exist` (HTTP 500) : middleware `subscribed` inexistant retiré de `routes/api.php` — la vérification d'abonnement est dans `ApiAuthController@login`
- Redirection après inscription vers `/dashboard` (inexistant) → corrigé en `route('home')` dans `RegisteredUserController@store`

---

## [0.7.0] - 17/04/2026

### Ajouté

- Packages `laravel/socialite` et `socialiteproviders/discord` installés
- Colonne `oauth_id` (nullable, unique) sur la table `users` — stocke l'identifiant Discord pour retrouver l'utilisateur au second login
- `oauth_id` ajouté dans `#[Fillable]` du modèle `User`
- Provider Discord enregistré dans `AppServiceProvider` via l'événement `SocialiteWasCalled`
- Entrée `discord` dans `config/services.php` avec les variables `DISCORD_CLIENT_ID`, `DISCORD_CLIENT_SECRET`, `DISCORD_REDIRECT_URI`
- `SocialiteController` avec `redirectToDiscord` et `handleDiscordCallback` (`->stateless()`)
- Logique de liaison de compte : si un utilisateur avec le même email existe déjà, son `oauth_id` est mis à jour — sinon un nouveau compte est créé
- Routes `/auth/discord` et `/auth/discord/callback` hors middleware dans `web.php`
- Bouton "Se connecter avec Discord" sur la page de login avec logo SVG Discord et séparateur "ou"

### Corrigé

- `InvalidStateException` Socialite : ajout de `->stateless()` sur le redirect et le callback
- `UniqueConstraintViolationException` sur `users.email` : remplacement de `firstOrCreate(['oauth_id'])` par une recherche `orWhere('email')` pour gérer les comptes existants

---

## [0.6.0] - 17/04/2026

### Ajouté

- Hook git `pre-commit` : exécute `./vendor/bin/pint` puis `git add -u` automatiquement avant chaque commit
- Section « Qualité du code » dans `README.md` : instructions pour lancer Pint manuellement et créer le hook pre-commit après un clone

### Corrigé

- Nom de commande erroné `app:clean-old-locations` → `app:clean-old-localisations` dans la section « Commandes utiles » du `README.md`

---

## [0.5.0] - 17/04/2026

### Ajouté

- Commande Artisan `app:clean-old-localisations` (classe `CleanOldLocalisations`) : supprime les localisations créées depuis plus de 14 jours ayant moins de 2 upvotes, affiche le nombre de lignes supprimées via `$this->info()`
- Entrée dans le scheduler (`routes/console.php`) : `Schedule::command('app:clean-old-localisations')->daily()` — exécution automatique chaque nuit

### Corrigé

- Import incorrect `Illuminate\Console\Scheduling\Schedule` (classe concrète) → `Illuminate\Support\Facades\Schedule` (facade) dans `routes/console.php`
- Signature de commande `app:clean-old-locations` → `app:clean-old-localisations` pour correspondre à l'appel du scheduler

---

## [0.4.0] - 16/04/2026

### Ajouté

- Tables `localisation_votes` et `film_votes` avec contrainte d'unicité (`user_id` + entité) et `cascadeOnDelete`
- Champ `is_upvote` (boolean) sur `film_votes` pour distinguer upvote et downvote
- Colonnes dénormalisées `upvotes_count` sur `localisations`, `upvotes_count` et `downvotes_count` sur `films`
- Modèles `LocalisationVote` et `FilmVote` (`$fillable`, `$casts`, relations `belongsTo`)
- Jobs `RecalculateLocalisationVotes` et `RecalculateFilmVotes` : recalculent les compteurs en arrière-plan, modèle injecté dans le constructeur
- Queue driver `database` (`QUEUE_CONNECTION=database`)
- Routes `POST /localisations/{localisation}/vote` et `POST /films/{film}/vote` dans le groupe `middleware('auth')`
- Action `LocalisationController@vote` : toggle (re-cliquer annule le vote), dispatch du job
- Action `FilmController@vote` : toggle ou changement de sens (upvote ↔ downvote), dispatch du job
- Bouton `+N` (upvote) sur `localisations/show` et dans la liste de `films/show` et `home` — vert si vote actif
- Boutons `+N` / `-N` (upvote/downvote) sur `films/show` et dans les cards de `home` — vert/rouge selon vote actif
- Compteurs et état des boutons visibles en lecture seule pour les non-connectés
- `LocalisationController@show` passe `$userHasVoted` à la vue
- `FilmController@show` passe `$userVote` et `$localisationVotes` (keyBy) à la vue
- `HomeController` charge `$filmVotes` et `$localisationVotes` en une requête chacun (`keyBy`) pour éviter les N+1
- Dashboard : barre de stats globales (total films, localisations, upvotes/downvotes), top 5 films avec score net, top 5 localisations par upvotes
- `films/index` (admin) : colonnes Upvotes et Downvotes ajoutées au tableau
- `localisations/index` (admin) : colonne Upvotes ajoutée au tableau

### Supprimé

- Anciens champs `upvotes` et `downvotes` (entiers bruts) sur la migration `films` et le modèle `Film`
- Ancien champ `upvotes_count` sur la migration `localisations` et le modèle `Localisation` (remplacé par compteur mis à jour par job)
- Entrées correspondantes dans `FilmFactory` et `LocalisationFactory`

### Corrigé

- `FilmVote` créé vide par `make:model` — `$fillable`, `$casts` et relations ajoutés manuellement
- `LocalisationVote` manquait l'import `use BelongsTo` — corrigé
- Les deux migrations de votes générées avec le même timestamp — renommage avec décalage d'une seconde
- Doublon `$table->timestamp('created_at')` dans les migrations de votes — supprimé (`timestamps()` suffit)
- Méthodes `vote` inversées entre `LocalisationController` et `FilmController` — remises dans le bon controller
- `$request` absent des paramètres de `FilmController@vote` — ajouté

---

## [0.3.0] - 16/04/2026

### Ajouté

- Champ booléen `is_admin` sur la table `users` (migration + cast `bool` dans le modèle `User`)
- Factory state `admin()` sur `UserFactory` pour créer des utilisateurs administrateurs
- `AdminSeeder` : crée le compte admin par défaut (`admin@cinemap.fr` / `Admin123!`) via le state factory
- `AdminMiddleware` (`app/Http/Middleware/AdminMiddleware.php`) : renvoie un 403 si l'utilisateur n'est pas authentifié ou n'a pas `is_admin = true`
- Alias `admin` enregistré dans `bootstrap/app.php` pour utiliser le middleware dans les routes

### Changement

- Route `/dashboard` : middleware `['auth', 'admin']` — accès réservé aux administrateurs
- Routes CRUD Films et route `localisations.index` déplacées dans un groupe `['auth', 'admin']`
- `LocalisationController` (`edit`, `update`, `destroy`) : la vérification du propriétaire autorise désormais aussi les admins (`|| auth()->user()->is_admin`)
- Navigation (`layouts/navigation.blade.php`) : lien « Dashboard » conditionnel — affiché uniquement si `auth()->user()->is_admin`, sur desktop et mobile
- Vue `films.show` : boutons « Modifier » / « Supprimer » d'une localisation visibles pour le propriétaire **ou** un admin

---

## [0.2.0] - 16/04/2026

### Ajouté

- Migration, modèle, factory, seeder et controller pour `Film`
- Migration, modèle, factory, seeder et controller pour `Localisation`
- `HomeController` pour la page d'accueil publique
- Relations Eloquent : `Film hasMany Localisation`, `Localisation belongsTo Film`, `Localisation belongsTo User`
- CRUD complet Films (index, create, store, show, edit, update, destroy)
- CRUD complet Localisations (index, create, store, show, edit, update, destroy)
- Champ `photo_url` (nullable) sur la table `localisations`
- `cascadeOnDelete()` sur les FK `film_id` et `user_id` de `localisations`
- Page d'accueil publique `/home` : liste des films en cards avec leurs localisations, bouton "Plus d'infos", bouton "Ajouter une localisation" pour les utilisateurs connectés
- Page publique `films.show` : détail complet du film + liste des localisations cliquables + boutons modifier/supprimer visibles uniquement pour le propriétaire
- Page publique `localisations.show` : détail de la localisation + boutons modifier/supprimer visibles uniquement pour le propriétaire
- Dashboard (`/dashboard`) : cards avec accès rapide à la gestion des films et des localisations
- Vérification du propriétaire (`abort_if`) dans `LocalisationController` pour edit, update et destroy
- `user_id` injecté côté serveur depuis `auth()->id()` à la création d'une localisation
- Pré-sélection du film dans le formulaire de création via query param `?film_id=`
- Lien "Accueil" dans la navigation, lien "Dashboard" conditionnel (`@auth`)
- Eager loading des relations pour éviter les requêtes N+1

### Corrigé

- Import manquant de `LocalisationController` dans `routes/web.php`
- Import manquant de `Localisation` dans `LocalisationSeeder`
- Vues `create` et `edit` de localisations qui référençaient les champs du modèle `Film` au lieu de `Localisation`
- Vue `show` de localisations qui référençait la variable `$film` inexistante
- Colonnes de la vue `index` des localisations qui affichaient des champs de films

---

## [0.1.0] - 15/04/2026

### Ajouté

- Initialisation du dépôt GitHub
- Installation Laravel (projet `cinemap-app`)
- Installation Laravel Breeze (authentification) :
  - Pages `/register`, `/login`, `/logout`, `/forgot-password`, `/reset-password`, `/verify-email`
  - Gestion du profil utilisateur (`/profile` — modifier infos, changer mot de passe, supprimer le compte)
  - Controllers d'auth (`AuthenticatedSessionController`, `RegisteredUserController`, etc.)
  - Composants Blade Breeze (`x-input-label`, `x-text-input`, `x-primary-button`, `x-dropdown`, etc.)
  - Layouts `app.blade.php` et `guest.blade.php`
  - Routes d'auth dans `routes/auth.php`
  - Suite de tests feature pour l'authentification (Pest)
- Page d'accueil publique `/home` avec contenu conditionnel selon l'état de connexion (`@auth` / `@guest`)
- Navigation adaptée : liens login/register pour les visiteurs, dropdown utilisateur pour les connectés
- Redirection vers `/home` après connexion
- Suppression de la page `welcome.blade.php` par défaut de Laravel