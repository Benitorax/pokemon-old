<?php

namespace App\Handler;

use App\Manager\BattleManager;
use Doctrine\Common\Persistence\ObjectManager;


class AdventureHandler
{
    private $battleManager;

    private $manager;

    public function __construct(BattleManager $battleManager, ObjectManager $manager)
    {
        $this->battleManager = $battleManager;
        $this->manager = $manager;
    }
    
    public function handle(string $command, $data) 
    {
        switch($command)
        {
            case 'travel':
                return $this->handleTravel();
            case 'submitPokemon':
                return $this->handleSelectPokemon($data);
            case 'attack':
                return $this->handleAttack();
            case 'throwPokeball':
                return $this->handleThrowPokeball();
        }
    }

    public function clear()
    {
        return $this->battleManager->clearLastBattle();
    }

    public function handleTravel() 
    {
        $battle = $this->battleManager->getCurrentBattle();
        
        if(!$battle)
        {
            $battle = $this->battleManager->createAdventureBattle();
        } 
        $messages = [];
            $messages[] = "You're located around <strong>". $battle->getArena()->getName() ."</strong> area.";
            $messages[] = "And you come across... <strong>". $battle->getOpponentTeam()->getCurrentFighter()->getName() ."</strong>!";

        return [
            'messages' => $messages,
            'opponent' => $battle->getOpponentTeam(),
            'player' => $battle->getPlayerTeam(),
        ];
    }

    public function handleSelectPokemon($data) 
    {
        $this->battleManager->addFighterSelected($data->get('selectPokemon')->getData());
        $battle = $this->battleManager->getCurrentBattle();

        $messages = [];
        $messages[] = "You have selected <strong>". $battle->getPlayerTeam()->getCurrentFighter()->getName() ."</strong>!";

        return [
            'messages' => $messages,
            'opponent' => $battle->getOpponentTeam(),
            'player' => $battle->getPlayerTeam(),
        ];

    }

    public function handleAttack() 
    {
    }

    public function handleThrowPokeball() 
    {
        $battle = $this->battleManager->manageThrowPokeball();
        $messages = [];
        $messages[] = "<strong>".$battle->getPlayerTeam()->getCurrentFighter()->getName() ."</strong> was captured!";
        $messages[] = "You missed!";

        return [
            'messages' => $messages,
            'opponent' => $battle->getOpponentTeam(),
            'player' => $battle->getPlayerTeam(),
        ];
    }
}