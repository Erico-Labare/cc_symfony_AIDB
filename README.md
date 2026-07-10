# Projet PHP : Programmation d'un site web CRUD MVC (Symfony)

## Contexte du Projet

Ce projet consiste en la réalisation d'une application web (CRUD) avec le framework Symfony, servant de système de réservation pour un groupe hôtelier disposant d'une centrale de réservation nationale. Les données sont stockées dans une base de données SQL.

## Spécifications Fonctionnelles

### Règles de Gestion
1.  Un client effectue une réservation déterminée pour un hôtel, une chambre, avec une date de début et une date de fin d'occupation.
2.  Un client peut réserver plusieurs chambres à la même date, mais doit réserver au moins une chambre.
3.  Un hôtel contient plusieurs chambres de différents types (single, double, etc.).
4.  Chaque hôtel correspond à une catégorie particulière (*, **, ***, etc.).

### Fonctionnalités
L'application propose différents espaces avec des fonctionnalités spécifiques :

#### Espace Public
*   **Accueil**: Permet de rechercher une chambre disponible (via date de début/fin).
*   **Réservation**: Possibilité de réserver une chambre (sans paiement). Une inscription ou connexion est nécessaire (collecte de l'email et du numéro de téléphone du client en plus des données du MCD).
*   **Connexion**: Accès à la plateforme.
*   **Mot de passe perdu**: Fonctionnalité de récupération de mot de passe.

#### Espace Client
*   **Visualiser les réservations**: Affichage des réservations effectuées par le client.
*   **Ajouter un commentaire**: Possibilité d'ajouter un commentaire à une réservation pour des demandes spéciales (ex: ajout d'un lit bébé).

#### Espace Administrateur
*   **CRUD Chambres**: Gestion complète (Création, Lecture, Mise à jour, Suppression) des chambres avec pagination et possibilité de recherche.
*   **CRUD Réservations**: Gestion complète des réservations clients avec pagination et recherche via le numéro de réservation. Le détail de la réservation doit afficher l'ensemble des chambres réservées.
*   **CRUD Clients**: Gestion complète des clients avec pagination et recherche via nom/email.

## Stack Technique

*   **Framework**: Symfony
*   **Langage**: PHP
*   **Base de données**: SQL
*   **Templating**: Twig
*   **Front-end**: Bootstrap (pour le responsive design)

## Structure du Projet

Le projet est organisé en couches, respectant les bonnes pratiques de Symfony :
*   **Contrôleurs**: Gèrent les requêtes HTTP et les réponses.
*   **Services**: Contiennent la logique métier.
*   **Entités**: Représentent les objets métier et sont mappées à la base de données.
*   **Repositories**: Fournissent des méthodes pour interagir avec les entités en base de données.

## Modèle Conceptuel de Données (MCD)

Basé sur les règles de gestion, le MCD se traduit par les entités suivantes :

TODO : METTRE IMAGE

## Critères d'Évaluation

*   **Configuration du projet** (/1)
*   **Organisation des packages** (/1)
*   **Bonnes pratiques** (SOLID, Tell don’t ask, généricité, etc.) (/2)
*   **Documentation du code** (/1)
*   **Vues responsives design** (/1)
*   **Couverture des exigences/Code opérationnel** (/5)
*   **Maîtrise de la dette technique** (évaluée avec SonarQube) (/1)
*   **Gestion des exceptions** (individuellement ou globalement) (/1)
*   **Gestion de l’authentification/mot de passe oublié** (/1)
*   **Tests** (/2)
*   **Présentation orale** (/4)
*   **TOTAL** /20

## Installations

### Installation du projet
- Cloner le projet depuis le dépôt GitHub.
- Installer les dépendances avec `composer install` dans le terminal à la racine du projet.
- (Optionnel) si erreur sur le clear:cache, utiliser `Remove-Item -Recurse -Force vendor` et `Remove-Item -Force composer.lock` dans le terminal à la racine du projet, puis réinstaller les dépendances avec `composer install`.

### Installation de la BDD
- Vérifiez que votre php.ini (localisable avec la commande `php --ini`) est correctement configuré pour le projet (extension mysql et pdo_mysql activées).
- Créer la base de données avec `php bin/console doctrine:database:create` dans le terminal à la racine du projet ou manuellement dans votre SGBD.
- (Optionnel)Générer une migration avec `php bin/console make:migration` dans le terminal à la racine si le fichier de migration n'est pas déjà présent dans /migrations.
- Exécuter la migration avec `php bin/console doctrine:migrations:migrate` dans le terminal à la racine du projet ou manuellement dans votre SGBD.
- (Optionnel) Vérifier doctrine avec `php bin/console doctrine:schema:validate` dans le terminal à la racine du projet (Resultat attendu : `[OK] The mapping files are correct. [OK] The database schema is in sync with the mapping files.`).
- (Optionnel) Installer des données par défaut configurées dans `DataFixtures\AppFixtures` avec `php bin/console doctrine:fixtures:load --group=dev` dans le terminal à la racine du projet.

## Utilisation & Fonctionnement

### Lancer le projet
- Lancer le serveur de développement avec `symfony server:start` dans le terminal à la racine du projet.
- Accéder **à la page d'accueil Symfony** via l'URL http://localhost:8000/ dans votre navigateur.
- Accéder **à la page d'accueil de l'application** via l'URL http://localhost:8000/home dans votre navigateur.

## .env

Un fichier .env est versionné ici. Il est versionné dans le cadre du contexte du projet qui est un projet de rendu Contrôle Continu. Dans ce cadre-là, il n'y a pas de risque particulier sur le partage de valeurs de production.

## Configuration

Il est possible de configurer le .env ainsi :
- TODO config .env.local

## Tests & Couverture

- Vérifiez que votre php.ini (localisable avec la commande `php --ini`) est correctement configuré pour le projet (extension mysql et pdo_mysql activées).
- (Optionnel) Supprimer la base de données de test avec `php bin/console doctrine:database:drop --env=test --force` dans le terminal à la racine du projet ou manuellement dans votre SGBD.
- Créer la base de données avec `php bin/console doctrine:database:create --env=test` dans le terminal à la racine du projet ou manuellement dans votre SGBD.
- Exécuter la migration avec `php bin/console doctrine:migrations:migrate --env=test` dans le terminal à la racine du projet ou manuellement dans votre SGBD.
- (Optionnel) Vérifier doctrine avec `php bin/console doctrine:schema:validate --env=test` dans le terminal à la racine du projet (Resultat attendu : `[OK] The mapping files are correct. [OK] The database schema is in sync with the mapping files.`).
- Installer des données par défaut configurées dans `DataFixtures\TestFixtures` avec `php bin/console doctrine:fixtures:load --env=test --group=test` dans le terminal à la racine du projet.
- Lancer les tests avec `php bin/phpunit` dans le terminal à la racine du projet.

## Risques, Limites et Dettes techniques

TODO

## Crédits

TODO
