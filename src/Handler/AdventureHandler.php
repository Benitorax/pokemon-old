<?php

namespace App\Handler;

use App\Entity\Pokemon;
use App\Manager\BattleManager;
use App\Manager\CommandManager;
use App\Manager\BattleFormManager;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;

class AdventureHandler
{
    protected $battleManager;
    protected $manager;
    protected $commandManager;
    protected $battleFormManager;

    public function __construct(BattleManager $battleManager, ObjectManager $manager, CommandManager $commandManager, BattleFormManager $battleFormManager)
    {
        $this->battleManager = $battleManager;
        $this->manager = $manager;
        $this->commandManager = $commandManager;
        $this->battleFormManager = $battleFormManager;
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
        $user = $battle->getPlayerTeam()->getTrainer();

        return [
            'messages' => [
                'messages' => $messages,
                "textColor" => "text-white"
            ],
            'opponent' => $battle->getOpponentTeam(),
            'player' => null,
            'form' => [$this->battleFormManager->createSelectPokemonField($user)]
        ];
    }

    public function handleSelectPokemon(Pokemon $pokemon) 
    {
        $this->battleManager->addFighterSelected($pokemon);
        $battle = $this->battleManager->getCurrentBattle();
        $messages[] = "You have selected <strong>". $battle->getPlayerTeam()->getCurrentFighter()->getName() ."</strong>!";

        return [
            'messages' => [
                'messages' => $messages,
                "textColor" => "text-white"
            ],
            'opponent' => $battle->getOpponentTeam(),
            'player' => $battle->getPlayerTeam(),
            'form' => $this->battleFormManager->createAdventureButtons()
        ];

    }

    public function handleAttack() 
    {
        if($this->battleManager->getOpponentFighter()->getIsSleep()) {
            $messages[] = "<strong>".$this->battleManager->getOpponentFighter()->getName()."</strong> is already harmless.";
        } else {
            $damage = $this->battleManager->manageAttackOpponent();

            if($this->battleManager->getOpponentFighter()->getIsSleep()) {
                $messages[] = "<strong>". $this->battleManager->getPlayerFighter()->getName() .
                "</strong> attacks <strong>". $this->battleManager->getOpponentFighter()->getName()."</strong> with ".$damage." points of damage!";
                $messages[] = "<strong>".$this->battleManager->getOpponentFighter()->getName()."</strong> has fainted.";
            } else {
                $messages[] = "<strong>". $this->battleManager->getPlayerFighter()->getName() .
                              "</strong> attacks <strong>". $this->battleManager->getOpponentFighter()->getName()."</strong>!";
                $messages[] = "It inflicts ".$damage." points of damage.";
            }    
        }

        $battle = $this->battleManager->getCurrentBattle();
        return [
            'messages' => [
                'messages' => $messages,
                "textColor" => "text-white"
            ],
            'opponent' => $battle->getOpponentTeam(),
            'player' => $battle->getPlayerTeam(),
            'form' => [$this->battleFormManager->createNextButton()]
        ];
    }

    public function handleThrowPokeball() 
    {
        $result = $this->battleManager->manageThrowPokeball();
        $battle = $this->battleManager->getCurrentBattle();
        $opponentTeam = $battle->getOpponentTeam();
        $playerTeam =$battle->getPlayerTeam();

        if($result == 'success') {
        
            $form = [$this->battleFormManager->createTravelButton()];
            $data = $this->battleManager->manageLevelupForAdventure();
            $messages[] = "<strong>". $opponentTeam->getCurrentFighter()->getName() ."</strong> was captured!";
            $textColor = "text-success";
            if($data['hasEvolved']) {
                $spriteFrontUrl = $playerTeam->getCurrentFighter()->getspriteFrontUrl();
                $messages[] = "<strong>". $data['name'] ."</strong> evolves to <strong>".
                              $data['newName'].'</strong> (level: '.$data['newLevel'].").";
            } elseif($data['hasLeveledUp']) {
                $messages[] = "<strong>". $data['name'] ."</strong> levels up to ".
                $playerTeam->getCurrentFighter()->getLevel()." (+". $data['increasedLevel'] .").";
            }
            $this->clear();
            $opponentTeam = null;
            $playerTeam = null;

        } else {
            if($result == 'failed') { $messages[] = "You missed!"; }
            else { $messages[] = 'You don\'t have any pokeball!'; }
            $textColor = 'text-danger';
            $form = $this->battleFormManager->createAdventureButtons();
        }

        return [
            'messages' => [
               'messages' => $messages,
               "textColor" => isset($textColor) ? $textColor : "text-white"
            ],
            'opponent' => $opponentTeam,
            'player' => $playerTeam,
            'form' => $form,
            'centerImageUrl' => $spriteFrontUrl ?? null,
        ];
    }

