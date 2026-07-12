<?php

namespace App\Controller;

use App\Entity\Compte;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Form\AccountPasswordType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Route('/client')]
final class ClientController extends AbstractController
{
    /**
     * Affiche le profil de l'utilisateur connecté.
     * GET /client/profile
     */
    #[Route('/profile', name: 'app_client_profile', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function profile(): Response
    {
        $compte = $this->getUser();
        if (!$compte instanceof Compte) {
            throw $this->createAccessDeniedException('Vous devez être connecté.');
        }

        return $this->render('client/profile.html.twig', [
            'compte' => $compte,
        ]);
    }
    /**
     * Permet à l'utilisateur connecté de modifier son mot de passe.
     */
    #[Route('/change-password', name: 'app_client_change_password', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function changePassword(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager,
    ): Response {
        $compte = $this->getUser();

        if (!$compte instanceof Compte) {
            throw $this->createAccessDeniedException('Vous devez être connecté.');
        }

        $form = $this->createForm(AccountPasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $currentPassword = $form->get('currentPassword')->getData();

            if (!$passwordHasher->isPasswordValid($compte, $currentPassword)) {

                $this->addFlash(
                    'danger',
                    'Votre ancien mot de passe est incorrect.'
                );

                return $this->redirectToRoute('app_client_change_password');
            }

            $newPassword = $form->get('plainPassword')->getData();

            $hashedPassword = $passwordHasher->hashPassword(
                $compte,
                $newPassword
            );

            $compte->setPassword($hashedPassword);

            $entityManager->flush();

            $this->addFlash(
                'success',
                'Votre mot de passe a été modifié avec succès.'
            );

            return $this->redirectToRoute('app_client_profile');
        }

        return $this->render('client/change-password.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
