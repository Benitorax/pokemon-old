<?php

namespace App\Handler;

use App\Form\Command\NextType;
use App\Manager\BattleManager;
use App\Manager\CommandManager;
use App\Form\Command\TravelType;
use App\Form\Command\SelectPokemonType;
use App\Form\Command\AdventureBattleType;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;

class AdventureHandler
{
    protected $battleManager;
    protected $manager;
    protected $commandManager;

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
            if($battle = $this->battleManager->getCurrentBattle()) {
                if($battle->getTurn() == 'opponent' && $form->getClickedButton()->getName() == 'attack') 
                {
                    return $this->handleNext();
                }
            }
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
            case 'selectPokemon':
                return $this->handleSelectPokemon($form);
            case 'attack':
                return $this->handleAttack();
            case 'throwPokeball':
                return $this->handleThrowPokeball();
            case 'leave':
                return $this->handleLeave();
            case 'heal':
                return $this->handleHeal();
            case 'next':
                return $this->handleNext();
        }
    }

    public function clear()
    {
        return $this->battleManager->clearLastBattle();
    }

    public function handleTravel() 
    {
        $this->clear();
        $battle = $this->battleManager->createAdventureBattle();
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
        $this->battleManager->addFighterSelected($data->get('choicePokemon')->getData());
        $battle = $this->battleManager->getCurrentBattle();
        $messages[] = "You have selected <strong>". $battle->getPlayerTeam()->getCurrentFighter()->getName() ."</strong>!";

        return [
            'messages' => $messages,
            'opponent' => $battle->getOpponentTeam(),
            'player' => $battle->getPlayerTeam(),
            'form' => $this->commandManager->createCommandForm(AdventureBattleType::class)
        ];

    }

    public function handleAttack() 
    {
        $damage = $this->battleManager->manageAttackOpponent();
        $battle = $this->battleManager->getCurrentBattle();
        $messages[] = "<strong>". $this->battleManager->getPlayerFighter()->getName() .
                      "</strong> attacks <strong>".
                      $this->battleManager->getOpponentFighter()->getName()."</strong>!";
        $messages[] = "It inflicts ".$damage." points of damage.";

        if($this->battleManager->getOpponentFighter()->getIsSleep()) {
            $messages[] = "<strong>".$this->battleManager->getOpponentFighter()->getName()."</strong> has fainted.";
        }

        return [
            'messages' => $messages,
            'opponent' => $battle->getOpponentTeam(),
            'player' => $battle->getPlayerTeam(),
            'form' => $this->commandManager->createCommandForm(NextType::class)
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

            $form = $this->commandManager->createCommandForm(AdventureBattleType::class);
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
            $form = $this->commandManager->createCommandForm(AdventureBattleType::class);
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

    public function handleHeal() {
        /** @var BattleManager $this->battleManager */
        $hpRange = $this->battleManager->manageHealPlayerFighter();
        $battle = $this->battleManager->getCurrentBattle();
        $opponentTeam = $battle->getOpponentTeam();
        $playerTeam = $battle->getPlayerTeam();

        if($hpRange) {
            $messages[] = "<strong>".$playerTeam->getCurrentFighter()->getName() ."</strong> has been healed (+".$hpRange."HP)!";
        } else {
            $messages[] = "You don't have any health potions!";
        }

        return [
            'messages' => $messages,
            'opponent' => $opponentTeam,
            'player' => $playerTeam,
            'form' => $this->commandManager->createCommandForm(AdventureBattleType::class)
        ];
    }

    public function handleNext() {
        /** @var BattleManager $this->battleManager */
        $damage = $this->battleManager->manageDamagePlayerFighter();
        $battle = $this->battleManager->getCurrentBattle();
        $opponentFighter = $battle->getOpponentTeam()->getCurrentFighter();
        $playerFighter = $battle->getPlayerTeam()->getCurrentFighter(); 
        $form = $this->commandManager->createCommandForm(AdventureBattleType::class);

        if($playerFighter->getIsSleep()) {
            $form = $this->commandManager->createCommandForm(TravelType::class);
            $messages[] = "<strong>". $opponentFighter->getName() ."</strong> has knocked <strong>". 
                            $playerFighter->getName() ."</strong> out (-".$damage." HP).";

            $messages[] = "Besides, <strong>". $opponentFighter->getName() ."</strong> has escaped.";
            $this->clear();
        } else {
            $messages[] = "<strong>". $opponentFighter->getName() ."</strong> attacks <strong>". $playerFighter->getName() ."</strong>"; 
            $messages[] = "It inflicts ".$damage." points of damage.";
        }

        return [
            'messages' => $messages,
            'opponent' => $battle->getOpponentTeam(),
            'player' => $battle->getPlayerTeam(),
            'form' => $form
        ];
    }
}