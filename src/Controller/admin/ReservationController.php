<?php

namespace App\Controller\admin;

use App\Entity\Reservation;
use App\Form\ReservationType;
use App\Repository\ReservationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException; // Added for error handling
use Doctrine\ORM\Exception\ORMException; // Added for error handling
use Symfony\Contracts\Translation\TranslatorInterface; // Added for translation
use Psr\Log\LoggerInterface; // Added for logging

/**
 * Contrôleur d'administration pour la gestion des réservations.
 *
 * Ce contrôleur permet aux administrateurs (ROLE_ADMIN) de lister, créer,
 * afficher, modifier et supprimer des réservations. Il intègre la gestion des
 * erreurs et la journalisation.
 */
#[Route('/admin/reservation')]
#[IsGranted('ROLE_ADMIN')]
final class ReservationController extends AbstractController
{
    /**
     * Liste toutes les réservations avec des options de pagination et de recherche.
     *
     * @param Request $request La requête HTTP, utilisée pour récupérer les paramètres de page et de recherche.
     * @param ReservationRepository $reservationRepository Le dépôt des réservations pour l'accès aux données.
     * @return Response Une réponse HTTP affichant la liste des réservations.
     */
    #[Route('/', name: 'app_admin_reservation_index', methods: ['GET'])]
    public function index(Request $request, ReservationRepository $reservationRepository): Response
    {
        $page = $request->query->getInt('page', 1);
        $limit = 10; // Nombre d'éléments par page
        $search = $request->query->getString('search');
        $reservationId = $request->query->getString('reservation_id'); // Nouveau paramètre

        $reservations = $reservationRepository->paginateReservations($page, $limit, $search, $reservationId); // Passage du nouveau paramètre
        $maxPages = ceil(count($reservations) / $limit);

        return $this->render('admin/reservation/index.html.twig', [
            'reservations' => $reservations,
            'page' => $page,
            'maxPages' => $maxPages,
            'search' => $search,
            'reservation_id' => $reservationId, // Passage du nouveau paramètre au template
        ]);
    }

    /**
     * Crée une nouvelle réservation.
     *
     * Affiche le formulaire de création et gère sa soumission. En cas de succès,
     * la réservation est persistée en base de données.
     *
     * @param Request $request La requête HTTP.
     * @param EntityManagerInterface $entityManager Le gestionnaire d'entités Doctrine.
     * @param TranslatorInterface $translator Le service de traduction.
     * @param LoggerInterface $logger Le service de journalisation.
     * @return Response Une réponse HTTP affichant le formulaire ou redirigeant.
     */
    #[Route('/new', name: 'app_admin_reservation_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, TranslatorInterface $translator, LoggerInterface $logger): Response
    {
        $reservation = new Reservation();
        $form = $this->createForm(ReservationType::class, $reservation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $entityManager->persist($reservation);
                $entityManager->flush();
                $this->addFlash('success', $translator->trans('admin.reservation.new.success', [], 'app'));
                return $this->redirectToRoute('app_admin_reservation_index', [], Response::HTTP_SEE_OTHER);
            } catch (UniqueConstraintViolationException $e) {
                $logger->error('Admin reservation creation failed due to unique constraint violation: ' . $e->getMessage());
                $this->addFlash('error', $translator->trans('admin.reservation.error.unique_constraint', [], 'app'));
            } catch (ORMException $e) {
                $logger->error('Admin reservation creation failed due to ORM exception: ' . $e->getMessage());
                $this->addFlash('error', $translator->trans('admin.reservation.error.orm_exception', [], 'app'));
            } catch (\Exception $e) {
                $logger->critical('Unexpected error during admin reservation creation: ' . $e->getMessage());
                $this->addFlash('error', $translator->trans('admin.reservation.error.unexpected', [], 'app'));
            }
        }

