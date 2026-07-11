<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use App\Entity\Compte;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Test du contrôleur de connexion.
 *
 * Cette classe contient les tests fonctionnels pour le processus de connexion.
 */
class LoginControllerTest extends BaseWebTestCase
{
    private KernelBrowser $client;
    private ?Compte $testUser = null;

    /**
     * Initialise le client de test et les données nécessaires avant chaque test.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient();
        $this->setupData();
    }

    /**
     * Configure les données de test, s'assurant qu'un utilisateur de test existe.
     */
    private function setupData(): void
    {
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $passwordHasher = self::getContainer()->get(UserPasswordHasherInterface::class);

        // Ensure test user exists or create it
        $this->testUser = $entityManager->getRepository(Compte::class)
            ->findOneBy(['email' => 'test@test.com']);

        if ($this->testUser === null) {
            $this->testUser = new Compte();
            $this->testUser->setEmail('test@test.com');
            $this->testUser->setPassword($passwordHasher->hashPassword($this->testUser, 'password'));
            $this->testUser->setRole('ROLE_USER');
            $this->testUser->setIsVerified(true);
            $entityManager->persist($this->testUser);
            $entityManager->flush();
            $entityManager->clear();
        }
    }

    /**
     * Teste le processus de connexion avec différents scénarios (email invalide, mot de passe incorrect, connexion réussie).
     */
    public function testLogin(): void
    {
        /*
         * TEST : EMAIL INVALIDE
         * Vérifie que la connexion échoue avec un email non enregistré.
         */

        $this->client->request('GET', '/login');
        self::assertResponseIsSuccessful();

        $this->client->submitForm('Sign in', [
            '_username' => 'doesNotExist@example.com',
            '_password' => 'password',
        ]);

        self::assertResponseRedirects('/login');
        $this->client->followRedirect();

        // Vérifie que le message d'erreur est affiché.
        self::assertSelectorTextContains(
            '.alert-danger',
            'Invalid credentials.'
        );

        /*
         * TEST : MOT DE PASSE INCORRECT
         * Vérifie que la connexion échoue avec un mot de passe incorrect.
         */

        $this->client->request('GET', '/login');
        self::assertResponseIsSuccessful();

        $this->client->submitForm('Sign in', [
            '_username' => 'test@test.com',
            '_password' => 'bad-password',
        ]);

        self::assertResponseRedirects('/login');
        $this->client->followRedirect();

        // Vérifie que le message d'erreur est affiché.
        self::assertSelectorTextContains(
            '.alert-danger',
            'Invalid credentials.'
        );

        /*
         * TEST : CONNEXION RÉUSSIE
         * Vérifie que la connexion réussit avec des identifiants valides.
         */

        $this->client->submitForm('Sign in', [
            '_username' => 'test@test.com',
            '_password' => 'password',
        ]);

        // Vérifie la redirection après connexion.
        self::assertResponseRedirects('/');
        $this->client->followRedirect();

        // Vérifie qu'aucune erreur n'est affichée.
        self::assertSelectorNotExists('.alert-danger');
    }
}
