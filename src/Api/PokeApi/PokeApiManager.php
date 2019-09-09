<?php
namespace App\Api\PokeApi;

use App\Entity\Habitat;
use App\Entity\Pokemon;
use App\Api\PokeApi\HabitatApi;
use App\Api\PokeApi\PokemonApi;

class PokeApiManager
{
    private $pokemonApi;

    private $habitatApi;

    public function __construct(PokemonApi $pokemonApi, HabitatApi $habitatApi) {
        $this->pokemonApi = $pokemonApi;
        $this->habitatApi = $habitatApi;
    }

    public function getNextEvolution(Pokemon $pokemon) {
        return $this->pokemonApi->getNextEvolution($pokemon);
    }

    public function checkNextEvolution(Pokemon $pokemon) {
        return $this->pokemonApi->checkNextEvolution($pokemon);
    }

    public function getNewPokemon($pokemonId) {
        return $this->pokemonApi->getNewPokemon($pokemonId);
    }

    public function getRandomHabitat() {
        return $this->habitatApi->getRandomHabitat();
    }

    public function getRandomPokemonFromHabitat(Habitat $habitat) {
        return $this->pokemonApi->getRandomPokemonFromHabitat($habitat);
    }

    public function getHabitat($id) {
        return $this->habitatApi->getHabitat($id);
    }
}