<?php

namespace App\Form;

use App\Entity\Chambre;
use App\Entity\Hotel;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

/**
 * Formulaire pour la gestion des entités Chambre.
 *
 * Ce formulaire permet de créer et modifier les informations d'une chambre,
 * telles que son étage, son type, le nombre de lits et l'hôtel auquel elle appartient.
 */
class ChambreType extends AbstractType
{
    /**
     * Construit le formulaire pour l'entité Chambre.
     *
     * @param FormBuilderInterface $builder Le constructeur de formulaire.
     * @param array $options Les options du formulaire.
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('etage', IntegerType::class, [
                'label' => 'Étage',
                'attr' => ['min' => 0, 'placeholder' => 'Ex: 3'],
            ])
            ->add('type', ChoiceType::class, [
                'label' => 'Type de chambre',
                'choices' => [
                    'Simple' => 'single',
                    'Double' => 'double',
                    'Suite' => 'suite',
                ],
                'placeholder' => 'Choisir un type',
            ])
            ->add('nombreLit', IntegerType::class, [
                'label' => 'Nombre de lits',
                'attr' => ['min' => 1, 'placeholder' => 'Ex: 2'],
            ])
            ->add('hotel', EntityType::class, [
                'class' => Hotel::class,
                'choice_label' => 'nom', // Affiche le nom de l'hôtel dans la liste déroulante
                'label' => 'Hôtel',
                'placeholder' => 'Sélectionner un hôtel',
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
            'data_class' => Chambre::class,
        ]);
    }
}
