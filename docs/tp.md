# TP Laravel - Gestion d'emplacements de tournage

## Contexte

Vous devez realiser une application Laravel simple de gestion d'emplacements de tournage de films.

Le principe est le suivant :

- un visiteur peut s'inscrire et se connecter ;
- un utilisateur connecte peut consulter les films et ajouter des emplacements de tournage ;
- un administrateur peut tout modifier.

L'objectif du TP est de pratiquer plusieurs briques importantes de Laravel sans perdre trop de temps dans des CRUDs trop nombreux.

Le projet doit donc rester volontairement simple :

- 2 CRUDs metier principaux seulement ;
- 1 petite fonctionnalite transverse sans CRUD complet ;
- peu de relations en base ;
- interface HTML tres simple, sans recherche de design.

---

## Objectifs pedagogiques

A la fin du TP, vous devez avoir pratique :

- l'authentification Laravel ;
- les CRUDs standards ;
- un middleware personnalise ;
- les queues et jobs ;
- une commande Artisan personnalisee et la planification ;
- Laravel Pint ;
- une connexion OAuth via un reseau social ;
- une API JSON protegee par abonnement ;
- une premiere integration autour de MCP pour des usages IA.

---

## Sujet

Vous allez realiser une application nommee `CineMap`.

Cette application permet de gerer des lieux de tournage associes a des films.

### Modele de donnees minimal

Le projet doit rester simple. Vous devez limiter le domaine metier principal a :

### 1. `Film`

Champs minimum conseilles :

- `title`
- `release_year`
- `synopsis`

### 2. `Location`

Champs minimum conseilles :

- `film_id`
- `user_id`
- `name`
- `city`
- `country`
- `description`
- `upvotes_count` (valeur par défaut : 0)

### Relations

- un film possede plusieurs emplacements ;
- un emplacement appartient a un film ;
- un emplacement appartient a l'utilisateur qui l'a cree.

### Fonctionnalite supplementaire sans CRUD complet

Vous devez ajouter un systeme simple d'upvotes sur les emplacements.

Table conseillee :

- `location_votes`

Champs minimum conseilles :

- `user_id`
- `location_id`
- `created_at`

Contraintes conseillees :

- un utilisateur ne peut voter qu'une seule fois par emplacement ;
- vous pouvez ajouter une contrainte d'unicite sur `user_id` + `location_id` ;

### Utilisateurs

Le modele `User` devra contenir au minimum :

- les champs de base lies a l'authentification ;
- un champ booleen `is_admin`.

---

## Travail demande

Vous devez realiser les fonctionnalites suivantes dans l'ordre ci-dessous.

Ne passez a l'etape suivante que lorsque l'etape precedente fonctionne correctement.

---

## Etape 1 - Authentification

Mettre en place l'authentification de base.

Attendus :

- inscription ;
- connexion ;
- deconnexion ;
- pages accessibles uniquement aux utilisateurs connectes ;
- page d'accueil ou tableau de bord simple apres connexion.

Remarque :

- vous pouvez utiliser un starter kit Laravel si vous le souhaitez ;
- l'objectif n'est pas de refaire un systeme d'authentification from scratch.

---

## Etape 2 - Les 2 CRUDs metier

Mettre en place uniquement les 2 CRUDs suivants :

- CRUD `Film`
- CRUD `Location`

Attendus minimaux :

- liste ;
- creation ;
- modification ;
- suppression ;
- page de detail si vous le jugez utile.

Contraintes fonctionnelles :

- un emplacement doit etre rattache a un film ;
- un emplacement doit etre rattache a l'utilisateur qui l'a cree ;
- lors de la creation d'un emplacement, l'utilisateur choisit un film dans une liste.

Consigne importante :

- ne creez pas d'autres CRUDs metier sauf si vous avez termine tres vite ;
- l'objectif du TP n'est pas de multiplier les formulaires.

---

## Etape 3 - Middleware administrateur

Mettre en place un middleware personnalise pour gerer les droits d'acces administrateur.

Attendus :

- ajout du champ `is_admin` sur les utilisateurs ;
- creation d'un middleware `admin` ;
- protection des routes reservees a l'administration.

Regles minimales a respecter :

- un administrateur peut creer, modifier et supprimer tous les films ;
- un administrateur peut modifier et supprimer tous les emplacements ;
- un utilisateur classique peut creer un emplacement ;
- un utilisateur classique ne peut modifier ou supprimer que ses propres emplacements.

Vous etes libres sur l'organisation exacte des routes et des controles, mais le middleware administrateur doit etre reellement utilise.

---

## Etape 4 - Queues et Jobs

Mettre en place un cas d'usage simple pour pratiquer les jobs.

Cas impose :

- lorsqu'un utilisateur upvote un emplacement, vous devez declencher un job mis en file d'attente.

Le vote doit etre enregistre dans l'application, puis le job doit recalculer et mettre a jour `upvotes_count` sur l'emplacement

Attendus :

- configuration d'une queue simple ;
- creation d'un job ;
- creation d'une action de vote, sans CRUD complet ;
- dispatch du job depuis votre application apres le vote ;
- verification que le traitement passe bien par le worker.

Important :

- restez sur un cas simple et rapide a valider ;
- inutile de construire un systeme complexe de likes ou de classement ;
- un simple bouton "upvote" suffit.

---

## Etape 5 - Commande Artisan + tache planifiee

