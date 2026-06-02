<?php

namespace App\Tests\Controller\admin;

use App\Entity\Compte;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

// Test du contrôleur de gestion des comptes
final class CompteControllerTest extends WebTestCase
{
    private ?Compte $admin = null;

    // Initialiser les données de test
    private function setupData(): void
    {
        if ($this->admin === null) {
            $entityManager = self::getContainer()->get(EntityManagerInterface::class);
            $this->admin = $entityManager->getRepository(Compte::class)
                ->findOneBy(['email' => 'admin@test.com']);
        }
    }

    // Tester l'accès à l'index sans authentification
    public function testIndexWithoutAuthentication(): void
    {
        $client = static::createClient();
        $client->request('GET', '/admin/compte');
        self::assertResponseRedirects();
    }

    // Tester l'accès à l'index avec un utilisateur non-admin
    public function testIndexWithNonAdmin(): void
    {
        $client = static::createClient();
        $this->setupData();
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $user = $entityManager->getRepository(Compte::class)
            ->findOneBy(['email' => 'test@test.com']);
        $client->loginUser($user);
        $client->request('GET', '/admin/compte');
        self::assertResponseStatusCodeSame(403);
    }

    // Tester l'accès à l'index avec un admin
    public function testIndexWithAdmin(): void
    {
        $client = static::createClient();
        $this->setupData();
        $client->loginUser($this->admin);
        $client->request('GET', '/admin/compte');
        self::assertResponseStatusCodeSame(200);
    }

    // Tester l'affichage du formulaire de création
    public function testNewFormGet(): void
    {
        $client = static::createClient();
        $this->setupData();
        $client->loginUser($this->admin);
        $client->request('GET', '/admin/compte/new');
        self::assertResponseStatusCodeSame(200);
    }

    // Tester la soumission du formulaire de création
    public function testNewFormSubmit(): void
    {
        $client = static::createClient();
        $this->setupData();
        $client->loginUser($this->admin);


        // Soumission du formulaire
        $crawler = $client->request('GET', '/admin/compte/new');
        $form = $crawler->selectButton('Save')->form();
        $form['compte[email]'] = 'testcreate' . time() . '@test.com';
        $form['compte[plainPassword]'] = 'SecurePassword123!';
        $form['compte[role]'] = 'ROLE_USER';

        $client->submit($form);
        self::assertResponseRedirects();


        // Vérifier que le compte a été créé
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $comptes = $entityManager->getRepository(Compte::class)
            ->findAll();
        self::assertGreaterThanOrEqual(2, count($comptes));
    }

    // Tester l'affichage d'un compte existant
    public function testShowExistingCompte(): void
    {
        $client = static::createClient();
        $this->setupData();
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $user = $entityManager->getRepository(Compte::class)
            ->findOneBy(['email' => 'test@test.com']);

        $client->loginUser($this->admin);
        $client->request('GET', '/admin/compte/' . $user->getId());
        self::assertResponseStatusCodeSame(200);
    }

    // Tester l'affichage d'un compte inexistant
    public function testShowNonExistentCompte(): void
    {
        $client = static::createClient();
        $this->setupData();
        $client->loginUser($this->admin);
        $client->request('GET', '/admin/compte/99999');
        self::assertResponseStatusCodeSame(404);
    }

    // Tester l'affichage du formulaire de modification
    public function testEditFormGet(): void
    {
        $client = static::createClient();
        $this->setupData();
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $user = $entityManager->getRepository(Compte::class)
            ->findOneBy(['email' => 'test@test.com']);

        $client->loginUser($this->admin);
        $client->request('GET', '/admin/compte/' . $user->getId() . '/edit');
        self::assertResponseStatusCodeSame(200);
    }

    // Tester la suppression d'un compte avec un jeton CSRF valide
    public function testDeleteWithValidCsrfToken(): void
    {
        $client = static::createClient();
        $this->setupData();

        // Créer un compte de test
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $passwordHasher = self::getContainer()->get(UserPasswordHasherInterface::class);

        $user = new Compte();
        $user->setEmail('todelete' . time() . '@test.com');
        $user->setRole('ROLE_USER');
        $user->setPassword($passwordHasher->hashPassword($user, 'password'));
        $entityManager->persist($user);
        $entityManager->flush();
        $id = $user->getId();


        // Supprimer le compte
        $client->loginUser($this->admin);
        $client->request('GET', '/admin/compte/' . $id);
        $form = $client->getCrawler()->selectButton('Delete')->form();

        $client->submit($form);
        self::assertResponseRedirects();


        // Vérifier que le compte a été supprimé
        $entityManager->clear();
        $deletedUser = $entityManager->getRepository(Compte::class)->find($id);
        self::assertNull($deletedUser);
    }
}
