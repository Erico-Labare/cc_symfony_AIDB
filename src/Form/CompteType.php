<?php

namespace App\Form;

use App\Entity\Compte;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Formulaire pour la gestion des entités Compte.
 *
 * Ce formulaire permet de créer et modifier les informations d'un compte utilisateur,
 * telles que son rôle, son email, son mot de passe et son statut de vérification.
 */
class CompteType extends AbstractType
{
    /**
     * Construit le formulaire pour l'entité Compte.
     *
     * @param FormBuilderInterface $builder Le constructeur de formulaire.
     * @param array $options Les options du formulaire.
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'Adresse Email',
                'attr' => ['placeholder' => 'adresse@example.com'],
            ])
            ->add('plainPassword', PasswordType::class, [
                'mapped' => false, // Ce champ n'est pas directement mappé à une propriété de l'entité
                'required' => $options['is_new'], // Requis seulement lors de la création d'un nouveau compte
                'label' => 'Mot de passe',
                'attr' => ['autocomplete' => 'new-password', 'placeholder' => 'Laissez vide pour ne pas changer'],
                'constraints' => [
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Votre mot de passe doit contenir au moins {{ limit }} caractères',
                        'max' => 4096, // Longueur max de Symfony Security
                    ]),
                    // NotBlank est ajouté conditionnellement si 'is_new' est vrai
                    ($options['is_new'] ? new NotBlank(['message' => 'Veuillez entrer un mot de passe']) : null),
                ],
            ])
            ->add('role', ChoiceType::class, [
                'label' => 'Rôle',
                'choices' => [
                    'Utilisateur' => 'ROLE_USER',
                    'Administrateur' => 'ROLE_ADMIN',
                ],
                'placeholder' => 'Choisir un rôle',
            ])
            ->add('isVerified', CheckboxType::class, [
                'label' => 'Compte vérifié',
                'required' => false, // Le champ n'est pas obligatoire
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
            'data_class' => Compte::class,
            'is_new' => true, // Option pour indiquer si c'est un nouveau compte (pour la validation du mot de passe)
        ]);
    }
}
