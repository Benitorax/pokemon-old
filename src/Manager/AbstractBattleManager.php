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
use Doctrine\ORM\EntityManagerInterface;

abstract class AbstractBattleManager
{
    protected $manager;
    protected $pokeApiManager;
    protected $user;
    private $battle;
    private $playerTeam;
    private $playerFighter;
    private $lastPlayerPokemon;
    private $opponentTrainer;
    private $opponentTeam;
    private $opponentFighter;
    public function __construct(EntityManagerInterface $manager, PokeApiManager $pokeApiManager, Security $security)
    {
        $this->manager = $manager;
        $this->pokeApiManager = $pokeApiManager;
        $this->user = $security->getUser();
    }

    public function getCurrentBattle()
    {
        if ($this->battle instanceof Battle) {
            return $this->battle;
        }
        return $this->battle = $this->manager->getRepository(Battle::class)->findOneByTrainer($this->user);
    }

    public function getUser()
    {
        return $this->user;
    }

    public function getPlayerTeam()
    {
        if ($this->playerTeam instanceof BattleTeam) {
            return $this->playerTeam;
        }
        return $this->playerTeam = $this->getCurrentBattle()->getPlayerTeam();
    }

    public function getOpponentTeam()
    {
        if ($this->opponentTeam instanceof BattleTeam) {
            return $this->opponentTeam;
        }
        return $this->opponentTeam = $this->getCurrentBattle()->getOpponentTeam();
    }

    public function persistAndFlush(...$objects)
    {
        foreach ($objects as $object) {
            $this->manager->persist($object);
        }

        $this->manager->flush();
    }

    public function removeAndFlush(...$objects)
    {
        foreach ($objects as $object) {
            $this->manager->remove($object);
        }
        $this->manager->flush();
    }

    public function createBattle(BattleTeam $playerTeam, BattleTeam $opponentTeam, Habitat $habitat, string $type)
    {
        $battle = new Battle();
        $battle
            ->setPlayerTeam($playerTeam)
            ->setOpponentTeam($opponentTeam)
            ->setArena($habitat)
            ->setType($type);
        return $battle;
    }

    public function createAdventureOpponent(string $name = 'unknown')
    {
        $user = new User();
        return $user->setUsername($name)
             ->setPassword('unknown')
             ->setEmail(uniqid())
             ->setCreatedAt(new \DateTime('now'));
    }

    public function createTournamentOpponent()
    {
        $user = new User();
        $trainer = (new Trainer())->getRandomTrainer();
        return $user->setUsername($trainer['username'])
             ->setPassword('unknown')
             ->setEmail($trainer['email'])
             ->setCreatedAt(new \DateTime('now'));
    }

    public function getPlayerFighter()
    {
        if ($this->playerFighter instanceof Pokemon) {
            return $this->playerFighter;
        }
        return $this->playerFighter = $this->getPlayerTeam()->getCurrentFighter();
    }

    public function getOpponentFighter()
    {
        if ($this->opponentFighter instanceof Pokemon) {
            return $this->opponentFighter;
        }
        return $this->opponentFighter = $this->getOpponentTeam()->getCurrentFighter();
    }

    public function getOpponentTrainer()
    {
        if ($this->opponentTrainer instanceof User) {
            return $this->opponentTrainer;
        }
        return $this->opponentTrainer = $this->getOpponentTeam()->getTrainer();
    }

    public function getLastPlayerPokemon()
    {
        if ($this->lastPlayerPokemon instanceof Pokemon) {
            return $this->lastPlayerPokemon;
        }
        return $this->lastPlayerPokemon = $this->getPlayerTeam()->getPokemons()->last();
    }
}
