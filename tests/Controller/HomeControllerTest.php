<?php

namespace App\Tests\Controller;

use App\Entity\Compte;
use Doctrine\ORM\EntityManagerInterface;
use App\Tests\BaseWebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

/**
 * Test du contrôleur d'accueil.
 *
 * Cette classe contient les tests fonctionnels pour la page d'accueil,
 * y compris l'accès à la page et l'affichage des informations utilisateur
 * après connexion.
 */
final class HomeControllerTest extends BaseWebTestCase
{
    private ?Compte $nonAdminUser = null;
    private ?KernelBrowser $client = null;

    /**
     * Initialise le client de test et les données nécessaires avant chaque test.
     *
     * Cette méthode est appelée avant chaque exécution de test pour s'assurer
     * d'un environnement propre et cohérent.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient();
        $this->setupData();
    }

    /**
     * Configure les données de test, s'assurant qu'un utilisateur non-admin existe.
     *
     * Crée un utilisateur de test avec le rôle 'ROLE_USER' s'il n'existe pas déjà,
     * pour être utilisé dans les scénarios de test de connexion.
     */
    private function setupData(): void
    {
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $passwordHasher = self::getContainer()->get(UserPasswordHasherInterface::class);

        // Ensure non-admin user exists or create it
        $this->nonAdminUser = $entityManager->getRepository(Compte::class)
            ->findOneBy(['email' => 'test@test.com']);

        if ($this->nonAdminUser === null) {
            $this->nonAdminUser = new Compte();
            $this->nonAdminUser->setEmail('test@test.com');
            $this->nonAdminUser->setPassword($passwordHasher->hashPassword($this->nonAdminUser, 'password'));
            $this->nonAdminUser->setRole('ROLE_USER');
            $this->nonAdminUser->setIsVerified(true);
            $entityManager->persist($this->nonAdminUser);
            $entityManager->flush();
            $entityManager->clear();
        }
    }

    /**
     * Teste l'accès à la page d'accueil sans être connecté.
     *
     * Vérifie que la page d'accueil est accessible et renvoie une réponse réussie (statut 200).
     */
    public function testIndex(): void
    {
        $this->client->request('GET', '/home');

        self::assertResponseIsSuccessful();
    }

    /**
     * Teste que le rôle de l'utilisateur connecté s'affiche sur la page d'accueil.
     *
     * Connecte un utilisateur non-admin et vérifie que son rôle ('ROLE_USER')
     * est présent dans le contenu de la page d'accueil, confirmant ainsi
     * l'affichage des informations utilisateur après connexion.
     */
    public function testHomeShowsUserIfLogged(): void
    {
        // Connecter l'utilisateur
        $this->client->loginUser($this->nonAdminUser);

        $this->client->request('GET', '/home');

        self::assertResponseIsSuccessful();

        // Vérifier que le rôle est affiché
        self::assertSelectorTextContains('body', 'ROLE_USER');
    }
}
