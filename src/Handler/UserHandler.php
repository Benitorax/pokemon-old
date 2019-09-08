<?php

namespace App\Handler;

use App\Entity\User;
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
        $pokemon = $this->pokeApiManager->getNewPokemon($data['pokemon']);

        $user = new User();
        $user->setUsername($data['username'])
            ->setPassword($this->encoder->encodePassword(
                $user,
                $data['password']
            ))
            ->setEmail($data['email'])
            ->addPokemon($pokemon)
        ;

        return $user;
    }
}