<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class ShopType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('pokeball', ChoiceType::class, [
                'label' => 'Pokeball (10$)',
                'choices' => range(0,100),
                'attr' => [
                    'class' => "custom-select"
                ]
            ])
            ->add('healthPotion', ChoiceType::class, [
                'label' => 'Health potion (15$)',
                'choices' => range(0,100),
                'attr' => [
                    'class' => "custom-select"
                ]
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Buy',
                'attr' => [
                    'class' => "btn btn-primary"
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
