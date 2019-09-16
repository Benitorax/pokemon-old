<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\PokemonRepository;
use App\Repository\UserRepository;
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

    /**
     * @Route("/trainer/list", name="trainer_list")
     */
    public function showTrainers(UserRepository $userRepository)
    {
        $users = $userRepository->findAllActivated();
        return $this->render('trainer/show_trainers.html.twig', [
            'users' => $users,
        ]);
    }

    /**
     * @Route("/trainer/{id}", name="trainer_show")
     */
    public function showTrainer(User $user)
    {
        return $this->render('trainer/show_trainer.html.twig', [
            'user' => $user,
        ]);
    }
}