    public function handleLeave() {
        $battle = $this->battleManager->getCurrentBattle();
        $opponentTeam = null;
        $playerTeam = null;
        $result = $this->battleManager->manageLeave();

        if($result) {
            $messages[] = "You leave with success!";
            $textColor = 'text-success';
            $form = [$this->battleFormManager->createTravelButton()];
            $this->clear();
        } else {
            $messages[] = "<strong>".$battle->getOpponentTeam()->getCurrentFighter()->getName() ."</strong> has prevented your escape!";
            $textColor = 'text-danger';
            $form = $this->battleFormManager->createAdventureButtons();
            $opponentTeam = $battle->getOpponentTeam();
            $playerTeam = $battle->getPlayerTeam(); 
        }

        return [
            'messages' => [
                'messages' => $messages,
                "textColor" => isset($textColor) ? $textColor : "text-white"
            ],
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
            $textColor = 'text-info';
        } else {
            $messages[] = "You don't have any health potions!";
            $textColor = 'text-danger';
        }

        return [
            'messages' => [
                'messages' => $messages,
                "textColor" => isset($textColor) ? $textColor : "text-white"
            ],
            'opponent' => $opponentTeam,
            'player' => $playerTeam,
            'form' => $this->battleFormManager->createAdventureButtons(),
        ];
    }

    public function handleNext() {
        $battle = $this->battleManager->getCurrentBattle();
        $opponentTeam = $battle->getOpponentTeam();
        $playerTeam = $battle->getPlayerTeam();
        $opponentFighter = $battle->getOpponentTeam()->getCurrentFighter();
        $playerFighter = $battle->getPlayerTeam()->getCurrentFighter(); 
        $form = $this->battleFormManager->createAdventureButtons();

        if(!$opponentFighter->getIsSleep()) {
            $damage = $this->battleManager->manageDamagePlayerFighter();

            if($playerFighter->getIsSleep()) {
                $form = [$this->battleFormManager->createTravelButton()];
                $messages[] = "<strong>". $opponentFighter->getName() ."</strong> has knocked <strong>". 
                                $playerFighter->getName() ."</strong> out (-".$damage." HP).";
                $messages[] = "Besides, <strong>". $opponentFighter->getName() ."</strong> has escaped.";
                $textColor = 'text-danger';
                $this->clear();
            } else {
                $messages[] = "<strong>". $opponentFighter->getName() ."</strong> attacks <strong>". $playerFighter->getName() ."</strong>"; 
                $messages[] = "It inflicts ".$damage." points of damage.";
                $textColor = 'text-danger';
            }    
        } else {
            $messages[] = 'Try to capture it!';
        }

        return [
            'messages' => [
                'messages' => $messages,
                "textColor" => isset($textColor) ? $textColor : "text-white"
            ],
            'opponent' => $opponentTeam,
            'player' => $playerTeam,
            'form' => $form,
        ];
    }

    public function checkTurn() {
        if($battle = $this->battleManager->getCurrentBattle()) {
            if($battle->getTurn() == 'opponent' && $form->getClickedButton()->getName() == 'attack') 
            {
                return $this->handleNext();
            }
        }
    }
}