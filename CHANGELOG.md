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