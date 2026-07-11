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
    ): Response {
        $form = $this->createForm(ReservationFormType::class);
        $form->handleRequest($request);

        $availableRooms = [];
        $hotels = $hotelRepository->findAll();

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $hotelId = $data['hotel']->getId() ?? null;
            $dateDebut = $data['dateDebut'] ?? null;
            $dateFin = $data['dateFin'] ?? null;

            if ($hotelId && $dateDebut && $dateFin) {
                try {
                    $availableRooms = $disponibiliteService->findAvailableRooms($dateDebut, $dateFin, $hotelId);
                } catch (\InvalidArgumentException $e) {
                    $this->addFlash('error', $e->getMessage());
                }
            }
        }

        return $this->render('reservation/search.html.twig', [
            'form' => $form,
            'availableRooms' => $availableRooms,
            'hotels' => $hotels,
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

        try {
            $chambre = $chambreRepository->find($chambreId);
            if (!$chambre) {
                throw new \Exception('Chambre non trouvée.');
            }

            $client = $clientRepository->findOneBy(['email' => $compte->getEmail()]);
            if (!$client) {
                $client = new Client();
                $client->setNom($compte->getEmail());
                $client->setEmail($compte->getEmail());
                $client->setAdresse('');
                $client->setTelephone('');
                $entityManager->persist($client);
                $entityManager->flush();
            }

            $dateDebutObj = new \DateTime($dateDebut);
            $dateFinObj = new \DateTime($dateFin);

            $reservation = $reservationService->createReservation(
                $chambre,
                $client,
                $compte,
                $dateDebutObj,
                $dateFinObj,
                $commentaire,
            );

            $this->addFlash('success', 'Réservation créée avec succès !');

            return $this->redirectToRoute('app_reservation_my_reservations');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de la création de la réservation : ' . $e->getMessage());

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
