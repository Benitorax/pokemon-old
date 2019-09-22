<?php

namespace App\Form\Command;

use App\Manager\BattleManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;

class TournamentBattleType extends AbstractType
{
    private $battleManager;
    private $user;

    public function __construct(BattleManager $battleManager, Security $security)
    {
        $this->battleManager = $battleManager;
        $this->user = $security->getUser();
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->remove('leave')->remove('throwPokeball');
    }

    public function getParent()
    {
        return AdventureBattleType::class;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
