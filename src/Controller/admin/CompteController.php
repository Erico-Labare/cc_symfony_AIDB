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
use Symfony\Contracts\Translation\TranslatorInterface;
use Psr\Log\LoggerInterface;

/**
 * Contrôleur d'administration pour la gestion des comptes utilisateurs.
 *
 * Ce contrôleur permet aux administrateurs (ROLE_ADMIN) de lister, créer,
 * afficher, modifier et supprimer des comptes utilisateurs. Il intègre la
 * gestion des mots de passe, des rôles, des erreurs et la journalisation.
 */
#[Route('/admin/compte')]
#[IsGranted('ROLE_ADMIN')]
final class CompteController extends AbstractController
{
    /**
     * Liste tous les comptes utilisateurs avec des options de pagination et de recherche.
     *
     * @param Request $request La requête HTTP, utilisée pour récupérer les paramètres de page et de recherche.
     * @param CompteRepository $compteRepository Le dépôt des comptes pour l'accès aux données.
     * @return Response Une réponse HTTP affichant la liste des comptes.
     */
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

    /**
     * Crée un nouveau compte utilisateur.
     *
     * Affiche le formulaire de création et gère sa soumission. En cas de succès,
     * le compte est persisté en base de données avec le mot de passe haché.
     *
     * @param Request $request La requête HTTP.
     * @param EntityManagerInterface $entityManager Le gestionnaire d'entités Doctrine.
     * @param UserPasswordHasherInterface $passwordHasher Le service de hachage de mot de passe.
     * @param TranslatorInterface $translator Le service de traduction.
     * @param LoggerInterface $logger Le service de journalisation.
     * @return Response Une réponse HTTP affichant le formulaire ou redirigeant.
     */
    #[Route('/new', name: 'app_admin_compte_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher, TranslatorInterface $translator, LoggerInterface $logger): Response
    {
        $compte = new Compte();
        $form = $this->createForm(CompteType::class, $compte, ['is_new' => true]); // Pass is_new option
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
                $this->addFlash('success', 'Le compte a été créé avec succès.');
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

    /**
     * Affiche les détails d'un compte utilisateur spécifique.
     *
     * @param Compte $compte L'entité Compte à afficher (résolue par le ParamConverter).
     * @return Response Une réponse HTTP affichant les détails du compte.
     */
    #[Route('/{id}', name: 'app_admin_compte_show', methods: ['GET'])]
    public function show(Compte $compte): Response
    {
        return $this->render('admin/compte/show.html.twig', [
            'compte' => $compte,
        ]);
    }

    /**
     * Modifie un compte utilisateur existant.
     *
     * Affiche le formulaire de modification et gère sa soumission. En cas de succès,
     * les modifications sont persistées en base de données. Le mot de passe est
     * haché s'il est fourni.
     *
     * @param Request $request La requête HTTP.
     * @param Compte $compte L'entité Compte à modifier (résolue par le ParamConverter).
     * @param EntityManagerInterface $entityManager Le gestionnaire d'entités Doctrine.
     * @param UserPasswordHasherInterface $passwordHasher Le service de hachage de mot de passe.
     * @param TranslatorInterface $translator Le service de traduction.
     * @param LoggerInterface $logger Le service de journalisation.
     * @return Response Une réponse HTTP affichant le formulaire ou redirigeant.
     */
    #[Route('/{id}/edit', name: 'app_admin_compte_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Compte $compte, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher, TranslatorInterface $translator, LoggerInterface $logger): Response
    {
        $form = $this->createForm(CompteType::class, $compte, ['is_new' => false]); // Pass is_new option
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                // hacher le mot de passe en clair si fourni
                $plainPassword = $form->get('plainPassword')->getData();
                if ($plainPassword) {
                    $hashedPassword = $passwordHasher->hashPassword($compte, $plainPassword);
                    $compte->setPassword($hashedPassword);
                }

                $entityManager->flush();
                $this->addFlash('success', 'Le compte a été modifié avec succès.');
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

    /**
     * Supprime un compte utilisateur.
     *
     * Gère la suppression d'un compte après vérification du jeton CSRF.
     *
     * @param Request $request La requête HTTP.
     * @param Compte $compte L'entité Compte à supprimer (résolue par le ParamConverter).
     * @param EntityManagerInterface $entityManager Le gestionnaire d'entités Doctrine.
     * @param TranslatorInterface $translator Le service de traduction.
     * @param LoggerInterface $logger Le service de journalisation.
     * @return Response Une réponse de redirection après la suppression ou en cas d'erreur.
     */
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
