<?php

namespace App\Controller\admin;

use App\Entity\Chambre;
use App\Form\ChambreType;
use App\Repository\ChambreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\Exception\ORMException;

#[Route('/admin/chambre')]
#[IsGranted('ROLE_ADMIN')]
final class ChambreController extends AbstractController
{
    // Lister toutes les chambres
    #[Route(name: 'app_chambre_index', methods: ['GET'])]
    public function index(ChambreRepository $chambreRepository): Response
    {
        return $this->render('admin/chambre/index.html.twig', [
            'chambres' => $chambreRepository->findAll(),
        ]);
    }

    // Créer une nouvelle chambre
    #[Route('/new', name: 'app_chambre_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $chambre = new Chambre();
        $form = $this->createForm(ChambreType::class, $chambre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $entityManager->persist($chambre);
                $entityManager->flush();
                $this->addFlash('success', 'La chambre a été créée avec succès.');
                return $this->redirectToRoute('app_chambre_index', [], Response::HTTP_SEE_OTHER);
            } catch (UniqueConstraintViolationException $e) {
                $this->addFlash('error', 'Une erreur est survenue : Une chambre avec le même numéro existe déjà pour cet hôtel.');
            } catch (ORMException $e) {
                $this->addFlash('error', 'Une erreur est survenue lors de la création de la chambre : ' . $e->getMessage());
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur inattendue est survenue : ' . $e->getMessage());
            }
        }

        return $this->render('admin/chambre/new.html.twig', [
            'chambre' => $chambre,
            'form' => $form,
        ]);
    }

    // Afficher une chambre spécifique
    #[Route('/{id}', name: 'app_chambre_show', methods: ['GET'])]
    public function show(Chambre $chambre): Response
    {
        return $this->render('admin/chambre/show.html.twig', [
            'chambre' => $chambre,
        ]);
    }

    // Modifier une chambre existante
    #[Route('/{id}/edit', name: 'app_chambre_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Chambre $chambre, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ChambreType::class, $chambre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $entityManager->flush();
                $this->addFlash('success', 'La chambre a été modifiée avec succès.');
                return $this->redirectToRoute('app_chambre_index', [], Response::HTTP_SEE_OTHER);
            } catch (UniqueConstraintViolationException $e) {
                $this->addFlash('error', 'Une erreur est survenue : Une chambre avec le même numéro existe déjà pour cet hôtel.');
            } catch (ORMException $e) {
                $this->addFlash('error', 'Une erreur est survenue lors de la modification de la chambre : ' . $e->getMessage());
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur inattendue est survenue : ' . $e->getMessage());
            }
        }

        return $this->render('admin/chambre/edit.html.twig', [
            'chambre' => $chambre,
            'form' => $form,
        ]);
    }

    // Supprimer une chambre
    #[Route('/{id}', name: 'app_chambre_delete', methods: ['POST'])]
    public function delete(Request $request, Chambre $chambre, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $chambre->getId(), $request->getPayload()->getString('_token'))) {
            try {
                $entityManager->remove($chambre);
                $entityManager->flush();
                $this->addFlash('success', 'La chambre a été supprimée avec succès.');
            } catch (ORMException $e) {
                $this->addFlash('error', 'Une erreur est survenue lors de la suppression de la chambre : ' . $e->getMessage());
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur inattendue est survenue : ' . $e->getMessage());
            }
        } else {
            $this->addFlash('error', 'Token CSRF invalide.');
        }

        return $this->redirectToRoute('app_chambre_index', [], Response::HTTP_SEE_OTHER);
    }
}
