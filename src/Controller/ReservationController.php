<?php

namespace App\Controller;

use App\Entity\Chambre;
use App\Entity\Client;
use App\Entity\Compte;
use App\Entity\Reservation;
use App\Form\ReservationCommentType;
use App\Form\ReservationFormType;
use App\Repository\ClientRepository;
use App\Repository\ChambreRepository;
use App\Repository\HotelRepository;
use App\Services\DisponibiliteService;
use App\Services\ReservationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/reservation')]
final class ReservationController extends AbstractController
{
    /**
     * Affiche le formulaire de recherche de chambres disponibles.
     * GET /reservation/search
     */
    #[Route('/search', name: 'app_reservation_search', methods: ['GET', 'POST'])]
    public function search(
        Request $request,
        HotelRepository $hotelRepository,
        DisponibiliteService $disponibiliteService,
        ClientRepository $clientRepository,
    ): Response {
        $form = $this->createForm(ReservationFormType::class);
        $form->handleRequest($request);

        $availableRooms = [];
        $hotels = $hotelRepository->findAll();
        if ($form->isSubmitted() && $form->isValid()) {
            $hotel = $form->get('hotel')->getData();
            $dateDebut = $form->get('dateDebut')->getData();
            $dateFin = $form->get('dateFin')->getData();
            $hotelId = $hotel?->getId();

            if ($hotelId && $dateDebut && $dateFin) {
                try {
                    $availableRooms = $disponibiliteService->findAvailableRooms($dateDebut, $dateFin, $hotelId);
                } catch (\InvalidArgumentException $e) {
                    $this->addFlash('error', $e->getMessage());
                }
            }
        }
        $clients = [];

        if ($this->getUser() instanceof Compte) {
            $clients = $clientRepository->findByCompte($this->getUser());
        }

        return $this->render('reservation/search.html.twig', [
            'form' => $form,
            'availableRooms' => $availableRooms,
            'hotels' => $hotels,
            'clients' => $clients,
        ]);
    }

            /**
     * Crée une nouvelle réservation.
     * POST /reservation/create
     */
    #[Route('/create', name: 'app_reservation_create', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function create(
        Request $request,
        ReservationService $reservationService,
        ClientRepository $clientRepository,
        ChambreRepository $chambreRepository,
        EntityManagerInterface $entityManager,
    ): Response {
        $compte = $this->getUser();

        if (!$compte instanceof Compte) {
            throw $this->createAccessDeniedException('Vous devez être connecté.');
        }

        $chambreId = $request->request->getInt('chambre_id');
        $dateDebut = $request->request->get('dateDebut');
        $dateFin = $request->request->get('dateFin');
        $commentaire = $request->request->get('commentaire');

        $clientId = $request->request->get('client_id');

        $nom = trim($request->request->get('client_nom'));
        $email = trim($request->request->get('client_email'));
        $telephone = trim($request->request->get('client_telephone'));
        $adresse = trim($request->request->get('client_adresse'));

        try {

            /*
            |--------------------------------------------------------------------------
            | Vérification de la chambre
            |--------------------------------------------------------------------------
            */

            $chambre = $chambreRepository->find($chambreId);

            if (!$chambre) {
                throw new \Exception('Chambre non trouvée.');
            }

            /*
            |--------------------------------------------------------------------------
            | Gestion du client
            |--------------------------------------------------------------------------
            */

            if ($clientId) {

                // L'utilisateur a sélectionné un client existant.
                $client = $clientRepository->find($clientId);

                if (!$client) {
                    throw new \Exception('Client introuvable.');
                }

                // Mise à jour éventuelle des informations.
                $client->setNom($nom);
                $client->setEmail($email);
                $client->setTelephone($telephone);
                $client->setAdresse($adresse);

            } else {

                // Aucun client sélectionné.
                // On vérifie qu'un client avec cet email n'existe pas déjà.

                $existingClient = $clientRepository->findClientForCompteByEmail(
                $compte,
                $email
                );

                if ($existingClient) {
                    throw new \Exception(
                        'Un client avec cette adresse email existe déjà. Veuillez le sélectionner dans la liste.'
                    );
                }

                // Création d'un nouveau client.
                $client = new Client();

                $client->setNom($nom);
                $client->setEmail($email);
                $client->setTelephone($telephone);
                $client->setAdresse($adresse);

                $entityManager->persist($client);
            }

            // Sauvegarde des modifications du client.
            $entityManager->flush();

            /*
            |--------------------------------------------------------------------------
            | Création de la réservation
            |--------------------------------------------------------------------------
            */

            $reservationService->createReservation(
                $chambre,
                $client,
                $compte,
                new \DateTime($dateDebut),
                new \DateTime($dateFin),
                $commentaire,
            );

            $this->addFlash('success', 'Réservation créée avec succès !');

            return $this->redirectToRoute('app_reservation_my_reservations');

        } catch (\Exception $e) {

            $this->addFlash(
                'error',
                'Erreur lors de la création de la réservation : ' . $e->getMessage()
            );

            return $this->redirectToRoute('app_reservation_search');
        }
    }

    /**
     * Affiche les réservations de l'utilisateur connecté.
     * GET /my-reservations
     */
    #[Route('/my-reservations', name: 'app_reservation_my_reservations', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function myReservations(): Response
    {
        $compte = $this->getUser();
        if (!$compte instanceof Compte) {
            throw $this->createAccessDeniedException('Vous devez être connecté.');
        }

        $reservations = $compte->getReservations();

        return $this->render('reservation/my-reservations.html.twig', [
            'reservations' => $reservations,
        ]);
    }

    /**
     * Modifie le commentaire d'une réservation.
     * GET|POST /reservation/{id}/comment/edit
     */
    #[Route('/{id}/comment/edit', name: 'app_reservation_comment_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function editComment(
        Request $request,
        Reservation $reservation,
        EntityManagerInterface $entityManager,
    ): Response {
        $compte = $this->getUser();
        if (!$compte instanceof Compte) {
            throw $this->createAccessDeniedException('Vous devez être connecté.');
        }

        if ($reservation->getCompte() !== $compte) {
            throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à modifier cette réservation.');
        }

        $form = $this->createForm(ReservationCommentType::class, $reservation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Commentaire modifié avec succès !');

            return $this->redirectToRoute('app_reservation_my_reservations');
        }

        return $this->render('reservation/edit-comment.html.twig', [
            'reservation' => $reservation,
            'form' => $form,
        ]);
    }

    /**
     * Annule une réservation.
     * POST /reservation/{id}/cancel
     */
    #[Route('/{id}/cancel', name: 'app_reservation_cancel', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function cancel(
        Request $request,
        Reservation $reservation,
        EntityManagerInterface $entityManager,
    ): Response {
        $compte = $this->getUser();
        if (!$compte instanceof Compte) {
            throw $this->createAccessDeniedException('Vous devez être connecté.');
        }

        if ($reservation->getCompte() !== $compte) {
            throw $this->createAccessDeniedException('Vous n\'êtes pas autorisé à annuler cette réservation.');
        }

        if ($this->isCsrfTokenValid('cancel' . $reservation->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($reservation);
            $entityManager->flush();

            $this->addFlash('success', 'Réservation annulée avec succès !');
        }

        return $this->redirectToRoute('app_reservation_my_reservations');
    }
}
