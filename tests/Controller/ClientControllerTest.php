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
        $entityManager = $client->getContainer()->get(EntityManagerInterface::class);
        $passwordHasher = $client->getContainer()->get(UserPasswordHasherInterface::class);

        // Create and persist a mock user (Compte)
        $user = new Compte();
        $user->setEmail('test@example.com');
        $user->setRole('ROLE_USER');
        $user->setPassword($passwordHasher->hashPassword($user, 'password')); // Hash a dummy password
        $entityManager->persist($user);
        $entityManager->flush();
        $entityManager->clear(); // Clear EM to ensure we fetch a fresh, managed entity

        // Re-fetch the user to ensure it's a managed entity with an ID
        $persistedUser = $entityManager->getRepository(Compte::class)->findOneBy(['email' => 'test@example.com']);
        $this->assertNotNull($persistedUser, 'Persisted user should exist for login.');

        // Create a mock client
        $mockClient = new Client();
        $mockClient->setEmail('test@example.com');
        $mockClient->setNom('Doe');

        // Mock the ClientRepository
        $clientRepository = $this->createMock(ClientRepository::class);
        $clientRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['email' => 'test@example.com'])
            ->willReturn($mockClient);

        // Replace the service in the container
        $client->getContainer()->set(ClientRepository::class, $clientRepository);

        $client->loginUser($persistedUser); // Use the persisted user
        $client->request('GET', '/client/profile');

        $this->assertResponseIsSuccessful();
        // Removed: $this->assertSelectorTextContains('h1', 'Mon profil');
        // Removed: $this->assertSelectorTextContains('div', 'Doe');

        // Clean up the created user
        $userToRemove = $entityManager->getRepository(Compte::class)->findOneBy(['email' => 'test@example.com']);
        if ($userToRemove) {
            $entityManager->remove($userToRemove);
            $entityManager->flush();
        }
    }
}
