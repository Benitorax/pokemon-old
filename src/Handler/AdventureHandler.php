<?php

namespace App\Handler;

use App\Form\Command\AttackOrPokeballType;
use App\Form\Command\SelectPokemonType;
use App\Form\Command\ThrowPokeballType;
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
            case 'leave':
                return $this->handleLeave();
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
        $damage = $this->battleManager->manageAttackOpponent();
        $battle = $this->battleManager->getCurrentBattle();
        $form = $this->commandManager->createCommandForm(AttackOrPokeballType::class);
        $messages[] = "<strong>". $this->battleManager->getPlayerFighter()->getName() .
                      "</strong> attacks <strong>".
                      $this->battleManager->getOpponentFighter()->getName()."</strong>!";
        $messages[] = "It inflicts ".$damage." points of damage.";

        if($this->battleManager->getOpponentFighter()->getIsSleep()) {
            $messages[] = "<strong>".$this->battleManager->getOpponentFighter()->getName()."</strong> has fainted.";
            $form = $this->commandManager->createCommandForm(ThrowPokeballType::class);
        }

        return [
            'messages' => $messages,
            'opponent' => $battle->getOpponentTeam(),
            'player' => $battle->getPlayerTeam(),
            'form' => $form
        ];
    }

    public function handleThrowPokeball() 
    {
        $result = $this->battleManager->manageThrowPokeball();
        $battle = $this->battleManager->getCurrentBattle();
        $opponentTeam = $battle->getOpponentTeam();
        $playerTeam =$battle->getPlayerTeam();

        if($result == 'success') {
        
            $form = $this->commandManager->createCommandForm(TravelType::class);
            $data = $this->battleManager->manageLevelupForAdventure();
            $messages[] = "<strong>". $opponentTeam->getCurrentFighter()->getName() ."</strong> was captured!";
            $messages[] = "<strong>". $data['name'] ."</strong> levels up to ".
                          $playerTeam->getCurrentFighter()->getLevel()." (+". $data['increasedLevel'] .").";
            if($data['hasEvolved']) {
                $spriteFrontUrl = $playerTeam->getCurrentFighter()->getspriteFrontUrl();
                $messages[] = "<strong>". $data['name'] ."</strong> evolves to <strong>".
                              $data['newName'].'</strong>.';
            }
            $this->clear();
            $opponentTeam = null;
            $playerTeam = null;
    
        
        } else {
        
            if($result == 'failed') { $messages[] = "You missed!"; }
            else { $messages[] = 'You don\'t have any pokeball!'; }

            if($opponentTeam->getCurrentFighter()->getIsSleep()) {
                $form = $this->commandManager->createCommandForm(ThrowPokeballType::class);
            }else {
                $form = $this->commandManager->createCommandForm(AttackOrPokeballType::class);
            }
        }

        return [
            'messages' => $messages,
            'opponent' => $opponentTeam,
            'player' => $playerTeam,
            'form' => $form,
            'centerImageUrl' => $spriteFrontUrl ?? null
        ];
    }

    public function handleLeave() {
        $battle = $this->battleManager->getCurrentBattle();
        $opponentTeam = null;
        $playerTeam = null;
        $result = $this->battleManager->manageLeave();

        if($result) {
            $messages[] = "You leave with success!";
            $form = $this->commandManager->createCommandForm(TravelType::class);
            $this->clear();
        } else {
            $messages[] = "<strong>".$battle->getOpponentTeam()->getCurrentFighter()->getName() ."</strong> has prevented your escape!";
            $form = $this->commandManager->createCommandForm(AttackOrPokeballType::class);
            $opponentTeam = $battle->getOpponentTeam();
            $playerTeam = $battle->getPlayerTeam(); 
        }

        return [
            'messages' => $messages,
            'opponent' => $opponentTeam,
            'player' => $playerTeam,
            'form' => $form
        ];
    }
}