<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\PokemonExchange;
use App\Form\PokemonExchangeType;
use App\Repository\UserRepository;
use App\Repository\PokemonRepository;
use App\Manager\PokemonExchangeManager;
use App\Repository\PokemonExchangeRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class TrainerController extends AbstractController
{
    #[Route(path: '/trainer/', name: 'trainer_profile', methods: ['GET'])]
    public function profile(): Response
    {
        $user = $this->getUser();

        return $this->render('trainer/profile.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route(path: '/trainer/pokemons', name: 'trainer_pokemons', methods: ['GET'])]
    public function listPokemons(PokemonRepository $repository): Response
    {
        /** @var User */
        $user = $this->getUser();
        $pokemons = $repository->findPokemonsByTrainer($user);

        return $this->render('trainer/pokemons.html.twig', [
            'pokemons' => $pokemons,
        ]);
    }

    #[Route(path: '/trainer/list', name: 'trainer_list', methods: ['GET'])]
    public function showTrainers(UserRepository $userRepository): Response
    {
        $users = $userRepository->findAllActivated();

        return $this->render('trainer/show_trainers.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route(path: '/trainer/{id}', name: 'trainer_show', methods: ['GET'])]
    public function showTrainer(User $user): Response
    {
        return $this->render('trainer/show_trainer.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route(path: '/trainer/{id}/exchange/create', name: 'pokemon_exchange_create', methods: ['GET', 'POST'])]
    public function createPokemonExchange(
        User $trader,
        Request $request,
        PokemonExchangeManager $pokExManager
    ): Response {
        /** @var User */
        $user = $this->getUser();
        $pokemonExchangeForm = $this->createForm(PokemonExchangeType::class, null, [
            'user' => $user,
            'trader' => $trader
        ]);

        $pokemonExchangeForm->handleRequest($request);

        if ($pokemonExchangeForm->isSubmitted() && $pokemonExchangeForm->isValid()) {
            $pokExManager->createPokemonExchange($pokemonExchangeForm->getData());
            $this->addFlash('success', 'Your request of pokemons exchange has been submit.');

            return $this->redirectToRoute('pokemon_exchange_list');
        }

        return $this->render('trainer/pokemon_exchange_create.html.twig', [
            'pokemonExchangeForm' => $pokemonExchangeForm->createView()
        ]);
    }


    #[Route(path: '/exchange', name: 'pokemon_exchange_list', methods: ['GET'])]
    public function listPokemonExchange(PokemonExchangeRepository $pokExRepository): Response
    {
        /** @var User */
        $user = $this->getUser();
        $pokemonExchanges = $pokExRepository->findAllByTrainer($user);
        $csrfToken = $user->getUuid()->__toString();

        return $this->render('trainer/pokemon_exchange_list.html.twig', [
            'pokemonExchanges' => $pokemonExchanges,
            'csrfToken' => $csrfToken
        ]);
    }

    #[Route(path: '/exchange/count', name: 'pokemon_exchange_count', methods: ['GET'])]
    public function getPokemonsExchangeCount(PokemonExchangeRepository $pokExRepository): Response
    {
        /** @var User */
        $user = $this->getUser();
        $pokemonExchanges = $pokExRepository->findAllByTrainer($user);
        $exchangeCount = count($pokemonExchanges);

        return $this->json([
            'count' => $exchangeCount
        ]);
    }

    #[Route(path: '/exchange/{id}', name: 'pokemon_exchange_edit', methods: ['GET', 'POST'])]
    public function editPokemonExchange(
        PokemonExchange $pokemonExchange,
        Request $request,
        PokemonExchangeManager $pokExManager
    ): Response {
        $pokemonExchangeForm = $this->createForm(PokemonExchangeType::class, $pokemonExchange, [
            'user' => $pokemonExchange->getTrainer1(),
            'trader' => $pokemonExchange->getTrainer2()
        ]);

        $pokemonExchangeForm->handleRequest($request);
        if ($pokemonExchangeForm->isSubmitted() && $pokemonExchangeForm->isValid()) {
            /** @var User */
            $user = $this->getUser();
            $pokExManager->editPokemonExchange($pokemonExchange, $user);
            $this->addFlash('success', 'The modification of pokemons exchange has been submit.');

            return $this->redirectToRoute('pokemon_exchange_list');
        }

        return $this->render('trainer/pokemon_exchange_edit.html.twig', [
            'pokemonExchangeForm' => $pokemonExchangeForm->createView()
        ]);
    }

    #[Route(path: '/exchange/{id}/accept/{csrfToken}', name: 'pokemon_exchange_accept', methods: ['GET'])]
    public function acceptPokemonExchange(
        PokemonExchange $pokemonExchange,
        PokemonExchangeManager $pokExManager,
        string $csrfToken
    ): Response {
        /** @var User */
        $user = $this->getUser();

        if (!$this->isCsrfTokenValid($user->getUuid()->__toString(), $csrfToken)) {
            throw new AccessDeniedException('Forbidden.');
        }

        $pokExManager->acceptPokemonExchange($pokemonExchange, $user);
        $this->addFlash('success', 'You have accepted the exchange.');

        return $this->redirectToRoute('pokemon_exchange_list');
    }

    #[Route(path: '/exchange/{id}/refuse/{csrfToken}', name: 'pokemon_exchange_delete', methods: ['GET'])]
    public function refusePokemonExchange(
        PokemonExchange $pokemonExchange,
        PokemonExchangeManager $pokExManager,
        string $csrfToken
    ): Response {
        /** @var User */
        $user = $this->getUser();

        if (!$this->isCsrfTokenValid($user->getUuid()->__toString(), $csrfToken)) {
            throw new AccessDeniedException('Forbidden.');
        }

        if ($pokemonExchange->getTrainer1() === $user) {
            $this->addFlash('success', 'You have withdrawn the exchange.');
        } elseif ($pokemonExchange->getTrainer2() === $user) {
            $this->addFlash('success', 'You have refused the exchange.');
        }

        $pokExManager->deletePokemonExchange($pokemonExchange, $user);

        return $this->redirectToRoute('pokemon_exchange_list');
    }
}
