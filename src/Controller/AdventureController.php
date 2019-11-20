<?php

namespace App\Controller;

use App\Handler\AdventureHandler;
use App\Manager\BattleFormManager;
use App\Repository\PokemonRepository;
use App\Serializer\PokemonSerializer;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdventureController extends AbstractController
{
    /**
     * @Route("/adventure/", name="adventure", methods={"GET"})
     */
    public function index(AdventureHandler $adventureHandler, ObjectManager $manager)
    {
        $adventureHandler->clear();
        $csrfToken = \uniqid();
        $this->getUser()->setCurrentGameId($csrfToken);
        $manager->flush();

        return $this->render('adventure/index.html.twig', [
            'csrfToken' => $csrfToken
        ]);
    }

    /**
     * @Route("/adventure/start", name="adventure_start", methods={"GET"})
     */
    public function start(PokemonRepository $pokemonRepository,BattleFormManager $formManager)
    {
        if(count($pokemonRepository->findReadyPokemonsByTrainer($this->getUser())) === 0) {
            $messages = [
                'messages' => ['You need at least one pokemon to go on adventure'],
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

    /**
     * @Route("/adventure/travel", name="adventure_travel", methods={"POST"})
     */
    public function travel(Request $request, AdventureHandler $adventureHandler, PokemonSerializer $pokemonSerialiser)
    {
        $data = $request->getContent();
        /** stdclass */
        $data = json_decode($data);
        $csrfToken = $data->csrfToken;
        if (!$this->isCsrfTokenValid($this->getUser()->getCurrentGameId(), $csrfToken)) {
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

    /**
     * @Route("/adventure/select-pokemon", name="adventure_pokemon_select", methods={"POST"})
     */
    public function selectPokemon(Request $request, AdventureHandler $adventureHandler, PokemonSerializer $pokemonSerialiser, PokemonRepository $pokemonRepository)
    {
        $data = $request->getContent();
        /** stdclass */
        $data = json_decode($data);
        $csrfToken = $data->csrfToken;
        if (!$this->isCsrfTokenValid($this->getUser()->getCurrentGameId(), $csrfToken)) {
            return $this->json([], 403);
        }
        $pokemon = $pokemonRepository->find($data->pokemonId);
        $data = $adventureHandler->handleSelectPokemon($pokemon);

        return $this->json([
            'form' => $data['form'],
            'opponent' => $pokemonSerialiser->normalizeForBattle($data['opponent']->getCurrentFighter()),
            'player' => $pokemonSerialiser->normalizeForBattle($data['player']->getCurrentFighter()),
            'messages' => $data['messages'],
            'centerImageUrl' => null,
            "turn" => 'player',
            'pokeballCount' => $this->getUser()->getPokeball(),
            'healingPotionCount' => $this->getUser()->getHealingPotion()
        ]);
    }

    /**
     * @Route("/adventure/attack", name="adventure_attack", methods={"POST"})
     */
    public function attack(Request $request, AdventureHandler $adventureHandler, PokemonSerializer $pokemonSerialiser)
    {
        $data = $request->getContent();
        /** stdclass */
        $data = json_decode($data);
        $csrfToken = $data->csrfToken;
        if (!$this->isCsrfTokenValid($this->getUser()->getCurrentGameId(), $csrfToken)) {
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
            'pokeballCount' => $this->getUser()->getPokeball(),
            'healingPotionCount' => $this->getUser()->getHealingPotion()
        ]);
    }

    /**
     * @Route("/adventure/heal", name="adventure_heal", methods={"POST"})
     */
    public function heal(Request $request, AdventureHandler $adventureHandler, PokemonSerializer $pokemonSerialiser)
    {
        $data = $request->getContent();
        /** stdclass */
        $data = json_decode($data);
        $csrfToken = $data->csrfToken;
        if (!$this->isCsrfTokenValid($this->getUser()->getCurrentGameId(), $csrfToken)) {
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
            'pokeballCount' => $this->getUser()->getPokeball(),
            'healingPotionCount' => $this->getUser()->getHealingPotion()
        ]);
    }

    /**
     * @Route("/adventure/throw-pokeball", name="adventure_pokeball_throw", methods={"POST"})
     */
    public function throwPokeball(Request $request, AdventureHandler $adventureHandler, PokemonSerializer $pokemonSerialiser)
    {
        $data = $request->getContent();
        /** stdclass */
        $data = json_decode($data);
        $csrfToken = $data->csrfToken;
        if (!$this->isCsrfTokenValid($this->getUser()->getCurrentGameId(), $csrfToken)) {
            return $this->json([], 403);
        }

        $data = $adventureHandler->handleThrowPokeball();

        return $this->json([
            'form' => $data['form'],
            'opponent' => $data['opponent'] ? $pokemonSerialiser->normalizeForBattle($data['opponent']->getCurrentFighter()) : null,
            'player' => $data['player'] ? $pokemonSerialiser->normalizeForBattle($data['player']->getCurrentFighter()) : null,
            'messages' => $data['messages'],
            'centerImageUrl' => $data['centerImageUrl'],
            "turn" => $data['turn'],
            'pokeballCount' => $this->getUser()->getPokeball(),
            'healingPotionCount' => $this->getUser()->getHealingPotion()
        ]);
    }

    /**
     * @Route("/adventure/leave", name="adventure_leave", methods={"POST"})
     */
    public function leave(Request $request, AdventureHandler $adventureHandler, PokemonSerializer $pokemonSerialiser)
    {
        $data = $request->getContent();
        /** stdclass */
        $data = json_decode($data);
        $csrfToken = $data->csrfToken;
        if (!$this->isCsrfTokenValid($this->getUser()->getCurrentGameId(), $csrfToken)) {
            return $this->json([], 403);
        }

        $data = $adventureHandler->handleLeave();

        return $this->json([
            'form' => $data['form'],
            'opponent' => $data['opponent'] ? $pokemonSerialiser->normalizeForBattle($data['opponent']->getCurrentFighter()) : null,
            'player' => $data['player'] ? $pokemonSerialiser->normalizeForBattle($data['player']->getCurrentFighter()) : null,
            'messages' => $data['messages'],
            'centerImageUrl' => null,
            "turn" => 'player',
            'pokeballCount' => $this->getUser()->getPokeball(),
            'healingPotionCount' => $this->getUser()->getHealingPotion()
        ]);
    }

    /**
     * @Route("/adventure/next", name="adventure_next", methods={"POST"})
     */
    public function next(Request $request, AdventureHandler $adventureHandler, PokemonSerializer $pokemonSerialiser)
    {
        $data = $request->getContent();
        /** stdclass */
        $data = json_decode($data);
        $csrfToken = $data->csrfToken;
        if (!$this->isCsrfTokenValid($this->getUser()->getCurrentGameId(), $csrfToken)) {
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
            'pokeballCount' => $this->getUser()->getPokeball(),
            'healingPotionCount' => $this->getUser()->getHealingPotion()
        ]);
    }
}
