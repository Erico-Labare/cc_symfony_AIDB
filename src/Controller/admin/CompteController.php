<?php

namespace App\Controller\admin;

use App\Entity\Compte;
use App\Form\CompteType;
use App\Repository\CompteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\Exception\ORMException;
use Symfony\Contracts\Translation\TranslatorInterface; // Import TranslatorInterface
use Psr\Log\LoggerInterface; // Import LoggerInterface

#[Route('/admin/compte')]
#[IsGranted('ROLE_ADMIN')]
final class CompteController extends AbstractController
{
    // Lister tous les comptes avec pagination et recherche
    #[Route(name: 'app_admin_compte_index', methods: ['GET'])]
    public function index(Request $request, CompteRepository $compteRepository): Response
    {
        $page = $request->query->getInt('page', 1);
        $limit = 10; // Nombre d'éléments par page
        $search = $request->query->getString('search');

        $comptes = $compteRepository->paginateComptes($page, $limit, $search);
        $maxPages = ceil(count($comptes) / $limit);

        return $this->render('admin/compte/index.html.twig', [
            'comptes' => $comptes,
            'page' => $page,
            'maxPages' => $maxPages,
            'search' => $search,
        ]);
    }

    // Créer un nouveau compte
    #[Route('/new', name: 'app_admin_compte_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher, TranslatorInterface $translator, LoggerInterface $logger): Response
    {
        $compte = new Compte();
        $form = $this->createForm(CompteType::class, $compte);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                // hacher le mot de passe en clair
                $plainPassword = $form->get('plainPassword')->getData();
                if ($plainPassword) {
                    $hashedPassword = $passwordHasher->hashPassword($compte, $plainPassword);
                    $compte->setPassword($hashedPassword);
                }

                $entityManager->persist($compte);
                $entityManager->flush();
                $this->addFlash('success', $translator->trans('admin.compte.new.success', [], 'app'));
                return $this->redirectToRoute('app_admin_compte_index', [], Response::HTTP_SEE_OTHER);
            } catch (UniqueConstraintViolationException $e) {
                $logger->error('Admin compte creation failed due to unique constraint violation: ' . $e->getMessage());
                $this->addFlash('error', $translator->trans('admin.compte.error.email_exists', [], 'app'));
            } catch (ORMException $e) {
                $logger->error('Admin compte creation failed due to ORM exception: ' . $e->getMessage());
                $this->addFlash('error', $translator->trans('admin.compte.error.orm_exception', [], 'app'));
            } catch (\Exception $e) {
                $logger->critical('Unexpected error during admin compte creation: ' . $e->getMessage());
                $this->addFlash('error', $translator->trans('admin.compte.error.unexpected', [], 'app'));
            }
        }

        return $this->render('admin/compte/new.html.twig', [
            'compte' => $compte,
            'form' => $form,
        ]);
    }

    // Afficher un compte spécifique
    #[Route('/{id}', name: 'app_admin_compte_show', methods: ['GET'])]
    public function show(Compte $compte): Response
    {
        return $this->render('admin/compte/show.html.twig', [
            'compte' => $compte,
        ]);
    }

    // Modifier un compte existant
    #[Route('/{id}/edit', name: 'app_admin_compte_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Compte $compte, EntityManagerInterface $entityManager, TranslatorInterface $translator, LoggerInterface $logger): Response
    {
        $form = $this->createForm(CompteType::class, $compte);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $entityManager->flush();
                $this->addFlash('success', $translator->trans('admin.compte.edit.success', [], 'app'));
                return $this->redirectToRoute('app_admin_compte_index', [], Response::HTTP_SEE_OTHER);
            } catch (UniqueConstraintViolationException $e) {
                $logger->error('Admin compte edit failed due to unique constraint violation: ' . $e->getMessage());
                $this->addFlash('error', $translator->trans('admin.compte.error.email_exists', [], 'app'));
            } catch (ORMException $e) {
                $logger->error('Admin compte edit failed due to ORM exception: ' . $e->getMessage());
                $this->addFlash('error', $translator->trans('admin.compte.error.orm_exception', [], 'app'));
            } catch (\Exception $e) {
                $logger->critical('Unexpected error during admin compte edit: ' . $e->getMessage());
                $this->addFlash('error', $translator->trans('admin.compte.error.unexpected', [], 'app'));
            }
        }

        return $this->render('admin/compte/edit.html.twig', [
            'compte' => $compte,
            'form' => $form,
        ]);
    }

    // Supprimer un compte
    #[Route('/{id}', name: 'app_admin_compte_delete', methods: ['POST'])]
    public function delete(Request $request, Compte $compte, EntityManagerInterface $entityManager, TranslatorInterface $translator, LoggerInterface $logger): Response
    {
        if ($this->isCsrfTokenValid('delete' . $compte->getId(), $request->getPayload()->getString('_token'))) {
            try {
                $entityManager->remove($compte);
                $entityManager->flush();
                $this->addFlash('success', $translator->trans('admin.compte.delete.success', [], 'app'));
            } catch (ORMException $e) {
                $logger->error('Admin compte deletion failed due to ORM exception: ' . $e->getMessage());
                $this->addFlash('error', $translator->trans('admin.compte.delete.error.orm_exception', [], 'app'));
            } catch (\Exception $e) {
                $logger->critical('Unexpected error during admin compte deletion: ' . $e->getMessage());
                $this->addFlash('error', $translator->trans('admin.compte.delete.error.unexpected', [], 'app'));
            }
        } else {
            $this->addFlash('error', $translator->trans('csrf.invalid_token', [], 'app'));
        }

        return $this->redirectToRoute('app_admin_compte_index', [], Response::HTTP_SEE_OTHER);
    }
}
