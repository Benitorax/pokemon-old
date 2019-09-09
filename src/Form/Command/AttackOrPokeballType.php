<?php

namespace App\Form\Command;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class AttackOrPokeballType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('attack', SubmitType::class, [
                'label' => 'Attack',
                'attr' => [
                    'class' => "btn btn-outline-success"
                ]
            ])
            ->add('throwPokeball', SubmitType::class, [
                'label' => 'Throw pokeball',
                'attr' => [
                    'class' => "btn btn-outline-info"
                ]
            ])
            ->add('leave', SubmitType::class, [
                'label' => 'Leave',
                'attr' => [
                    'class' => "btn btn-outline-danger"
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
