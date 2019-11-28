<?php

namespace App\Controller;

use App\Form\InfirmaryType;
use App\Form\ShopType;
use App\Handler\CityHandler;
use App\Repository\PokemonRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class CityController extends AbstractController
{
    /**
     * @Route("/city", name="city", methods={"GET","POST"})
     */
    public function index(Request $request, CityHandler $cityHandler, PokemonRepository $pokemonRepository)
    {
        $shopForm = $this->createForm(ShopType::class);
        $infirmaryForm = $this->createForm(InfirmaryType::class);

        if($request->isMethod('POST')) {
            $shopForm->handleRequest($request);
            $infirmaryForm->handleRequest($request);

            if($shopForm->isSubmitted() && $shopForm->isValid()) {
                $cityHandler->handleShopForm($shopForm);
                return $this->redirectToRoute('city');

            } elseif($infirmaryForm->isSubmitted() && $infirmaryForm->isValid()) {
                $cityHandler->handleInfirmaryForm($infirmaryForm);
                return $this->redirectToRoute('city');
            }
        }

        $pokemons = $pokemonRepository->findAllFullHPByTrainer($this->getUser());
        
        return $this->render('city/index.html.twig', [
            'shopForm' => $shopForm->createView(),
            'infirmaryForm' => $infirmaryForm->createView(),
            'pokemonFullHPCount' => count($pokemons)
        ]);
    }

    /**
     * @Route("/city/association-trainer/help", name="city_association_trainer_help", methods={"GET"})
     */
    public function trainerAssociationHelp(ObjectManager $manager, PokemonRepository $pokemonRepository)
    {
        $user = $this->getUser();
        $pokedollar = $user->getPokedollar();
        $pokemons = $user->getPokemons();
        $fullHPPokemons = $pokemonRepository->findAllFullHPByTrainer($user);

        if(count($pokemons) > 3 && count($fullHPPokemons) < 3 && $pokedollar < 30) {
            $user->increasePokedollar(50);
            $manager->flush();
            $this->addFlash('success', "The trainer's association gives you 50 $.");
        }

        return $this->redirectToRoute('city');
    }
}
