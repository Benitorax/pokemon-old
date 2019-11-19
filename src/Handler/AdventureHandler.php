<?php

namespace App\Handler;

use App\Entity\Pokemon;
use App\Manager\BattleManager;
use App\Manager\BattleFormManager;
use Doctrine\Common\Persistence\ObjectManager;

class AdventureHandler
{
    protected $battleManager;
    protected $manager;
    protected $battleFormManager;

    public function __construct(BattleManager $battleManager, ObjectManager $manager, BattleFormManager $battleFormManager)
    {
        $this->battleManager = $battleManager;
        $this->manager = $manager;
        $this->battleFormManager = $battleFormManager;
    }

    public function clear()
    {
        return $this->battleManager->clearLastBattle();
    }

    public function handleTravel() 
    {
        $this->clear();
        $battle = $this->battleManager->createAdventureBattle();
        $messages[] = 'You\'re located around <strong>'. $battle->getArena()->getName() .'</strong> area.';
        $messages[] = 'And you come across... <strong>'. $battle->getOpponentTeam()->getCurrentFighter()->getName() .'</strong>!';

        return [
            'messages' => [
                'messages' => $messages,
                'textColor' => 'text-white'
            ],
            'opponent' => $battle->getOpponentTeam(),
            'player' => null,
            'form' => [$this->battleFormManager->createSelectPokemonField()]
        ];
    }

    public function handleSelectPokemon(Pokemon $pokemon) 
    {
        $this->battleManager->addFighterSelected($pokemon);
        $battle = $this->battleManager->getCurrentBattle();
        $messages[] = 'You have selected <strong>'. $battle->getPlayerTeam()->getCurrentFighter()->getName() .'</strong>!';
        return [
            'messages' => [
                'messages' => $messages,
                'textColor' => 'text-white'
            ],
            'opponent' => $battle->getOpponentTeam(),
            'player' => $battle->getPlayerTeam(),
            'form' => $this->battleFormManager->createAdventureButtons()
        ];
    }

    public function handleAttack() 
    {
        $turn = 'player';
        if($this->battleManager->getOpponentFighter()->getIsSleep()) {
            $messages[] = '<strong>'.$this->battleManager->getOpponentFighter()->getName().'</strong> is already harmless.';
            $messages[] = 'Try to capture it!';
            $form = $this->battleFormManager->createAdventureButtons();
        } else {
            $damage = $this->battleManager->manageAttackOpponent();
            $form = [$this->battleFormManager->createNextButton()];
            if($this->battleManager->getOpponentFighter()->getIsSleep()) {
                $messages[] = '<strong>'. $this->battleManager->getPlayerFighter()->getName() .
                '</strong> attacks <strong>'. $this->battleManager->getOpponentFighter()->getName().'</strong> with '.$damage.' points of damage!';
                $messages[] = '<strong>'.$this->battleManager->getOpponentFighter()->getName().'</strong> has fainted.';
            } else {
                $messages[] = '<strong>'. $this->battleManager->getPlayerFighter()->getName() .
                              '</strong> attacks <strong>'. $this->battleManager->getOpponentFighter()->getName().'</strong>!';
                $messages[] = 'It inflicts '.$damage.' points of damage.';
                $turn = 'opponent';
            }    
        }

        $battle = $this->battleManager->getCurrentBattle();
        return [
            'messages' => [
                'messages' => $messages,
                'textColor' => 'text-white'
            ],
            'opponent' => $battle->getOpponentTeam(),
            'player' => $battle->getPlayerTeam(),
            'form' => $form,
            'turn' => $turn
        ];
    }

