# Projet PHP : Système de Réservation Hôtelière (Symfony CRUD MVC)

## Table des Matières

*   [1. Contexte du Projet](#1-contexte-du-projet)
*   [2. Spécifications Fonctionnelles](#2-spécifications-fonctionnelles)
    *   [2.1. Règles de Gestion](#21-règles-de-gestion)
    *   [2.2. Fonctionnalités Détaillées](#221-espace-public)
        *   [2.2.1. Espace Public](#221-espace-public)
        *   [2.2.2. Espace Client](#222-espace-client)
        *   [2.2.3. Espace Administrateur](#223-espace-administrateur)
*   [3. Stack Technique](#3-stack-technique)
*   [4. Architecture du Projet](#4-architecture-du-projet)
*   [5. Modèle Conceptuel de Données (MCD)](#5-modèle-conceptuel-de-données-mcd)
*   [6. Guide d'Installation](#6-guide-dinstallation)
    *   [6.1. Installation du Projet](#61-installation-du-projet)
    *   [6.2. Configuration de la Base de Données (Développement)](#62-configuration-de-la-base-de-données-développement)
    *   [6.3. Base de Données (Tests)](#63-base-de-données-tests)
*   [7. Utilisation et Lancement](#7-utilisation-et-lancement)
    *   [7.1. Lancer l'Application](#71-lancer-lapplication)
    *   [7.2. Scénarios d'Utilisation](#72-scénarios-dutilisation)
*   [8. Gestion des Variables d'Environnement (.env)](#8-gestion-des-variables-denvironnement-env)
    *   [8.1. Introduction aux Variables d'Environnement](#81-introduction-aux-variables-denvironnement)
    *   [8.2. Configuration Locale Essentielle](#82-configuration-locale-essentielle)
    *   [8.3. Configuration du Service d'E-mail](#83-configuration-du-service-de-mail)
    *   [8.4. Exemple de Fichier .env.local Complet](#84-exemple-de-fichier-envlocal-complet)
*   [9. Analyse de Code avec SonarQube](#9-analyse-de-code-avec-sonarqube)
*   [10. Dettes techniques](#10-dettes-techniques)
*   [11. Crédits](#11-crédits)

---

## 1. Contexte du Projet

Ce projet a pour objectif la réalisation d'une **application web de type CRUD** (Create, Read, Update, Delete) développée avec le **framework Symfony**. Elle simule un système de réservation centralisé pour un groupe hôtelier national, avec des données persistées dans une **base de données SQL**. Dans le cadre du Master AIDB, ce projet vise à consolider les compétences en développement web moderne, en architecture logicielle et en gestion de projet.

## 2. Spécifications Fonctionnelles

Cette section détaille les exigences fonctionnelles et les règles métier qui régissent le comportement de l'application.

### 2.1. Règles de Gestion

Les règles fondamentales régissant les interactions et les données du système sont les suivantes :

1.  Un client effectue une **réservation spécifique** pour un hôtel et une chambre, avec des dates de début et de fin d'occupation définies.
2.  Un client peut **réserver plusieurs chambres** pour la même période, mais chaque réservation doit inclure au moins une chambre.
3.  Un hôtel propose **plusieurs chambres** de différents types (ex: simple, double, suite).
4.  Chaque hôtel est classifié selon une **catégorie spécifique** (ex: \*, \*\*, \*\*\*).

### 2.2. Fonctionnalités Détaillées

L'application est structurée en plusieurs espaces, chacun offrant des fonctionnalités adaptées à son rôle d'utilisateur.

#### 2.2.1. Espace Public

*   **Accueil** : Permet la recherche de chambres disponibles en fonction de dates de début et de fin.
*   **Réservation** : Offre la possibilité de réserver une chambre (sans gestion de paiement). Une inscription ou une connexion est requise, collectant l'email et le numéro de téléphone du client en complément des données du MCD.
*   **Connexion** : Accès sécurisé à la plateforme.
*   **Mot de passe oublié** : Fonctionnalité de récupération de mot de passe.

#### 7.2.2. Scénarios pour les Utilisateurs Connectés (Clients)

*   **Gérer ses réservations** :
    1.  Le client se connecte à son espace.
    2.  Il accède à la section "**Mes Réservations**".
    3.  Il peut visualiser le statut et les détails de ses réservations passées et futures.
*   **Ajouter un commentaire à une réservation** :
    1.  Depuis la liste de ses réservations, le client sélectionne une réservation.
    2.  Il utilise le formulaire dédié pour ajouter une demande spéciale (ex: lit bébé, préférences).
    3.  Le commentaire est enregistré et visible par l'administration.
*   **Effectuer une nouvelle réservation** :
    1.  Le client, une fois connecté, peut rechercher et réserver une chambre.
    2.  Ses informations personnelles (email, téléphone) sont pré-remplies ou facilement accessibles.

#### 7.2.3. Scénarios pour les Administrateurs

*   **Gérer les chambres (CRUD)** :
    1.  L'administrateur se connecte à l'espace d'administration.
    2.  Il navigue vers la section "**Gestion des Chambres**".
    3.  Il peut **ajouter** de nouvelles chambres, **modifier** les informations existantes, **supprimer** des chambres, et **rechercher** des chambres spécifiques.
    4.  La pagination est disponible pour les listes longues.
*   **Gérer les réservations (CRUD)** :
    1.  L'administrateur accède à la section "**Gestion des Réservations**".
    2.  Il peut **consulter** toutes les réservations, les **modifier** (ex: statut, dates si nécessaire), les **supprimer**, et **rechercher** par numéro de réservation.
    3.  Le détail d'une réservation affiche toutes les chambres associées.
    4.  La pagination est disponible.
*   **Gérer les clients (CRUD)** :
    1.  L'administrateur accède à la section "**Gestion des Clients**".
    2.  Il peut **ajouter**, **modifier** ou **supprimer** des profils clients, et **rechercher** des clients par nom ou e-mail.
    3.  La pagination est disponible.

**Note :** Toutes les vues Twig sont conçues pour être ***responsive design***, sans charte graphique imposée. L'intégration d'un template Bootstrap est autorisée.

## 3. Stack Technique

Cette section présente l'ensemble des technologies et outils utilisés pour le développement de l'application, avec une brève justification des choix effectués.

*   **Framework** : **Symfony** (choisi pour sa robustesse, sa modularité et son écosystème mature, facilitant le développement rapide et maintenable d'applications web complexes).
*   **Langage de Programmation** : **PHP**
*   **Base de Données** : **SQL** (pour sa fiabilité et sa large adoption dans les systèmes de gestion de données relationnelles).
*   **Moteur de Templating** : **Twig** (pour sa syntaxe concise et sa performance, permettant une séparation claire entre la logique métier et la présentation).
*   **Développement Front-end** : **Bootstrap** (sélectionné pour son efficacité à créer des interfaces *responsive design* rapidement et de manière standardisée).

## 4. Architecture du Projet

Le projet adhère à une architecture en couches, respectant les bonnes pratiques de Symfony, afin d'assurer la maintenabilité, la scalabilité et la séparation des préoccupations.

*   **Contrôleurs** : Gèrent les requêtes HTTP et orchestrent les réponses.
*   **Services** : Encapsulent la logique métier et les opérations complexes.
*   **Entités** : Représentent les objets métier et sont mappées aux tables de la base de données.
*   **Repositories** : Fournissent des méthodes pour interagir avec les entités et la base de données.

*(**Recommandation :** Il est fortement conseillé d'ajouter ici un diagramme d'architecture (ex: C4 model, diagramme de composants) pour une meilleure visualisation des interactions entre les couches et les composants du système.)*

## 5. Modèle Conceptuel de Données (MCD)

Cette section présente la structure des données de l'application, essentielle à la compréhension de la logique métier et de la persistance.

### Modèle Conceptuel de Données (MCD)
![Diagramme MCD](_Doc-Conception/MCD.png)

### Modèle Logique de Données (MLD)
![Diagramme MLD](_Doc-Conception/MLD.PNG)

*(**Note :** Les diagrammes ci-dessus illustrent la structure des données et leurs relations. Le **MCD** présente une vue conceptuelle des entités métier, tandis que le **MLD** détaille l'organisation des tables dans la base de données, incluant les types de données et les contraintes.)*

## 6. Guide d'Installation

Ce guide fournit les étapes nécessaires pour installer et configurer le projet sur votre environnement local.

### 6.1. Installation du Projet

1.  **Cloner le dépôt** : Récupérez le projet depuis son dépôt GitHub.
2.  **Installer les dépendances** : Exécutez `composer install` à la racine du projet.
3.  **(Optionnel) Résolution des problèmes de cache** : En cas d'erreur lors du `clear:cache`, supprimez les dossiers `vendor` et le fichier `composer.lock` (`Remove-Item -Recurse -Force vendor` et `Remove-Item -Force composer.lock` sur PowerShell ou `rm -rf vendor composer.lock` sur Bash/Zsh), puis réinstallez les dépendances avec `composer install`.

### 6.2. Configuration de la Base de Données (Développement)

Ces étapes concernent la mise en place de la base de données pour l'environnement de développement.

1.  **Vérification PHP.ini** : Assurez-vous que votre fichier `php.ini` (localisable via `php --ini`) est correctement configuré avec les extensions `mysql` et `pdo_mysql` activées.
2.  **Création de la base de données** : Exécutez `php bin/console doctrine:database:create` (pour un SGBD comme **MySQL** ou **PostgreSQL**) ou créez-la manuellement via votre SGBD.
3.  **(Optionnel) Génération de migration** : Si aucun fichier de migration n'est présent dans `/migrations`, générez-en un avec `php bin/console make:migration`.
4.  **Exécution des migrations** : Appliquez les migrations à la base de données avec `php bin/console doctrine:migrations:migrate`.
5.  **(Optionnel) Validation du schéma** : Vérifiez la synchronisation du schéma de la base de données avec les entités Doctrine via `php bin/console doctrine:schema:validate`. Le résultat attendu est : `[OK] The mapping files are correct. [OK] The database schema is in sync with the mapping files.`
6.  **(Optionnel) Chargement des données de développement** : Installez les données par défaut configurées dans `DataFixtures\AppFixtures` avec `php bin/console doctrine:fixtures:load --group=dev`.

### 6.3. Base de Données (Tests)

Cette sous-section détaille la configuration de la base de données spécifiquement pour l'environnement de test.

1.  **Vérification PHP.ini** : Comme pour le développement, assurez-vous que les extensions `mysql` et `pdo_mysql` sont activées.
2.  **(Optionnel) Suppression de la base de données de test existante** : `php bin/console doctrine:database:drop --env=test --force`.
3.  **Création de la base de données de test** : `php bin/console doctrine:database:create --env=test`.
4.  **Exécution des migrations de test** : `php bin/console doctrine:migrations:migrate --env=test`.
5.  **(Optionnel) Validation du schéma de test** : `php bin/console doctrine:schema:validate --env=test`. Le résultat attendu est : `[OK] The mapping files are correct. [OK] The database schema is in sync with the mapping files.`
6.  **Chargement des données de test** : Installez les données par défaut configurées dans `DataFixtures\TestFixtures` avec `php bin/console doctrine:fixtures:load --env=test --group=test`.
7.  **Lancement des tests** : Exécutez les tests unitaires et fonctionnels avec `php bin/phpunit`.

## 7. Utilisation et Lancement

Cette section explique comment démarrer l'application et présente les différents scénarios d'utilisation pour les différents profils d'utilisateurs.

### 7.1. Lancer l'Application

1.  **Démarrer le serveur de développement** : Exécutez `symfony server:start` à la racine du projet.
2.  **Accéder à l'accueil Symfony** : Ouvrez votre navigateur et naviguez vers `http://localhost:8000/`.
3.  **Accéder à l'application** : L'application est accessible via `http://localhost:8000/home`.

### 7.2. Scénarios d'Utilisation

Cette section décrit les principaux parcours utilisateurs et les fonctionnalités accessibles selon le rôle.

#### 7.2.1. Scénarios pour les Utilisateurs Publics (Non Connectés)

*   **Rechercher une chambre** :
    1.  L'utilisateur accède à la page d'accueil.
    2.  Il utilise le formulaire de recherche pour spécifier des dates de début et de fin de séjour.
    3.  Le système affiche les chambres disponibles correspondant aux critères.
*   **Consulter les détails d'une chambre** :
    1.  Depuis les résultats de recherche ou une liste, l'utilisateur clique sur une chambre.
    2.  Il visualise les informations détaillées de la chambre (type, catégorie d'hôtel, description, etc.).
*   **Tenter de réserver une chambre** :
    1.  L'utilisateur sélectionne une chambre et clique sur "**Réserver**".
    2.  Le système le redirige vers la page de connexion/inscription pour finaliser la réservation.
*   **S'inscrire / Se connecter** :
    1.  L'utilisateur clique sur "**Connexion**" ou "**Inscription**".
    2.  Il remplit le formulaire correspondant (email, mot de passe, numéro de téléphone, etc.).
    3.  Après validation, il accède à son espace client.
*   **Réinitialiser son mot de passe** :
    1.  L'utilisateur clique sur "**Mot de passe oublié**".
    2.  Il saisit son adresse e-mail.
    3.  Le système envoie un lien de réinitialisation par e-mail.
    4.  L'utilisateur clique sur le lien et définit un nouveau mot de passe.

#### 7.2.2. Espace Client

*   **Visualisation des Réservations** : Affichage de l'historique des réservations effectuées par le client.
*   **Ajout de Commentaires** : Possibilité d'ajouter des demandes spéciales ou commentaires à une réservation (ex: lit bébé, préférences).
*   **Effectuer une nouvelle réservation** : Le client, une fois connecté, peut rechercher et réserver une chambre. Ses informations personnelles (email, téléphone) sont pré-remplies ou facilement accessibles.

#### 2.2.3. Espace Administrateur

*   **Gestion des Chambres (CRUD)** : Interface complète pour la création, lecture, mise à jour et suppression des chambres, incluant pagination et recherche.
*   **Gestion des Réservations (CRUD)** : Interface complète pour la gestion des réservations clients, avec pagination et recherche par numéro de réservation. Le détail d'une réservation affiche toutes les chambres associées.
*   **Gestion des Clients (CRUD)** : Interface complète pour la gestion des profils clients, avec pagination et recherche par nom ou email.

**Note :** Toutes les vues Twig sont conçues pour être ***responsive design***, sans charte graphique imposée. L'intégration d'un template Bootstrap est autorisée.

## 8. Gestion des Variables d'Environnement (.env)

La configuration des variables d'environnement est cruciale pour le bon fonctionnement de l'application, en particulier pour adapter le projet à différents environnements (développement, test, production) sans modifier le code source.

### 8.1. Introduction aux Variables d'Environnement

Le fichier `.env` est versionné dans le cadre de ce projet de contrôle continu. Dans ce contexte spécifique, le partage des valeurs de configuration ne présente pas de risque particulier lié à des environnements de production. Pour une configuration locale, il est fortement recommandé de créer un fichier `.env.local` à la racine du projet. Ce fichier surchargera les valeurs définies dans `.env` et ne doit **pas** être versionné.

### 8.2. Configuration Locale Essentielle

Voici les variables d'environnement clés à configurer dans votre fichier `.env.local` pour faire fonctionner le projet en local :

*   **`APP_ENV`** : Définit l'environnement de l'application. Pour le développement local, utilisez `dev`.
    ```dotenv
    APP_ENV=dev
    ```

*   **`APP_SECRET`** : Une chaîne de caractères unique utilisée par Symfony pour la sécurité (signatures, sessions, etc.). Générez une chaîne aléatoire et complexe.
    ```dotenv
    APP_SECRET="votre_secret_unique_et_complexe_ici"
    ```

*   **`DATABASE_URL`** : L'URL de connexion à votre base de données. Le format varie selon le SGBD.

    *   **Exemple pour MySQL (avec XAMPP/WAMP ou Docker) :**
        ```dotenv
        DATABASE_URL="mysql://db_user:db_password@127.0.0.1:3306/db_name?serverVersion=5.7&charset=utf8mb4"
        ```
        *   `db_user` : Nom d'utilisateur de votre base de données (souvent `root` en local).
        *   `db_password` : Mot de passe de votre base de données (souvent vide en local pour `root`).
        *   `127.0.0.1` : Adresse IP du serveur de base de données (souvent `localhost` ou `127.0.0.1`).
        *   `3306` : Port de votre serveur MySQL.
        *   `db_name` : Nom de la base de données que vous avez créée pour le projet.
        *   `serverVersion` : Version de votre serveur MySQL (ajustez si nécessaire, ex: `8.0`).

    *   **Exemple pour PostgreSQL :**
        ```dotenv
        DATABASE_URL="postgresql://db_user:db_password@127.0.0.1:5432/db_name?serverVersion=13&charset=utf8"
        ```
        *   `db_user` : Nom d'utilisateur de votre base de données.
        *   `db_password` : Mot de passe de votre base de données.
        *   `127.0.0.1` : Adresse IP du serveur de base de données.
        *   `5432` : Port de votre serveur PostgreSQL.
        *   `db_name` : Nom de la base de données que vous avez créée pour le projet.

### 8.3. Configuration du Service d'E-mail

Pour assurer le bon fonctionnement des fonctionnalités d'envoi d'e-mails (réinitialisation de mot de passe, vérification d'e-mail), vous devez configurer la variable d'environnement `MAILER_DSN` dans votre fichier `.env` ou `.env.local`.

1.  **Sélectionnez un service d'e-mail** : Choisissez un service SMTP (Gmail, Outlook), un service de test (Mailtrap), ou un service transactionnel (SendGrid, Mailgun).
2.  **Récupérez vos identifiants** : Obtenez les informations nécessaires (nom d'utilisateur, mot de passe, clé API, hôte SMTP, port).
3.  **Modifiez votre fichier `.env` ou `.env.local`** : Remplacez la ligne `MAILER_DSN=null://null` par la configuration appropriée.

    **Exemples de configuration `MAILER_DSN` :**

    *   **Mailtrap (développement/test) :**
        ```dotenv
        MAILER_DSN=smtp://VOTRE_USERNAME_MAILTRAP:VOTRE_PASSWORD_MAILTRAP@smtp.mailtrap.io:2525
        ```
        *(Remplacez par vos identifiants Mailtrap, disponibles sur votre compte.)*

    *   **Serveur SMTP générique (ex: Gmail avec mot de passe d'application si 2FA activé) :**
        ```dotenv
        MAILER_DSN=smtp://VOTRE_EMAIL:VOTRE_MOT_DE_PASSE@smtp.VOTRE_SERVEUR_SMTP:PORT
        ```
        *(Exemple Gmail: `smtp://monemail@gmail.com:monmotdepasseapp@smtp.gmail.com:587`)*

    *   **SendGrid :**
        ```dotenv
        MAILER_DSN=smtp://apikey:VOTRE_CLE_API_SENDGRID@smtp.sendgrid.net:587
        ```

4.  **Redémarrez le serveur Symfony** : Après toute modification du fichier `.env` ou `.env.local`, redémarrez votre serveur web Symfony pour que les changements soient pris en compte.

Assurez-vous également que l'adresse e-mail de l'expéditeur configurée dans votre application (ex: `no-reply@hotel-reservation.com`) est autorisée à envoyer des e-mails via le service choisi.

### 8.4. Exemple de Fichier `.env.local` Complet

Voici un exemple de ce à quoi pourrait ressembler votre fichier `.env.local` pour un environnement de développement local utilisant MySQL :

```dotenv
# .env.local
# Fichier de configuration des variables d'environnement locales.
# NE PAS VERSIONNER CE FICHIER !

APP_ENV=dev
APP_SECRET="votre_secret_unique_et_complexe_ici_genere_par_symfony_ou_vous-meme"

# Configuration de la base de données MySQL
DATABASE_URL="mysql://root:@127.0.0.1:3306/cc_symfony_aidb?serverVersion=5.7&charset=utf8mb4"

# Configuration du service d'e-mail (exemple Mailtrap)
MAILER_DSN=smtp://VOTRE_USERNAME_MAILTRAP:VOTRE_PASSWORD_MAILTRAP@smtp.mailtrap.io:2525

# Pour les tests, vous pouvez définir une URL de base de données différente si nécessaire
# DATABASE_URL_TEST="mysql://root:@127.0.0.1:3306/cc_symfony_aidb_test?serverVersion=5.7&charset=utf8mb4"
```

## 9. Analyse de Code avec SonarQube

SonarQube est une plateforme open source dédiée à l'amélioration continue de la qualité du code. Voici les étapes pour configurer et lancer une analyse SonarQube en local.

### 9.1. Installation de SonarQube (via Docker)

1.  **Prérequis** : Assurez-vous que Docker est installé et en cours d'exécution sur votre machine.
2.  **Lancer l'image SonarQube** : Ouvrez votre terminal et exécutez la commande suivante pour démarrer un conteneur SonarQube. Cela téléchargera l'image si elle n'est pas déjà présente.
    ```bash
    docker run -d --name sonarqube -p 9000:9000 -p 9092:9092 sonarqube
    ```
    *   `-d` : Démarre le conteneur en mode détaché (en arrière-plan).
    *   `--name sonarqube` : Donne un nom au conteneur pour faciliter sa gestion.
    *   `-p 9000:9000` : Mappe le port 9000 de votre machine hôte au port 9000 du conteneur (interface web SonarQube).
    *   `-p 9092:9092` : Mappe le port 9092 de votre machine hôte au port 9092 du conteneur (pour le SonarScanner).
3.  **Accéder à SonarQube** : Une fois le conteneur démarré (cela peut prendre quelques minutes), ouvrez votre navigateur et accédez à `http://localhost:9000`.
4.  **Connexion initiale** : Les identifiants par défaut sont `admin` / `admin`. Il vous sera demandé de changer le mot de passe lors de la première connexion.
5.  **Générer un jeton (token)** : Pour lancer l'analyse depuis SonarScanner, vous aurez besoin d'un jeton.
    *   Connectez-vous à SonarQube.
    *   Allez dans `Create a project`.
    *   Suiver les instructions que votre version de sonarqube vous donne.
    *   Un nouveau jeton sera généré, copiez-le. Ce jeton sera utilisé dans la commande d'analyse.

### 9.2. Installation de SonarScanner

SonarScanner est l'outil client qui analyse votre code et envoie les résultats à votre instance SonarQube.

1.  **Télécharger SonarScanner** : Rendez-vous sur la page de téléchargement de SonarScanner : [https://docs.sonarqube.org/latest/analyzing-source/scanners/sonarscanner/](https://docs.sonarqube.org/latest/analyzing-source/scanners/sonarscanner/) et téléchargez la version correspondant à votre système d'exploitation.
2.  **Extraire l'archive** : Décompressez le fichier téléchargé dans un répertoire de votre choix (par exemple, `C:\sonar-scanner` sur Windows ou `/opt/sonar-scanner` sur Linux/macOS).
3.  **Ajouter au PATH (recommandé)** : Ajoutez le répertoire `bin` de SonarScanner à la variable d'environnement `PATH` de votre système. Cela vous permettra d'exécuter `sonar-scanner` depuis n'importe quel répertoire.

### 9.3. Lancer l'Analyse

Une fois SonarQube et SonarScanner configurés, vous pouvez lancer l'analyse de votre projet.

1.  **Ouvrir un terminal** : Naviguez jusqu'à la racine de votre projet Symfony.
2.  **Exécuter la commande d'analyse** : Utilisez la commande suivante, en remplaçant `YOUR_TOKEN` par le jeton que vous avez généré précédemment dans SonarQube.
    ```bash
    sonar-scanner.bat -D"sonar.projectKey=CC-Symfony" -D"sonar.sources=src" -D"sonar.tests=tests" -D"sonar.host.url=http://localhost:9000" -D"sonar.token=YOUR_TOKEN"
    ```
    *   `sonar.projectKey` : Une clé unique pour identifier votre projet dans SonarQube.
    *   `sonar.sources` : Le répertoire contenant le code source à analyser.
    *   `sonar.tests` : Le répertoire contenant les tests (optionnel, mais recommandé).
    *   `sonar.host.url` : L'URL de votre instance SonarQube.
    *   `sonar.token` : Le jeton d'authentification généré dans SonarQube.

3.  **Consulter les résultats** : Après l'exécution de la commande, les résultats de l'analyse seront envoyés à votre instance SonarQube. Vous pouvez les consulter en vous rendant sur `http://localhost:9000` et en sélectionnant votre projet (`CC-Symfony`).

## 10. Dettes techniques

Cette section aborde les améliorations techniques envisagées pour des évolutions futures, témoignant d'une approche proactive et réaliste face aux exigences d'un développement logiciel professionnel.

*   **Absence de tests unitaires et fonctionnels exhaustifs** : Bien que des tests soient mentionnés dans le guide d'installation, une couverture de test insuffisante peut entraîner des régressions et rendre les refactorings risqués.
*   **Optimisation des requêtes base de données (N+1 problem)** : Certaines requêtes Doctrine ORM pourraient ne pas être optimisées, menant à des problèmes de performance, notamment lors de l'affichage de listes d'entités avec des relations.
*   **Gestion des erreurs et logs** : Une gestion des erreurs et une journalisation (logging) basiques peuvent être présentes, mais une stratégie plus robuste (ex: Sentry, ELK Stack) pourrait manquer pour la production.
*   **Sécurité approfondie** : Les fonctionnalités de sécurité de base de Symfony sont utilisées, mais des analyses de sécurité plus poussées (OWASP Top 10) ou l'intégration de solutions de sécurité tierces pourraient être nécessaires.
*   **Internationalisation (i18n) et Localisation (l10n)** : Le projet est actuellement monolingue. L'ajout de la prise en charge de plusieurs langues nécessiterait une refonte des textes et des formats.
*   **Amélioration de l'expérience utilisateur (UX)** : Bien que le design soit responsive, des améliorations UX (animations, chargement asynchrone, feedback utilisateur plus riche) pourraient être apportées.
*   **Code dupliqué ou non DRY (Don't Repeat Yourself)** : Certaines parties du code, notamment dans les contrôleurs ou les vues, pourraient contenir des répétitions qui pourraient être factorisées dans des services, des Twig components ou des macros.
*   **Mise à jour des dépendances** : Ne pas maintenir les dépendances à jour peut introduire des vulnérabilités de sécurité et empêcher l'accès aux nouvelles fonctionnalités ou optimisations.
*   **Documentation interne du code** : La documentation du code (docblocks, commentaires explicatifs) pourrait être améliorée pour faciliter la compréhension et la maintenance par d'autres développeurs.
*   **Scalabilité de l'infrastructure** : Le projet est conçu pour un environnement local. Pour une mise en production à grande échelle, des considérations d'infrastructure (équilibrage de charge, bases de données répliquées, mise en cache distribuée) seraient à adresser.

## 11. Crédits

*   **Uly AUSTRIE** - Master AIDB
*   **Abel CORREIA** - Master AIDB
