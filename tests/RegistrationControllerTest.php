<?php

namespace App\Tests;

use App\Repository\CompteRepository;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RegistrationControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private CompteRepository $userRepository;

    protected function setUp(): void
    {
        $this->client = static::createClient();

        $container = static::getContainer();

        /** @var EntityManager $em */
        $em = $container->get('doctrine')->getManager();

        $this->userRepository = $container->get(CompteRepository::class);

        /*
        |--------------------------------------------------------------------------
        | Nettoyage de la base de test
        |--------------------------------------------------------------------------
        */

        foreach ($this->userRepository->findAll() as $user) {
            $em->remove($user);
        }

        $em->flush();
    }

    public function testRegister(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Accès à la page d'inscription
        |--------------------------------------------------------------------------
        */

        $this->client->request('GET', '/register');

        self::assertResponseIsSuccessful();

        /*
        |--------------------------------------------------------------------------
        | Soumission du formulaire
        |--------------------------------------------------------------------------
        */

        $this->client->submitForm('Register', [
            'registration_form[email]' => 'me@example.com',
            'registration_form[plainPassword]' => 'password',
            'registration_form[agreeTerms]' => 1,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Vérifie qu'un utilisateur a été créé
        |--------------------------------------------------------------------------
        */

        self::assertCount(1, $this->userRepository->findAll());

        /*
        |--------------------------------------------------------------------------
        | Vérifie l'email enregistré
        |--------------------------------------------------------------------------
        */

        $user = $this->userRepository->findOneBy([
            'email' => 'me@example.com'
        ]);

        self::assertNotNull($user);

        /*
        |--------------------------------------------------------------------------
        | Vérifie la redirection après inscription
        |--------------------------------------------------------------------------
        */

        self::assertResponseRedirects();

        $this->client->followRedirect();
        $this->client->followRedirect();

        self::assertResponseIsSuccessful();
    }
}