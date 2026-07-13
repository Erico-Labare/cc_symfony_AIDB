<?php

namespace App\Controller;

use App\Entity\Compte;
use App\Repository\ClientRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Form\AccountPasswordType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException; // Keep this for other potential uses if needed, but won't be thrown here.

/**
 * Contrôleur gérant les actions spécifiques aux clients.
 *
 * Ce contrôleur permet aux utilisateurs avec le rôle 'ROLE_USER' d'accéder
 * à leur profil et de modifier leur mot de passe.
 */
#[Route('/client')]
final class ClientController extends AbstractController
{
    /**
     * Affiche le profil de l'utilisateur connecté.
     *
     * Récupère les informations du compte utilisateur et du client associé
     * pour les afficher sur la page de profil.
     *
     * @param ClientRepository $clientRepository Le dépôt des clients pour récupérer les données du client.
     * @return Response Une réponse HTTP affichant le profil client.
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException Si l'utilisateur n'est pas connecté.
     */
    #[Route('/profile', name: 'app_client_profile', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function profile(ClientRepository $clientRepository): Response
    {
        $compte = $this->getUser();
        if (!$compte instanceof Compte) {
            throw $this->createAccessDeniedException('Vous devez être connecté.');
        }

        // Find the Client associated with the logged-in Compte via email
        // If no client profile is found for the logged-in user, $client will be null.
        // We no longer throw an exception here, allowing admins (who might not have a Client entity)
        // to still view their Compte profile.
        $client = $clientRepository->findOneBy(['email' => $compte->getEmail()]);

        return $this->render('client/profile.html.twig', [
            'compte' => $compte,
            'client' => $client, // Pass the client object (can be null) to the template
        ]);
    }

    /**
     * Permet à l'utilisateur connecté de modifier son mot de passe.
     *
     * Gère l'affichage et la soumission du formulaire de changement de mot de passe,
     * y compris la vérification de l'ancien mot de passe et le hachage du nouveau.
     *
     * @param Request $request La requête HTTP.
     * @param UserPasswordHasherInterface $passwordHasher Le service de hachage de mot de passe.
     * @param EntityManagerInterface $entityManager Le gestionnaire d'entités Doctrine.
     * @return Response Une réponse HTTP affichant le formulaire ou redirigeant après succès/échec.
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException Si l'utilisateur n'est pas connecté.
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
