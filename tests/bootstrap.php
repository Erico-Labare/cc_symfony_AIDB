<?php

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';

/**
 * Fichier de bootstrapping pour les tests PHPUnit.
 *
 * Ce fichier est exécuté avant l'ensemble des tests. Il est responsable de :
 * - Charger l'autoloader de Composer.
 * - Initialiser les variables d'environnement à partir du fichier .env
 *   (ou .env.local si présent) pour configurer l'application en mode test.
 * - Ajuster le masque de création de fichiers (umask) si l'application est en mode debug.
 */
if (method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv(dirname(__DIR__).'/.env');
}

if ($_SERVER['APP_DEBUG']) {
    umask(0000);
}
