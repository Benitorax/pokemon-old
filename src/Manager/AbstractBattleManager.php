<?php

namespace App\Manager;

use App\Entity\User;
use App\Entity\Battle;
use App\Entity\Habitat;
use App\Entity\Pokemon;
use App\Entity\Trainer;
use App\Entity\BattleTeam;
use App\Api\PokeApi\PokeApiManager;
use App\Repository\BattleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;

abstract class AbstractBattleManager
{
    protected EntityManagerInterface $manager;
    protected PokeApiManager $pokeApiManager;
    protected User $user;
    private ?Battle $battle = null;
    private ?BattleTeam $playerTeam = null;
    private ?Pokemon $playerFighter = null;
    private ?Pokemon $lastPlayerPokemon = null;
    private ?User $opponentTrainer = null;
    private ?BattleTeam $opponentTeam = null;
    private ?Pokemon $opponentFighter = null;

    public function __construct(
        EntityManagerInterface $manager,
        PokeApiManager $pokeApiManager,
        Security $security
    ) {
        $user = $security->getUser();

        if ($user instanceof User) {
            $this->user = $user;
        }

        $this->manager = $manager;
        $this->pokeApiManager = $pokeApiManager;
    }

    public function getCurrentBattle(): Battle
    {
        if ($this->battle instanceof Battle) {
            return $this->battle;
        }

        /** @var BattleRepository */
        $repository = $this->manager->getRepository(Battle::class);

        /** @phpstan-ignore-next-line */
        return $this->battle = $repository->findOneByTrainer($this->user);
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getPlayerTeam(): BattleTeam
    {
        if ($this->playerTeam instanceof BattleTeam) {
            return $this->playerTeam;
        }

        return $this->playerTeam = $this->getCurrentBattle()->getPlayerTeam();
    }

    public function getOpponentTeam(): BattleTeam
    {
        if ($this->opponentTeam instanceof BattleTeam) {
            return $this->opponentTeam;
        }

        return $this->opponentTeam = $this->getCurrentBattle()->getOpponentTeam();
    }

    /**
     * @param object $objects
     */
    public function persistAndFlush(...$objects): void
    {
        foreach ($objects as $object) {
            $this->manager->persist($object);
        }

        $this->manager->flush();
    }

    /**
     * @param object $objects
     */
    public function removeAndFlush(...$objects): void
    {
        foreach ($objects as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function createBattle(
        BattleTeam $playerTeam,
        BattleTeam $opponentTeam,
        Habitat $habitat,
        string $type
    ): Battle {
        $battle = new Battle();
        $battle
            ->setPlayerTeam($playerTeam)
            ->setOpponentTeam($opponentTeam)
            ->setArena($habitat)
            ->setType($type);

        return $battle;
    }

    public function createAdventureOpponent(string $name = 'unknown'): User
    {
        $user = new User();

        return $user->setUsername($name)
             ->setPassword('unknown')
             ->setEmail(uniqid())
             ->setCreatedAt(new \DateTime('now'));
    }

    public function createTournamentOpponent(): User
    {
        $user = new User();
        $trainer = (new Trainer())->getRandomTrainer();

        return $user->setUsername($trainer['username'])
             ->setPassword('unknown')
             ->setEmail($trainer['email'])
             ->setCreatedAt(new \DateTime('now'));
    }

    public function getPlayerFighter(): Pokemon
    {
        if ($this->playerFighter instanceof Pokemon) {
            return $this->playerFighter;
        }

        return $this->playerFighter = $this->getPlayerTeam()->getCurrentFighter();
    }

    public function getOpponentFighter(): Pokemon
    {
        if ($this->opponentFighter instanceof Pokemon) {
            return $this->opponentFighter;
        }

        return $this->opponentFighter = $this->getOpponentTeam()->getCurrentFighter();
    }

    public function getOpponentTrainer(): User
    {
        if ($this->opponentTrainer instanceof User) {
            return $this->opponentTrainer;
        }

        return $this->opponentTrainer = $this->getOpponentTeam()->getTrainer();
    }

    public function getLastPlayerPokemon(): Pokemon
    {
        if ($this->lastPlayerPokemon instanceof Pokemon) {
            return $this->lastPlayerPokemon;
        }

        return $this->lastPlayerPokemon = $this->getPlayerTeam()->getPokemons()->last();
    }
}
