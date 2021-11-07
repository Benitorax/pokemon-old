<?php

namespace App\Api\PokeApi;

use App\Entity\Habitat;
use App\Entity\Pokemon;
use App\Api\PokeApi\HabitatApi;
use App\Api\PokeApi\PokemonApi;

class PokeApiManager
{
    private PokemonApi $pokemonApi;
    private HabitatApi $habitatApi;

    public function __construct(PokemonApi $pokemonApi, HabitatApi $habitatApi)
    {
        $this->pokemonApi = $pokemonApi;
        $this->habitatApi = $habitatApi;
    }

    public function checkNextEvolution(Pokemon $pokemon): ?Pokemon
    {
        return $this->pokemonApi->checkNextEvolution($pokemon);
    }

    public function getNewPokemon(int $pokemonId): Pokemon
    {
        return $this->pokemonApi->getNewPokemon($pokemonId);
    }

    public function getRandomHabitat(): Habitat
    {
        return $this->habitatApi->getRandomHabitat();
    }

    public function getRandomPokemonFromHabitat(Habitat $habitat): Pokemon
    {
        return $this->pokemonApi->getRandomPokemonFromHabitat($habitat);
    }

    public function getHabitat(int $id): Habitat
    {
        return $this->habitatApi->getHabitat($id);
    }
}
