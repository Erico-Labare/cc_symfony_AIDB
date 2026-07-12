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
use Symfony\Contracts\Translation\TranslatorInterface; // Import TranslatorInterface
use Psr\Log\LoggerInterface; // Import LoggerInterface

#[Route('/admin/hotel')]
#[IsGranted('ROLE_ADMIN')]
final class HotelController extends AbstractController
{
    // Lister tous les hôtels avec pagination et recherche
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

    // Créer un nouvel hôtel
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
                $this->addFlash('success', 'L\'hôtel a été créé avec succès.');
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

    // Afficher un hôtel spécifique
    #[Route('/{id}', name: 'app_admin_hotel_show', methods: ['GET'])]
    public function show(Hotel $hotel): Response
    {
        return $this->render('admin/hotel/show.html.twig', [
            'hotel' => $hotel,
        ]);
    }

    // Modifier un hôtel existant
    #[Route('/{id}/edit', name: 'app_admin_hotel_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Hotel $hotel, EntityManagerInterface $entityManager, TranslatorInterface $translator, LoggerInterface $logger): Response
    {
        $form = $this->createForm(HotelType::class, $hotel);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $entityManager->flush();
                $this->addFlash('success', 'L\'hôtel a été modifié avec succès.');
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

    // Supprimer un hôtel
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
                $this->addFlash('error', $translator->trans('admin.hotel.delete.error.unexpected', [], 'app'));
            }
        } else {
            $this->addFlash('error', $translator->trans('csrf.invalid_token', [], 'app'));
        }

        return $this->redirectToRoute('app_admin_hotel_index', [], Response::HTTP_SEE_OTHER);
    }
}
