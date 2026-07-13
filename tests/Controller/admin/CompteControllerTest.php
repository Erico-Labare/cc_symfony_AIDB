<?php

namespace App\Tests\Controller\admin;

use App\Entity\Compte;
use Doctrine\ORM\EntityManagerInterface;
use App\Tests\BaseWebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

/**
 * Test du contrôleur d'administration des comptes.
 *
 * Cette classe contient les tests fonctionnels pour la gestion des comptes utilisateur
 * par un administrateur. Elle couvre les scénarios d'accès, de création,
 * de modification, de consultation et de suppression de comptes, en vérifiant
 * les autorisations nécessaires pour chaque action.
 */
final class CompteControllerTest extends BaseWebTestCase
{
    private ?Compte $admin = null;
    private ?Compte $nonAdminUser = null;
    private ?KernelBrowser $client = null;

    /**
     * Configure l'environnement de test avant chaque test.
     *
     * Initialise le client de test et prépare les données utilisateur (admin et non-admin).
     * Cette méthode est appelée avant chaque exécution de test pour s'assurer
     * d'un environnement propre et cohérent.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient(); // Crée le client en premier, ce qui démarre le kernel
        $this->setupData(); // Appelle setupData après la création du client
    }

    /**
     * Initialise les données de test, s'assurant qu'un utilisateur admin et un utilisateur non-admin existent.
     *
     * Crée un utilisateur avec le rôle 'ROLE_ADMIN' et un autre avec 'ROLE_USER'
     * s'ils n'existent pas déjà, pour être utilisés dans les scénarios de test
     * d'autorisation.
     */
    private function setupData(): void
    {
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $passwordHasher = self::getContainer()->get(UserPasswordHasherInterface::class);

        // Assure qu'un utilisateur admin existe ou le crée
        $this->admin = $entityManager->getRepository(Compte::class)
            ->findOneBy(['email' => 'admin@test.com']);

        if ($this->admin === null) {
            $this->admin = new Compte();
            $this->admin->setEmail('admin@test.com');
            $this->admin->setPassword($passwordHasher->hashPassword($this->admin, 'password'));
            $this->admin->setRole('ROLE_ADMIN');
            $this->admin->setIsVerified(true);
            $entityManager->persist($this->admin);
        }

        // Assure qu'un utilisateur non-admin existe ou le crée
        $this->nonAdminUser = $entityManager->getRepository(Compte::class)
            ->findOneBy(['email' => 'test@test.com']);

        if ($this->nonAdminUser === null) {
            $this->nonAdminUser = new Compte();
            $this->nonAdminUser->setEmail('test@test.com');
            $this->nonAdminUser->setPassword($passwordHasher->hashPassword($this->nonAdminUser, 'password'));
            $this->nonAdminUser->setRole('ROLE_USER');
            $this->nonAdminUser->setIsVerified(true);
            $entityManager->persist($this->nonAdminUser);
        }

        $entityManager->flush();
        $entityManager->clear(); // Efface l'EntityManager pour détacher les entités
    }

    /**
     * Teste l'accès à la liste des comptes sans authentification.
     *
     * Vérifie qu'un utilisateur non connecté est redirigé vers la page de connexion
     * lorsqu'il tente d'accéder à la liste des comptes.
     */
    public function testIndexWithoutAuthentication(): void
    {
        $this->client->request('GET', '/admin/compte');
        self::assertResponseRedirects();
    }

    /**
     * Teste l'accès à la liste des comptes avec un utilisateur non-admin.
     *
     * Vérifie qu'un utilisateur avec le rôle 'ROLE_USER' reçoit une erreur 403
     * (Accès interdit) lorsqu'il tente d'accéder à la liste des comptes.
     */
    public function testIndexWithNonAdmin(): void
    {
        $this->client->loginUser($this->nonAdminUser);
        $this->client->request('GET', '/admin/compte');
        self::assertResponseStatusCodeSame(403);
    }

    /**
     * Teste l'accès à la liste des comptes avec un admin.
     *
     * Vérifie qu'un utilisateur avec le rôle 'ROLE_ADMIN' peut accéder
     * à la liste des comptes avec succès (statut 200).
     */
    public function testIndexWithAdmin(): void
    {
        $this->client->loginUser($this->admin);
        $this->client->request('GET', '/admin/compte');
        self::assertResponseStatusCodeSame(200);
    }

    /**
     * Teste l'affichage du formulaire de création d'un nouveau compte.
     *
     * Vérifie qu'un administrateur peut accéder au formulaire de création
     * d'un compte avec succès (statut 200).
     */
    public function testNewFormGet(): void
    {
        $this->client->loginUser($this->admin);
        $this->client->request('GET', '/admin/compte/new');
        self::assertResponseStatusCodeSame(200);
    }

    /**
     * Teste la soumission du formulaire de création d'un nouveau compte.
     *
     * Simule la soumission d'un formulaire de création de compte avec des données valides.
     * Vérifie la redirection après soumission et la persistance du compte en base de données.
     */
    public function testNewFormSubmit(): void
    {
        $this->client->loginUser($this->admin);

        // Accède au formulaire de création
        $crawler = $this->client->request('GET', '/admin/compte/new');
        // Soumet le formulaire avec des données valides
        $form = $crawler->selectButton('Enregistrer')->form();
        $form['compte[email]'] = 'testcreate' . uniqid() . '@test.com';
        $form['compte[plainPassword]'] = 'SecurePassword123!';
        $form['compte[role]'] = 'ROLE_USER';

        $this->client->submit($form);
        self::assertResponseRedirects('/admin/compte');

        // Vérifie que le compte a été créé en consultant la base de données
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $comptes = $entityManager->getRepository(Compte::class)
            ->findAll();
        // On s'attend à au moins 2 comptes (admin et le nouveau créé)
        self::assertGreaterThanOrEqual(2, count($comptes));
    }

