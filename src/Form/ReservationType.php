<?php

namespace App\Form;

use App\Entity\Reservation;
use App\Entity\Compte;
use App\Entity\Client;
use App\Entity\Chambre;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Formulaire pour la gestion des entités Reservation.
 *
 * Ce formulaire permet de créer et modifier les informations d'une réservation,
 * telles que les dates de début et de fin, un commentaire, et les entités
 * associées (Compte, Client, Chambre).
 */
class ReservationType extends AbstractType
{
    /**
     * Construit le formulaire pour l'entité Reservation.
     *
     * @param FormBuilderInterface $builder Le constructeur de formulaire.
     * @param array $options Les options du formulaire.
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('dateDebut', DateTimeType::class, [
                'widget' => 'single_text',
                'html5' => true,
                'attr' => ['class' => 'form-control'],
                'label' => 'Date de début',
            ])
            ->add('dateFin', DateTimeType::class, [
                'widget' => 'single_text',
                'html5' => true,
                'attr' => ['class' => 'form-control'],
                'label' => 'Date de fin',
            ])
            ->add('commentaire', TextareaType::class, [
                'required' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => 'Commentaire ou demande spéciale'],
                'label' => 'Commentaire',
            ])
            ->add('compte', EntityType::class, [
                'class' => Compte::class,
                'choice_label' => 'email', // Affiche l'email du compte dans la liste déroulante
                'attr' => ['class' => 'form-control'],
                'label' => 'Compte utilisateur',
                'placeholder' => 'Sélectionner un compte',
            ])
            ->add('client', EntityType::class, [
                'class' => Client::class,
                'choice_label' => 'email', // Affiche l'email du client dans la liste déroulante
                'attr' => ['class' => 'form-control'],
                'label' => 'Client',
                'placeholder' => 'Sélectionner un client',
            ])
            ->add('chambre', EntityType::class, [
                'class' => Chambre::class,
                'choice_label' => function (Chambre $chambre) {
                    return 'Chambre ID: ' . $chambre->getId() . ' (Type: ' . $chambre->getType() . ', Étage: ' . $chambre->getEtage() . ')';
                }, // Affiche un libellé plus descriptif pour la chambre
                'attr' => ['class' => 'form-control'],
                'label' => 'Chambre',
                'placeholder' => 'Sélectionner une chambre',
            ]);
    }

    /**
     * Configure les options par défaut pour ce type de formulaire.
     *
     * @param OptionsResolver $resolver Le résolveur d'options.
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reservation::class,
        ]);
    }
}
