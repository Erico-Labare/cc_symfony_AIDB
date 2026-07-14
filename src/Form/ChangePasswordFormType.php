<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Formulaire pour la modification du mot de passe suite à une demande de réinitialisation.
 *
 * Ce formulaire est utilisé dans le processus de réinitialisation de mot de passe
 * pour permettre à l'utilisateur de définir un nouveau mot de passe.
 */
class ChangePasswordFormType extends AbstractType
{
    /**
     * Construit le formulaire de modification du mot de passe.
     *
     * @param FormBuilderInterface $builder Le constructeur de formulaire.
     * @param array $options Les options du formulaire.
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options' => [
                    'attr' => ['autocomplete' => 'new-password', 'class' => 'form-control'],
                    'constraints' => [
                        new NotBlank(message: 'Veuillez entrer un mot de passe.'),
                        new Length(
                            min: 6,
                            minMessage: 'Votre mot de passe doit contenir au moins {{ limit }} caractères.',
                            max: 4096, // Longueur maximale autorisée par Symfony pour des raisons de sécurité
                        ),
                    ],
                    'label' => 'Nouveau mot de passe',
                ],
                'second_options' => [
                    'attr' => ['autocomplete' => 'new-password', 'class' => 'form-control'],
                    'label' => 'Confirmer le mot de passe',
                ],
                'invalid_message' => 'Les champs du mot de passe doivent correspondre.',
                // Au lieu d'être directement défini sur l'objet,
                // ce champ est lu et encodé dans le contrôleur.
                'mapped' => false,
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
            // Aucune option spécifique n'est définie pour ce formulaire,
            // car il ne mappe pas directement une entité.
        ]);
    }
}
