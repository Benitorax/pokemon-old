<?php

namespace App\Handler;

use App\Form\Command\AttackOrPokeballType;
use App\Form\Command\SelectPokemonType;
use App\Form\Command\TravelType;
use App\Manager\BattleManager;
use App\Manager\CommandManager;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Request;

class AdventureHandler
{
    private $battleManager;
    private $manager;
    private $commandManager;

    public function __construct(BattleManager $battleManager, ObjectManager $manager, CommandManager $commandManager)
    {
        $this->battleManager = $battleManager;
        $this->manager = $manager;
        $this->commandManager = $commandManager;
    }
    
    public function handleRequest(Request $request)
    {
        $command = $request->request->keys()[0];
        $form = $this->commandManager->createFormByCommand($command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->handle($form);
        }

        return $this->handleTravel();
    }


    public function handle($form) 
    {
        $command = $form->getClickedButton()->getName();
        switch($command)
        {
            case 'travel':
                return $this->handleTravel();
            case 'submitPokemon':
                return $this->handleSelectPokemon($form);
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
        $messages[] = "You're located around <strong>". $battle->getArena()->getName() ."</strong> area.";
        $messages[] = "And you come across... <strong>". $battle->getOpponentTeam()->getCurrentFighter()->getName() ."</strong>!";

        return [
            'messages' => $messages,
            'opponent' => $battle->getOpponentTeam(),
            'player' => $battle->getPlayerTeam(),
            'form' => $this->commandManager->createCommandForm(SelectPokemonType::class)
        ];
    }

    public function handleSelectPokemon($data) 
    {
        $this->battleManager->addFighterSelected($data->get('selectPokemon')->getData());
        $battle = $this->battleManager->getCurrentBattle();

        $messages[] = "You have selected <strong>". $battle->getPlayerTeam()->getCurrentFighter()->getName() ."</strong>!";

        return [
            'messages' => $messages,
            'opponent' => $battle->getOpponentTeam(),
            'player' => $battle->getPlayerTeam(),
            'form' => $this->commandManager->createCommandForm(AttackOrPokeballType::class)
        ];

    }

    public function handleAttack() 
    {
    }

    public function handleThrowPokeball() 
    {
        $result = $this->battleManager->manageThrowPokeball();
        $battle = $this->battleManager->getCurrentBattle();

        if($result == 'success') {
        
            $messages[] = "<strong>".$battle->getOpponentTeam()->getCurrentFighter()->getName() ."</strong> was captured!";
            $form = $this->commandManager->createCommandForm(TravelType::class);
            $this->battleManager->clearLastBattle();

            return [
                'messages' => $messages,
                'opponent' => null,
                'player' => null,
                'form' => $form
            ];
        
        } else {
        
            if($result == 'failed') { $messages[] = "You missed!"; }
            else { $messages[] = 'You don\'t have any pokeball!'; }
            $form = $this->commandManager->createCommandForm(AttackOrPokeballType::class);
            
            return [
                'messages' => $messages,
                'opponent' => $battle->getOpponentTeam(),
                'player' => $battle->getPlayerTeam(),
                'form' => $form
            ];
        }
    }
}