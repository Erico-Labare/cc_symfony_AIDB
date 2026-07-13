<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * Contrôleur gérant les fonctionnalités de sécurité de l'application.
 *
 * Ce contrôleur est responsable de l'affichage du formulaire de connexion
 * et de la gestion de la déconnexion des utilisateurs.
 */
class SecurityController extends AbstractController
{
    /**
     * Affiche le formulaire de connexion.
     *
     * Récupère les éventuelles erreurs d'authentification et le dernier nom
     * d'utilisateur saisi pour pré-remplir le formulaire.
     *
     * @param AuthenticationUtils $authenticationUtils Utilitaire d'authentification de Symfony.
     * @return Response Une réponse HTTP affichant le formulaire de connexion.
     */
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // obtenir l'erreur de connexion s'il y en a une
        $error = $authenticationUtils->getLastAuthenticationError();

        // dernier nom d'utilisateur entré par l'utilisateur
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    /**
     * Gère la déconnexion de l'utilisateur.
     *
     * Cette méthode est vide car la déconnexion est interceptée par le pare-feu
     * de sécurité de Symfony.
     *
     * @throws \LogicException Cette exception est levée si la méthode est appelée directement,
     *                         ce qui ne devrait pas arriver en production.
     */
    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
