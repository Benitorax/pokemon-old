<?php

namespace App\Manager;

use App\Entity\BattleTeam;
use App\Entity\Pokemon;
use App\Manager\AbstractBattleManager;

class BattleManager extends AbstractBattleManager
{
    const POKEMON_HP_FULL = 1;
    const NO_HP_POTION = 2;

    public function createAdventureBattle()
    {
        $playerTeam = new BattleTeam();
        $playerTeam->setTrainer($this->user);

        $habitat = $this->pokeApiManager->getRandomHabitat();
        $this->persistAndFlush($habitat);

        $opponentTeam = $this->createAdventureOpponentTeam($habitat);
        $battle = $this->createBattle($playerTeam, $opponentTeam, $habitat, 'adventure');
        $this->persistAndFlush($battle);

        return $battle;
    }

    public function createTournamentBattle()
    {
        $playerTeam = new BattleTeam();
        $playerTeam->setTrainer($this->user);

        $habitat = $this->pokeApiManager->getRandomHabitat();

        $opponentTeam = $this->createTournamentOpponentTeam($habitat);
        $battle = $this->createBattle($playerTeam, $opponentTeam, $habitat, 'tournament');
        $this->persistAndFlush($battle);

        return $battle;
    }

    public function clearLastBattle()
    {
        if($battle = $this->getCurrentBattle())
        {
            $playerPokemons = $battle->getPlayerTeam()->getPokemons();
            foreach($playerPokemons as $pokemon) {
                $pokemon->setBattleTeam(null);
            }

            $opponent = $battle->getOpponentTeam()->getTrainer();
            $opponentPokemons = $battle->getOpponentTeam()->getPokemons()->toArray();        
            foreach($opponentPokemons as $oPokemon) {
                $oPokemon->setBattleTeam(null);
            }

            $this->removeAndFlush($battle);
            $this->removeAndFlush($opponent);
        }
    }

    public function createAdventureOpponentTeam($habitat)
    {
        $pokemon = $this->pokeApiManager->getRandomPokemonFromHabitat($habitat);
        $opponent = $this->createAdventureOpponent();
        $opponent->addPokemon($pokemon);
    
        $team = new BattleTeam();
        $team->setTrainer($opponent)
             ->addPokemon($pokemon)
             ->setCurrentFighter($pokemon);
        
        return $team;
    }

    public function createTournamentOpponentTeam($habitat)
    {
        $opponent = $this->createTournamentOpponent();
        $team = new BattleTeam();
        $team->setTrainer($opponent);

        for($i = 0; $i < 3; $i++) {
            $pokemon = $this->pokeApiManager->getRandomPokemonFromHabitat($habitat);
            $opponent->addPokemon($pokemon);
            $team->addPokemon($pokemon);

            if($i == 0) {
                $team->setCurrentFighter($pokemon);
            }
        }
        
        return $team;
    }

    public function addFighterSelected($pokemon)
    {
        $playerTeam = $this->getPlayerTeam();
        if($playerTeam->getPokemons()->contains($pokemon)) {
            return;
        }

        $playerTeam->addPokemon($pokemon);
        if($playerTeam->getPokemons()->count() == 1) {
            $playerTeam->setCurrentFighter($pokemon);
        }

        $this->persistAndFlush($pokemon);
    }

    public function manageThrowPokeball() 
    {
        $result = 'impossible';
        $user = $this->user;

        if($user->getPokeball() > 0) {
            $this->user->usePokeball();
            $captureRate = $this->getOpponentFighter()->getCaptureRate();   
            $hp = $this->getOpponentFighter()->getHealthPoint();
            $result = 'failed';

            if(rand(1,100) <= $captureRate * (115-$hp) / 100 ) {
                $result = 'success';
                $this->user->addPokemon($this->getOpponentFighter());
            } 
            
            $this->manager->flush();

            return $result;
        }

        return $result;
    }

    public function manageAttackOpponent() {
        $this->getCurrentBattle()->setTurn('opponent');
        $opponentFighter = $this->getOpponentFighter();
        $playerLevel = $this->getPlayerFighter()->getLevel();
        $damage = round(rand(5,20) * (100 + $playerLevel) / 100);
        $opponentFighter->decreaseHealthPoint($damage);
        $this->manager->flush();

        return $damage;
    }

    public function manageLeave() {
        $isSleep = $this->getOpponentFighter()->getIsSleep();
        if($isSleep) { return true; }

        $hp = $this->getOpponentFighter()->getHealthPoint();
        
        if($hp > 70) {
            return rand(1,100) < 30;
        } 
        elseif($hp >= 30) {
            return rand(1,100) < 50;
        } 
        else {
            return rand(1,100) < 70;
        }
    }

