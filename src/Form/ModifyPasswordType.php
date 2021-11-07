<?php

namespace App\Form;

use App\Entity\ModifyPasswordDTO;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Validator\Constraints\Length;

class ModifyPasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('password', PasswordType::class, [
                'label' => 'Actual password'
            ])
            ->add('newPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'The new password must match in both fields.',
                'options' => ['attr' => ['class' => 'password-field']],
                'required' => true,
                'first_options'  => ['label' => 'New Password'],
                'second_options' => ['label' => 'Repeat new password'],
                'constraints' => [
                    new Length([
                        'min' => 6,
                        'max' => 40,
                        'minMessage' => "Your username must be at least {{ limit }} characters long",
                        'maxMessage' => "Your usernamename cannot be longer than {{ limit }} characters"

                    ])
                ]
            ])
            ->add('modify', SubmitType::class, [
                'label' => 'Modify',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
            'data_class' => ModifyPasswordDTO::class
        ]);
    }
}
