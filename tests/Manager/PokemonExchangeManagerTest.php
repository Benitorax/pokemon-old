<?php

namespace App\Tests\Manager;

use App\Entity\User;
use App\Entity\Pokemon;
use App\Mailer\CustomMailer;
use App\Entity\PokemonExchange;
use PHPUnit\Framework\TestCase;
use App\Manager\PokemonExchangeManager;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\PokemonExchangeRepository;

class PokemonExchangeManagerTest extends TestCase
{
    public function testCreatePokemonExchange()
    {
        $manager = $this->createMock(EntityManagerInterface::class);
        $mailer = $this->createMock(CustomMailer::class);
        $repository = $this->createMock(PokemonExchangeRepository::class);
        $pokExManager = new PokemonExchangeManager($manager, $mailer, $repository);

        $mailer->expects($this->once())
            ->method('sendMailForNewPokemonExchange');
        $manager->expects($this->once())
            ->method('persist');
        $manager->expects($this->once())
            ->method('flush');

        $pokemon1 = new Pokemon();
        $pokemon2 = new Pokemon();
        $trainer1 = (new User())->addPokemon($pokemon1);
        $trainer2 = (new User())->addPokemon($pokemon2);

        $pokemonExchange = (new PokemonExchange())->setPokemon1($pokemon1)->setPokemon2($pokemon2);

        $pokemonExchange = $pokExManager->createPokemonExchange($pokemonExchange);
        $this->assertInstanceOf(PokemonExchange::class, $pokemonExchange);
        $this->assertInstanceOf(\DateTime::class, $pokemonExchange->getCreatedAt());
        $this->assertNotNull($pokemonExchange->getCreatedAt());
        $this->assertNull($pokemonExchange->getUpdatedAt());
        $this->assertSame($trainer1, $pokemonExchange->getTrainer1());
        $this->assertSame($trainer2, $pokemonExchange->getTrainer2());
        $this->assertSame(PokemonExchange::USER_ACCEPT_CONTRACT, $pokemonExchange->getAnswer1());
        $this->assertSame(PokemonExchange::USER_NO_ANSWER_CONTRACT, $pokemonExchange->getAnswer2());
        $this->assertSame(PokemonExchange::STATUS_WAITING_FOR_RESPONSE, $pokemonExchange->getStatus());
    }

    public function testEditPokemonExchange()
    {
        $manager = $this->createMock(EntityManagerInterface::class);
        $mailer = $this->createMock(CustomMailer::class);
        $repository = $this->createMock(PokemonExchangeRepository::class);
        $pokExManager = new PokemonExchangeManager($manager, $mailer, $repository);

        $mailer->expects($this->once())
            ->method('sendMailForEditPokemonExchange');
        $manager->expects($this->once())
            ->method('flush');

        $pokemon1 = new Pokemon();
        $pokemon2 = new Pokemon();
        $trainer1 = (new User())->addPokemon($pokemon1);
        $trainer2 = (new User())->addPokemon($pokemon2);

        $pokemonExchange = (new PokemonExchange())
            ->setPokemon1($pokemon1)->setPokemon2($pokemon2)
            ->setTrainer1($trainer1)->setTrainer2($trainer2)
            ->setAnswer1(PokemonExchange::USER_ACCEPT_CONTRACT)
            ->setAnswer2(PokemonExchange::USER_NO_ANSWER_CONTRACT)
            ->setCreatedAt(new \DateTime('now'));

        $pokemonExchange = $pokExManager->editPokemonExchange($pokemonExchange, $trainer2);
        $this->assertInstanceOf(PokemonExchange::class, $pokemonExchange);
        $this->assertInstanceOf(\DateTime::class, $pokemonExchange->getUpdatedAt());
        $this->assertNotNull($pokemonExchange->getUpdatedAt());
        $this->assertSame($trainer1, $pokemonExchange->getTrainer1());
        $this->assertSame($trainer2, $pokemonExchange->getTrainer2());
        $this->assertSame(PokemonExchange::USER_NO_ANSWER_CONTRACT, $pokemonExchange->getAnswer1());
        $this->assertSame(PokemonExchange::USER_ACCEPT_CONTRACT, $pokemonExchange->getAnswer2());
        $this->assertSame(PokemonExchange::STATUS_MODIFIED, $pokemonExchange->getStatus());
    }

    public function testAcceptPokemonExchange()
    {
        $manager = $this->createMock(EntityManagerInterface::class);
        $mailer = $this->createMock(CustomMailer::class);
        $repository = $this->createMock(PokemonExchangeRepository::class);
        $pokExManager = new PokemonExchangeManager($manager, $mailer, $repository);

        $mailer->expects($this->once())
            ->method('sendMailForAcceptPokemonExchange');
        $manager->expects($this->any())
            ->method('remove');
        $manager->expects($this->once())
            ->method('flush');

        $pokemon1 = new Pokemon();
        $pokemon2 = new Pokemon();
        $trainer1 = (new User())->addPokemon($pokemon1);
        $trainer2 = (new User())->addPokemon($pokemon2);

        $pokemonExchange = (new PokemonExchange())
            ->setPokemon1($pokemon1)->setPokemon2($pokemon2)
            ->setTrainer1($trainer1)->setTrainer2($trainer2)
            ->setAnswer1(PokemonExchange::USER_ACCEPT_CONTRACT)
            ->setAnswer2(PokemonExchange::USER_NO_ANSWER_CONTRACT)
            ->setCreatedAt(new \DateTime('now'));

        $pokemonExchange = $pokExManager->acceptPokemonExchange($pokemonExchange, $trainer2);
        $this->assertSame($pokemon2, $trainer1->getPokemons()->first());
        $this->assertSame($pokemon1, $trainer2->getPokemons()->first());
        $this->assertNull($pokemonExchange);
    }

    public function testDeletePokemonExchange()
    {
        $manager = $this->createMock(EntityManagerInterface::class);
        $mailer = $this->createMock(CustomMailer::class);
        $repository = $this->createMock(PokemonExchangeRepository::class);
        $pokExManager = new PokemonExchangeManager($manager, $mailer, $repository);

        $mailer->expects($this->once())
            ->method('sendMailForRefusePokemonExchange');
        $manager->expects($this->once())
            ->method('remove');
        $manager->expects($this->once())
            ->method('flush');

        $pokemon1 = new Pokemon();
        $pokemon2 = new Pokemon();
        $trainer1 = (new User())->addPokemon($pokemon1);
        $trainer2 = (new User())->addPokemon($pokemon2);

        $pokemonExchange = (new PokemonExchange())
            ->setPokemon1($pokemon1)->setPokemon2($pokemon2)
            ->setTrainer1($trainer1)->setTrainer2($trainer2)
            ->setAnswer1(PokemonExchange::USER_ACCEPT_CONTRACT)
            ->setAnswer2(PokemonExchange::USER_NO_ANSWER_CONTRACT)
            ->setCreatedAt(new \DateTime('now'));

        $pokemonExchange = $pokExManager->deletePokemonExchange($pokemonExchange, $trainer2);
        $this->assertSame($pokemon1, $trainer1->getPokemons()->first());
        $this->assertSame($pokemon2, $trainer2->getPokemons()->first());
        $this->assertNull($pokemonExchange);
    }
}
