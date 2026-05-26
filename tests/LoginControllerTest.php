<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

use App\Entity\Compte;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class LoginControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();

        $container = static::getContainer();

        /** @var EntityManagerInterface $em */
        $em = $container->get('doctrine')->getManager();

        $em->flush();
    }

    public function testLogin(): void
    {
        /*
        |--------------------------------------------------------------------------
        | TEST : EMAIL INVALIDE
        |--------------------------------------------------------------------------
        */

        $this->client->request('GET', '/login');

        self::assertResponseIsSuccessful();

        $this->client->submitForm('Sign in', [
            '_username' => 'doesNotExist@example.com',
            '_password' => 'password',
        ]);

        self::assertResponseRedirects('/login');

        $this->client->followRedirect();

        /*
        | Vérifie que le message d'erreur est affiché.
        */

        self::assertSelectorTextContains(
            '.alert-danger',
            'Invalid credentials.'
        );

        /*
        |--------------------------------------------------------------------------
        | TEST : MOT DE PASSE INCORRECT
        |--------------------------------------------------------------------------
        */

        $this->client->request('GET', '/login');

        self::assertResponseIsSuccessful();

        $this->client->submitForm('Sign in', [
            '_username' => 'test@test.com',
            '_password' => 'bad-password',
        ]);

        self::assertResponseRedirects('/login');

        $this->client->followRedirect();

        /*
        | Vérifie que le message d'erreur est affiché.
        */

        self::assertSelectorTextContains(
            '.alert-danger',
            'Invalid credentials.'
        );

        /*
        |--------------------------------------------------------------------------
        | TEST : CONNEXION RÉUSSIE
        |--------------------------------------------------------------------------
        */

        $this->client->submitForm('Sign in', [
            '_username' => 'test@test.com',
            '_password' => 'password',
        ]);

        /*
        | Vérifie la redirection après connexion.
        */

        self::assertResponseRedirects('/');

        $this->client->followRedirect();

        /*
        | Vérifie qu'aucune erreur n'est affichée.
        */

        self::assertSelectorNotExists('.alert-danger');
    }
}