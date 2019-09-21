<?php
namespace App\Manager;

use App\Entity\User;
use App\Entity\Battle;
use App\Entity\Habitat;
use App\Entity\Pokemon;
use App\Entity\BattleTeam;
use App\Api\PokeApi\PokeApiManager;
use App\Entity\Trainer;
use Symfony\Component\Security\Core\Security;
use Doctrine\Common\Persistence\ObjectManager;

abstract class AbstractBattleManager
{
    protected $manager;
    protected $pokeApiManager;
    protected $user;

    public function __construct(
        ObjectManager $manager, 
        PokeApiManager $pokeApiManager,
        Security $security
    )
    {
        $this->manager = $manager;
        $this->pokeApiManager = $pokeApiManager;
        $this->user = $security->getUser();
    }

    public function getCurrentBattle()
    {
        $playerTeam = $this->getPlayerTeam();

        return $this->manager->getRepository(Battle::class)->findOneBy(['playerTeam' => $playerTeam]);
    }

    public function getUser() {
        return $this->user;
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

    public function getOpponentTeam()
    {
        return $this->getCurrentBattle()->getOpponentTeam();
    }

    public function persistAndFlush(...$objects)
    {
        foreach($objects as $object) {
            $this->manager->persist($object);
        }

        $this->manager->flush();
    }

    public function removeAndFlush(...$objects)
    {
        foreach($objects as $object) {
            $this->manager->remove($object);
        }
        $this->manager->flush();
    }

    public function getDBPokemonFromId($idPokemon) {
        return $this->manager->getRepository(Pokemon::class)->find($idPokemon);
    }

    public function createBattle(BattleTeam $playerTeam, BattleTeam $opponentTeam, Habitat $habitat, string $type) {
        $battle = new Battle();
        $battle
            ->setPlayerTeam($playerTeam)
            ->setOpponentTeam($opponentTeam)
            ->setArena($habitat)
            ->setType($type);

        return $battle;
    }

    public function createAdventureOpponent(string $name = 'unknown') {
        $user = new User();
        
        return $user->setUsername($name)
             ->setPassword('unknown')
             ->setEmail('unknown')
             ->setCreatedAt(new \DateTime('now'));
    }

    public function createTournamentOpponent(string $name = 'unknown') {
        $user = new User();
        $trainer = (new Trainer)->getRandomTrainer();
        return $user->setUsername($trainer['username'])
             ->setPassword('unknown')
             ->setEmail($trainer['email'])
             ->setCreatedAt(new \DateTime('now'));
    }

    public function getPlayerFighter() {
        return $this->getPlayerTeam()->getCurrentFighter();
    }

    public function getOpponentFighter() {
        return $this->getOpponentTeam()->getCurrentFighter();
    }

    public function getOpponentTrainer() {
        return $this->getOpponentTeam()->getTrainer();
    }

    public function getLastPlayerPokemon() {
        return $this->getPlayerTeam()->getPokemons()->last();
    }

    public function getLastOpponentPokemon() {
        return $this->getOpponentTeam()->getPokemons()->last();
    }
}