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