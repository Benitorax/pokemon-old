<?php

namespace App\Controller;

use App\Entity\Pokemon;
use App\Manager\PokemonManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class PokemonController extends AbstractController
{
    /**
     * @Route("/pokemon/{id}/evolve", name="pokemon_evolve")
     */
    public function evolvePokemon(Pokemon $pokemon, PokemonManager $pokemonManager)
    {
        $pokemonManager->makeEvolve($pokemon);

        return $this->redirectToRoute('trainer_pokemons', [
            'id' => $this->getUser()->getId()
        ]);
    }
}
