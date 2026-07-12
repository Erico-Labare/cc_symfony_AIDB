<?php

namespace App\Tests\Controller;

use App\Entity\Compte;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class SecurityControllerTest extends WebTestCase
{
    private $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    public function testLoginPageLoadsSuccessfully(): void
    {
        $this->client->request('GET', '/login');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Please sign in'); // Corrected assertion text
        $this->assertSelectorExists('form');
        $this->assertSelectorExists('input[name="_username"]');
        $this->assertSelectorExists('input[name="_password"]');
    }

    public function testLoginWithBadCredentials(): void
    {
        $crawler = $this->client->request('GET', '/login');
        $form = $crawler->selectButton('Sign in')->form([
            '_username' => 'wrong@example.com',
            '_password' => 'wrongpassword',
        ]);
        $this->client->submit($form);

        $this->assertResponseRedirects('/login');
        $this->client->followRedirect();

        $this->assertSelectorTextContains('.alert.alert-danger', 'Invalid credentials.'); // Adjust based on your error message
        $this->assertSame('wrong@example.com', $form['_username']->getValue());
    }

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
        $form = $crawler->selectButton('Sign in')->form([
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
