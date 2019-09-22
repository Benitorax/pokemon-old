<?php

namespace App\Form\Command;

use App\Manager\BattleManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class RestorePokemonsType extends AbstractType
{
    private $battleManager;

    public function __construct(BattleManager $battleManager)
    {
        $this->battleManager = $battleManager;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('restorePokemons', SubmitType::class, [
                'label' => 'Restore your pokemons for free',
                'attr' => [
                    'class' => "btn btn-outline-success"
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
