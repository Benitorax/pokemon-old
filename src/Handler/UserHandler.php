<?php

namespace App\Handler;

use App\Entity\User;
use App\Manager\BattleManager;
use App\Entity\RegisterUserDTO;
use App\Api\PokeApi\PokeApiManager;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\PokemonExchangeRepository;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserHandler
{
    private $manager;
    private $passwordHasher;
    private $pokeApiManager;
    private $pokExRepository;
    private $battleManager;

    public function __construct(
        EntityManagerInterface $manager,
        UserPasswordHasherInterface $passwordHasher,
        PokeApiManager $pokeApiManager,
        PokemonExchangeRepository $pokExRepository,
        BattleManager $battleManager
    ) {
        $this->manager = $manager;
        $this->passwordHasher = $passwordHasher;
        $this->pokeApiManager = $pokeApiManager;
        $this->pokExRepository = $pokExRepository;
        $this->battleManager = $battleManager;
    }

    public function handle($data)
    {
        $user = $this->createUserWithFirstPokemon($data);

        $this->manager->persist($user);
        $this->manager->flush();

        return $user;
    }

    public function createUserWithFirstPokemon($data)
    {
        /** @var RegisterUserDTO $data */
        $pokemon = $this->pokeApiManager->getNewPokemon($data->getPokemonApiId());

        $user = new User();
        $user->setUsername($data->getUsername())
            ->setPassword($this->passwordHasher->hashPassword(
                $user,
                $data->getPassword()
            ))
            ->setEmail($data->getEmail())
            ->addPokemon($pokemon)
            ->setCreatedAt(new \DateTime('now'))
        ;

        return $user;
    }

    public function modifyPassword($user, $newPassword)
    {
        $user->setPassword($this->passwordHasher->hashPassword(
            $user,
            $newPassword
        ));
        $this->manager->flush();
    }

    public function deleteUser($user)
    {
        $this->battleManager->clearLastBattleOfTrainer($user);
        $pokExs = $this->pokExRepository->findAllByTrainer($user);

        foreach ($pokExs as $pokEx) {
            $this->manager->remove($pokEx);
        }

        $this->manager->remove($user);
        $this->manager->flush();
    }
}
