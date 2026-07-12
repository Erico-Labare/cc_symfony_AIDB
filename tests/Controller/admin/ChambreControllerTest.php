<?php

namespace App\Tests\Controller\admin;

use App\Entity\Chambre;
use App\Entity\Hotel;
use App\Entity\Compte;
use Doctrine\ORM\EntityManagerInterface;
use App\Tests\BaseWebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

/**
 * Test du contrôleur d'administration des chambres.
 *
 * Cette classe contient les tests fonctionnels pour la gestion des chambres
 * par un administrateur.
 */
final class ChambreControllerTest extends BaseWebTestCase
{
    private ?Compte $admin = null;
    private ?Compte $nonAdminUser = null;
    private ?Hotel $hotel = null;
    private ?KernelBrowser $client = null;

    /**
     * Configure l'environnement de test avant chaque test.
     *
     * Initialise le client de test et prépare les données utilisateur (admin et non-admin),
     * ainsi qu'un hôtel de test.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient(); // Crée le client en premier, ce qui démarre le kernel
        $this->setupData();
    }

    /**
     * Initialise les données de test, s'assurant qu'un utilisateur admin, un utilisateur non-admin,
     * et un hôtel de test existent.
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

        // Crée un hôtel de test unique pour chaque méthode de test
        $uniqueId = uniqid();
        $this->hotel = new Hotel();
        $this->hotel->setNom('Hotel Test Chambre ' . $uniqueId);
        $this->hotel->setAdresse('123 Rue Test ' . $uniqueId);
        $this->hotel->setCategorie('****');
        $entityManager->persist($this->hotel);
        $entityManager->flush();
        $entityManager->clear();
    }

    /**
     * Teste l'accès à la liste des chambres sans authentification.
     *
     * Doit rediriger vers la page de connexion.
     */
    public function testIndexWithoutAuthentication(): void
    {
        $this->client->request('GET', '/admin/chambre');
        self::assertResponseRedirects();
    }

    /**
     * Teste l'accès à la liste des chambres avec un utilisateur non-admin.
     *
     * Doit retourner un statut 403 (Accès interdit).
     */
    public function testIndexWithNonAdmin(): void
    {
        $this->client->loginUser($this->nonAdminUser);
        $this->client->request('GET', '/admin/chambre');
        self::assertResponseStatusCodeSame(403);
    }

    /**
     * Teste l'accès à la liste des chambres avec un admin.
     *
     * Doit retourner un statut 200 (Succès).
     */
    public function testIndexWithAdmin(): void
    {
        $this->client->loginUser($this->admin);
        $this->client->request('GET', '/admin/chambre');
        self::assertResponseStatusCodeSame(200);
    }

    /**
     * Teste l'affichage du formulaire de création d'une nouvelle chambre.
     *
     * Doit retourner un statut 200 (Succès) pour un admin.
     */
    public function testNewFormGet(): void
    {
        $this->client->loginUser($this->admin);
        $this->client->request('GET', '/admin/chambre/new');
        self::assertResponseStatusCodeSame(200);
    }

    /**
     * Teste la soumission du formulaire de création d'une nouvelle chambre.
     *
     * Vérifie la redirection après soumission et la persistance de la chambre en base de données.
     */
    public function testNewFormSubmit(): void
    {
        $this->client->loginUser($this->admin);

        // Accède au formulaire de création
        $crawler = $this->client->request('GET', '/admin/chambre/new');
        // Soumet le formulaire avec des données valides
        $form = $crawler->selectButton('Enregistrer')->form([
            'chambre[etage]' => '3',
            'chambre[type]' => 'double',
            'chambre[nombreLit]' => '2',
            'chambre[hotel]' => $this->hotel->getId(),
        ]);
        $this->client->submit($form);
        self::assertResponseRedirects('/admin/chambre');

        // Vérifie que la chambre a été créée en consultant la base de données
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $chambres = $entityManager->getRepository(Chambre::class)->findBy(['etage' => 3]);
        self::assertGreaterThanOrEqual(1, count($chambres));
    }

