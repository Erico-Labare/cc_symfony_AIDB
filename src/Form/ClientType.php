<?php

namespace App\Form;

use App\Entity\Client;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Formulaire pour la gestion des entités Client.
 *
 * Ce formulaire permet de créer et modifier les informations d'un client,
 * telles que son nom, son adresse, son email et son numéro de téléphone.
 */
class ClientType extends AbstractType
{
    /**
     * Construit le formulaire pour l'entité Client.
     *
     * @param FormBuilderInterface $builder Le constructeur de formulaire.
     * @param array $options Les options du formulaire.
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'attr' => ['class' => 'form-control', 'placeholder' => 'Nom complet'],
                'label' => 'Nom',
            ])
            ->add('adresse', TextareaType::class, [
                'attr' => ['class' => 'form-control', 'placeholder' => 'Adresse postale complète'],
                'label' => 'Adresse',
            ])
            ->add('email', EmailType::class, [
                'attr' => ['class' => 'form-control', 'placeholder' => 'adresse@example.com'],
                'label' => 'Email',
            ])
            ->add('telephone', TextType::class, [
                'attr' => ['class' => 'form-control', 'placeholder' => '0123456789'],
                'label' => 'Téléphone',
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
            'data_class' => Client::class,
        ]);
    }
}
