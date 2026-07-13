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
use Symfony\Contracts\Translation\TranslatorInterface;
use Psr\Log\LoggerInterface;

/**
 * Contrôleur d'administration pour la gestion des chambres.
 *
 * Ce contrôleur permet aux administrateurs (ROLE_ADMIN) de lister, créer,
 * afficher, modifier et supprimer des chambres. Il intègre la gestion des
 * erreurs et la journalisation.
 */
#[Route('/admin/chambre')]
#[IsGranted('ROLE_ADMIN')]
final class ChambreController extends AbstractController
{
    /**
     * Liste toutes les chambres avec des options de pagination et de recherche.
     *
     * @param Request $request La requête HTTP, utilisée pour récupérer les paramètres de page et de recherche.
     * @param ChambreRepository $chambreRepository Le dépôt des chambres pour l'accès aux données.
     * @return Response Une réponse HTTP affichant la liste des chambres.
     */
    #[Route(name: 'app_admin_chambre_index', methods: ['GET'])]
    public function index(Request $request, ChambreRepository $chambreRepository): Response
    {
        $page = $request->query->getInt('page', 1);
        $limit = 10; // Nombre d'éléments par page
        $search = $request->query->getString('search');

        $chambres = $chambreRepository->paginateChambres($page, $limit, $search);
        $maxPages = ceil(count($chambres) / $limit);

        return $this->render('admin/chambre/index.html.twig', [
            'chambres' => $chambres,
            'page' => $page,
            'maxPages' => $maxPages,
            'search' => $search,
        ]);
    }

    /**
     * Crée une nouvelle chambre.
     *
     * Affiche le formulaire de création et gère sa soumission. En cas de succès,
     * la chambre est persistée en base de données.
     *
     * @param Request $request La requête HTTP.
     * @param EntityManagerInterface $entityManager Le gestionnaire d'entités Doctrine.
     * @param TranslatorInterface $translator Le service de traduction.
     * @param LoggerInterface $logger Le service de journalisation.
     * @return Response Une réponse HTTP affichant le formulaire ou redirigeant.
     */
    #[Route('/new', name: 'app_admin_chambre_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, TranslatorInterface $translator, LoggerInterface $logger): Response
    {
        $chambre = new Chambre();
        $form = $this->createForm(ChambreType::class, $chambre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $entityManager->persist($chambre);
                $entityManager->flush();
                $this->addFlash('success', 'La chambre a été créée avec succès.');
                return $this->redirectToRoute('app_admin_chambre_index', [], Response::HTTP_SEE_OTHER); // Corrected route name
            } catch (UniqueConstraintViolationException $e) {
                $logger->error('Admin chambre creation failed due to unique constraint violation: ' . $e->getMessage());
                $this->addFlash('error', $translator->trans('admin.chambre.error.unique_constraint', [], 'app'));
            } catch (ORMException $e) {
                $logger->error('Admin chambre creation failed due to ORM exception: ' . $e->getMessage());
                $this->addFlash('error', $translator->trans('admin.chambre.error.orm_exception', [], 'app'));
            } catch (\Exception $e) {
                $logger->critical('Unexpected error during admin chambre creation: ' . $e->getMessage());
                $this->addFlash('error', $translator->trans('admin.chambre.error.unexpected', [], 'app'));
            }
        }

