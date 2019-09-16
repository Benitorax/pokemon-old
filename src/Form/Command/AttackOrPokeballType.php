<?php

namespace App\Form\Command;

use App\Manager\BattleManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class AttackOrPokeballType extends AbstractType
{
    private $battleManager;

    public function __construct(BattleManager $battleManager)
    {
        $this->battleManager = $battleManager;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if(!$this->isOpponentFighterSleeping()) {
            $builder->add('attack', SubmitType::class, [
                'label' => 'Attack',
                'attr' => [
                    'class' => "btn btn-outline-primary"
                ]
            ]);
        }

        if($this->isPlayerFighterDamaged()) {
            $builder->add('heal', SubmitType::class, [
                'label' => 'Heal',
                'attr' => [
                    'class' => "btn btn-outline-secondary"
                ]
            ]);
        }

        $builder
            ->add('throwPokeball', SubmitType::class, [
                'label' => 'Throw pokeball',
                'attr' => [
                    'class' => "btn btn-outline-success"
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

    public function isOpponentFighterSleeping()
    {
        return $this->battleManager->getOpponentFighter()->getIsSleep();
    }

    public function isPlayerFighterDamaged()
    {
        return $this->battleManager->getPlayerFighter()->getHealthPoint() < 100;
    }
}
