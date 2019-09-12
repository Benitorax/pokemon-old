<?php
namespace App\Handler;

use App\Repository\PokemonRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Security;

class CityHandler
{
    private $user;
    private $pokemonRepository;
    private $manager;

    const POKEBALL_PRICE = 10;
    const HEALTH_POTION_PRICE = 15;

    public function __construct(Security $security, PokemonRepository $pokemonRepository, ObjectManager $manager) 
    {
        $this->user = $security->getUser();
        $this->pokemonRepository = $pokemonRepository;
        $this->manager = $manager;
    }

    public function handleShopForm($form)
    {
        $form = $form->getData();
        if(is_int($number = $form['pokeball'])) {
            $this->user->addPokeball($number);
            $this->user->decreasePokedollar($number*self::POKEBALL_PRICE);
        }

        if(is_int($number = $form['healthPotion'])) {
            $this->user->addHealthPotion($number);
            $this->user->decreasePokedollar($number*self::HEALTH_POTION_PRICE);
        }

        $this->manager->flush();
    }

    public function handleInfirmaryForm($form)
    {
        $request = $form->getClickedButton()->getName();

        if($request === 'restorePokemon') {
            $this->restorePokemon();

        } elseif($request === 'donatePokemon') {

            if(is_int($pokemonId = $form->getData()['selectPokemon'])) {

                $this->donatePokemon($pokemonId);
            }
        }
    }

    public function restorePokemon() {
        $pokemons = $this->pokemonRepository->findPokemonsByTrainer($this->user);
        foreach($pokemons as $pokemon) {
            $pokemon->setHealthPoint(100);
            $pokemon->setIsSleep(false);
        }

        $this->manager->flush();
    }

    public function donatePokemon($pokemonId) {
        $pokemons = $this->pokemonRepository->findPokemonsByTrainer($this->user);
        $donatedPokemon = $this->pokemonRepository->find($pokemonId);
        
        if(in_array($donatedPokemon, $pokemons)) {
            $this->user->removePokemon($donatedPokemon);
            $this->manager->remove($donatedPokemon);
            $this->manager->flush();
        }
    }
}