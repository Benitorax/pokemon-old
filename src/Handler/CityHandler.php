<?php

namespace App\Handler;

use App\Entity\User;
use App\Entity\Pokemon;
use App\Security\CustomSession;
use App\Repository\PokemonRepository;
use App\Manager\PokemonExchangeManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Security\Core\Security;

class CityHandler
{
    private User $user;
    private PokemonRepository $pokemonRepository;
    private EntityManagerInterface $manager;
    private CustomSession $session;
    private PokemonExchangeManager $pokExManager;
    public const POKEBALL_PRICE = 10;
    public const HEALTH_POTION_PRICE = 15;
    public const RESTORE_POKEMON_PRICE = 30;

    public function __construct(
        Security $security,
        PokemonRepository $pokemonRepository,
        EntityManagerInterface $manager,
        CustomSession $session,
        PokemonExchangeManager $pokExManager
    ) {
        /** @var User */
        $user = $security->getUser();
        $this->user = $user;
        $this->pokemonRepository = $pokemonRepository;
        $this->manager = $manager;
        $this->session = $session;
        $this->pokExManager = $pokExManager;
    }

    public function handleShopForm(FormInterface $form): void
    {
        $form = $form->getData();
        $isValidated = $this->validatePurchaseMoney($form);

        if (!$isValidated || ($form['pokeball'] == 0 && $form['healingPotion'] == 0)) {
            $this->session->add('danger', 'Select the number of items you would like to buy.');

            return;
        }

        if (is_int($pokeballNumber = $form['pokeball']) && $form['pokeball'] != 0) {
            $this->user->addPokeball($pokeballNumber);
            $this->user->decreasePokedollar($pokeballNumber * self::POKEBALL_PRICE);
            $pokeballMessage = sprintf("You have %s pokeballs (+%d)", $this->user->getPokeball(), $pokeballNumber);
            $this->session->add('success', $pokeballMessage);
        }

        if (is_int($hpNumber = $form['healingPotion']) && $form['healingPotion'] != 0) {
            $this->user->addHealingPotion($hpNumber);
            $this->user->decreasePokedollar($hpNumber * self::HEALTH_POTION_PRICE);
            $hpMessage = sprintf("You have %s healing potions (+%d)", $this->user->getHealingPotion(), $hpNumber);
            $this->session->add('success', $hpMessage);
        }

        $this->manager->flush();
    }

    public function handleInfirmaryForm(FormInterface $form): void
    {
        $request = $form->getClickedButton()->getName(); /** @phpstan-ignore-line */

        if ($request === 'restorePokemon') {
            $this->restorePokemonIfAllowed();
        } elseif ($request === 'donatePokemon') {
            if ($pokemonId = $form->getData()['selectPokemon']) {
                $this->donatePokemon($pokemonId);
            } else {
                $this->session->add('danger', 'You have to select a pokemon');
            }
        }
    }

    public function restorePokemonIfAllowed(): void
    {
        $pokemons = $this->pokemonRepository->findPokemonsByTrainer($this->user);

        if (count($pokemons) > 3 && $this->user->getPokedollar() < self::RESTORE_POKEMON_PRICE) {
            $this->session->add('danger', 'You don\'t have enough money to restore pokemons!');

            return;
        }

        if (count($pokemons) >= 3) {
            $this->user->decreasePokedollar(self::RESTORE_POKEMON_PRICE);
        }

        $this->restorePokemon($pokemons);
        $this->manager->flush();
        $this->session->add('success', 'Your pokemons are now in good shape.');
    }

    private function restorePokemon(array $pokemons): void
    {
        foreach ($pokemons as $pokemon) {
            $pokemon->setHealthPoint(100);
            $pokemon->setIsSleep(false);
        }
    }

    public function donatePokemon(int $pokemonId): void
    {
        $pokemons = $this->pokemonRepository->findPokemonsByTrainer($this->user);
        /** @var Pokemon */
        $donatedPokemon = $this->pokemonRepository->find($pokemonId);

        if (count($pokemons) <= 1) {
            $this->session->add('danger', "You can't donate your only pokemon.");
        } elseif (in_array($donatedPokemon, $pokemons)) {
            $this->pokExManager->removeInvalidPokemonExchangeWithPokemon($donatedPokemon);
            $this->user->removePokemon($donatedPokemon);
            $this->manager->remove($donatedPokemon);
            $this->manager->flush();
            $this->session->add('success', sprintf("%s has been donated", $donatedPokemon->getName()));
        }
    }

    public function validatePurchaseMoney(array $data): bool
    {
        $money = $this->user->getPokedollar();
        $amount = $data['pokeball'] * self::POKEBALL_PRICE +
                  $data['healingPotion'] * self::HEALTH_POTION_PRICE;
        $isValidated = $money >= $amount;

        if (!$isValidated) {
            $errorMessage = sprintf("You need enough money to purchase (bill: %d $)", $amount);
            $this->session->add('danger', $errorMessage);
        }

        return $isValidated;
    }
}
