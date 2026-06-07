<?php

namespace App\Form;

use App\Entity\Chambre;
use App\Entity\Hotel;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
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
            ->add('chambre', EntityType::class, [
                'class' => Chambre::class,
                'choice_label' => function (Chambre $chambre) {
                    return sprintf('Chambre %d - Type: %s - %d lit(s)', $chambre->getId(), $chambre->getType(), $chambre->getNombreLit());
                },
                'placeholder' => 'Choisir une chambre',
                'required' => true,
                'mapped' => false,
            ])
            ->add('dateDebut', DateTimeType::class, [
                'widget' => 'single_text',
                'required' => true,
                'mapped' => false,
            ])
            ->add('dateFin', DateTimeType::class, [
                'widget' => 'single_text',
                'required' => true,
                'mapped' => false,
            ])
            ->add('commentaire', TextareaType::class, [
                'required' => false,
                'attr' => ['rows' => 4],
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
