<?php

namespace App\Tests\Controller;

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

        $user = $container->get('doctrine')
            ->getRepository(\App\Entity\Compte::class)
            ->findOneBy(['email' => 'test@test.com']);

        self::assertNotNull($user);

        $client->loginUser($user);

        $client->request('GET', '/home');

        self::assertResponseIsSuccessful();

        self::assertSelectorTextContains('body', 'ROLE_USER');
    }
}