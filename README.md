# Projet PHP : Système de Réservation Hôtelière (Symfony CRUD MVC)

## Table des Matières

*   [1. Contexte du Projet](#1-contexte-du-projet)
*   [2. Spécifications Fonctionnelles](#2-spécifications-fonctionnelles)
    *   [2.1. Règles de Gestion](#21-règles-de-gestion)
    *   [2.2. Fonctionnalités Détaillées](#22-fonctionnalités-détaillées)
        *   [2.2.1. Espace Public](#221-espace-public)
        *   [2.2.2. Espace Client](#222-espace-client)
        *   [2.2.3. Espace Administrateur](#223-espace-administrateur)
*   [3. Stack Technique](#3-stack-technique)
*   [4. Architecture du Projet](#4-architecture-du-projet)
*   [5. Modèle Conceptuel de Données (MCD)](#5-modèle-conceptuel-de-données-mcd)
*   [6. Guide d'Installation](#6-guide-dinstallation)
    *   [6.1. Installation du Projet](#61-installation-du-projet)
    *   [6.2. Configuration de la Base de Données (Développement)](#62-configuration-de-la-base-de-données-développement)
*   [7. Utilisation et Lancement](#7-utilisation-et-lancement)
    *   [7.1. Lancer l'Application](#71-lancer-lapplication)
*   [8. Gestion des Variables d'Environnement (.env)](#8-gestion-des-variables-denvironnement-env)
    *   [8.1. Configuration Locale](#81-configuration-locale)
    *   [8.2. Configuration du Service d'E-mail](#82-configuration-du-service-de-mail)
*   [9. Base de Données (Tests)](#9-base-de-données-tests)
*   [10. Risques, Limites et Dettes Techniques](#10-risques-limites-et-dettes-techniques)
    *   [10.1. Risques](#101-risques)
    *   [10.2. Limites](#102-limites)
    *   [10.3. Dettes Techniques](#103-dettes-techniques)
*   [11. Crédits](#11-crédits)

---

## 1. Contexte du Projet

Ce projet a pour objectif la réalisation d'une application web de type CRUD (Create, Read, Update, Delete) développée avec le framework Symfony. Elle simule un système de réservation centralisé pour un groupe hôtelier national. Les données sont persistées dans une base de données SQL. Dans le cadre du Master AIDB, ce projet vise à consolider les compétences en développement web moderne, en architecture logicielle et en gestion de projet.

## 2. Spécifications Fonctionnelles

### 2.1. Règles de Gestion

1.  Un client effectue une réservation spécifique pour un hôtel et une chambre, avec des dates de début et de fin d'occupation définies.
2.  Un client peut réserver plusieurs chambres pour la même période, mais chaque réservation doit inclure au moins une chambre.
3.  Un hôtel propose plusieurs chambres de différents types (ex: simple, double, suite).
4.  Chaque hôtel est classifié selon une catégorie spécifique (ex: \*, \*\*, \*\*\*).

### 2.2. Fonctionnalités Détaillées

L'application est structurée en plusieurs espaces, chacun offrant des fonctionnalités adaptées à son rôle :

#### 2.2.1. Espace Public

*   **Accueil** : Permet la recherche de chambres disponibles en fonction de dates de début et de fin.
*   **Réservation** : Offre la possibilité de réserver une chambre (sans gestion de paiement). Une inscription ou une connexion est requise, collectant l'email et le numéro de téléphone du client en complément des données du MCD.
*   **Connexion** : Accès sécurisé à la plateforme.
*   **Mot de passe oublié** : Fonctionnalité de récupération de mot de passe.

#### 2.2.2. Espace Client

*   **Visualisation des Réservations** : Affichage de l'historique des réservations effectuées par le client.
*   **Ajout de Commentaires** : Possibilité d'ajouter des demandes spéciales ou commentaires à une réservation (ex: ajout d'un lit bébé).

#### 2.2.3. Espace Administrateur

*   **Gestion des Chambres (CRUD)** : Interface complète pour la création, lecture, mise à jour et suppression des chambres, incluant pagination et recherche.
*   **Gestion des Réservations (CRUD)** : Interface complète pour la gestion des réservations clients, avec pagination et recherche par numéro de réservation. Le détail de chaque réservation affiche l'ensemble des chambres concernées.
*   **Gestion des Clients (CRUD)** : Interface complète pour la gestion des clients, avec pagination et recherche par nom ou email.

**Note :** Toutes les vues Twig sont conçues pour être *responsive design*, sans charte graphique imposée. L'intégration d'un template Bootstrap est autorisée.

## 3. Stack Technique

*   **Framework** : Symfony (choisi pour sa robustesse, sa modularité et son écosystème mature, facilitant le développement rapide et maintenable d'applications web complexes).
*   **Langage de Programmation** : PHP
*   **Base de Données** : SQL (pour sa fiabilité et sa large adoption dans les systèmes de gestion de données relationnelles).
*   **Moteur de Templating** : Twig (pour sa syntaxe concise et sa performance, permettant une séparation claire entre la logique métier et la présentation).
*   **Développement Front-end** : Bootstrap (sélectionné pour son efficacité à créer des interfaces *responsive design* rapidement et de manière standardisée).

## 4. Architecture du Projet

Le projet adhère à une architecture en couches, respectant les bonnes pratiques de Symfony :

*   **Contrôleurs** : Gèrent les requêtes HTTP et orchestrent les réponses.
*   **Services** : Encapsulent la logique métier et les opérations complexes.
*   **Entités** : Représentent les objets métier et sont mappées aux tables de la base de données.
*   **Repositories** : Fournissent des méthodes pour interagir avec les entités et la base de données.

*(Il est recommandé d'ajouter ici un diagramme d'architecture (ex: C4 model, diagramme de composants) pour une meilleure visualisation des interactions entre les couches et les composants du système.)*

## 5. Modèle Conceptuel de Données (MCD)

Basé sur les règles de gestion, le MCD se traduit par les entités suivantes :

### Modèle Conceptuel de Données (MCD)
![Diagramme MCD](_Doc-Conception/MCD.png)

### Modèle Logique de Données (MLD)
![Diagramme MLD](_Doc-Conception/MLD.PNG)

*(Les diagrammes ci-dessus illustrent la structure des données et leurs relations. Le MCD présente une vue conceptuelle des entités métier, tandis que le MLD détaille l'organisation des tables dans la base de données.)*

## 6. Guide d'Installation

### 6.1. Installation du Projet

1.  **Cloner le dépôt** : Récupérez le projet depuis son dépôt GitHub.
2.  **Installer les dépendances** : Exécutez `composer install` à la racine du projet.
3.  **(Optionnel) Résolution des problèmes de cache** : En cas d'erreur lors du `clear:cache`, supprimez les dossiers `vendor` et le fichier `composer.lock` (`Remove-Item -Recurse -Force vendor` et `Remove-Item -Force composer.lock` sur PowerShell ou `rm -rf vendor composer.lock` sur Bash/Zsh), puis réinstallez les dépendances avec `composer install`.

### 6.2. Configuration de la Base de Données (Développement)

1.  **Vérification PHP.ini** : Assurez-vous que votre fichier `php.ini` (localisable via `php --ini`) est correctement configuré avec les extensions `mysql` et `pdo_mysql` activées.
2.  **Création de la base de données** : Exécutez `php bin/console doctrine:database:create` (pour un SGBD comme MySQL ou PostgreSQL) ou créez-la manuellement via votre SGBD.
3.  **(Optionnel) Génération de migration** : Si aucun fichier de migration n'est présent dans `/migrations`, générez-en un avec `php bin/console make:migration`.
4.  **Exécution des migrations** : Appliquez les migrations à la base de données avec `php bin/console doctrine:migrations:migrate`.
5.  **(Optionnel) Validation du schéma** : Vérifiez la synchronisation du schéma de la base de données avec les entités Doctrine via `php bin/console doctrine:schema:validate`. Le résultat attendu est : `[OK] The mapping files are correct. [OK] The database schema is in sync with the mapping files.`
6.  **(Optionnel) Chargement des données de développement** : Installez les données par défaut configurées dans `DataFixtures\AppFixtures` avec `php bin/console doctrine:fixtures:load --group=dev`.

## 7. Utilisation et Lancement

### 7.1. Lancer l'Application

1.  **Démarrer le serveur de développement** : Exécutez `symfony server:start` à la racine du projet.
2.  **Accéder à l'accueil Symfony** : Ouvrez votre navigateur et naviguez vers `http://localhost:8000/`.
3.  **Accéder à l'application** : L'application est accessible via `http://localhost:8000/home`.

## 8. Gestion des Variables d'Environnement (.env)

Le fichier `.env` est versionné dans le cadre de ce projet de contrôle continu. Dans ce contexte spécifique, le partage des valeurs de configuration ne présente pas de risque particulier lié à des environnements de production.

### 8.1. Configuration Locale

Il est recommandé de créer un fichier `.env.local` pour surcharger les variables d'environnement spécifiques à votre machine de développement.

### 8.2. Configuration du Service d'E-mail

Pour assurer le bon fonctionnement des fonctionnalités d'envoi d'e-mails (réinitialisation de mot de passe, vérification d'e-mail), vous devez configurer la variable d'environnement `MAILER_DSN` dans votre fichier `.env` ou `.env.local`.

1.  **Sélectionnez un service d'e-mail** : Choisissez un service SMTP (Gmail, Outlook), un service de test (Mailtrap), ou un service transactionnel (SendGrid, Mailgun).
2.  **Récupérez vos identifiants** : Obtenez les informations nécessaires (nom d'utilisateur, mot de passe, clé API, hôte SMTP, port).
3.  **Modifiez votre fichier `.env` ou `.env.local`** : Remplacez la ligne `MAILER_DSN=null://null` par la configuration appropriée.

    **Exemples de configuration `MAILER_DSN` :**

    *   **Mailtrap (développement/test) :**
        ```
        MAILER_DSN=smtp://VOTRE_USERNAME_MAILTRAP:VOTRE_PASSWORD_MAILTRAP@smtp.mailtrap.io:2525
        ```
        *(Remplacez par vos identifiants Mailtrap, disponibles sur votre compte.)*

    *   **Serveur SMTP générique (ex: Gmail avec mot de passe d'application si 2FA activé) :**
        ```
        MAILER_DSN=smtp://VOTRE_EMAIL:VOTRE_MOT_DE_PASSE@smtp.VOTRE_SERVEUR_SMTP:PORT
        ```
        *(Exemple Gmail: `smtp://monemail@gmail.com:monmotdepasseapp@smtp.gmail.com:587`)*

    *   **SendGrid :**
        ```
        MAILER_DSN=smtp://apikey:VOTRE_CLE_API_SENDGRID@smtp.sendgrid.net:587
        ```

4.  **Redémarrez le serveur Symfony** : Après toute modification du fichier `.env` ou `.env.local`, redémarrez votre serveur web Symfony pour que les changements soient pris en compte.

Assurez-vous également que l'adresse e-mail de l'expéditeur configurée dans votre application (ex: `no-reply@hotel-reservation.com`) est autorisée à envoyer des e-mails via le service choisi.

## 9. Base de Données (Tests)

Pour l'environnement de test, suivez ces étapes :

1.  **Vérification PHP.ini** : Comme pour le développement, assurez-vous que les extensions `mysql` et `pdo_mysql` sont activées.
2.  **(Optionnel) Suppression de la base de données de test existante** : `php bin/console doctrine:database:drop --env=test --force`.
3.  **Création de la base de données de test** : `php bin/console doctrine:database:create --env=test`.
4.  **Exécution des migrations de test** : `php bin/console doctrine:migrations:migrate --env=test`.
5.  **(Optionnel) Validation du schéma de test** : `php bin/console doctrine:schema:validate --env=test`. Le résultat attendu est : `[OK] The mapping files are correct. [OK] The database schema is in sync with the mapping files.`
6.  **Chargement des données de test** : Installez les données par défaut configurées dans `DataFixtures\TestFixtures` avec `php bin/console doctrine:fixtures:load --env=test --group=test`.
7.  **Lancement des tests** : Exécutez les tests unitaires et fonctionnels avec `php bin/phpunit`.

## 10. Risques, Limites et Dettes Techniques

Cette section aborde les défis inhérents au projet, les contraintes imposées par son périmètre actuel et les améliorations techniques envisagées pour des évolutions futures. Elle témoigne d'une approche proactive et réaliste face aux exigences d'un développement logiciel professionnel.

### 10.1. Risques

1.  **Sécurité des Données et Authentification** :
    *   **Risque** : Vulnérabilités potentielles dans la gestion des mots de passe (stockage, réinitialisation) et des sessions utilisateurs, pouvant mener à des accès non autorisés. Les injections SQL ou autres attaques web sont également un risque constant si les validations d'entrée ne sont pas rigoureuses.
    *   **Mitigation** : Utilisation des fonctionnalités de sécurité intégrées de Symfony (Symfony Security Component), validation stricte des entrées utilisateur, hachage des mots de passe avec des algorithmes robustes (ex: Argon2i, bcrypt).

2.  **Performance de la Base de Données** :
    *   **Risque** : Avec l'augmentation du nombre de réservations, de clients ou de chambres, les requêtes de recherche et de pagination pourraient devenir lentes, impactant l'expérience utilisateur.
    *   **Mitigation** : Optimisation des requêtes Doctrine, ajout d'index pertinents sur les colonnes fréquemment utilisées (dates, IDs, noms), utilisation de caches (ex: Redis pour les résultats de recherche fréquents).

3.  **Complexité de la Gestion des Conflits de Réservation** :
    *   **Risque** : Bien que le MCD permette de réserver plusieurs chambres à la même date, la gestion des disponibilités en temps réel et la prévention des sur-réservations peut devenir complexe, surtout en cas de forte concurrence.
    *   **Mitigation** : Implémentation de mécanismes de verrouillage optimistes ou pessimistes au niveau de la base de données lors des tentatives de réservation.

4.  **Dépendances Tierces (Bootstrap, etc.)** :
    *   **Risque** : L'intégration d'un template Bootstrap ou d'autres bibliothèques tierces peut introduire des vulnérabilités ou des problèmes de compatibilité si elles ne sont pas maintenues à jour.
    *   **Mitigation** : Suivi régulier des mises à jour de sécurité des dépendances, utilisation d'outils comme `composer audit`.

### 10.2. Limites

1.  **Fonctionnalités Commerciales** :
    *   **Paiement** : L'absence de système de paiement intégré est une limitation majeure pour une application de réservation réelle.
    *   **Tarification Dynamique** : Le projet ne gère pas de règles de tarification complexes (saisons, promotions, durée du séjour, etc.).
    *   **Gestion des Annulations/Modifications** : Les fonctionnalités CRUD sont axées sur la création et la visualisation, mais la gestion complète des cycles de vie des réservations (annulations, modifications de dates/chambres) n'est pas détaillée.

2.  **Expérience Utilisateur (UX) Avancée** :
    *   Bien que le design responsive soit une exigence, l'application ne propose pas de fonctionnalités UX avancées comme des calendriers interactifs pour la sélection des dates, des suggestions de chambres basées sur les préférences, ou des notifications en temps réel.

3.  **Internationalisation (i18n)** :
    *   Le projet est monolingue. L'ajout de la prise en charge de plusieurs langues serait nécessaire pour une centrale de réservation nationale ou internationale.

4.  **Reporting et Statistiques** :
    *   L'application ne fournit pas d'outils de reporting ou de statistiques pour les administrateurs (taux d'occupation, revenus, etc.).

### 10.3. Dettes Techniques

1.  **Couverture des Tests** :
    *   **Dette** : Bien que des tests soient requis, la couverture complète (tests unitaires, fonctionnels, d'intégration) de toutes les fonctionnalités critiques peut être limitée par le temps. Une couverture insuffisante peut rendre les futures modifications risquées.
    *   **Plan d'action** : Prioriser les tests des logiques métier critiques (réservation, disponibilité) et des contrôleurs principaux. Augmenter progressivement la couverture.

2.  **Documentation du Code** :
    *   **Dette** : La documentation du code source peut être hétérogène ou incomplète, en particulier pour les logiques complexes ou les choix d'architecture.
    *   **Plan d'action** : Maintenir une documentation Javadoc/PHPDoc à jour pour les classes, méthodes et interfaces, ainsi qu'un README détaillé pour le projet.

3.  **Optimisation SonarQube** :
    *   **Dette** : Les rapports SonarQube peuvent révéler des "code smells", des duplications ou des vulnérabilités qui, bien que non bloquantes, augmentent la dette technique et la complexité de maintenance à long terme.
    *   **Plan d'action** : Réviser et corriger les problèmes de haute priorité identifiés par SonarQube, en intégrant l'analyse SonarQube dans le processus de développement.

4.  **Gestion des Exceptions et Messages Utilisateur** :
    *   **Dette** : La gestion des exceptions peut être fonctionnelle mais ne pas toujours fournir des messages d'erreur clairs et conviviaux à l'utilisateur final, ou ne pas logguer suffisamment de détails pour le débogage.
    *   **Plan d'action** : Affiner les messages d'erreur pour l'utilisateur, implémenter un système de logging robuste (ex: Monolog) pour les erreurs serveur.

5.  **Évolutivité de l'Architecture** :
    *   **Dette** : Bien que le projet suive une architecture MVC, certaines décisions de conception initiales pourraient ne pas être optimales pour une évolution vers des microservices ou une architecture plus distribuée.
    *   **Plan d'action** : Réévaluer l'architecture à mesure que les besoins évoluent, en se concentrant sur la séparation des préoccupations et la modularité.

## 11. Crédits

*   **Uly AUSTRIE** - Master AIDB
*   **Abel CORREIA** - Master AIDB
