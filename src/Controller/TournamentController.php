<?php

namespace App\Controller;

use App\Handler\TournamentHandler;
use App\Repository\PokemonRepository;
use App\Repository\BattleTeamRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\Command\SelectPokemonForTournamentType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class TournamentController extends AbstractController
{
    /**
     * @Route("/tournament/", name="tournament")
     */
    public function index(PokemonRepository $pokemonRepository)
    {
        $pokemons = $pokemonRepository->findAllFullHPByTrainer($user = $this->getUser());
        $isAllowed = false;
        if(count($pokemons) >= 3 ) {
            $isAllowed = true;
        }

        $wins = $user->getConsecutiveWin() % 3;
        if($wins === 0) { $buttonMessage = 'First round'; }
        elseif($wins === 1) { $buttonMessage = 'Semi-final round'; }
        elseif($wins === 2) { $buttonMessage = 'Final round'; }

        return $this->render('tournament/index.html.twig', [
            'isAllowed' => $isAllowed,
            'buttonMessage' => $buttonMessage
        ]);
    }

    /**
     * @Route("/tournament/battle", name="tournament_battle")
     */
    public function battle(Request $request, TournamentHandler $tournamentHandler, BattleTeamRepository $battleTeamRepository, PokemonRepository $pokemonRepository)
    {
        $playerTeam = $battleTeamRepository->findOneByTrainer($this->getUser());
        $pokemonsCount = $pokemonRepository->findAllFullHPByTrainerNumber($this->getUser());

        if($pokemonsCount < 3 && $playerTeam->getPokemons()->count() != 3) {
            return $this->redirectToRoute('tournament');
        }
        
        if($request->isMethod('POST')) {
            $data = $tournamentHandler->handleRequest($request);

            return $this->render('adventure/index.html.twig', [
                'form' => $data['form']->createView(),
                'opponent' => $data['opponent'],
                'player' => $data['player'],
                'messages' => $data['messages'],
                'centerImageUrl' => $data['centerImageUrl'] ?? null
            ]);
        }

        $tournamentHandler->clear();
        $tournamentHandler->createBattle();
        $form = $this->createForm(SelectPokemonForTournamentType::class);

        return $this->render('tournament/battle.html.twig', [
            'form' => $form->createView(),
            'opponent' => null,
            'player' => null,
            'messages' => null,
            'centerImageUrl' => null
        ]);
    }
}
