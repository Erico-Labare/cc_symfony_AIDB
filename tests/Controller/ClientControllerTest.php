<?php

namespace App\Tests\Controller;

use App\Entity\Client;
use App\Entity\Compte;
use App\Repository\ClientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ClientControllerTest extends WebTestCase
{
    public function testProfileAccessDeniedForAnonymousUser(): void
    {
        $client = static::createClient();
        $client->request('GET', '/client/profile');

        $this->assertResponseRedirects('/login');
    }

    public function testProfileAccessForAuthenticatedUser(): void
    {
        $client = static::createClient();
        $container = self::getContainer();

        $entityManager = $container->get(EntityManagerInterface::class);
        $passwordHasher = $container->get(UserPasswordHasherInterface::class);

        // Create and persist a real user (Compte)
        $user = new Compte();
        $user->setEmail('test@example.com');
        $user->setRole('ROLE_USER');
        $user->setPassword($passwordHasher->hashPassword($user, 'password'));
        $entityManager->persist($user);
        $entityManager->flush();
        $entityManager->clear(); // Clear EM to ensure we fetch a fresh, managed entity

        // Re-fetch the user to ensure it's a managed entity with an ID
        $persistedUser = $entityManager->getRepository(Compte::class)->findOneBy(['email' => 'test@example.com']);
        $this->assertNotNull($persistedUser, 'Persisted user should exist for login.');

        // Create and persist a real Client entity associated with the user's email
        $realClient = new Client();
        $realClient->setEmail('test@example.com');
        $realClient->setNom('Doe');
        $realClient->setTelephone('0123456789');
        $realClient->setAdresse('123 Main St');
        $entityManager->persist($realClient);
        $entityManager->flush();
        $entityManager->clear(); // Clear EM to ensure fresh data is fetched

        // Log in the user
        $client->loginUser($persistedUser);
        $client->request('GET', '/client/profile');

        $this->assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Mon compte'); // Updated assertion
        self::assertSelectorTextContains('p', 'Doe'); // Updated assertion to check for client name in a <p> tag

        // Clean up the created entities
        $userToRemove = $entityManager->getRepository(Compte::class)->findOneBy(['email' => 'test@example.com']);
        if ($userToRemove) {
            $entityManager->remove($userToRemove);
        }
        $clientToRemove = $entityManager->getRepository(Client::class)->findOneBy(['email' => 'test@example.com']);
        if ($clientToRemove) {
            $entityManager->remove($clientToRemove);
        }
        $entityManager->flush();
    }
}
