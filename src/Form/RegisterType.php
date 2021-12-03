<?php

namespace App\Form;

use App\Entity\RegisterUserDTO;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;

class RegisterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', TextType::class, [
                //'attr' => ['class' => 'Username'],
                'required' => true
            ])
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'The password fields must match.',
                'options' => ['attr' => ['class' => 'password-field']],
                'required' => true,
                'first_options'  => ['label' => 'Password'],
                'second_options' => ['label' => 'Repeat Password'],
            ])
            ->add('email', TextType::class, [
                'attr' => ['class' => 'Username'],
                'required' => true,
            ])
            ->add('pokemonApiId', ChoiceType::class, [
                'choices'  => [
                    'Bulbasaur' => 1,
                    'Charmander' => 4,
                    'Squirtle' => 7,
                ],
                'required' => true,
                'label' => 'Pokemon',
                'placeholder' => 'Choose your Pokemon',
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Register',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
            'data_class' => RegisterUserDTO::class
        ]);
    }
}
