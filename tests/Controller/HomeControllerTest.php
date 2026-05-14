<?php

namespace App\Tests\Controller;

use App\Entity\Compte;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class HomeControllerTest extends WebTestCase
{
    public function testIndex(): void
    {
        $client = static::createClient();

        $client->request('GET', '/home');

        self::assertResponseIsSuccessful();
    }

    public function testHomeShowsUserIfLogged(): void
    {
        $client = static::createClient();

        $container = static::getContainer();

        $em = $container->get(EntityManagerInterface::class);

        /*
        |--------------------------------------------------------------------------
        | Création utilisateur de test
        |--------------------------------------------------------------------------
        */

        $user = new Compte();

        $user->setEmail('test@test.com');
        $user->setPassword('password');
        $user->setRole('ROLE_USER');

        $em->persist($user);
        $em->flush();

        /*
        |--------------------------------------------------------------------------
        | Connexion utilisateur
        |--------------------------------------------------------------------------
        */

        $client->loginUser($user);

        $client->request('GET', '/home');

        /*
        |--------------------------------------------------------------------------
        | Vérifications
        |--------------------------------------------------------------------------
        */

        self::assertResponseIsSuccessful();

        self::assertSelectorTextContains('body', 'ROLE_USER');
    }
}