<?php

namespace App\Manager;

use App\Entity\BattleTeam;
use App\Manager\AbstractBattleManager;

class BattleManager extends AbstractBattleManager
{
    public function createAdventureBattle()
    {
        $playerTeam = $this->getPlayerTeam();
        $playerTeam->setTrainer($this->user);

        $habitat = $this->pokeApiManager->getRandomHabitat();
        $this->persistAndFlush($habitat);

        $opponentTeam = $this->createAdventureOpponentTeam($habitat);
        $battle = $this->createBattle($playerTeam, $opponentTeam, $habitat, 'adventure');
        $this->persistAndFlush($battle);

        return $battle;
    }

    public function clearLastBattle()
    {
        if($battle = $this->getCurrentBattle())
        {
            $playerPokemons = $this->getPlayerTeam()->getPokemons();
            foreach($playerPokemons as $pokemon) {
                $pokemon->setBattleTeam(null);
            }

            $opponent = $this->getOpponentTeam()->getTrainer();
            $opponentPokemons = $this->getOpponentTeam()->getPokemons()->toArray();        
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
        $opponent = $this->createOpponent();
        $opponent->addPokemon($pokemon);
    
        $team = new BattleTeam();
        $team->setTrainer($opponent)
             ->setCurrentFighter($pokemon)
             ->addPokemon($pokemon);
        
        return $team;
    }

    /**
     * Have to refactor when create battle for tournament
     */
    public function addFighterSelected($idPokemon)
    {
        $pokemon = $this->getDBPokemonFromId($idPokemon);     
        $this->getPlayerTeam()->setCurrentFighter($pokemon)->addPokemon($pokemon);
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
        $opponentFighter = $this->getOpponentFighter();
        $playerLevel = $this->getPlayerFighter()->getLevel();
        $damage = round(rand(5,20) * (100 + $playerLevel) / 100);
        $opponentFighter->decreaseHealthPoint($damage);

        $this->manager->flush();

        return $damage;
    }

    public function manageOpponentAttack() {
        $playerFighter = $this->getPlayerFighter();
        $opponentLevel = $this->getOpponentFighter()->getLevel();
        $damage = intval(round(rand(5,20) * (100 + $opponentLevel) / 100));
        $playerFighter->decreaseHealthPoint($damage);

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
    }

    public function manageLevelUpForAdventure() {
        $pokemon = $this->getPlayerFighter();
        $level = $pokemon->getLevel();
        $name = $pokemon->getName();
        $pokemon->increaseLevel($increasedLevel = rand(10,22));
        $newPokemon = $this->pokeApiManager->checkNextEvolution($pokemon);

        if($newPokemon) {
            $this->manager->flush();

            return [
                'hasEvolved' => true,
                'name' => $name,
                'newName' => $newPokemon->getName(),
                'increasedLevel' => $newPokemon->getLevel() - $level
            ];
        }
        $this->manager->flush();

        return [
            'hasEvolved' => false,
            'name' => $name,
            'increasedLevel' => $increasedLevel
        ];
    }
}