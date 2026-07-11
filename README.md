## Contexte


## Stack technique (Languages et packages utilisés)


## Installations

### Installation du projet
- Cloner le projet depuis le dépôt GitHub.
- Installer les dépendances avec ````composer install```` dans le terminal à la racine du projet.
- (Optionel) si erreur sur le clear:cache, utiliser ````Remove-Item -Recurse -Force vendor```` et ````Remove-Item -Force composer.lock```` dans le terminal à la racine du projet, puis réinstaller les dépendances avec ````composer install````.

### Installation de la BDD
- Vérifiez que votre php.ini (localisable avec la commande ````php --ini````) est correctement configuré pour le projet (extention mysql et pdo_mysql activés).
- Créer la base de données avec ````php bin/console doctrine:database:create```` dans le terminal à la racine du projet ou manuellement dans votre SGBD.
- (Optionel)Générer une migration avec ````php bin/console make:migration```` dans le terminal à la racine si le fichier de migration n'est pas déjà présent dans /migrations.
- Exécuter la migration avec ````php bin/console doctrine:migrations:migrate```` dans le terminal à la racine du projet ou manuellement dans votre SGBD.
- (Optionel) Vérifier doctrine avec ````php bin/console doctrine:schema:validate```` dans le terminal à la racine du projet (Resultat attendu : ````[OK] The mapping files are correct. [OK] The database schema is in sync with the mapping files.````).
- (Optionel) Installer des données par défault configurer dans ````DataDixtures\AppFixtures```` avec ````php bin/console doctrine:fixtures:load --group=dev```` dans le terminal à la racine du projet.


## Utilisation & Fonctionnement

### Lancer le projet
- Lancer le serveur de développement avec ````symfony server:start```` dans le terminal à la racine du projet.
- Accéder **à la page d'acceuil Symfony** via l'URL http://localhost:8000/ dans votre navigateur.
- Accéder **à la page d'acceuil de l'application** via l'URL http://localhost:8000/home dans votre navigateur.


## .env

Un fichier .env est versionné ici. Il est versionné dans le cadre du contexte du projet qui est un projet de rendu Contrôle Continue. Dans ce cadre-là, il n'y a pas de risque particulier sur le partage de valeurs de production.


## Configuration

Il est possible de configurer le .env ainsi :
- TODO config .env.local

### Configuration du service d'e-mail

Pour que les fonctionnalités d'envoi d'e-mails (comme la réinitialisation de mot de passe et la vérification d'e-mail) fonctionnent correctement, vous devez configurer un service d'envoi d'e-mails. Symfony utilise la variable d'environnement `MAILER_DSN` pour cela.

1.  **Choisissez un service d'e-mail** : Vous pouvez utiliser un service SMTP standard (comme Gmail, Outlook), un service de développement/test (comme Mailtrap), ou un service d'e-mail transactionnel (comme SendGrid, Mailgun).
2.  **Obtenez vos identifiants** : Récupérez les informations nécessaires pour le service choisi (par exemple, nom d'utilisateur, mot de passe, clé API, hôte SMTP, port).
3.  **Modifiez votre fichier `.env`** : Ouvrez le fichier `.env` à la racine de votre projet.
4.  **Configurez `MAILER_DSN`** : Remplacez la ligne `MAILER_DSN=null://null` par la configuration appropriée pour votre service.

    **Exemples de configuration `MAILER_DSN` :**

    *   **Pour Mailtrap (développement/test) :**
        ```
        MAILER_DSN=smtp://VOTRE_USERNAME_MAILTRAP:VOTRE_PASSWORD_MAILTRAP@smtp.mailtrap.io:2525
        ```
        (Remplacez par vos identifiants Mailtrap, disponibles sur votre compte Mailtrap.)

    *   **Pour un serveur SMTP générique (ex: Gmail avec mot de passe d'application si 2FA activé) :**
        ```
        MAILER_DSN=smtp://VOTRE_EMAIL:VOTRE_MOT_DE_PASSE@smtp.VOTRE_SERVEUR_SMTP:PORT
        ```
        (Exemple Gmail: `smtp://monemail@gmail.com:monmotdepasseapp@smtp.gmail.com:587`)

    *   **Pour SendGrid :**
        ```
        MAILER_DSN=smtp://apikey:VOTRE_CLE_API_SENDGRID@smtp.sendgrid.net:587
        ```

5.  **Redémarrez votre serveur Symfony** : Après avoir modifié le fichier `.env`, redémarrez votre serveur web Symfony pour que les changements soient pris en compte.

Assurez-vous également que l'adresse e-mail de l'expéditeur configurée dans votre application (par exemple, `no-reply@hotel-reservation.com`) est autorisée à envoyer des e-mails via le service que vous avez choisi.

## BDD



## Architecture


## Testes & Converture

- Vérifiez que votre php.ini (localisable avec la commande ````php --ini````) est correctement configuré pour le projet (extention mysql et pdo_mysql activés).
- (Optionel) Supprimer la base de données de test avec ````php bin/console doctrine:database:drop --env=test --force```` dans le terminal à la racine du projet ou manuellement dans votre SGBD.
- Créer la base de données avec ````php bin/console doctrine:database:create --env=test```` dans le terminal à la racine du projet ou manuellement dans votre SGBD.
- Exécuter la migration avec ````php bin/console doctrine:migrations:migrate --env=test```` dans le terminal à la racine du projet ou manuellement dans votre SGBD.
- (Optionel) Vérifier doctrine avec ````php bin/console doctrine:schema:validate --env=test```` dans le terminal à la racine du projet (Resultat attendu : ````[OK] The mapping files are correct. [OK] The database schema is in sync with the mapping files.````).
- Installer des données par défault configurer dans ````DataDixtures\TestFixtures```` avec ````php bin/console doctrine:fixtures:load --env=test --group=test```` dans le terminal à la racine du projet.
- Lancer les tests avec ````php bin/phpunit```` dans le terminal à la racine du projet.

## Risques, Limites et Dettes techniques


## Crédits


# Random TODO :
- Vérifier que les emails envoyés lors de l'insciption marches bien et sont testés en teste fonctionnel

- Ajouter des messages flash pour les actions de création, modification et suppression d'entités dans les controllers admin
