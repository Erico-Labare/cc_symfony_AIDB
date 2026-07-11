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

#[Route('/admin/compte')]
#[IsGranted('ROLE_ADMIN')]
final class CompteController extends AbstractController
{
    // Lister tous les comptes
    #[Route(name: 'app_compte_index', methods: ['GET'])]
    public function index(CompteRepository $compteRepository): Response
    {
        return $this->render('admin/compte/index.html.twig', [
            'comptes' => $compteRepository->findAll(),
        ]);
    }

    // Créer un nouveau compte
    #[Route('/new', name: 'app_compte_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
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
                $this->addFlash('success', 'Le compte a été créé avec succès.');
                return $this->redirectToRoute('app_compte_index', [], Response::HTTP_SEE_OTHER);
            } catch (UniqueConstraintViolationException $e) {
                $this->addFlash('error', 'Une erreur est survenue : Un compte avec le même email existe déjà.');
            } catch (ORMException $e) {
                $this->addFlash('error', 'Une erreur est survenue lors de la création du compte : ' . $e->getMessage());
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur inattendue est survenue : ' . $e->getMessage());
            }
        }

        return $this->render('admin/compte/new.html.twig', [
            'compte' => $compte,
            'form' => $form,
        ]);
    }

    // Afficher un compte spécifique
    #[Route('/{id}', name: 'app_compte_show', methods: ['GET'])]
    public function show(Compte $compte): Response
    {
        return $this->render('admin/compte/show.html.twig', [
            'compte' => $compte,
        ]);
    }

    // Modifier un compte existant
    #[Route('/{id}/edit', name: 'app_compte_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Compte $compte, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CompteType::class, $compte);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $entityManager->flush();
                $this->addFlash('success', 'Le compte a été modifié avec succès.');
                return $this->redirectToRoute('app_compte_index', [], Response::HTTP_SEE_OTHER);
            } catch (UniqueConstraintViolationException $e) {
                $this->addFlash('error', 'Une erreur est survenue : Un compte avec le même email existe déjà.');
            } catch (ORMException $e) {
                $this->addFlash('error', 'Une erreur est survenue lors de la modification du compte : ' . $e->getMessage());
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur inattendue est survenue : ' . $e->getMessage());
            }
        }

        return $this->render('admin/compte/edit.html.twig', [
            'compte' => $compte,
            'form' => $form,
        ]);
    }

    // Supprimer un compte
    #[Route('/{id}', name: 'app_compte_delete', methods: ['POST'])]
    public function delete(Request $request, Compte $compte, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $compte->getId(), $request->getPayload()->getString('_token'))) {
            try {
                $entityManager->remove($compte);
                $entityManager->flush();
                $this->addFlash('success', 'Le compte a été supprimé avec succès.');
            } catch (ORMException $e) {
                $this->addFlash('error', 'Une erreur est survenue lors de la suppression du compte : ' . $e->getMessage());
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur inattendue est survenue : ' . $e->getMessage());
            }
        } else {
            $this->addFlash('error', 'Token CSRF invalide.');
        }

        return $this->redirectToRoute('app_compte_index', [], Response::HTTP_SEE_OTHER);
    }
}
