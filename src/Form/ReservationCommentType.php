<?php

namespace App\Form;

use App\Entity\Reservation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Formulaire pour l'ajout ou la modification du commentaire d'une réservation.
 *
 * Ce formulaire est simple et ne contient qu'un champ pour le commentaire
 * d'une réservation existante.
 */
class ReservationCommentType extends AbstractType
{
    /**
     * Construit le formulaire pour le commentaire de réservation.
     *
     * @param FormBuilderInterface $builder Le constructeur de formulaire.
     * @param array $options Les options du formulaire.
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('commentaire', TextareaType::class, [
                'required' => false, // Le commentaire n'est pas obligatoire
                'label' => 'Commentaire ou demande spéciale',
                'attr' => [
                    'rows' => 4, // Définit la hauteur du champ de texte
                    'placeholder' => 'Ex: Lit bébé, chambre non-fumeur, etc.',
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
            'data_class' => Reservation::class, // Le formulaire est lié à l'entité Reservation
        ]);
    }
}
