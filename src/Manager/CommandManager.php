<?php
namespace App\Manager;

use App\Form\Command\NextType;
use App\Form\Command\TravelType;
use App\Form\Command\SelectPokemonType;
use App\Form\Command\ThrowPokeballType;
use App\Form\Command\AdventureBattleType;
use App\Form\Command\RestorePokemonsType;
use App\Form\Command\TournamentBattleType;
use Symfony\Component\Form\FormFactoryInterface;
use App\Form\Command\SelectPokemonForTournamentType;

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
            case 'adventure_battle':
                return $this->createCommandForm(AdventureBattleType::class);
            case 'tournament_battle':
                return $this->createCommandForm(TournamentBattleType::class);
            case 'throw_pokeball':
                return $this->createCommandForm(ThrowPokeballType::class);
            case 'next':
                return $this->createCommandForm(NextType::class);
            case 'select_pokemon_for_tournament':
                return $this->createCommandForm(SelectPokemonForTournamentType::class);
            case 'restore_pokemons':
                return $this->createCommandForm(RestorePokemonsType::class);
        }

        return $this->createCommandForm(TravelType::class);
    }
}