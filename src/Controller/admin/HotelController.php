<?php

namespace App\Controller\admin;

use App\Entity\Hotel;
use App\Form\HotelType;
use App\Repository\HotelRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\Exception\ORMException;
use Symfony\Contracts\Translation\TranslatorInterface;
use Psr\Log\LoggerInterface;

/**
 * Contrôleur d'administration pour la gestion des hôtels.
 *
 * Ce contrôleur permet aux administrateurs (ROLE_ADMIN) de lister, créer,
 * afficher, modifier et supprimer des hôtels. Il intègre la gestion des
 * erreurs et la journalisation.
 */
#[Route('/admin/hotel')]
#[IsGranted('ROLE_ADMIN')]
final class HotelController extends AbstractController
{
    /**
     * Liste tous les hôtels avec des options de pagination et de recherche.
     *
     * @param Request $request La requête HTTP, utilisée pour récupérer les paramètres de page et de recherche.
     * @param HotelRepository $hotelRepository Le dépôt des hôtels pour l'accès aux données.
     * @return Response Une réponse HTTP affichant la liste des hôtels.
     */
    #[Route(name: 'app_admin_hotel_index', methods: ['GET'])]
    public function index(Request $request, HotelRepository $hotelRepository): Response
    {
        $page = $request->query->getInt('page', 1);
        $limit = 10; // Nombre d'éléments par page
        $search = $request->query->getString('search');

        $hotels = $hotelRepository->paginateHotels($page, $limit, $search);
        $maxPages = ceil(count($hotels) / $limit);

        return $this->render('admin/hotel/index.html.twig', [
            'hotels' => $hotels,
            'page' => $page,
            'maxPages' => $maxPages,
            'search' => $search,
        ]);
    }

