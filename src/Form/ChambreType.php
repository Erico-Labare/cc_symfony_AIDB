<?php

namespace App\Form;

use App\Entity\Chambre;
use App\Entity\Hotel;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

// Formulaire pour la gestion des chambres
class ChambreType extends AbstractType
{
    // Construire le formulaire avec les champs de la chambre
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('etage')
            ->add('type')
            ->add('nombreLit')
            ->add('hotel', EntityType::class, [
                'class' => Hotel::class,
                'choice_label' => 'nom',
            ])
        ;
    }

    // Configurer les options du formulaire
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Chambre::class,
        ]);
    }
}