    /**
     * Teste l'affichage des détails d'un compte existant.
     *
     * Vérifie qu'un administrateur peut accéder à la page de détails
     * d'un compte existant avec succès (statut 200).
     */
    public function testShowExistingCompte(): void
    {
        $this->client->loginUser($this->admin);
        $this->client->request('GET', '/admin/compte/' . $this->nonAdminUser->getId());
        self::assertResponseStatusCodeSame(200);
    }

    /**
     * Teste l'affichage des détails d'un compte inexistant.
     *
     * Vérifie qu'une tentative d'accès aux détails d'un compte avec un ID
     * qui n'existe pas renvoie une erreur 404 (Non trouvé).
     */
    public function testShowNonExistentCompte(): void
    {
        $this->client->loginUser($this->admin);
        $this->client->request('GET', '/admin/compte/99999'); // ID qui n'existe probablement pas
        self::assertResponseStatusCodeSame(404);
    }

    /**
     * Teste l'affichage du formulaire de modification d'un compte existant.
     *
     * Vérifie qu'un administrateur peut accéder au formulaire de modification
     * d'un compte existant avec succès (statut 200).
     */
    public function testEditFormGet(): void
    {
        $this->client->loginUser($this->admin);
        $this->client->request('GET', '/admin/compte/' . $this->nonAdminUser->getId() . '/edit');
        self::assertResponseStatusCodeSame(200);
    }

    /**
     * Teste la soumission du formulaire de modification d'un compte.
     *
     * Crée un compte de test, simule la soumission de son formulaire de modification
     * avec de nouvelles données. Vérifie la redirection après soumission et que
     * les données du compte ont été mises à jour en base de données.
     */
    public function testEditFormSubmit(): void
    {
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $passwordHasher = self::getContainer()->get(UserPasswordHasherInterface::class);

        // Crée un nouvel utilisateur à modifier
        $userToEdit = new Compte();
        $uniqueId = uniqid();
        $userToEdit->setEmail('edit_user' . $uniqueId . '@test.com');
        $userToEdit->setRole('ROLE_USER');
        $userToEdit->setPassword($passwordHasher->hashPassword($userToEdit, 'oldpassword'));
        $userToEdit->setIsVerified(true);
        $entityManager->persist($userToEdit);
        $entityManager->flush();
        $id = $userToEdit->getId();
        $entityManager->clear();

        // Soumission du formulaire de modification
        $this->client->loginUser($this->admin);
        $crawler = $this->client->request('GET', '/admin/compte/' . $id . '/edit');
        $form = $crawler->selectButton('Mettre à jour')->form();
        $form['compte[email]'] = 'updated_user' . uniqid() . '@test.com';
        $form['compte[plainPassword]'] = 'NewSecurePassword123!';
        $form['compte[role]'] = 'ROLE_ADMIN';

        $this->client->submit($form);
        self::assertResponseRedirects('/admin/compte');

        // Vérifie que le compte a été modifié en le récupérant de la base de données
        $entityManager->clear(); // Efface l'EntityManager pour s'assurer de récupérer les dernières données
        $updatedUser = $entityManager->getRepository(Compte::class)->find($id);
        self::assertNotNull($updatedUser);
        self::assertStringContainsString('updated_user', $updatedUser->getEmail());
        self::assertTrue($passwordHasher->isPasswordValid($updatedUser, 'NewSecurePassword123!'));
        self::assertSame('ROLE_ADMIN', $updatedUser->getRole());
    }

    /**
     * Teste la suppression d'un compte avec un jeton CSRF valide.
     *
     * Crée un compte de test, le supprime via le formulaire de suppression
     * (qui inclut un jeton CSRF). Vérifie la redirection après suppression et que
     * le compte n'existe plus en base de données.
     */
    public function testDeleteWithValidCsrfToken(): void
    {
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $passwordHasher = self::getContainer()->get(UserPasswordHasherInterface::class);

        // Crée un utilisateur à supprimer
        $userToDelete = new Compte();
        $uniqueId = uniqid();
        $userToDelete->setEmail('todelete' . $uniqueId . '@test.com');
        $userToDelete->setRole('ROLE_USER');
        $userToDelete->setPassword($passwordHasher->hashPassword($userToDelete, 'password'));
        $userToDelete->setIsVerified(true);
        $entityManager->persist($userToDelete);
        $entityManager->flush();
        $id = $userToDelete->getId();
        $entityManager->clear();

        // Supprime le compte via le formulaire
        $this->client->loginUser($this->admin);
        $crawler = $this->client->request('GET', '/admin/compte/' . $id); // Accède à la page du compte pour obtenir le formulaire de suppression
        $form = $crawler->selectButton('Supprimer')->form();

        $this->client->submit($form);
        self::assertResponseRedirects('/admin/compte');

        // Vérifie que le compte a été supprimé
        $entityManager->clear();
        $deletedUser = $entityManager->getRepository(Compte::class)->find($id);
        self::assertNull($deletedUser);
    }
}
