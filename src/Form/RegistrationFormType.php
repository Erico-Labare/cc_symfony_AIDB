<?php

namespace App\Form;

use App\Entity\Compte;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder

            ->add('email', EmailType::class, [

                'label' => 'Adresse email',

                'attr' => [

                    'class' => 'form-control',

                    'placeholder' => 'nom@exemple.fr',

                    'autocomplete' => 'email',

                ],

            ])

            ->add('plainPassword', PasswordType::class, [

                'mapped' => false,

                'label' => 'Mot de passe',

                'attr' => [

                    'class' => 'form-control',

                    'placeholder' => 'Choisissez un mot de passe',

                    'autocomplete' => 'new-password',

                ],

                'constraints' => [

                    new NotBlank(message: 'Veuillez entrer un mot de passe.'),

                    new Length(

                        min: 6,

                        minMessage: 'Votre mot de passe doit contenir au moins {{ limit }} caractères.',

                        max: 4096,

                    ),

                ],

            ])

            ->add('agreeTerms', CheckboxType::class, [

                'mapped' => false,

                'label' => "J'accepte les conditions d'utilisation",

                'attr' => [

                    'class' => 'form-check-input',

                ],

                'constraints' => [

                    new IsTrue(message: "Vous devez accepter les conditions d'utilisation."),

                ],

            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Compte::class,
        ]);
    }
}
