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
- (Optionel) Vérifier doctrine avec ````php bin/console doctrine:schema:validate```` dans le terminal à la racine du projet (Resultat attendu : ````[OK] The mapping files are correct.
[OK] The database schema is in sync with the mapping files.````).

## Utilisation & Fonctionnement


## .env

Un fichier .env est versionné ici. Il est versionné dans le cadre du contexte du projet qui est un projet de rendu Contrôle Continue. Dans ce cadre-là, il n'y a pas de risque particulier sur le partage de valeurs de production.


## Configuration

Il est possible de configurer le .env ainsi :
- TODO config .env.local


TODO : Compte de teste / BDD déjà remplit

## BDD



## Architecture


## Testes & Converture


## Risques, Limites et Dettes techniques


## Crédits