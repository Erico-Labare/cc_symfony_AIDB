<?php

namespace App\Controller;

use App\Entity\Compte;
use App\Repository\ClientRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/client')]
final class ClientController extends AbstractController
{
    /**
     * Affiche le profil de l'utilisateur connecté.
     * GET /client/profile
     */
    #[Route('/profile', name: 'app_client_profile', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function profile(ClientRepository $clientRepository): Response
    {
        $compte = $this->getUser();
        if (!$compte instanceof Compte) {
            throw $this->createAccessDeniedException('Vous devez être connecté.');
        }

        $client = $clientRepository->findOneBy(['email' => $compte->getEmail()]);

        return $this->render('client/profile.html.twig', [
            'compte' => $compte,
            'client' => $client,
        ]);
    }
}
