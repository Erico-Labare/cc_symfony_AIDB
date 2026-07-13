<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Contrôleur gérant les pages d'accueil de l'application.
 *
 * Ce contrôleur fournit les actions pour la redirection de la racine
 * et l'affichage de la page d'accueil principale.
 */
final class HomeController extends AbstractController
{
    /**
     * Redirige le chemin racine ('/') vers la page d'accueil ('/home').
     *
     * @return Response Une réponse de redirection.
     */
    #[Route('/', name: 'app_root')]
    public function root(): Response
    {
        return $this->redirectToRoute('app_home');
    }

    /**
     * Affiche la page d'accueil de l'application.
     *
     * Cette action rend le template 'home/index.html.twig'.
     *
     * @return Response Une réponse HTTP contenant le contenu de la page d'accueil.
     */
    #[Route('/home', name: 'app_home')]
    public function index(): Response
    {
        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }
}
