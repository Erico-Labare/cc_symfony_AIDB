<?php

namespace App\Form;

use App\Entity\Hotel;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType; // Changed from DateTimeType
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReservationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('hotel', EntityType::class, [
                'class' => Hotel::class,
                'choice_label' => 'nom',
                'placeholder' => 'Choisir un hôtel',
                'required' => true,
            ])
            ->add('dateDebut', DateType::class, [ // Changed to DateType
                'widget' => 'single_text',
                'html5' => true,
                'required' => true,
                // 'mapped' => false, // Not needed for DateType when handling form data directly
            ])
            ->add('dateFin', DateType::class, [ // Changed to DateType
                'widget' => 'single_text',
                'html5' => true,
                'required' => true,
                // 'mapped' => false, // Not needed for DateType when handling form data directly
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // 'data_class' => Reservation::class,
        ]);
    }
}
