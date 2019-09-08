<?php

namespace App\Manager;

use App\Entity\User;
use App\Entity\Battle;
use App\Entity\Pokemon;
use App\Entity\BattleTeam;
use App\Manager\PokemonManager;
use App\Api\PokeApi\PokeApiManager;
use Symfony\Component\Security\Core\Security;
use Doctrine\Common\Persistence\ObjectManager;


class BattleManager
{
    private $pokemonManager;

    private $manager;

    private $pokeApiManager;

    private $user;

    public function __construct(
        PokemonManager $pokemonManager, 
        ObjectManager $manager, 
        PokeApiManager $pokeApiManager,
        Security $security
    )
    {
        $this->pokemonManager = $pokemonManager;
        $this->manager = $manager;
        $this->pokeApiManager = $pokeApiManager;
        $this->user = $security->getUser();
    }

    public function getCurrentBattle()
    {
        $playerTeam = $this->getPlayerTeam();

        return $this->manager->getRepository(Battle::class)->findOneBy(['playerTeam' => $playerTeam]);
    }

    public function createAdventureBattle()
    {
        $playerTeam = $this->getPlayerTeam();
        $playerTeam->setTrainer($this->user);
        $habitat = $this->pokeApiManager->getRandomHabitat();
        $opponentTeam = $this->createAdventureOpponentTeam($habitat);

        $battle = new Battle();
        $battle
            ->setPlayerTeam($playerTeam)
            ->setOpponentTeam($opponentTeam)
            ->setArena($habitat)
            ->setType('adventure');

        $this->manager->persist($battle);
        $this->manager->flush();

        return $battle;
    }

    public function clearLastBattle()
    {
        $playerTeam = $this->getPlayerTeam();
        
        if($battle = $this->manager->getRepository(Battle::class)
                                   ->findOneBy(['playerTeam' => $playerTeam])
        )
        {
            $playerPokemons = $playerTeam->getPokemons();
            foreach($playerPokemons as $pokemon) {
                $pokemon->setBattleTeam(null);
            }

            $opponentTeam = $battle->getOpponentTeam();
            $opponent = $opponentTeam->getTrainer();
            $opponentPokemons = $opponentTeam->getPokemons()->toArray();        
            foreach($opponentPokemons as $oPokemon) {
                $oPokemon->setBattleTeam(null);
            }

            $this->manager->remove($battle);
            $this->manager->flush();
            
            $this->manager->remove($opponent);
            $this->manager->flush();
        }
    }

    public function getPlayerTeam()
    {
        $playerTeam = $this->manager->getRepository(BattleTeam::class)->findOneBy(['trainer' => $this->user]);

        if($playerTeam) {
            return $playerTeam;
        }

        $team = new BattleTeam();

        return $team->setTrainer($this->user);
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

    public function addFighterSelected($idPokemon)
    {
        $pokemon = $this->manager->getRepository(Pokemon::class)->find($idPokemon);
        $this->getPlayerTeam()->setCurrentFighter($pokemon);
        $this->getPlayerTeam()->addPokemon($pokemon);
        $this->manager->persist($pokemon);   
        $this->manager->flush();
    }

    public function createOpponent() {
        $user = new User();
        $user->setUsername('noname')
             ->setPassword('unknown')
             ->setEmail('unknown');

        return $user;
    }
}