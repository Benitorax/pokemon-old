<?php
namespace App\Manager;

use App\Entity\User;
use App\Repository\PokemonRepository;
use App\Serializer\PokemonSerializer;
use Symfony\Component\Routing\RouterInterface;

class BattleFormManager
{
    private $router;
    private $pokemonRepository;
    private $pokemonSerializer;

    public function __construct(RouterInterface $router, PokemonRepository $pokemonRepository, PokemonSerializer $pokemonSerializer) {
        $this->router = $router;
        $this->pokemonRepository = $pokemonRepository;
        $this->pokemonSerializer = $pokemonSerializer;
    }

    public function generateButton(string $name, string $route, string $buttonText = "Button Text", string $className = "") {
        $url = $this->router->generate($route);
        return [
            'name' => $name,
            'url' => $url,
            'className' => $className,
            'buttonText' => $buttonText,
            'type' => 'button'
        ];
    }

    public function createTravelButton() {
        return $this->generateButton('travel', 'adventure_travel', 'Travel around', 'btn btn-outline-secondary');
    }

    public function createAttackButton() {
        return $this->generateButton('attack', 'adventure_attack', 'Attack', 'btn btn-outline-primary');
    }

    public function createHealButton() {
        return $this->generateButton('heal', 'adventure_heal', 'Heal', 'btn btn-outline-secondary');
    }

    public function createThrowPokeballButton() {
        return $this->generateButton('throwPokeball', 'adventure_pokeball_throw', 'Throw pokeball', 'btn btn-outline-success');
    }

    public function createLeaveButton() {
        return $this->generateButton('leave', 'adventure_leave', 'Leave', 'btn btn-outline-danger');
    }

    public function createNextButton() {
        return $this->generateButton('next', 'adventure_next', 'Next', 'btn btn-outline-secondary');
    }

    public function createAdventureButtons() {
        return [
            $this->createAttackButton(),
            $this->createHealButton(),
            $this->createThrowPokeballButton(),
            $this->createLeaveButton()
        ];
    }
    public function createSelectPokemonField(User $user) {
        $pokemons = $this->pokemonRepository->findReadyPokemonsByTrainer($user);
        $pokemonList = [];
        foreach($pokemons as $pokemon) {
            $pokemonList[] = $this->pokemonSerializer->normalizeForSelection($pokemon);
        }

        return [
            'name' => 'pokemonsToSelect',
            'pokemons' => $pokemonList,
            'className' => 'btn btn-outline-info',
            'type' => 'select',
            'button' => $this->createSelectButton()
        ];
    }

    public function createSelectButton() {
        return $this->generateButton('selectPokemon', 'adventure_pokemon_select', 'SELECT', 'btn btn-outline-success');
    }
}