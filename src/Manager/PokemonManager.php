<?php

namespace App\Manager;

use App\Api\PokeApi\PokeApiManager;
use Doctrine\Common\Persistence\ObjectManager;

class PokemonManager
{
    private $pokeApiManager;

    public function __construct(
        PokeApiManager $pokeApiManager,
        ObjectManager $manager
    )
    {
        $this->pokeApiManager = $pokeApiManager;
        $this->manager = $manager;
    }
    
    public function makeEvolve($pokemon) 
    {
        $pokemon = $this->pokeApiManager->getNextEvolution($pokemon);
        
        if($pokemon)
        {
            $this->manager->persist($pokemon);
            $this->manager->flush();
    
            return $pokemon;
        }

        return;
    }
}