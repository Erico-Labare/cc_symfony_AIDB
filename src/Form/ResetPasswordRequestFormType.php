<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Formulaire de demande de réinitialisation de mot de passe.
 *
 * Ce formulaire permet à un utilisateur de soumettre son adresse email
 * pour initier le processus de réinitialisation de mot de passe.
 */
class ResetPasswordRequestFormType extends AbstractType
{
    /**
     * Construit le formulaire de demande de réinitialisation de mot de passe.
     *
     * @param FormBuilderInterface $builder Le constructeur de formulaire.
     * @param array $options Les options du formulaire.
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'attr' => ['autocomplete' => 'email', 'class' => 'form-control', 'placeholder' => 'Votre adresse e-mail'],
                'constraints' => [
                    new NotBlank(message: 'Veuillez entrer votre adresse e-mail.'),
                ],
                'label' => 'Adresse E-mail',
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
