<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ShopType;
use App\Form\InfirmaryType;
use App\Handler\CityHandler;
use App\Repository\PokemonRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CityController extends AbstractController
{
    #[Route(path: '/city', name: 'city', methods: ['GET', 'POST'])]
    public function index(
        Request $request,
        CityHandler $cityHandler,
        PokemonRepository $pokemonRepository
    ): Response {
        $shopForm = $this->createForm(ShopType::class);
        $infirmaryForm = $this->createForm(InfirmaryType::class);

        if ($request->isMethod('POST')) {
            $shopForm->handleRequest($request);
            $infirmaryForm->handleRequest($request);

            if ($shopForm->isSubmitted() && $shopForm->isValid()) {
                $cityHandler->handleShopForm($shopForm);

                return $this->redirectToRoute('city');
            } elseif ($infirmaryForm->isSubmitted() && $infirmaryForm->isValid()) {
                $cityHandler->handleInfirmaryForm($infirmaryForm);

                return $this->redirectToRoute('city');
            }
        }

        /** @var User */
        $user = $this->getUser();
        $pokemons = $pokemonRepository->findAllFullHPByTrainer($user);

        return $this->render('city/index.html.twig', [
            'shopForm' => $shopForm->createView(),
            'infirmaryForm' => $infirmaryForm->createView(),
            'pokemonFullHPCount' => count($pokemons)
        ]);
    }

    #[Route(path: '/city/association-trainer/help', name: 'city_association_trainer_help', methods: ['GET'])]
    public function trainerAssociationHelp(
        EntityManagerInterface $manager,
        PokemonRepository $pokemonRepository
    ): Response {
        /** @var User */
        $user = $this->getUser();
        $pokedollar = $user->getPokedollar();
        $pokemons = $user->getPokemons();
        $fullHPPokemons = $pokemonRepository->findAllFullHPByTrainer($user);

        if (count($pokemons) > 3 && count($fullHPPokemons) < 3 && $pokedollar < 30) {
            $user->increasePokedollar(50);
            $manager->flush();
            $this->addFlash('success', "The trainer's association gives you 50 $.");
        }

        return $this->redirectToRoute('city');
    }
}
