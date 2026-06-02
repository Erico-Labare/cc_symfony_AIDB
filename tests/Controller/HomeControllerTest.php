<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

// Test du contrôleur d'accueil
final class HomeControllerTest extends WebTestCase
{
    // Tester l'accès à la page d'accueil
    public function testIndex(): void
    {
        $client = static::createClient();

        $client->request('GET', '/home');

        self::assertResponseIsSuccessful();
    }

    // Tester que le rôle de l'utilisateur connecté s'affiche
    public function testHomeShowsUserIfLogged(): void
    {
        $client = static::createClient();

        $container = static::getContainer();

        // Récupérer l'utilisateur de test
        $user = $container->get('doctrine')
            ->getRepository(\App\Entity\Compte::class)
            ->findOneBy(['email' => 'test@test.com']);

        self::assertNotNull($user);

        // Connecter l'utilisateur
        $client->loginUser($user);

        $client->request('GET', '/home');

        self::assertResponseIsSuccessful();

        // Vérifier que le rôle est affiché
        self::assertSelectorTextContains('body', 'ROLE_USER');
    }
}