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

        if($user->getPokeballs() > 0) {
            $this->user->usePokeball();
            $captureRate = $this->getOpponentFighter()->getCaptureRate();    
            $result = 'failed';

            if(rand(1,100) <= $captureRate) {
                $result = 'success';
                $this->user->addPokemon($this->getOpponentFighter());
            } 
            
            $this->manager->flush();

            return $result;
        }

        return $result;
    }
}