<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\PokemonExchange;
use App\Form\PokemonExchangeType;
use App\Manager\PokemonExchangeManager;
use App\Repository\PokemonExchangeRepository;
use App\Repository\UserRepository;
use App\Repository\PokemonRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class TrainerController extends AbstractController
{
    /**
     * @Route("/trainer/", name="trainer_profile", methods={"GET"})
     */
    public function profile()
    {
        $user = $this->getUser();
        return $this->render('trainer/profile.html.twig', [
            'user' => $user,
        ]);
    }

    /**
     * @Route("/trainer/pokemons", name="trainer_pokemons", methods={"GET"})
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
     * @Route("/trainer/list", name="trainer_list", methods={"GET"})
     */
    public function showTrainers(UserRepository $userRepository)
    {
        $users = $userRepository->findAllActivated();
        return $this->render('trainer/show_trainers.html.twig', [
            'users' => $users,
        ]);
    }

    /**
     * @Route("/trainer/{id}", name="trainer_show", methods={"GET"})
     */
    public function showTrainer(User $user)
    {
        return $this->render('trainer/show_trainer.html.twig', [
            'user' => $user,
        ]);
    }

    /**
     * @Route("/trainer/{id}/exchange/create", name="pokemon_exchange_create", methods={"GET","POST"})
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
     * @Route("/exchange", name="pokemon_exchange_list", methods={"GET"})
     */
    public function listPokemonExchange(PokemonExchangeRepository $pokExRepository) 
    {
        $pokemonExchanges = $pokExRepository->findAllByTrainer($this->getUser());

        $csrfToken = $this->getUser()->getId()->toString();

        return $this->render('trainer/pokemon_exchange_list.html.twig', [
            'pokemonExchanges' => $pokemonExchanges,
            'csrfToken' => $csrfToken
        ]);
    }
    
    /**
     * @Route("/exchange/count", name="pokemon_exchange_count", methods={"GET"})
     */
    public function getPokemonsExchangeCount(PokemonExchangeRepository $pokExRepository)
    {
        $user = $this->getUser();
        $pokemonExchanges = $pokExRepository->findAllByTrainer($user);
        $exchangeCount = count($pokemonExchanges);

        return $this->json([
            'exchangeCount' => $exchangeCount
        ]);
    }

    /**
     * @Route("/exchange/{id}", name="pokemon_exchange_edit", methods={"GET","POST"})
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
     * @Route("/exchange/{id}/accept/{csrfToken}", name="pokemon_exchange_accept", methods={"GET"})
     */
    public function acceptPokemonExchange(PokemonExchange $pokemonExchange, PokemonExchangeManager $pokExManager, $csrfToken) 
    {
        if (!$this->isCsrfTokenValid($this->getUser()->getId()->toString(), $csrfToken)) {
            throw new AccessDeniedException('Forbidden.');
        }

        $pokExManager->acceptPokemonExchange($pokemonExchange, $this->getUser());
        $this->addFlash('success', 'You have accepted the exchange.');
        return $this->redirectToRoute('pokemon_exchange_list');
    }

    /**
     * @Route("/exchange/{id}/refuse/{csrfToken}", name="pokemon_exchange_delete", methods={"GET"})
     */
    public function refusePokemonExchange(PokemonExchange $pokemonExchange, PokemonExchangeManager $pokExManager, $csrfToken) 
    {
        if (!$this->isCsrfTokenValid($this->getUser()->getId()->toString(), $csrfToken)) {
            throw new AccessDeniedException('Forbidden.');
        }

        if($pokemonExchange->getTrainer1() === $this->getUser()) {
            $this->addFlash('success', 'You have withdrawn the exchange.');
        } elseif($pokemonExchange->getTrainer2() === $this->getUser()) {
            $this->addFlash('success', 'You have refused the exchange.');
        }
        $pokExManager->deletePokemonExchange($pokemonExchange, $this->getUser());

        return $this->redirectToRoute('pokemon_exchange_list');
    }
}
