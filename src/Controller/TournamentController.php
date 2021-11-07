<?php

namespace App\Controller;

use App\Handler\TournamentHandler;
use App\Manager\BattleFormManager;
use App\Repository\PokemonRepository;
use App\Serializer\PokemonSerializer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class TournamentController extends AbstractController
{
    /**
     * @Route("/tournament/", name="tournament", methods={"GET"})
     */
    public function index(PokemonRepository $pokemonRepository)
    {
        $pokemons = $pokemonRepository->findAllFullHPByTrainer($user = $this->getUser());
        $isAllowed = false;

        if (count($pokemons) >= 3) {
            $isAllowed = true;
        }

        $wins = $user->getConsecutiveWin() % 3;
        $baseMessage = 'Ready for the ';

        if ($wins === 0) {
            $buttonMessage = $baseMessage . 'first round';
        } elseif ($wins === 1) {
            $buttonMessage = $baseMessage . 'semi-final';
        } elseif ($wins === 2) {
            $buttonMessage = $baseMessage . 'final';
        }

        return $this->render('tournament/index.html.twig', [
            'isAllowed' => $isAllowed,
            'buttonMessage' => $buttonMessage
        ]);
    }

    /**
     * @Route("/tournament/battle", name="tournament_battle", methods={"GET"})
     */
    public function battle(
        TournamentHandler $tournamentHandler,
        PokemonRepository $pokemonRepository,
        EntityManagerInterface $manager
    ) {
        $user = $this->getUser();
        $pokemonsCount = $pokemonRepository->findAllFullHPByTrainerNumber($user);
        $csrfToken = \uniqid();
        $user->setCurrentGameId($csrfToken);
        $manager->flush();

        if ($pokemonsCount < 3) {
            return $this->redirectToRoute('tournament');
        }

        $tournamentHandler->clear();

        return $this->render('tournament/battle.html.twig', [
            'csrfToken' => $csrfToken
        ]);
    }

    /**
     * @Route("/tournament/start", name="tournament_start", methods={"GET"})
     */
    public function start(TournamentHandler $tournamentHandler, BattleFormManager $formManager)
    {
        $tournamentHandler->createBattle();
        $selectField = $formManager->createSelectPokemonFieldForTournament();
        $wins = $this->getUser()->getConsecutiveWin() % 3;
        $messages = [
            'messages' => ['You will battle against a trainer.', 'Select the 1st pokemon to fight!'],
            'textColor' => 'text-white'
        ];

        return $this->json([
            'form' => [$selectField],
            'opponent' => null,
            'player' => null,
            'messages' => $messages,
            'centerImageUrl' => ['/images/round' . $wins . '.png'],
            'turn' => 'player',
            'healingPotionCount' => null,
            'pokeballCount' => null
        ]);
    }

    /**
     * @Route("/tournament/select-pokemon", name="tournament_pokemon_select", methods={"POST"})
     */
    public function selectPokemon(
        Request $request,
        TournamentHandler $tournamentHandler,
        PokemonRepository $pokemonRepository
    ) {
        $data = $request->getContent();
        /** stdclass */
        $data = json_decode($data);
        $csrfToken = $data->csrfToken;

        if (!$this->isCsrfTokenValid($this->getUser()->getCurrentGameId(), $csrfToken)) {
            return $this->json([], 403);
        }
        $pokemon = $pokemonRepository->findOneBy(['uuid' => $data->pokemonUuid]);
        $data = $tournamentHandler->handleSelectPokemon($pokemon);

        return $this->json([
            'form' => $data['form'],
            'opponent' => null,
            'player' => null,
            'messages' => $data['messages'],
            'centerImageUrl' => $data['centerImageUrl'],
            'turn' => 'player',
            'pokeballCount' => $this->getUser()->getPokeball(),
            'healingPotionCount' => $this->getUser()->getHealingPotion()
        ]);
    }

    /**
     * @Route("/tournament/attack", name="tournament_attack", methods={"POST"})
     */
    public function attack(
        Request $request,
        TournamentHandler $tournamentHandler,
        PokemonSerializer $pokemonSerialiser
    ) {
        $data = $request->getContent();
        /** stdclass */
        $data = json_decode($data);
        $csrfToken = $data->csrfToken;

        if (!$this->isCsrfTokenValid($this->getUser()->getCurrentGameId(), $csrfToken)) {
            return $this->json([], 403);
        }

        $data = $tournamentHandler->handleAttack();

        return $this->json([
            'form' => $data['form'],
            'opponent' => $pokemonSerialiser->normalizeForBattle($data['opponent']->getCurrentFighter()),
            'player' => $pokemonSerialiser->normalizeForBattle($data['player']->getCurrentFighter()),
            'messages' => $data['messages'],
            'centerImageUrl' => null,
            'turn' => $data['turn'],
            'pokeballCount' => $this->getUser()->getPokeball(),
            'healingPotionCount' => $this->getUser()->getHealingPotion()
        ]);
    }

    /**
     * @Route("/tournament/heal", name="tournament_heal", methods={"POST"})
     */
    public function heal(
        Request $request,
        TournamentHandler $tournamentHandler,
        PokemonSerializer $pokemonSerialiser
    ) {
        $data = $request->getContent();
        /** stdclass */
        $data = json_decode($data);
        $csrfToken = $data->csrfToken;

        if (!$this->isCsrfTokenValid($this->getUser()->getCurrentGameId(), $csrfToken)) {
            return $this->json([], 403);
        }

        $data = $tournamentHandler->handleHeal();

        return $this->json([
            'form' => $data['form'],
            'opponent' => $pokemonSerialiser->normalizeForBattle($data['opponent']->getCurrentFighter()),
            'player' => $pokemonSerialiser->normalizeForBattle($data['player']->getCurrentFighter()),
            'messages' => $data['messages'],
            'centerImageUrl' => null,
            'turn' => 'player',
            'pokeballCount' => $this->getUser()->getPokeball(),
            'healingPotionCount' => $this->getUser()->getHealingPotion()
        ]);
    }

    /**
     * @Route("/tournament/throw-pokeball", name="tournament_pokeball_throw", methods={"POST"})
     */
    public function throwPokeball(
        Request $request,
        TournamentHandler $tournamentHandler,
        PokemonSerializer $pokemonSerialiser
    ) {
        $data = $request->getContent();
        /** stdclass */
        $data = json_decode($data);
        $csrfToken = $data->csrfToken;

        if (!$this->isCsrfTokenValid($this->getUser()->getCurrentGameId(), $csrfToken)) {
            return $this->json([], 403);
        }

        $data = $tournamentHandler->handleThrowPokeball();

        return $this->json([
            'form' => $data['form'],
            'opponent' => $data['opponent'] ?
                $pokemonSerialiser->normalizeForBattle($data['opponent']->getCurrentFighter()) : null,
            'player' => $data['player'] ?
                $pokemonSerialiser->normalizeForBattle($data['player']->getCurrentFighter()) : null,
            'messages' => $data['messages'],
            'centerImageUrl' => null,
            'turn' => $data['turn'],
            'pokeballCount' => $this->getUser()->getPokeball(),
            'healingPotionCount' => $this->getUser()->getHealingPotion()
        ]);
    }

    /**
     * @Route("/tournament/leave", name="tournament_leave", methods={"POST"})
     */
    public function leave(
        Request $request,
        TournamentHandler $tournamentHandler,
        PokemonSerializer $pokemonSerialiser
    ) {
        $data = $request->getContent();
        /** stdclass */
        $data = json_decode($data);
        $csrfToken = $data->csrfToken;

        if (!$this->isCsrfTokenValid($this->getUser()->getCurrentGameId(), $csrfToken)) {
            return $this->json([], 403);
        }

        $data = $tournamentHandler->handleLeave();

        return $this->json([
            'form' => $data['form'],
            'opponent' => $data['opponent'] ?
                $pokemonSerialiser->normalizeForBattle($data['opponent']->getCurrentFighter()) : null,
            'player' => $data['player'] ?
                $pokemonSerialiser->normalizeForBattle($data['player']->getCurrentFighter()) : null,
            'messages' => $data['messages'],
            'centerImageUrl' => null,
            'turn' => 'player',
            'pokeballCount' => $this->getUser()->getPokeball(),
            'healingPotionCount' => $this->getUser()->getHealingPotion()
        ]);
    }

    /**
     * @Route("/tournament/next", name="tournament_next", methods={"POST"})
     */
    public function next(
        Request $request,
        TournamentHandler $tournamentHandler,
        PokemonSerializer $pokemonSerialiser
    ) {
        $data = $request->getContent();
        /** stdclass */
        $data = json_decode($data);
        $csrfToken = $data->csrfToken;

        if (!$this->isCsrfTokenValid($this->getUser()->getCurrentGameId(), $csrfToken)) {
            return $this->json([], 403);
        }

        $data = $tournamentHandler->handleNext();

        return $this->json([
            'form' => $data['form'],
            'opponent' => $data['opponent'] ?
                $pokemonSerialiser->normalizeForBattle($data['opponent']->getCurrentFighter()) : null,
            'player' => $data['opponent'] ?
                $pokemonSerialiser->normalizeForBattle($data['player']->getCurrentFighter()) : null,
            'messages' => $data['messages'],
            'centerImageUrl' => isset($data['centerImageUrl']) ? $data['centerImageUrl'] : null,
            'turn' => 'player',
            'pokeballCount' => $this->getUser()->getPokeball(),
            'healingPotionCount' => $this->getUser()->getHealingPotion()
        ]);
    }

    /**
     * @Route("/tournament/restore-pokemons", name="tournament_pokemons_restore", methods={"POST"})
     */
    public function restorePokemons(Request $request, TournamentHandler $tournamentHandler)
    {
        $data = $request->getContent();
        /** stdclass */
        $data = json_decode($data);
        $csrfToken = $data->csrfToken;

        if (!$this->isCsrfTokenValid($this->getUser()->getCurrentGameId(), $csrfToken)) {
            return $this->json([], 403);
        }

        $data = $tournamentHandler->handleRestorePokemons();
        $round = $this->getUser()->getConsecutiveWin() % 3;

        return $this->json([
            'form' => $data['form'],
            'opponent' => null,
            'player' => null,
            'messages' => $data['messages'],
            'centerImageUrl' => ['/images/round' . $round . '.png'],
            'turn' => 'player',
            'pokeballCount' => null,
            'healingPotionCount' => null
        ]);
    }
}
