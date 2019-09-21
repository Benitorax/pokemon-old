<?php

namespace App\Controller;

use App\Entity\BattleTeam;
use App\Handler\TournamentHandler;
use App\Repository\PokemonRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\Command\SelectPokemonForTournamentType;
use App\Repository\BattleTeamRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class TournamentController extends AbstractController
{
    /**
     * @Route("/tournament/", name="tournament")
     */
    public function index(Request $request, TournamentHandler $tournamentHandler, PokemonRepository $pokemonRepository, BattleTeamRepository $battleTeamRepository)
    {
        $pokemons = $pokemonRepository->findAllFullHPByTrainer($this->getUser());
        $playerTeam = $battleTeamRepository->findOneBy(['trainer' =>$this->getUser()]);
        if(count($pokemons) < 3 && !$playerTeam) {
            return $this->render('tournament/not_enough_pokemons.html.twig');
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

        return $this->render('tournament/index.html.twig', [
            'form' => $form->createView(),
            'opponent' => null,
            'player' => null,
            'messages' => null,
            'centerImageUrl' => null
        ]);
    }
}
