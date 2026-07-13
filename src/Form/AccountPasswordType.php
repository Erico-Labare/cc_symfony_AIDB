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
 * Formulaire pour la modification du mot de passe d'un compte utilisateur.
 *
 * Ce formulaire permet à un utilisateur de changer son mot de passe en
 * fournissant son ancien mot de passe et en définissant un nouveau.
 */
class AccountPasswordType extends AbstractType
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
            ->add('currentPassword', PasswordType::class, [
                'mapped' => false, // Ce champ n'est pas directement mappé à une propriété de l'entité
                'label' => 'Ancien mot de passe',
                'attr' => [
                    'class' => 'form-control',
                    'autocomplete' => 'current-password', // Aide les navigateurs à suggérer le mot de passe actuel
                ],
                'constraints' => [
                    new NotBlank(message: 'Veuillez saisir votre ancien mot de passe.'),
                ],
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'mapped' => false, // Ce champ n'est pas directement mappé à une propriété de l'entité
                'invalid_message' => 'Les deux mots de passe doivent être identiques.', // Message si les deux champs ne correspondent pas
                'first_options' => [
                    'label' => 'Nouveau mot de passe',
                    'attr' => [
                        'class' => 'form-control',
                        'autocomplete' => 'new-password', // Aide les navigateurs à suggérer un nouveau mot de passe
                    ],
                    'constraints' => [
                        new NotBlank(message: 'Veuillez saisir un nouveau mot de passe.'),
                        new Length(
                            min: 6,
                            minMessage: 'Le mot de passe doit contenir au moins {{ limit }} caractères.',
                            max: 4096, // Longueur maximale supportée par Symfony Security
                        ),
                    ],
                ],
                'second_options' => [
                    'label' => 'Confirmer le nouveau mot de passe',
                    'attr' => [
                        'class' => 'form-control',
                        'autocomplete' => 'new-password',
                    ],
                ],
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
            // Aucune option spécifique n'est définie pour ce formulaire,
            // car il ne mappe pas directement une entité.
        ]);
    }
}
