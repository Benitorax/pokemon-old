<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\PokemonRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class TrainerController extends AbstractController
{
    /**
     * @Route("/trainer/", name="trainer_profile")
     */
    public function profile()
    {
        $user = $this->getUser();
        return $this->render('trainer/profile.html.twig', [
            'user' => $user,
        ]);
    }

    /**
     * @Route("/trainer/pokemons", name="trainer_pokemons")
     */
    public function listPokemons(PokemonRepository $rep)
    {
        $user = $this->getUser();
        $pokemons = $rep->findPokemonsByTrainer($user);

        return $this->render('trainer/pokemons.html.twig', [
            'pokemons' => $pokemons,
        ]);
    }
}
