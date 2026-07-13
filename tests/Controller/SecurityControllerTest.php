<?php

namespace App\Tests\Controller;

use App\Entity\Compte;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Teste le contrôleur de sécurité.
 *
 * Cette classe contient les tests fonctionnels pour les actions liées à la sécurité,
 * notamment la connexion, la déconnexion et la gestion des identifiants.
 */
class SecurityControllerTest extends WebTestCase
{
    private $client;

    /**
     * Configure l'environnement de test avant chaque test.
     *
     * Initialise le client de test.
     */
    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    /**
     * Teste que la page de connexion se charge avec succès.
     *
     * Vérifie que la page de connexion est accessible, affiche le titre correct
     * et contient le formulaire de connexion avec les champs attendus.
     */
    public function testLoginPageLoadsSuccessfully(): void
    {
        $this->client->request('GET', '/login');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Veuillez vous connecter'); // Corrected assertion text
        $this->assertSelectorExists('form');
        $this->assertSelectorExists('input[name="_username"]');
        $this->assertSelectorExists('input[name="_password"]');
    }

    /**
     * Teste la connexion avec de mauvais identifiants.
     *
     * Vérifie qu'une tentative de connexion avec des identifiants incorrects
     * entraîne une redirection vers la page de connexion et l'affichage d'un message d'erreur.
     */
    public function testLoginWithBadCredentials(): void
    {
        $crawler = $this->client->request('GET', '/login');
        $form = $crawler->selectButton('Se connecter')->form([
            '_username' => 'wrong@example.com',
            '_password' => 'wrongpassword',
        ]);
        $this->client->submit($form);

        $this->assertResponseRedirects('/login');
        $this->client->followRedirect();

        $this->assertSelectorTextContains('.alert.alert-danger', 'Identifiants invalides.'); // Adjust based on your error message
        $this->assertSame('wrong@example.com', $form['_username']->getValue());
    }

    /**
     * Teste la connexion réussie d'un utilisateur.
     *
     * Crée un utilisateur de test, simule une connexion avec des identifiants valides,
     * et vérifie que l'utilisateur est redirigé vers la page d'accueil et que son
     * email ou un élément spécifique à l'utilisateur connecté est affiché.
     */
    public function testSuccessfulLogin(): void
    {
        // Create a test user in the database for this test
        $entityManager = $this->client->getContainer()->get(EntityManagerInterface::class);
        $passwordHasher = $this->client->getContainer()->get(UserPasswordHasherInterface::class);

        $user = new Compte();
        $user->setEmail('valid_login@example.com');
        $user->setPassword($passwordHasher->hashPassword($user, 'password'));
        $user->setRole('ROLE_USER');
        $user->setIsVerified(true);
        $entityManager->persist($user);
        $entityManager->flush();

        $crawler = $this->client->request('GET', '/login');
        $form = $crawler->selectButton('Se connecter')->form([
            '_username' => 'valid_login@example.com',
            '_password' => 'password',
        ]);
        $this->client->submit($form);

        // Assuming successful login redirects to the home page ('/')
        $this->assertResponseRedirects('/');
        $crawler = $this->client->followRedirect(); // Follow the first redirect (to '/')
        $crawler = $this->client->followRedirect(); // Follow the second redirect (to '/home')

        // Assert that the user is now authenticated (e.g., by checking for a logout link or user-specific content)
        // Make the selector more specific for the logout link
        $this->assertSelectorTextContains('a.nav-link.text-danger[href="/logout"]', 'Déconnexion'); // More specific selector
        $this->assertSelectorTextContains('body', 'valid_login@example.com'); // Assuming user email is displayed somewhere

        // Clean up the created user
        // Re-fetch the user to ensure it's managed by the current EntityManager
        $userToRemove = $entityManager->getRepository(Compte::class)->findOneBy(['email' => 'valid_login@example.com']);
        if ($userToRemove) {
            $entityManager->remove($userToRemove);
            $entityManager->flush();
        }
    }

    /**
     * Teste la déconnexion d'un utilisateur.
     *
     * Crée et connecte un utilisateur de test, puis simule une déconnexion
     * et vérifie que l'utilisateur est redirigé vers la page d'accueil
     * et n'est plus authentifié.
     */
    public function testLogout(): void
    {
        // Create a test user in the database for this test
        $entityManager = $this->client->getContainer()->get(EntityManagerInterface::class);
        $passwordHasher = $this->client->getContainer()->get(UserPasswordHasherInterface::class);

        $user = new Compte();
        $user->setEmail('test@example.com');
        $user->setPassword($passwordHasher->hashPassword($user, 'password'));
        $user->setRole('ROLE_USER');
        $user->setIsVerified(true);
        $entityManager->persist($user);
        $entityManager->flush();

        $this->client->loginUser($user);

        $this->client->request('GET', '/logout');

        // After logout, Symfony's security component typically redirects to the home page ('/')
        $this->assertResponseRedirects('/home');

        // Optionally, follow the redirect and assert that the user is no longer authenticated
        $this->client->followRedirect();
        $this->assertSelectorTextContains('h1', 'Bienvenue sur la plateforme de réservation'); // Check for home page content (or whatever is on your home page)
        // You could also try to access a protected page and assert redirection to login

        // Clean up the created user
        // Re-fetch the user to ensure it's managed by the current EntityManager
        $userToRemove = $entityManager->getRepository(Compte::class)->findOneBy(['email' => 'test@example.com']);
        if ($userToRemove) {
            $entityManager->remove($userToRemove);
            $entityManager->flush();
        }
    }
}
