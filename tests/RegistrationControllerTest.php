<?php

namespace App\Tests;

use App\DataFixtures\TestFixtures;
use App\Repository\CompteRepository;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use App\Tests\BaseWebTestCase;

/**
 * Test du contrôleur d'enregistrement.
 *
 * Cette classe contient les tests fonctionnels pour le processus d'enregistrement des utilisateurs.
 */
class RegistrationControllerTest extends BaseWebTestCase
{
    private KernelBrowser $client;
    private CompteRepository $userRepository;

    /**
     * Initialise le client de test et charge les fixtures nécessaires avant chaque test.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient();

        $container = static::getContainer();
        /** @var EntityManager $em */
        $em = $container->get('doctrine')->getManager();

        // Load fixtures after database is ready
        $fixture = new TestFixtures($container->get('security.password_hasher'));
        $fixture->load($em);
        $em->flush(); // Ensure fixtures are persisted
        $em->clear(); // Clear entity manager after loading fixtures

        $this->userRepository = $container->get(CompteRepository::class);
    }

    /**
     * Teste le processus d'enregistrement d'un nouvel utilisateur.
     */
    public function testRegister(): void
    {
        /*
         * Accès à la page d'inscription
         */
        $this->client->request('GET', '/register');
        self::assertResponseIsSuccessful();

        /*
         * Soumission du formulaire d'enregistrement
         */
        $this->client->submitForm('Register', [
            'registration_form[email]' => 'newuser@example.com',
            'registration_form[plainPassword]' => 'password',
            'registration_form[agreeTerms]' => 1,
        ]);

        /*
         * Vérifie qu'un utilisateur a été créé
         */
        // Assuming 3 users from fixtures + 1 new
        self::assertCount(4, $this->userRepository->findAll());

        /*
         * Vérifie l'email enregistré
         */
        $user = $this->userRepository->findOneBy([
            'email' => 'newuser@example.com'
        ]);
        self::assertNotNull($user);

        /*
         * Vérifie la redirection après inscription réussie
         */
        self::assertResponseRedirects();
        $this->client->followRedirect();
        $this->client->followRedirect();

        self::assertResponseIsSuccessful();
    }
}