    public function handleThrowPokeball() 
    {
        $result = $this->battleManager->manageThrowPokeball();
        $battle = $this->battleManager->getCurrentBattle();
        $opponentTeam = $battle->getOpponentTeam();
        $playerTeam = $battle->getPlayerTeam();
        $turn = 'player';

        if($result == 'success') {
        
            $form = [$this->battleFormManager->createTravelButton()];
            $data = $this->battleManager->manageLevelupForAdventure();
            $messages[] = '<strong>'. $opponentTeam->getCurrentFighter()->getName() .'</strong> was captured!';
            $textColor = 'battle-text-success';
            if($data['hasEvolved']) {
                $spriteFrontUrl = $playerTeam->getCurrentFighter()->getspriteFrontUrl();
                $messages[] = '<strong>'. $data['name'] .'</strong> evolves to <strong>'.
                              $data['newName'].'</strong> (level: '.$data['newLevel'].').';
            } elseif($data['hasLeveledUp']) {
                $messages[] = '<strong>'. $data['name'] .'</strong> levels up to '.
                $playerTeam->getCurrentFighter()->getLevel()." (+". $data['increasedLevel'] .').';
            }
            $this->clear();
            $opponentTeam = null;
            $playerTeam = null;

        } else {
            if($result == 'failed') { 
                $messages[] = "You missed!";
                $form = [$this->battleFormManager->createNextButton()];
                if(!$opponentTeam->getCurrentFighter()->getIsSleep()) {
                    $turn = 'opponent';
                }
            }
            else { 
                $messages[] = 'You don\'t have any pokeball!';
                $form = $this->battleFormManager->createAdventureButtons();
            }
            $textColor = 'battle-text-danger';
        }

        return [
            'messages' => [
               'messages' => $messages,
               'textColor' => isset($textColor) ? $textColor : 'text-white'
            ],
            'opponent' => $opponentTeam,
            'player' => $playerTeam,
            'form' => $form,
            'centerImageUrl' => isset($spriteFrontUrl) ? [$spriteFrontUrl] : null,
            'turn' => $turn
        ];
    }

    public function handleLeave() {
        $battle = $this->battleManager->getCurrentBattle();
        $opponentTeam = null;
        $playerTeam = null;
        $result = $this->battleManager->manageLeave();

        if($result) {
            $messages[] = 'You leave with success!';
            $textColor = 'battle-text-success';
            $form = [$this->battleFormManager->createTravelButton()];
            $this->clear();
        } else {
            $messages[] = '<strong>'.$battle->getOpponentTeam()->getCurrentFighter()->getName() .'</strong> has prevented your escape!';
            $textColor = 'battle-text-danger';
            $form = $this->battleFormManager->createAdventureButtons();
            $opponentTeam = $battle->getOpponentTeam();
            $playerTeam = $battle->getPlayerTeam(); 
        }

        return [
            'messages' => [
                'messages' => $messages,
                'textColor' => isset($textColor) ? $textColor : 'text-white'
            ],
            'opponent' => $opponentTeam,
            'player' => $playerTeam,
            'form' => $form
        ];
    }

    public function handleHeal() {
        /** @var BattleManager $this->battleManager */
        $result = $this->battleManager->manageHealPlayerFighter();
        $battle = $this->battleManager->getCurrentBattle();
        $opponentTeam = $battle->getOpponentTeam();
        $playerTeam = $battle->getPlayerTeam();

        if($result === BattleManager::POKEMON_HP_FULL) {
            $messages[] = 'Your pokemon already has all its health points!';
        } elseif($result === BattleManager::NO_HP_POTION) {
            $messages[] = 'You don\'t have any health potions!';
            $textColor = 'battle-text-danger';
        } else {
            $messages[] = '<strong>'.$playerTeam->getCurrentFighter()->getName() .'</strong> has been healed (+'.$result.'HP)!';
            $textColor = 'battle-text-info';
        }

        return [
            'messages' => [
                'messages' => $messages,
                'textColor' => isset($textColor) ? $textColor : 'text-white'
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
                $messages[] = '<strong>'. $opponentFighter->getName() .'</strong> has knocked <strong>'. 
                                $playerFighter->getName() .'</strong> out (-'.$damage.' HP).';
                $messages[] = 'Besides, <strong>'. $opponentFighter->getName() .'</strong> has escaped.';
                $textColor = 'battle-text-danger';
                $this->clear();
            } else {
                $messages[] = '<strong>'. $opponentFighter->getName() .'</strong> attacks <strong>'. $playerFighter->getName() .'</strong>'; 
                $messages[] = 'It inflicts '.$damage.' points of damage.';
                $textColor = 'battle-text-danger';
            }    
        } else {
            $messages[] = 'Try to capture it!';
        }

        return [
            'messages' => [
                'messages' => $messages,
                'textColor' => isset($textColor) ? $textColor : 'text-white'
            ],
            'opponent' => $opponentTeam,
            'player' => $playerTeam,
            'form' => $form,
        ];
    }
}