<?php

namespace App\Form;

use App\Entity\Hotel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

/**
 * Formulaire pour la gestion des entités Hotel.
 *
 * Ce formulaire permet de créer et modifier les informations d'un hôtel,
 * telles que son nom, son adresse et sa catégorie.
 */
class HotelType extends AbstractType
{
    /**
     * Construit le formulaire pour l'entité Hotel.
     *
     * @param FormBuilderInterface $builder Le constructeur de formulaire.
     * @param array $options Les options du formulaire.
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom de l\'hôtel',
                'attr' => ['placeholder' => 'Ex: Grand Hôtel de Paris']
            ])
            ->add('adresse', TextareaType::class, [
                'label' => 'Adresse',
                'attr' => ['placeholder' => 'Ex: 123 Rue de la Paix, 75002 Paris']
            ])
            ->add('categorie', TextType::class, [
                'label' => 'Catégorie (étoiles)',
                'attr' => ['placeholder' => 'Ex: ***']
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
            'data_class' => Hotel::class,
        ]);
    }
}
