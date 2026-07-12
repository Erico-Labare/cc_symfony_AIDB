<?php

namespace App\Tests\Controller\admin;

use App\Entity\Compte; // Import the Compte entity
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AdminControllerTest extends WebTestCase
{
    public function testAccessDeniedForAnonymousUser(): void
    {
        $client = static::createClient();
        $client->request('GET', '/admin');

        $this->assertResponseRedirects('/login');
    }

    public function testAccessDeniedForUserWithUserRole(): void
    {
        $client = static::createClient();
        $entityManager = $client->getContainer()->get(EntityManagerInterface::class);
        $passwordHasher = $client->getContainer()->get(UserPasswordHasherInterface::class);

        // Simulate a user with ROLE_USER
        $user = new Compte(); // Use the correct User entity
        $user->setEmail('user@example.com'); // Set a dummy email
        $user->setPassword($passwordHasher->hashPassword($user, 'password'));
        $user->setRole('ROLE_USER');
        $user->setIsVerified(true);
        $entityManager->persist($user);
        $entityManager->flush();

        $client->loginUser($user);

        $client->request('GET', '/admin');

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);

        // Clean up the created user
        $entityManager->remove($user);
        $entityManager->flush();
    }

    public function testAdminDashboardAccessForAdminUser(): void
    {
        $client = static::createClient();
        $entityManager = $client->getContainer()->get(EntityManagerInterface::class);
        $passwordHasher = $client->getContainer()->get(UserPasswordHasherInterface::class);

        // Simulate an admin user
        $adminUser = new Compte(); // Use the correct User entity
        $adminUser->setEmail('admin@example.com'); // Set a dummy email
        $adminUser->setPassword($passwordHasher->hashPassword($adminUser, 'password'));
        $adminUser->setRole('ROLE_ADMIN');
        $adminUser->setIsVerified(true);
        $entityManager->persist($adminUser);
        $entityManager->flush();

        $client->loginUser($adminUser);

        $client->request('GET', '/admin');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Administration'); // Corrected assertion text

        // Clean up the created user
        $entityManager->remove($adminUser);
        $entityManager->flush();
    }
}
