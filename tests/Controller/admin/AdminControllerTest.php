<?php

namespace App\Tests\Controller\admin;

use App\Entity\Compte; // Import the Compte entity
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Teste le contrôleur d'administration.
 *
 * Cette classe contient les tests fonctionnels pour les actions du panneau
 * d'administration, vérifiant les droits d'accès pour les utilisateurs
 * anonymes, les utilisateurs avec le rôle 'ROLE_USER' et les administrateurs.
 */
class AdminControllerTest extends WebTestCase
{
    /**
     * Teste l'accès refusé pour un utilisateur anonyme.
     *
     * Vérifie qu'un utilisateur non connecté est redirigé vers la page de connexion
     * lorsqu'il tente d'accéder à une page d'administration.
     */
    public function testAccessDeniedForAnonymousUser(): void
    {
        $client = static::createClient();
        $client->request('GET', '/admin');

        $this->assertResponseRedirects('/login');
    }

    /**
     * Teste l'accès refusé pour un utilisateur avec le rôle 'ROLE_USER'.
     *
     * Crée un utilisateur avec le rôle 'ROLE_USER', le connecte, puis vérifie
     * qu'il reçoit une erreur 403 (Accès interdit) lorsqu'il tente d'accéder
     * à une page d'administration.
     */
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

    /**
     * Teste l'accès au tableau de bord d'administration pour un utilisateur administrateur.
     *
     * Crée un utilisateur avec le rôle 'ROLE_ADMIN', le connecte, puis vérifie
     * qu'il peut accéder au tableau de bord d'administration avec succès (statut 200)
     * et que le titre de la page est correct.
     */
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
