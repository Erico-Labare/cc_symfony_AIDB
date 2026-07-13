<?php

namespace App\Controller\admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Contrôleur du panneau d'administration.
 *
 * Ce contrôleur gère l'accès au tableau de bord d'administration.
 * L'accès est restreint aux utilisateurs ayant le rôle 'ROLE_ADMIN'.
 */
#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
final class AdminController extends AbstractController
{
    /**
     * Affiche le tableau de bord d'administration.
     *
     * @return Response Une réponse HTTP affichant le tableau de bord.
     */
    #[Route('', name: 'app_admin')]
    public function index(): Response
    {
        return $this->render('admin/index.html.twig');
    }
}
