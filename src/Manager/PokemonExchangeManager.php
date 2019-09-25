<?php
namespace App\Manager;

use App\Entity\PokemonExchange;
use App\Mailer\CustomMailer;
use App\Repository\PokemonExchangeRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\User\UserInterface;

class PokemonExchangeManager
{
    private $manager;
    private $mailer;

    public function __construct(ObjectManager $manager, CustomMailer $mailer)
    {
        $this->manager = $manager;
        $this->mailer = $mailer;
    }

    public function createPokemonExchange(PokemonExchange $pokemonExchange) 
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

    public function editPokemonExchange(PokemonExchange $pokemonExchange, UserInterface $user)
    {
        $pokemonExchange->setUpdatedAt(new \DateTime('now'))->setStatus(PokemonExchange::STATUS_MODIFIED);

        if($pokemonExchange->getTrainer1() === $user) {
            $pokemonExchange
                ->setAnswer1(PokemonExchange::USER_ACCEPT_CONTRACT)
                ->setAnswer2(PokemonExchange::USER_NO_ANSWER_CONTRACT);
            $this->mailer->sendMailForEditPokemonExchange($pokemonExchange->getTrainer2(), $pokemonExchange);
        } elseif($pokemonExchange->getTrainer2() === $user) {
            $pokemonExchange
                ->setAnswer1(PokemonExchange::USER_NO_ANSWER_CONTRACT)
                ->setAnswer2(PokemonExchange::USER_ACCEPT_CONTRACT);
            $this->mailer->sendMailForEditPokemonExchange($pokemonExchange->getTrainer1(), $pokemonExchange);
        }
        $this->manager->flush();

        return $pokemonExchange;
    }

    public function acceptPokemonExchange(PokemonExchange $pokemonExchange, UserInterface $user)
    {
        if($pokemonExchange->getTrainer1() === $user) {
            $pokemonExchange->setAnswer1(PokemonExchange::USER_ACCEPT_CONTRACT);
            $this->mailer->sendMailForAcceptPokemonExchange($pokemonExchange->getTrainer2(), $pokemonExchange);
        } elseif($pokemonExchange->getTrainer2() === $user) {
            $pokemonExchange->setAnswer2(PokemonExchange::USER_ACCEPT_CONTRACT);
            $this->mailer->sendMailForAcceptPokemonExchange($pokemonExchange->getTrainer1(), $pokemonExchange);
        }
        if($pokemonExchange->getAnswer1() === $pokemonExchange::USER_ACCEPT_CONTRACT && 
           $pokemonExchange->getAnswer2() === $pokemonExchange::USER_ACCEPT_CONTRACT) 
        {
            $pokemonExchange->getTrainer1()->removePokemon($pokemonExchange->getPokemon1());
            $pokemonExchange->getTrainer2()->removePokemon($pokemonExchange->getPokemon2());

            $pokemonExchange->getTrainer2()->addPokemon($pokemonExchange->getPokemon1());
            $pokemonExchange->getTrainer1()->addPokemon($pokemonExchange->getPokemon2());
        }
        $this->manager->remove($pokemonExchange);
        $this->manager->flush();    
    }

    public function deletePokemonExchange(PokemonExchange $pokemonExchange, UserInterface $user)
    {
        if($pokemonExchange->getTrainer1() === $user) {
            $pokemonExchange->setAnswer1(PokemonExchange::USER_REFUSE_CONTRACT);
            $this->mailer->sendMailForRefusePokemonExchange($pokemonExchange->getTrainer2(), $pokemonExchange);
        } elseif($pokemonExchange->getTrainer2() === $user) {
            $pokemonExchange->setAnswer2(PokemonExchange::USER_REFUSE_CONTRACT);
            $this->mailer->sendMailForRefusePokemonExchange($pokemonExchange->getTrainer1(), $pokemonExchange);
        }

        $this->manager->remove($pokemonExchange);
        $this->manager->flush();
    }
}