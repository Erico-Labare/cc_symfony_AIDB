<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Classe de base pour les tests fonctionnels.
 *
 * Cette classe étend WebTestCase et fournit des fonctionnalités pour la gestion de la base de données
 * (création, migration) avant l'exécution des tests.
 */
class BaseWebTestCase extends WebTestCase
{
    /**
     * Exécute les commandes de console pour initialiser la base de données avant l'exécution de tous les tests.
     *
     * Cela inclut la suppression, la création et la migration de la base de données.
     */
    public static function setUpBeforeClass(): void
    {
        // Boot a dedicated kernel for running console commands (database setup)
        $kernel = static::createKernel();
        $kernel->boot();

        $application = new Application($kernel);
        $application->setAutoExit(false);

        self::runConsoleCommandStatic($application, 'doctrine:database:drop', ['--force' => true, '--if-exists' => true]);
        self::runConsoleCommandStatic($application, 'doctrine:database:create');
        self::runConsoleCommandStatic($application, 'doctrine:migrations:migrate', ['--no-interaction' => true]);

        $kernel->shutdown(); // Shut down the kernel used for commands
    }

    /**
     * Méthode d'initialisation appelée avant chaque test.
     *
     * Les classes enfants doivent implémenter leur propre logique d'initialisation si nécessaire.
     */
    protected function setUp(): void
    {
        // No specific setup here, rely on child classes for createClient() or bootKernel()
        // parent::setUp() is implicitly called by WebTestCase descendants.
    }

    /**
     * Méthode de nettoyage appelée après chaque test.
     *
     * Assure que le kernel est arrêté pour éviter les fuites d'état entre les tests.
     */
    protected function tearDown(): void
    {
        parent::tearDown();
        // Ensure kernel is shut down after each test to prevent state leakage
        static::ensureKernelShutdown();
    }

    /**
     * Méthode de nettoyage appelée après l'exécution de tous les tests de la classe.
     *
     * Peut être utilisée pour des opérations de nettoyage globales, comme la suppression de la base de données.
     */
    public static function tearDownAfterClass(): void
    {
        // Optionally, drop the database after all tests are done
        // $kernel = static::createKernel();
        // $kernel->boot();
        // $application = new Application($kernel);
        // $application->setAutoExit(false);
        // self::runConsoleCommandStatic($application, 'doctrine:database:drop', ['--force' => true, '--if-exists' => true]);
        // $kernel->shutdown();
    }


    /**
     * Exécute une commande de console de manière statique.
     *
     * @param Application $application L'application console.
     * @param string $command Le nom de la commande à exécuter.
     * @param array $options Les options de la commande.
     * @return int Le code de sortie de la commande.
     */
    protected static function runConsoleCommandStatic(Application $application, string $command, array $options = []): int
    {
        $input = new ArrayInput(array_merge(['command' => $command], $options));
        $input->setInteractive(false);
        return $application->run($input, new NullOutput());
    }
}
