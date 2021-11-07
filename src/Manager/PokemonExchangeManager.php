<?php

namespace App\Manager;

use App\Entity\Pokemon;
use App\Entity\PokemonExchange;
use App\Mailer\CustomMailer;
use App\Repository\PokemonExchangeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class PokemonExchangeManager
{
    private EntityManagerInterface $manager;
    private CustomMailer $mailer;
    private PokemonExchangeRepository $pokExRepository;
    public function __construct(
        EntityManagerInterface $manager,
        CustomMailer $mailer,
        PokemonExchangeRepository $pokExRepository
    ) {
        $this->manager = $manager;
        $this->mailer = $mailer;
        $this->pokExRepository = $pokExRepository;
    }

    public function createPokemonExchange(PokemonExchange $pokemonExchange): PokemonExchange
    {
        $pokemonExchange
            ->setTrainer1($pokemonExchange->getPokemon1()->getTrainer())
            ->setTrainer2($pokemonExchange->getPokemon2()->getTrainer())
            ->setAnswer1(PokemonExchange::USER_ACCEPT_CONTRACT)
            ->setAnswer2(PokemonExchange::USER_NO_ANSWER_CONTRACT)
            ->setCreatedAt(new \DateTime('now'));

        $this->mailer->sendMailForNewPokemonExchange($pokemonExchange->getTrainer2(), $pokemonExchange);
        $this->manager->persist($pokemonExchange);
        $this->manager->flush();

        return $pokemonExchange;
    }

    public function editPokemonExchange(PokemonExchange $pokemonExchange, UserInterface $user): PokemonExchange
    {
        $pokemonExchange->setUpdatedAt(new \DateTime('now'))->setStatus(PokemonExchange::STATUS_MODIFIED);

        if ($pokemonExchange->getTrainer1() === $user) {
            $pokemonExchange
                ->setAnswer1(PokemonExchange::USER_ACCEPT_CONTRACT)
                ->setAnswer2(PokemonExchange::USER_NO_ANSWER_CONTRACT);
            $this->mailer->sendMailForEditPokemonExchange($pokemonExchange->getTrainer2(), $pokemonExchange);
        } elseif ($pokemonExchange->getTrainer2() === $user) {
            $pokemonExchange
                ->setAnswer1(PokemonExchange::USER_NO_ANSWER_CONTRACT)
                ->setAnswer2(PokemonExchange::USER_ACCEPT_CONTRACT);
            $this->mailer->sendMailForEditPokemonExchange($pokemonExchange->getTrainer1(), $pokemonExchange);
        }

        $this->manager->flush();

        return $pokemonExchange;
    }

    public function acceptPokemonExchange(PokemonExchange $pokemonExchange, UserInterface $user): void
    {
        if ($pokemonExchange->getTrainer1() === $user) {
            $pokemonExchange->setAnswer1(PokemonExchange::USER_ACCEPT_CONTRACT);
            $this->mailer->sendMailForAcceptPokemonExchange($pokemonExchange->getTrainer2(), $pokemonExchange);
        } elseif ($pokemonExchange->getTrainer2() === $user) {
            $pokemonExchange->setAnswer2(PokemonExchange::USER_ACCEPT_CONTRACT);
            $this->mailer->sendMailForAcceptPokemonExchange($pokemonExchange->getTrainer1(), $pokemonExchange);
        }

        if (
            $pokemonExchange->getAnswer1() === $pokemonExchange::USER_ACCEPT_CONTRACT &&
            $pokemonExchange->getAnswer2() === $pokemonExchange::USER_ACCEPT_CONTRACT
        ) {
            $pokemonExchange->getTrainer1()->removePokemon($pokemonExchange->getPokemon1());
            $pokemonExchange->getTrainer2()->removePokemon($pokemonExchange->getPokemon2());
            $pokemonExchange->getTrainer2()->addPokemon($pokemonExchange->getPokemon1());
            $pokemonExchange->getTrainer1()->addPokemon($pokemonExchange->getPokemon2());
            $this->removeInvalidPokemonExchange($pokemonExchange);
        }
        // $this->manager->remove($pokemonExchange);
        // $this->manager->flush();
    }

    private function removeInvalidPokemonExchange(PokemonExchange $pokemonExchange): void
    {
        $pokemon1 = $pokemonExchange->getPokemon1();
        $pokemon2 = $pokemonExchange->getPokemon2();
        $pokemonExchanges = $this->pokExRepository->findAll();
        $pokemonExchangesToDelete = [];

        foreach ($pokemonExchanges as $pokemonExchange) {
            /** @phpstan-ignore-next-line */
            $pokemonsArray = [$pokemonExchange->getPokemon1(), $pokemonExchange->getPokemon2()];

            if (in_array($pokemon1, $pokemonsArray) || in_array($pokemon2, $pokemonsArray)) {
                $pokemonExchangesToDelete[] = $pokemonExchange;
            }
        }

        foreach ($pokemonExchangesToDelete as $pokemonExchange) {
            $this->manager->remove($pokemonExchange);
        }

        $this->manager->flush();
    }

    public function removeInvalidPokemonExchangeWithPokemon(Pokemon $pokemon): void
    {

        $pokemonExchanges = $this->pokExRepository->findAll();
        $pokemonExchangesToDelete = [];

        foreach ($pokemonExchanges as $pokemonExchange) {
            /** @phpstan-ignore-next-line */
            $pokemonsArray = [$pokemonExchange->getPokemon1(), $pokemonExchange->getPokemon2()];

            if (in_array($pokemon, $pokemonsArray)) {
                $pokemonExchangesToDelete[] = $pokemonExchange;
            }
        }

        foreach ($pokemonExchangesToDelete as $pokemonExchange) {
            $this->manager->remove($pokemonExchange);
        }

        $this->manager->flush();
    }

    public function deletePokemonExchange(PokemonExchange $pokemonExchange, UserInterface $user): void
    {
        if ($pokemonExchange->getTrainer1() === $user) {
            $pokemonExchange->setAnswer1(PokemonExchange::USER_REFUSE_CONTRACT);
            $this->mailer->sendMailForRefusePokemonExchange($pokemonExchange->getTrainer2(), $pokemonExchange);
        } elseif ($pokemonExchange->getTrainer2() === $user) {
            $pokemonExchange->setAnswer2(PokemonExchange::USER_REFUSE_CONTRACT);
            $this->mailer->sendMailForRefusePokemonExchange($pokemonExchange->getTrainer1(), $pokemonExchange);
        }

        $this->manager->remove($pokemonExchange);
        $this->manager->flush();
    }
}
