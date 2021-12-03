<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Pokemon;
use App\Handler\AdventureHandler;
use App\Manager\BattleFormManager;
use App\Repository\PokemonRepository;
use App\Serializer\PokemonSerializer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdventureController extends AbstractController
{
    #[Route(path: '/adventure/', name: 'adventure', methods: ['GET'])]
    public function index(AdventureHandler $adventureHandler, EntityManagerInterface $manager): Response
    {
        $adventureHandler->clear();
        $csrfToken = \uniqid();

        /** @var User */
        $user = $this->getUser();
        $user->setCurrentGameId($csrfToken);
        $manager->flush();

        return $this->render('adventure/index.html.twig', [
            'csrfToken' => $csrfToken
        ]);
    }

    #[Route(path: '/adventure/start', name: 'adventure_start', methods: ['GET'])]
    public function start(PokemonRepository $pokemonRepository, BattleFormManager $formManager): Response
    {
        /** @var User */
        $user = $this->getUser();
        $pokemons = $pokemonRepository->findReadyPokemonsByTrainer($user);

        if ($pokemons === [] || $pokemons === null) {
            $messages = [
                'messages' => [
                    'You need at least one pokemon to go on adventure',
                    'You can take care of them in the city.'
                ],
                'textColor' => 'text-white'
            ];

            return $this->json(['messages' => $messages]);
        }

        $travelButton = $formManager->createTravelButton();
        $messages = [
            'messages' => ['Let\'s go for adventure!', 'You might run into some awesome pokemons!'],
            'textColor' => 'text-white'
        ];

        return $this->json([
            'form' => [$travelButton],
            'opponent' => null,
            'player' => null,
            'messages' => $messages,
            'centerImageUrl' => null,
            "turn" => 'player',
            'healingPotionCount' => null,
            'pokeballCount' => null
        ]);
    }

    #[Route(path: '/adventure/travel', name: 'adventure_travel', methods: ['POST'])]
    public function travel(
        Request $request,
        PokemonRepository $pokemonRepository,
        AdventureHandler $adventureHandler,
        PokemonSerializer $pokemonSerialiser
    ): Response {
        /** @var User */
        $user = $this->getUser();
        $pokemons = $pokemonRepository->findReadyPokemonsByTrainer($user);
        if ($pokemons === [] || $pokemons === null) {
            $messages = [
                'messages' => [
                    'You need at least one pokemon to go on adventure.',
                    'You can take care of them in the city.'
                ],
                'textColor' => 'text-white'
            ];
            return $this->json([
                'opponent' => null,
                'player' => null,
                'messages' => $messages
            ]);
        }

        $data = $request->getContent();
        /** stdclass */
        $data = json_decode((string) $data);
        $csrfToken = $data->csrfToken;

        /** @var user */
        $user = $this->getUser();

        if (!$this->isCsrfTokenValid($user->getCurrentGameId(), $csrfToken)) {
            return $this->json([], 403);
        }

        $data = $adventureHandler->handleTravel();

        return $this->json([
            'form' => $data['form'],
            'opponent' => $pokemonSerialiser->normalizeForBattle($data['opponent']->getCurrentFighter()),
            'player' => null,
            'messages' => $data['messages'],
            'centerImageUrl' => null,
            "turn" => 'player',
            'healingPotionCount' => null,
            'pokeballCount' => null
        ]);
    }

    #[Route(path: '/adventure/select-pokemon', name: 'adventure_pokemon_select', methods: ['POST'])]
    public function selectPokemon(
        Request $request,
        AdventureHandler $adventureHandler,
        PokemonSerializer $pokemonSerialiser,
        PokemonRepository $pokemonRepository
    ): Response {
        $data = $request->getContent();
        /** stdclass */
        $data = json_decode((string) $data);
        $csrfToken = $data->csrfToken;

        /** @var user */
        $user = $this->getUser();

        if (!$this->isCsrfTokenValid($user->getCurrentGameId(), $csrfToken)) {
            return $this->json([], 403);
        }

        /** @var Pokemon */
        $pokemon = $pokemonRepository->findOneBy(['uuid' => $data->pokemonUuid]);
        $data = $adventureHandler->handleSelectPokemon($pokemon);

        return $this->json([
            'form' => $data['form'],
            'opponent' => $pokemonSerialiser->normalizeForBattle($data['opponent']->getCurrentFighter()),
            'player' => $pokemonSerialiser->normalizeForBattle($data['player']->getCurrentFighter()),
            'messages' => $data['messages'],
            'centerImageUrl' => null,
            "turn" => 'player',
            'pokeballCount' => $user->getPokeball(),
            'healingPotionCount' => $user->getHealingPotion()
        ]);
    }

    #[Route(path: '/adventure/attack', name: 'adventure_attack', methods: ['POST'])]
    public function attack(
        Request $request,
        AdventureHandler $adventureHandler,
        PokemonSerializer $pokemonSerialiser
    ): Response {
        $data = $request->getContent();
        /** stdclass */
        $data = json_decode((string) $data);
        $csrfToken = $data->csrfToken;

        /** @var user */
        $user = $this->getUser();

        if (!$this->isCsrfTokenValid($user->getCurrentGameId(), $csrfToken)) {
            return $this->json([], 403);
        }

        $data = $adventureHandler->handleAttack();

        return $this->json([
            'form' => $data['form'],
            'opponent' => $pokemonSerialiser->normalizeForBattle($data['opponent']->getCurrentFighter()),
            'player' => $pokemonSerialiser->normalizeForBattle($data['player']->getCurrentFighter()),
            'messages' => $data['messages'],
            'centerImageUrl' => null,
            "turn" => $data['turn'],
            'pokeballCount' => $user->getPokeball(),
            'healingPotionCount' => $user->getHealingPotion()
        ]);
    }

    #[Route(path: '/adventure/heal', name: 'adventure_heal', methods: ['POST'])]
    public function heal(
        Request $request,
        AdventureHandler $adventureHandler,
        PokemonSerializer $pokemonSerialiser
    ): Response {
        $data = $request->getContent();
        /** stdclass */
        $data = json_decode((string) $data);
        $csrfToken = $data->csrfToken;

        /** @var user */
        $user = $this->getUser();

        if (!$this->isCsrfTokenValid($user->getCurrentGameId(), $csrfToken)) {
            return $this->json([], 403);
        }

        $data = $adventureHandler->handleHeal();

        return $this->json([
            'form' => $data['form'],
            'opponent' => $pokemonSerialiser->normalizeForBattle($data['opponent']->getCurrentFighter()),
            'player' => $pokemonSerialiser->normalizeForBattle($data['player']->getCurrentFighter()),
            'messages' => $data['messages'],
            'centerImageUrl' => null,
            "turn" => 'player',
            'pokeballCount' => $user->getPokeball(),
            'healingPotionCount' => $user->getHealingPotion()
        ]);
    }

    #[Route(path: '/adventure/throw-pokeball', name: 'adventure_pokeball_throw', methods: ['POST'])]
    public function throwPokeball(
        Request $request,
        AdventureHandler $adventureHandler,
        PokemonSerializer $pokemonSerialiser
    ): Response {
        $data = $request->getContent();
        /** stdclass */
        $data = json_decode((string) $data);
        $csrfToken = $data->csrfToken;

        /** @var user */
        $user = $this->getUser();

        if (!$this->isCsrfTokenValid($user->getCurrentGameId(), $csrfToken)) {
            return $this->json([], 403);
        }

        $data = $adventureHandler->handleThrowPokeball();

        return $this->json([
            'form' => $data['form'],
            'opponent' => $data['opponent'] ?
                $pokemonSerialiser->normalizeForBattle($data['opponent']->getCurrentFighter()) : null,
            'player' => $data['player'] ?
                $pokemonSerialiser->normalizeForBattle($data['player']->getCurrentFighter()) : null,
            'messages' => $data['messages'],
            'centerImageUrl' => $data['centerImageUrl'],
            "turn" => $data['turn'],
            'pokeballCount' => $user->getPokeball(),
            'healingPotionCount' => $user->getHealingPotion()
        ]);
    }

    #[Route(path: '/adventure/leave', name: 'adventure_leave', methods: ['POST'])]
    public function leave(
        Request $request,
        AdventureHandler $adventureHandler,
        PokemonSerializer $pokemonSerialiser
    ): Response {
        $data = $request->getContent();
        /** stdclass */
        $data = json_decode((string) $data);
        $csrfToken = $data->csrfToken;

        /** @var user */
        $user = $this->getUser();

        if (!$this->isCsrfTokenValid($user->getCurrentGameId(), $csrfToken)) {
            return $this->json([], 403);
        }

        $data = $adventureHandler->handleLeave();

        return $this->json([
            'form' => $data['form'],
            'opponent' => $data['opponent'] ?
                $pokemonSerialiser->normalizeForBattle($data['opponent']->getCurrentFighter()) : null,
            'player' => $data['player'] ?
                $pokemonSerialiser->normalizeForBattle($data['player']->getCurrentFighter()) : null,
            'messages' => $data['messages'],
            'centerImageUrl' => null,
            "turn" => 'player',
            'pokeballCount' => $user->getPokeball(),
            'healingPotionCount' => $user->getHealingPotion()
        ]);
    }

    #[Route(path: '/adventure/next', name: 'adventure_next', methods: ['POST'])]
    public function next(
        Request $request,
        AdventureHandler $adventureHandler,
        PokemonSerializer $pokemonSerialiser
    ): Response {
        $data = $request->getContent();
        /** stdclass */
        $data = json_decode((string) $data);
        $csrfToken = $data->csrfToken;

        /** @var user */
        $user = $this->getUser();

        if (!$this->isCsrfTokenValid($user->getCurrentGameId(), $csrfToken)) {
            return $this->json([], 403);
        }

        $data = $adventureHandler->handleNext();

        return $this->json([
            'form' => $data['form'],
            'opponent' => $pokemonSerialiser->normalizeForBattle($data['opponent']->getCurrentFighter()),
            'player' => $pokemonSerialiser->normalizeForBattle($data['player']->getCurrentFighter()),
            'messages' => $data['messages'],
            'centerImageUrl' => null,
            "turn" => 'player',
            'pokeballCount' => $user->getPokeball(),
            'healingPotionCount' => $user->getHealingPotion()
        ]);
    }
}
