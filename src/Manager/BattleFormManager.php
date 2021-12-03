<?php

namespace App\Manager;

use App\Entity\User;
use App\Repository\PokemonRepository;
use App\Serializer\PokemonSerializer;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Routing\RouterInterface;

class BattleFormManager
{
    public const ADVENTURE_MODE = 'adventure';
    public const TOURNAMENT_MODE = 'tournament';
    private RouterInterface $router;
    private PokemonRepository $pokemonRepository;
    private PokemonSerializer $pokemonSerializer;
    private User $user;

    public function __construct(
        RouterInterface $router,
        PokemonRepository $pokemonRepository,
        PokemonSerializer $pokemonSerializer,
        Security $security
    ) {
        /** @var User */
        $user = $security->getUser();
        $this->user = $user;
        $this->router = $router;
        $this->pokemonRepository = $pokemonRepository;
        $this->pokemonSerializer = $pokemonSerializer;
    }

    /**
     * @return string[]
     */
    public function generateButton(
        string $name,
        string $route,
        string $buttonText = "Button Text",
        string $className = ""
    ) {
        $url = $this->router->generate($route);

        return [
            'name' => $name,
            'url' => $url,
            'className' => $className,
            'buttonText' => $buttonText,
            'type' => 'button'
        ];
    }

    public function createTravelButton(string $mode = self::ADVENTURE_MODE): array
    {
        return $this->generateButton('travel', $mode . '_travel', 'Travel around', 'btn btn-outline-secondary');
    }

    public function createAttackButton(string $mode = self::ADVENTURE_MODE): array
    {
        return $this->generateButton('attack', $mode . '_attack', 'Attack', 'btn btn-outline-primary');
    }

    public function createHealButton(string $mode = self::ADVENTURE_MODE): array
    {
        return $this->generateButton('heal', $mode . '_heal', 'Heal', 'btn btn-outline-secondary');
    }

    public function createThrowPokeballButton(): array
    {
        return $this->generateButton('throwPokeball', 'adventure_pokeball_throw', 'Capture', 'btn btn-outline-success');
    }

    public function createLeaveButton(): array
    {
        return $this->generateButton('leave', 'adventure_leave', 'Leave', 'btn btn-outline-danger');
    }

    public function createNextButton(string $mode = self::ADVENTURE_MODE): array
    {
        return $this->generateButton('next', $mode . '_next', 'Next', 'btn btn-outline-secondary');
    }

    public function createAdventureButtons(): array
    {
        return [
            $this->createAttackButton(),
            $this->createHealButton(),
            $this->createThrowPokeballButton(),
            $this->createLeaveButton()
        ];
    }

    public function createSelectPokemonField(): array
    {
        $pokemons = $this->pokemonRepository->findReadyPokemonsByTrainer($this->user);
        $pokemonList = [];

        foreach ($pokemons as $pokemon) {
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

    private function createSelectButton(string $mode = self::ADVENTURE_MODE): array
    {
        return $this->generateButton('selectPokemon', $mode . '_pokemon_select', 'SELECT', 'btn btn-outline-success');
    }


    public function createSelectPokemonFieldForTournament(): array
    {
        $pokemons = $this->pokemonRepository->findAllFullHPByTrainer($this->user);
        $pokemonList = [];

        foreach ($pokemons as $pokemon) {
            if ($pokemon->getBattleTeam() === null) {
                $pokemonList[] = $pokemon;
            }
        }

        $serializedPokemonList = [];

        foreach ($pokemonList as $pokemon) {
            $serializedPokemonList[] = $this->pokemonSerializer->normalizeForSelection($pokemon);
        }

        return [
            'name' => 'pokemonsToSelect',
            'pokemons' => $serializedPokemonList,
            'className' => 'btn btn-outline-info',
            'type' => 'select',
            'button' => $this->createSelectButton(self::TOURNAMENT_MODE)
        ];
    }

    public function createTournamentButtons(): array
    {
        return [
            $this->createAttackButton(self::TOURNAMENT_MODE),
            $this->createHealButton(self::TOURNAMENT_MODE),
        ];
    }

    public function createRestorePokemonsButton(): array
    {
        return $this->generateButton(
            'restorePokemons',
            'tournament_pokemons_restore',
            'Restore your pokemons for free',
            'btn btn-outline-success'
        );
    }
}
