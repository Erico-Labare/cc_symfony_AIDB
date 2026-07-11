<?php

namespace App\Form;

use App\Entity\Reservation;
use App\Entity\Compte;
use App\Entity\Client;
use App\Entity\Chambre;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReservationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('dateDebut', DateTimeType::class, [
                'widget' => 'single_text',
                'html5' => true,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('dateFin', DateTimeType::class, [
                'widget' => 'single_text',
                'html5' => true,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('commentaire', TextareaType::class, [
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('compte', EntityType::class, [
                'class' => Compte::class,
                'choice_label' => 'email', // Assuming email is a good identifier for Compte
                'attr' => ['class' => 'form-control'],
            ])
            ->add('client', EntityType::class, [
                'class' => Client::class,
                'choice_label' => 'email', // Assuming email is a good identifier for Client
                'attr' => ['class' => 'form-control'],
            ])
            ->add('chambre', EntityType::class, [
                'class' => Chambre::class,
                'choice_label' => 'id', // Or a custom method like 'getNomComplet' if available
                'attr' => ['class' => 'form-control'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reservation::class,
        ]);
    }
}