    public function manageLevelUpForTournament() {
        $pokemons = $this->getPlayerTeam()->getPokemons();
        $data = [];
        foreach($pokemons as $pokemon) {
            $data[] = $this->manageLevelUpForAdventure($pokemon);
        }
        return $data;
    }

    public function manageLevelUpForAdventure(Pokemon $pokemon = null) {
        if(!$pokemon) {
            $pokemon = $this->getPlayerFighter();
        }
        $level = $pokemon->getLevel();
        if($level == 100) {
            return [ 
                'hasLeveledUp' => false,
                'hasEvolved' => false
            ];
        }

        $name = $pokemon->getName();
        $pokemon->increaseLevel($increasedLevel = rand(10,22));
        $newPokemon = $this->pokeApiManager->checkNextEvolution($pokemon);
        $this->manager->flush();

        if($newPokemon) {
            return [
                'hasLeveledUp' => true,
                'hasEvolved' => true,
                'name' => $name,
                'newName' => $newPokemon->getName(),
                'increasedLevel' => $newPokemon->getLevel() - $level,
                'newLevel' => $newPokemon->getLevel(),
                'SpriteFrontUrl' => $newPokemon->getSpriteFrontUrl()
            ];
        }

        return [
            'hasLeveledUp' => true,
            'hasEvolved' => false,
            'name' => $name,
            'increasedLevel' => $increasedLevel,
            'newLevel' => $pokemon->getLevel()
        ];
    }

    public function manageHealPlayerFighter()
    {
        if($this->getPlayerFighter()->getHealthPoint() === 100) {
            return self::POKEMON_HP_FULL;
        }

        if($this->getPlayerFighter()->getTrainer()->getHealingPotion() == 0) {
            return self::NO_HP_POTION;
        }

        $pokemon = $this->getPlayerFighter();
        $healthPoint = $pokemon->getHealthPoint();
        $pokemon->increaseHealthPoint(rand(40,60));
        $healthPointRange = $pokemon->getHealthPoint() - $healthPoint;
        $pokemon->getTrainer()->useHealingPotion();
        $this->getPlayerTeam()->increaseHealCount();
        $this->manager->flush();

        return $healthPointRange;
    }

    public function manageDamagePlayerFighter()
    {
        $this->getCurrentBattle()->setTurn('player');
        $playerFighter = $this->getPlayerFighter();
        $opponentLevel = $this->getOpponentFighter()->getLevel();
        $hp = $playerFighter->getHealthPoint();
        $damage = intval(round(rand(5,20) * (150 - $opponentLevel) / 100));
        $playerFighter->decreaseHealthPoint($damage);
        $newHp = $playerFighter->getHealthPoint();
        $this->manager->flush();

        return $hpLost = $hp - $newHp;
    }

    public function manageChangeFighterOfTeam(BattleTeam $battleTeam)
    {
        $pokemons = $battleTeam->getPokemons();
        $isAllSleep = true;
        foreach($pokemons as $pokemon) {
            if(!$pokemon->getIsSleep()) {
                $battleTeam->setCurrentFighter($pokemon);
                $isAllSleep = false;
                break;
            }
        }
        $this->getCurrentBattle()->setTurn('player');
        $this->manager->flush();

        if($isAllSleep) {
            $battleTeam->setHasNoMoreFighter(true);
            $battleTeam->setCurrentFighter(null);
            $this->endBattle($battleTeam);
            return false;
        }
        return true;
    }

    public function startBattle() {
        $this->getCurrentBattle()->setIsStart(true);
        $this->manager->flush();
    }

    public function endBattle(BattleTeam $battleTeam) {
        $this->getCurrentBattle()->setIsEnd(true);
        $battle = $this->getCurrentBattle();
        // $battleTeam has lost
        $battleTeam->setIsVictorious(false);
        if($battle->getPlayerTeam() === $battleTeam) {
            $battle->getOpponentTeam()->setIsVictorious(true);
        } elseif($battle->getOpponentTeam() === $battleTeam) {
            $battle->getPlayerTeam()->setIsVictorious(true);
        }
        $this->manager->flush();
    }

    public function restorePlayerPokemons()
    {
        $pokemons = $this->getPlayerTeam()->getPokemons();
        foreach($pokemons as $pokemon) {
            $pokemon->setHealthPoint(100);
            $pokemon->setIsSleep(false);
        }
        $this->manager->flush();
    }
}