<?php

namespace App\Form;

use App\Entity\Compte;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

// Formulaire pour la gestion des comptes
class CompteType extends AbstractType
{
    // Construire le formulaire avec les champs du compte
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('role', ChoiceType::class, [
                'choices' => [
                    'User' => 'ROLE_USER',
                    'Admin' => 'ROLE_ADMIN',
                ],
            ])
            ->add('email')
            ->add('plainPassword', PasswordType::class, [
                'mapped' => false,
                'required' => $options['is_new'], // Make required conditional
            ])
            ->add('isVerified')
        ;
    }

    // Configurer les options du formulaire
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Compte::class,
            'is_new' => true, // Default to true for new accounts
        ]);
    }
}
