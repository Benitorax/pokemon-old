<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\PokemonExchange;
use App\Form\PokemonExchangeType;
use App\Manager\PokemonExchangeManager;
use App\Repository\PokemonExchangeRepository;
use App\Repository\UserRepository;
use App\Repository\PokemonRepository;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

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

    /**
     * @Route("/trainer/{id}/exchange/create", name="pokemon_exchange_create")
     */
    public function createPokemonExchange(User $trader, Request $request, PokemonExchangeManager $pokExManager)
    {
        $pokemonExchangeForm = $this->createForm(PokemonExchangeType::class, null, [
            'user' => $this->getUser(),
            'trader' => $trader
        ]);

        $pokemonExchangeForm->handleRequest($request);

        if($pokemonExchangeForm->isSubmitted() && $pokemonExchangeForm->isValid()) {
            $pokEx = $pokExManager->createPokemonExchange($pokemonExchangeForm->getData());
            $this->addFlash('success', 'Your request of pokemons exchange has been submit.');
            return $this->redirectToRoute('pokemon_exchange_list');
        }

        return $this->render('trainer/pokemon_exchange_create.html.twig', [
            'pokemonExchangeForm' => $pokemonExchangeForm->createView()
        ]);
    }


    /**
     * @Route("/exchange", name="pokemon_exchange_list")
     */
    public function listPokemonExchange(PokemonExchangeRepository $pokExRepository) 
    {
        $pokemonExchanges = $pokExRepository->findAllByTrainer($this->getUser());
        return $this->render('trainer/pokemon_exchange_list.html.twig', [
            'pokemonExchanges' => $pokemonExchanges
        ]);
    }

    /**
     * @Route("/exchange/{id}", name="pokemon_exchange_edit")
     */
    public function editPokemonExchange(PokemonExchange $pokemonExchange, Request $request, PokemonExchangeManager $pokExManager) 
    {
        $pokemonExchangeForm = $this->createForm(PokemonExchangeType::class, $pokemonExchange, [
            'user' => $pokemonExchange->getTrainer1(),
            'trader' => $pokemonExchange->getTrainer2()
        ]);

        $pokemonExchangeForm->handleRequest($request);
        if($pokemonExchangeForm->isSubmitted() && $pokemonExchangeForm->isValid()) {
            $pokEx = $pokExManager->editPokemonExchange($pokemonExchange, $this->getUser());
            $this->addFlash('success', 'The modification of pokemons exchange has been submit.');
            return $this->redirectToRoute('pokemon_exchange_list');
        }

        return $this->render('trainer/pokemon_exchange_edit.html.twig', [
            'pokemonExchangeForm' => $pokemonExchangeForm->createView()
        ]);
    }

    /**
     * @Route("/exchange/{id}/accept", name="pokemon_exchange_accept")
     */
    public function acceptPokemonExchange(PokemonExchange $pokemonExchange, PokemonExchangeManager $pokExManager) 
    {
        $pokExManager->acceptPokemonExchange($pokemonExchange, $this->getUser());
        $this->addFlash('success', 'You have accepted the exchange.');
        return $this->redirectToRoute('pokemon_exchange_list');
    }

    /**
     * @Route("/exchange/{id}/refuse", name="pokemon_exchange_delete")
     */
    public function refusePokemonExchange(PokemonExchange $pokemonExchange, PokemonExchangeManager $pokExManager) 
    {
        if($pokemonExchange->getTrainer1() === $this->getUser()) {
            $this->addFlash('success', 'You have withdrawn the exchange.');
        } elseif($pokemonExchange->getTrainer2() === $this->getUser()) {
            $this->addFlash('success', 'You have refused the exchange.');
        }
        $pokExManager->deletePokemonExchange($pokemonExchange, $this->getUser());

        return $this->redirectToRoute('pokemon_exchange_list');
    }
}