        return $this->render('admin/chambre/new.html.twig', [
            'chambre' => $chambre,
            'form' => $form,
        ]);
    }

    /**
     * Affiche les détails d'une chambre spécifique.
     *
     * @param Chambre $chambre L'entité Chambre à afficher (résolue par le ParamConverter).
     * @return Response Une réponse HTTP affichant les détails de la chambre.
     */
    #[Route('/{id}', name: 'app_admin_chambre_show', methods: ['GET'])]
    public function show(Chambre $chambre): Response
    {
        return $this->render('admin/chambre/show.html.twig', [
            'chambre' => $chambre,
        ]);
    }

    /**
     * Modifie une chambre existante.
     *
     * Affiche le formulaire de modification et gère sa soumission. En cas de succès,
     * les modifications sont persistées en base de données.
     *
     * @param Request $request La requête HTTP.
     * @param Chambre $chambre L'entité Chambre à modifier (résolue par le ParamConverter).
     * @param EntityManagerInterface $entityManager Le gestionnaire d'entités Doctrine.
     * @param TranslatorInterface $translator Le service de traduction.
     * @param LoggerInterface $logger Le service de journalisation.
     * @return Response Une réponse HTTP affichant le formulaire ou redirigeant.
     */
    #[Route('/{id}/edit', name: 'app_admin_chambre_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Chambre $chambre, EntityManagerInterface $entityManager, TranslatorInterface $translator, LoggerInterface $logger): Response
    {
        $form = $this->createForm(ChambreType::class, $chambre);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $entityManager->flush();
                $this->addFlash('success', 'La chambre a été modifiée avec succès.');
                return $this->redirectToRoute('app_admin_chambre_index', [], Response::HTTP_SEE_OTHER); // Corrected route name
            } catch (UniqueConstraintViolationException $e) {
                $logger->error('Admin chambre edit failed due to unique constraint violation: ' . $e->getMessage());
                $this->addFlash('error', $translator->trans('admin.chambre.error.unique_constraint', [], 'app'));
            } catch (ORMException $e) {
                $logger->error('Admin chambre edit failed due to ORM exception: ' . $e->getMessage());
                $this->addFlash('error', $translator->trans('admin.chambre.error.orm_exception', [], 'app'));
            } catch (\Exception $e) {
                $logger->critical('Unexpected error during admin chambre edit: ' . $e->getMessage());
                $this->addFlash('error', $translator->trans('admin.chambre.error.unexpected', [], 'app'));
            }
        }

        return $this->render('admin/chambre/edit.html.twig', [
            'chambre' => $chambre,
            'form' => $form,
        ]);
    }

    /**
     * Supprime une chambre.
     *
     * Gère la suppression d'une chambre après vérification du jeton CSRF.
     *
     * @param Request $request La requête HTTP.
     * @param Chambre $chambre L'entité Chambre à supprimer (résolue par le ParamConverter).
     * @param EntityManagerInterface $entityManager Le gestionnaire d'entités Doctrine.
     * @param TranslatorInterface $translator Le service de traduction.
     * @param LoggerInterface $logger Le service de journalisation.
     * @return Response Une réponse de redirection après la suppression ou en cas d'erreur.
     */
    #[Route('/{id}', name: 'app_admin_chambre_delete', methods: ['POST'])]
    public function delete(Request $request, Chambre $chambre, EntityManagerInterface $entityManager, TranslatorInterface $translator, LoggerInterface $logger): Response
    {
        if ($this->isCsrfTokenValid('delete' . $chambre->getId(), $request->getPayload()->getString('_token'))) {
            try {
                $entityManager->remove($chambre);
                $entityManager->flush();
                $this->addFlash('success', $translator->trans('admin.chambre.delete.success', [], 'app'));
            } catch (ORMException $e) {
                $logger->error('Admin chambre deletion failed due to ORM exception: ' . $e->getMessage());
                $this->addFlash('error', $translator->trans('admin.chambre.delete.error.orm_exception', [], 'app'));
            } catch (\Exception $e) {
                $logger->critical('Unexpected error during admin chambre deletion: ' . $e->getMessage());
                $this->addFlash('error', $translator->trans('admin.chambre.delete.error.unexpected', [], 'app'));
            }
        } else {
            $this->addFlash('error', $translator->trans('csrf.invalid_token', [], 'app'));
        }

        return $this->redirectToRoute('app_admin_chambre_index', [], Response::HTTP_SEE_OTHER);
    }
}