        return $this->render('admin/reservation/new.html.twig', [
            'reservation' => $reservation,
            'form' => $form,
        ]);
    }

    /**
     * Affiche les détails d'une réservation spécifique.
     *
     * @param Reservation $reservation L'entité Reservation à afficher (résolue par le ParamConverter).
     * @return Response Une réponse HTTP affichant les détails de la réservation.
     */
    #[Route('/{id}', name: 'app_admin_reservation_show', methods: ['GET'])]
    public function show(Reservation $reservation): Response
    {
        return $this->render('admin/reservation/show.html.twig', [
            'reservation' => $reservation,
        ]);
    }

    /**
     * Modifie une réservation existante.
     *
     * Affiche le formulaire de modification et gère sa soumission. En cas de succès,
     * les modifications sont persistées en base de données.
     *
     * @param Request $request La requête HTTP.
     * @param Reservation $reservation L'entité Reservation à modifier (résolue par le ParamConverter).
     * @param EntityManagerInterface $entityManager Le gestionnaire d'entités Doctrine.
     * @param TranslatorInterface $translator Le service de traduction.
     * @param LoggerInterface $logger Le service de journalisation.
     * @return Response Une réponse HTTP affichant le formulaire ou redirigeant.
     */
    #[Route('/{id}/edit', name: 'app_admin_reservation_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Reservation $reservation, EntityManagerInterface $entityManager, TranslatorInterface $translator, LoggerInterface $logger): Response
    {
        $form = $this->createForm(ReservationType::class, $reservation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $entityManager->flush();
                $this->addFlash('success', $translator->trans('admin.reservation.edit.success', [], 'app'));
                return $this->redirectToRoute('app_admin_reservation_index', [], Response::HTTP_SEE_OTHER);
            } catch (UniqueConstraintViolationException $e) {
                $logger->error('Admin reservation edit failed due to unique constraint violation: ' . $e->getMessage());
                $this->addFlash('error', $translator->trans('admin.reservation.error.unique_constraint', [], 'app'));
            } catch (ORMException $e) {
                $logger->error('Admin reservation edit failed due to ORM exception: ' . $e->getMessage());
                $this->addFlash('error', $translator->trans('admin.reservation.error.orm_exception', [], 'app'));
            } catch (\Exception $e) {
                $logger->critical('Unexpected error during admin reservation edit: ' . $e->getMessage());
                $this->addFlash('error', $translator->trans('admin.reservation.error.unexpected', [], 'app'));
            }
        }

        return $this->render('admin/reservation/edit.html.twig', [
            'reservation' => $reservation,
            'form' => $form,
        ]);
    }

    /**
     * Supprime une réservation.
     *
     * Gère la suppression d'une réservation après vérification du jeton CSRF.
     *
     * @param Request $request La requête HTTP.
     * @param Reservation $reservation L'entité Reservation à supprimer (résolue par le ParamConverter).
     * @param EntityManagerInterface $entityManager Le gestionnaire d'entités Doctrine.
     * @param TranslatorInterface $translator Le service de traduction.
     * @param LoggerInterface $logger Le service de journalisation.
     * @return Response Une réponse de redirection après la suppression ou en cas d'erreur.
     */
    #[Route('/{id}', name: 'app_admin_reservation_delete', methods: ['POST'])]
    public function delete(Request $request, Reservation $reservation, EntityManagerInterface $entityManager, TranslatorInterface $translator, LoggerInterface $logger): Response
    {
        if ($this->isCsrfTokenValid('delete'.$reservation->getId(), $request->request->get('_token'))) {
            try {
                $entityManager->remove($reservation);
                $entityManager->flush();
                $this->addFlash('success', $translator->trans('admin.reservation.delete.success', [], 'app'));
            } catch (ORMException $e) {
                $logger->error('Admin reservation deletion failed due to ORM exception: ' . $e->getMessage());
                $this->addFlash('error', $translator->trans('admin.reservation.delete.error.orm_exception', [], 'app'));
            } catch (\Exception $e) {
                $logger->critical('Unexpected error during admin reservation deletion: ' . $e->getMessage());
                $this->addFlash('error', $translator->trans('admin.reservation.error.unexpected', [], 'app'));
            }
        } else {
            $this->addFlash('error', $translator->trans('csrf.invalid_token', [], 'app'));
        }

        return $this->redirectToRoute('app_admin_reservation_index', [], Response::HTTP_SEE_OTHER);
    }
}