    /**
     * Crée un nouvel hôtel.
     *
     * Affiche le formulaire de création et gère sa soumission. En cas de succès,
     * l'hôtel est persisté en base de données.
     *
     * @param Request $request La requête HTTP.
     * @param EntityManagerInterface $entityManager Le gestionnaire d'entités Doctrine.
     * @param TranslatorInterface $translator Le service de traduction.
     * @param LoggerInterface $logger Le service de journalisation.
     * @return Response Une réponse HTTP affichant le formulaire ou redirigeant.
     */
    #[Route('/new', name: 'app_admin_hotel_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, TranslatorInterface $translator, LoggerInterface $logger): Response
    {
        $hotel = new Hotel();
        $form = $this->createForm(HotelType::class, $hotel);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $entityManager->persist($hotel);
                $entityManager->flush();
                $this->addFlash('success', $translator->trans('admin.hotel.new.success', [], 'app'));
                return $this->redirectToRoute('app_admin_hotel_index', [], Response::HTTP_SEE_OTHER);
            } catch (UniqueConstraintViolationException $e) {
                $logger->error('Admin hotel creation failed due to unique constraint violation: ' . $e->getMessage());
                $this->addFlash('error', $translator->trans('admin.hotel.error.unique_constraint', [], 'app'));
            } catch (ORMException $e) {
                $logger->error('Admin hotel creation failed due to ORM exception: ' . $e->getMessage());
                $this->addFlash('error', $translator->trans('admin.hotel.error.orm_exception', [], 'app'));
            } catch (\Exception $e) {
                $logger->critical('Unexpected error during admin hotel creation: ' . $e->getMessage());
                $this->addFlash('error', $translator->trans('admin.hotel.error.unexpected', [], 'app'));
            }
        }

        return $this->render('admin/hotel/new.html.twig', [
            'hotel' => $hotel,
            'form' => $form,
        ]);
    }

    /**
     * Affiche les détails d'un hôtel spécifique.
     *
     * @param Hotel $hotel L'entité Hotel à afficher (résolue par le ParamConverter).
     * @return Response Une réponse HTTP affichant les détails de l'hôtel.
     */
    #[Route('/{id}', name: 'app_admin_hotel_show', methods: ['GET'])]
    public function show(Hotel $hotel): Response
    {
        return $this->render('admin/hotel/show.html.twig', [
            'hotel' => $hotel,
        ]);
    }

    /**
     * Modifie un hôtel existant.
     *
     * Affiche le formulaire de modification et gère sa soumission. En cas de succès,
     * les modifications sont persistées en base de données.
     *
     * @param Request $request La requête HTTP.
     * @param Hotel $hotel L'entité Hotel à modifier (résolue par le ParamConverter).
     * @param EntityManagerInterface $entityManager Le gestionnaire d'entités Doctrine.
     * @param TranslatorInterface $translator Le service de traduction.
     * @param LoggerInterface $logger Le service de journalisation.
     * @return Response Une réponse HTTP affichant le formulaire ou redirigeant.
     */
    #[Route('/{id}/edit', name: 'app_admin_hotel_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Hotel $hotel, EntityManagerInterface $entityManager, TranslatorInterface $translator, LoggerInterface $logger): Response
    {
        $form = $this->createForm(HotelType::class, $hotel);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $entityManager->flush();
                $this->addFlash('success', $translator->trans('admin.hotel.edit.success', [], 'app'));
                return $this->redirectToRoute('app_admin_hotel_index', [], Response::HTTP_SEE_OTHER);
            } catch (UniqueConstraintViolationException $e) {
                $logger->error('Admin hotel edit failed due to unique constraint violation: ' . $e->getMessage());
                $this->addFlash('error', $translator->trans('admin.hotel.error.unique_constraint', [], 'app'));
            } catch (ORMException $e) {
                $logger->error('Admin hotel edit failed due to ORM exception: ' . $e->getMessage());
                $this->addFlash('error', $translator->trans('admin.hotel.error.orm_exception', [], 'app'));
            } catch (\Exception $e) {
                $logger->critical('Unexpected error during admin hotel edit: ' . $e->getMessage());
                $this->addFlash('error', $translator->trans('admin.hotel.error.unexpected', [], 'app'));
            }
        }

        return $this->render('admin/hotel/edit.html.twig', [
            'hotel' => $hotel,
            'form' => $form,
        ]);
    }

    /**
     * Supprime un hôtel.
     *
     * Gère la suppression d'un hôtel après vérification du jeton CSRF.
     *
     * @param Request $request La requête HTTP.
     * @param Hotel $hotel L'entité Hotel à supprimer (résolue par le ParamConverter).
     * @param EntityManagerInterface $entityManager Le gestionnaire d'entités Doctrine.
     * @param TranslatorInterface $translator Le service de traduction.
     * @param LoggerInterface $logger Le service de journalisation.
     * @return Response Une réponse de redirection après la suppression ou en cas d'erreur.
     */
    #[Route('/{id}', name: 'app_admin_hotel_delete', methods: ['POST'])]
    public function delete(Request $request, Hotel $hotel, EntityManagerInterface $entityManager, TranslatorInterface $translator, LoggerInterface $logger): Response
    {
        if ($this->isCsrfTokenValid('delete' . $hotel->getId(), $request->getPayload()->getString('_token'))) {
            try {
                $entityManager->remove($hotel);
                $entityManager->flush();
                $this->addFlash('success', $translator->trans('admin.hotel.delete.success', [], 'app'));
            } catch (ORMException $e) {
                $logger->error('Admin hotel deletion failed due to ORM exception: ' . $e->getMessage());
                $this->addFlash('error', $translator->trans('admin.hotel.delete.error.orm_exception', [], 'app'));
            } catch (\Exception $e) {
                $logger->critical('Unexpected error during admin hotel deletion: ' . $e->getMessage());
                $this->addFlash('error', $translator->trans('admin.hotel.error.unexpected', [], 'app'));
            }
        } else {
            $this->addFlash('error', $translator->trans('csrf.invalid_token', [], 'app'));
        }

        return $this->redirectToRoute('app_admin_hotel_index', [], Response::HTTP_SEE_OTHER);
    }
}
