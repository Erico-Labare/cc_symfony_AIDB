<?php

namespace App\Form;

use App\Entity\Hotel;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Formulaire de recherche pour les réservations.
 *
 * Ce formulaire permet aux utilisateurs de rechercher des chambres disponibles
 * en spécifiant un hôtel, une date de début et une date de fin.
 */
class ReservationFormType extends AbstractType
{
    /**
     * Construit le formulaire de recherche de réservation.
     *
     * @param FormBuilderInterface $builder Le constructeur de formulaire.
     * @param array $options Les options du formulaire.
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('hotel', EntityType::class, [
                'class' => Hotel::class,
                'choice_label' => 'nom', // Affiche le nom de l'hôtel dans la liste déroulante
                'placeholder' => 'Choisir un hôtel',
                'required' => true,
                'label' => 'Hôtel',
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez sélectionner un hôtel.']),
                ],
            ])
            ->add('dateDebut', DateType::class, [
                'widget' => 'single_text',
                'html5' => true,
                'required' => true,
                'label' => 'Date de début',
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez sélectionner une date de début.']),
                    new GreaterThanOrEqual([
                        'value' => 'today',
                        'message' => 'La date de début ne peut pas être antérieure à aujourd\'hui.',
                    ]),
                ],
            ])
            ->add('dateFin', DateType::class, [
                'widget' => 'single_text',
                'html5' => true,
                'required' => true,
                'label' => 'Date de fin',
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez sélectionner une date de fin.']),
                    // La validation que dateFin > dateDebut sera gérée au niveau du contrôleur ou du service
                ],
            ])
        ;
    }

    /**
     * Configure les options par défaut pour ce type de formulaire.
     *
     * @param OptionsResolver $resolver Le résolveur d'options.
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Ce formulaire n'est pas directement mappé à une entité,
            // il sert à collecter des critères de recherche.
            'csrf_protection' => true, // Protection CSRF activée par défaut
            'csrf_field_name' => '_token',
            'csrf_token_id'   => 'reservation_search_item',
        ]);
    }
}
