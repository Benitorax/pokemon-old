<?php

namespace App\Handler;

use App\Entity\User;
use App\Entity\RegisterUserDTO;
use App\Api\PokeApi\PokeApiManager;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserHandler
{
    private $manager;

    private $encoder;

    private $pokeApiManager;

    public function __construct(
        ObjectManager $manager,
        UserPasswordEncoderInterface $encoder, 
        PokeApiManager $pokeApiManager)
    {
        $this->manager = $manager;
        $this->encoder = $encoder;
        $this->pokeApiManager = $pokeApiManager;
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
            ->setPassword($this->encoder->encodePassword(
                $user,
                $data->getPassword()
            ))
            ->setEmail($data->getEmail())
            ->addPokemon($pokemon)
            ->setCreatedAt(new \DateTime('now'))
        ;

        return $user;
    }

    public function modifyPassword($user, $newPassword) {
        $user->setPassword($this->encoder->encodePassword(
            $user,
            $newPassword
        ));
        $this->manager->flush();
    }
}