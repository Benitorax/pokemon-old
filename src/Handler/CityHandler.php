<?php
namespace App\Handler;

use App\Repository\PokemonRepository;
use App\Security\CustomSession;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Security;

class CityHandler
{
    private $user;
    private $pokemonRepository;
    private $manager;
    private $session;

    const POKEBALL_PRICE = 10;
    const HEALTH_POTION_PRICE = 15;

    public function __construct(Security $security, PokemonRepository $pokemonRepository, ObjectManager $manager, CustomSession $session) 
    {
        $this->user = $security->getUser();
        $this->pokemonRepository = $pokemonRepository;
        $this->manager = $manager;
        $this->session = $session;
    }

    public function handleShopForm($form)
    {
        $form = $form->getData();
        $isValidated = $this->validatePurchaseMoney($form);

        if(!$isValidated) {
            return;
        }

        if(is_int($pokeballNumber = $form['pokeball'])) {
            $this->user->addPokeball($pokeballNumber);
            $this->user->decreasePokedollar($pokeballNumber*self::POKEBALL_PRICE);

            $pokeballMessage = sprintf("You have now %s pokeballs (+%d)", $this->user->getPokeball(), $pokeballNumber);
            $this->session->add('success', $pokeballMessage);
        }

        if(is_int($hpNumber = $form['healthPotion'])) {
            $this->user->addHealthPotion($hpNumber);
            $this->user->decreasePokedollar($hpNumber*self::HEALTH_POTION_PRICE);

            $hpMessage = sprintf("You have now %s health potions (+%d)", $this->user->getHealthPotion(), $hpNumber);
            $this->session->add('success', $hpMessage);
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
            } else {
                $this->session->add('danger', 'You have to select a pokemon');
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

        $this->session->add('success', 'Your pokemons are now in good shape.');
    }

    public function donatePokemon($pokemonId) {
        $pokemons = $this->pokemonRepository->findPokemonsByTrainer($this->user);
        $donatedPokemon = $this->pokemonRepository->find($pokemonId);

        if($pokemons->count() <= 1) {
            $this->session->add('danger', "You can't donate your only pokemon.");
        }

        if(in_array($donatedPokemon, $pokemons)) {
            $this->user->removePokemon($donatedPokemon);
            $this->manager->remove($donatedPokemon);
            $this->manager->flush();
            $this->session->add('success', sprintf("%s has been donated", $donatedPokemon->getName()));
        }
    }

    public function validatePurchaseMoney($data) {
        $money = $this->user->getPokedollar();
        $amount = $data['pokeball'] * self::POKEBALL_PRICE +
                  $data['healthPotion'] * self::HEALTH_POTION_PRICE;
        
        $isValidated = $money >= $amount;
        if(!$isValidated) {
            $errorMessage = sprintf("You don't have enough money to purchase (bill: %d $)", $amount);
            $this->session->add('danger', $errorMessage);
        } 

        return $isValidated;
    }
}