Creer une commande Artisan personnalisee executee une fois par jour.

Cas impose :

- supprimer automatiquement certaines `locations`.

Regle imposee :

- supprimer les emplacements crees depuis plus de 14 jours et qui ont moins de 2 upvotes.

Attendus :

- une commande Artisan personnalisee ;
- l'enregistrement de cette commande dans le scheduler Laravel ;
- une explication simple pour montrer comment tester la commande manuellement ;
- une implementation claire de la condition metier utilisee pour selectionner les emplacements a supprimer.

---

## Etape 6 - Laravel Pint

Vous devez utiliser Laravel Pint sur votre projet.

Attendus :

- le code rendu doit etre formate ;
- la commande a utiliser doit etre indiquee dans votre README.

Minimum attendu avant rendu :

```bash
./vendor/bin/pint
```

---

## Etape 7 - Connexion via un reseau social

En plus de l'authentification classique, vous devez ajouter une connexion OAuth avec un fournisseur externe.

Un seul fournisseur suffit, au choix :

- Google
- Facebook
- GitHub
- un autre fournisseur equivalent

Attendus :

- un bouton de connexion sur la page de login ;
- redirection vers le fournisseur ;
- retour dans l'application avec creation ou connexion de l'utilisateur.

Objectif :

- montrer que vous savez brancher un fournisseur OAuth a une application Laravel.

---

## Etape 8 - Abonnement Stripe + route API JSON

Mettre en place un abonnement payant tres simple pour les utilisateurs.

But :

- seuls les utilisateurs abonnes peuvent appeler une route API qui retourne en JSON tous les emplacements d'un film donne.
- l'authentification de cette partie API doit se faire avec un token JWT.

Route attendue, par exemple :

```text
/api/films/{film}/locations
```

Le JSON doit au minimum contenir :

- les informations principales du film ;
- la liste de ses emplacements ;
- idealement le nombre d'upvotes par emplacement.

Contraintes :

- un utilisateur non abonne ne doit pas pouvoir utiliser cette route ;
- un utilisateur sans token JWT valide ne doit pas pouvoir utiliser cette route ;
- l'acces a cette route doit donc verifier a la fois l'authentification JWT et l'abonnement actif ;
- une verification claire de l'abonnement doit etre mise en place ;
- Stripe doit etre reellement fonctionnel en mode test ;
- vous devez etre capables de demonstrer un paiement ou un abonnement de test avec une carte de test Stripe, par exemple `4242 4242 4242 4242` ;
- une page tres simple de souscription suffit.

Remarque :

- vous pouvez rester minimalistes sur l'interface Stripe ;
- l'objectif n'est pas de faire une interface de paiement complete, mais de valider un flux Stripe de test fonctionnel ;
- l'important est le fonctionnement, pas le design.

---

## Etape 9 - MCP simple pour les IA

Mettre en place un MCP tres simple, en lecture seule, pour permettre a une IA de consulter des informations de votre application.

Le but n'est pas de faire un MCP complet ou complexe.

Minimum attendu :

- un outil pour lister les films ;
- un outil pour recuperer les emplacements d'un film.

Exemples de noms possibles :

- `list_films`
- `get_locations_for_film`

Vous pouvez choisir l'implementation technique que vous souhaitez, a condition que :

- le serveur MCP fonctionne ;
- il soit documente ;
- il permette a une IA ou a un client compatible MCP d'interroger votre application.

Conseil :

- gardez ce MCP en lecture seule ;
- vous pouvez faire un pont vers votre application Laravel ou reutiliser votre route JSON si cela vous simplifie le travail.

---

## Contraintes generales

- interface HTML/CSS tres simple ;
- pas de design complexe attendu ;
- pas de JavaScript complexe attendu ;
- validations serveur obligatoires sur les formulaires ;
- base de donnees proprement migree ;
- noms de routes et code suffisamment clairs ;
- les upvotes ne doivent pas donner lieu a un CRUD complet ;
- projet fonctionnel du debut a la fin.

---

## Ce qui est attendu dans le rendu

Votre rendu doit contenir :

- le code source complet ;
- les migrations ;
- les controllers, modeles, middleware, jobs, commandes et routes ;
- un README clair pour lancer le projet ;
- les instructions pour :
  - installer le projet ;
  - lancer les migrations ;
  - lancer les seeders si vous en avez ;
  - lancer le worker de queue ;
  - tester la commande planifiee ;
  - configurer le login social ;
  - configurer Stripe ;
  - generer et utiliser un token JWT pour l'API ;
  - lancer le MCP.

---

## Conseils de realisation

- commencez par faire simple ;
- terminez correctement les 2 CRUDs avant de complexifier le projet ;
- ne creez pas de fonctionnalites hors sujet ;
- preferez une application sobre mais fonctionnelle a une application ambitieuse mais inachevee ;
- testez chaque etape avant de passer a la suivante.

Conseil important :

- pour rester dans l'esprit du TP, les upvotes doivent rester une petite fonctionnalite rapide a coder, pas un sous-projet complet.

---

## Bonus facultatifs

Si vous avez termine en avance, vous pouvez ajouter un ou plusieurs bonus :

- filtres par ville ou pays ;
- recherche de films ;
- upload d'une photo du lieu ;
- seeders/factories propres ;
- affichage d'un classement des lieux les plus upvotes ;
- tests automatisees sur les parties critiques.

Le bonus ne compense pas un socle principal non fonctionnel.
