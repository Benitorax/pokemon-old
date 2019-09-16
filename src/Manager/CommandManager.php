<?php
namespace App\Manager;

use App\Form\Command\NextType;
use App\Form\Command\TravelType;
use App\Form\Command\SelectPokemonType;
use App\Form\Command\ThrowPokeballType;
use App\Form\Command\AttackOrPokeballType;
use Symfony\Component\Form\FormFactoryInterface;

class CommandManager
{
    const TRAVEL = 1;
    const SELECT_POKEMON = 2;
    const POKEBALL_OR_ATTACK = 3;
    const NEXT = 5;

    private $formFatory;

    public function __construct(FormFactoryInterface $formFatory) 
    {
        $this->formFatory = $formFatory;
    }

    public function createCommandForm(string $type, $data = null, array $options = []) 
    {
        return $this->formFatory->create($type, $data, $options);
    }

    public function createFormByCommand(string $command) 
    {
        switch($command)
        {
            case 'travel':
                return $this->createCommandForm(TravelType::class);
            case 'select_pokemon':
                return $this->createCommandForm(SelectPokemonType::class);
            case 'attack_or_pokeball':
                return $this->createCommandForm(AttackOrPokeballType::class);
            case 'throw_pokeball':
                return $this->createCommandForm(ThrowPokeballType::class);
            case 'next':
                return $this->createCommandForm(NextType::class);
        }

        return $this->createCommandForm(TravelType::class);
    }
}