    /**
     * Teste l'affichage des détails d'une chambre existante.
     *
     * Doit retourner un statut 200 (Succès) pour un admin.
     */
    public function testShowExistingChambre(): void
    {
        // Créer une chambre de test
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $chambre = new Chambre();
        $chambre->setEtage(2);
        $chambre->setType('single');
        $chambre->setNombreLit(1);
        $chambre->setHotel($this->hotel);
        $entityManager->persist($chambre);
        $entityManager->flush();
        $entityManager->clear();

        $this->client->loginUser($this->admin);
        $this->client->request('GET', '/admin/chambre/' . $chambre->getId());
        self::assertResponseStatusCodeSame(200);
    }

    /**
     * Teste l'affichage des détails d'une chambre inexistante.
     *
     * Doit retourner un statut 404 (Non trouvé).
     */
    public function testShowNonExistentChambre(): void
    {
        $this->client->loginUser($this->admin);
        $this->client->request('GET', '/admin/chambre/99999'); // ID qui n'existe probablement pas
        self::assertResponseStatusCodeSame(404);
    }

    /**
     * Teste l'affichage du formulaire de modification d'une chambre existante.
     *
     * Doit retourner un statut 200 (Succès) pour un admin.
     */
    public function testEditFormGet(): void
    {
        // Créer une chambre de test
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $chambre = new Chambre();
        $chambre->setEtage(1);
        $chambre->setType('suite');
        $chambre->setNombreLit(4);
        $chambre->setHotel($this->hotel);
        $entityManager->persist($chambre);
        $entityManager->flush();
        $entityManager->clear();

        $this->client->loginUser($this->admin);
        $this->client->request('GET', '/admin/chambre/' . $chambre->getId() . '/edit');
        self::assertResponseStatusCodeSame(200);
    }

    /**
     * Teste la soumission du formulaire de modification d'une chambre.
     *
     * Vérifie la redirection après soumission et que les données de la chambre ont été mises à jour.
     */
    public function testEditFormSubmit(): void
    {
        // Créer une chambre de test
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $chambre = new Chambre();
        $chambre->setEtage(1);
        $chambre->setType('single');
        $chambre->setNombreLit(1);
        $chambre->setHotel($this->hotel);
        $entityManager->persist($chambre);
        $entityManager->flush();
        $id = $chambre->getId();
        $entityManager->clear();

        // Soumission du formulaire de modification
        $this->client->loginUser($this->admin);
        $crawler = $this->client->request('GET', '/admin/chambre/' . $id . '/edit');
        $form = $crawler->selectButton('Mettre à jour')->form([
            'chambre[etage]' => '5',
            'chambre[type]' => 'double',
            'chambre[nombreLit]' => '2',
            'chambre[hotel]' => $this->hotel->getId(),
        ]);
        $this->client->submit($form);
        self::assertResponseRedirects('/admin/chambre');

        // Vérifie que la chambre a été modifiée en la récupérant de la base de données
        $entityManager->clear();
        $updatedChambre = $entityManager->getRepository(Chambre::class)->find($id);
        self::assertSame(5, $updatedChambre->getEtage());
    }

    /**
     * Teste la suppression d'une chambre avec un jeton CSRF valide.
     *
     * Crée une chambre, la supprime via le formulaire et vérifie qu'elle n'existe plus en base de données.
     */
    public function testDeleteWithValidCsrfToken(): void
    {
        // Créer une chambre de test
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $chambre = new Chambre();
        $chambre->setEtage(1);
        $chambre->setType('single');
        $chambre->setNombreLit(1);
        $chambre->setHotel($this->hotel);
        $entityManager->persist($chambre);
        $entityManager->flush();
        $id = $chambre->getId();
        $entityManager->clear();

        // Supprime la chambre via le formulaire
        $this->client->loginUser($this->admin);
        $crawler = $this->client->request('GET', '/admin/chambre/' . $id); // Accède à la page de la chambre pour obtenir le formulaire de suppression
        $form = $crawler->selectButton('Supprimer')->form();

        $this->client->submit($form);
        self::assertResponseRedirects('/admin/chambre');

        // Vérifie que la chambre a été supprimée
        $entityManager->clear();
        $deletedChambre = $entityManager->getRepository(Chambre::class)->find($id);
        self::assertNull($deletedChambre);
    }
}
