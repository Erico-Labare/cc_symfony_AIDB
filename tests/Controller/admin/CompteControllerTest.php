<?php

namespace App\Tests\Controller\admin;

use App\Entity\Compte;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class CompteControllerTest extends WebTestCase
{
    private ?Compte $admin = null;

    private function setupData(): void
    {
        if ($this->admin === null) {
            $entityManager = self::getContainer()->get(EntityManagerInterface::class);
            $this->admin = $entityManager->getRepository(Compte::class)
                ->findOneBy(['email' => 'admin@test.com']);
        }
    }

    public function testIndexWithoutAuthentication(): void
    {
        $client = static::createClient();
        $client->request('GET', '/admin/compte');
        self::assertResponseRedirects();
    }

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

    public function testIndexWithAdmin(): void
    {
        $client = static::createClient();
        $this->setupData();
        $client->loginUser($this->admin);
        $client->request('GET', '/admin/compte');
        self::assertResponseStatusCodeSame(200);
    }

    public function testNewFormGet(): void
    {
        $client = static::createClient();
        $this->setupData();
        $client->loginUser($this->admin);
        $client->request('GET', '/admin/compte/new');
        self::assertResponseStatusCodeSame(200);
    }

    public function testNewFormSubmit(): void
    {
        $client = static::createClient();
        $this->setupData();
        $client->loginUser($this->admin);

        $crawler = $client->request('GET', '/admin/compte/new');
        $form = $crawler->selectButton('Save')->form();
        $form['compte[email]'] = 'testcreate' . time() . '@test.com';
        $form['compte[plainPassword]'] = 'SecurePassword123!';
        $form['compte[role]'] = 'ROLE_USER';

        $client->submit($form);
        self::assertResponseRedirects();

        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $comptes = $entityManager->getRepository(Compte::class)
            ->findAll();
        self::assertGreaterThanOrEqual(2, count($comptes));
    }

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

    public function testShowNonExistentCompte(): void
    {
        $client = static::createClient();
        $this->setupData();
        $client->loginUser($this->admin);
        $client->request('GET', '/admin/compte/99999');
        self::assertResponseStatusCodeSame(404);
    }

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

    public function testDeleteWithValidCsrfToken(): void
    {
        $client = static::createClient();
        $this->setupData();

        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $passwordHasher = self::getContainer()->get(UserPasswordHasherInterface::class);

        $user = new Compte();
        $user->setEmail('todelete' . time() . '@test.com');
        $user->setRole('ROLE_USER');
        $user->setPassword($passwordHasher->hashPassword($user, 'password'));
        $entityManager->persist($user);
        $entityManager->flush();
        $id = $user->getId();

        $client->loginUser($this->admin);
        $client->request('GET', '/admin/compte/' . $id);
        $form = $client->getCrawler()->selectButton('Delete')->form();

        $client->submit($form);
        self::assertResponseRedirects();

        $entityManager->clear();
        $deletedUser = $entityManager->getRepository(Compte::class)->find($id);
        self::assertNull($deletedUser);
    }
}
