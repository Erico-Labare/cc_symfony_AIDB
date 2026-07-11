<?php

namespace App\Form;

use App\Entity\Client;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ClientType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'attr' => ['class' => 'form-control'],
                'label' => 'Nom',
            ])
            ->add('adresse', TextareaType::class, [
                'attr' => ['class' => 'form-control'],
                'label' => 'Adresse',
            ])
            ->add('email', EmailType::class, [
                'attr' => ['class' => 'form-control'],
                'label' => 'Email',
            ])
            ->add('telephone', TextType::class, [
                'attr' => ['class' => 'form-control'],
                'label' => 'Téléphone',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Client::class,
        ]);
    }
}
