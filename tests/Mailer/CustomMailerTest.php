<?php

namespace App\Tests\Mailer;

use App\Entity\User;
use Twig\Environment;
use App\Mailer\CustomMailer;
use App\Entity\ContactMessage;
use App\Entity\PokemonExchange;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;

class CustomMailerTest extends TestCase
{
    public function testSendMailAfterRegistration()
    {
        $mailer = $this->createMock(MailerInterface::class);
        $manager = $this->createMock(EntityManagerInterface::class);
        $userRepository = $this->createMock(UserRepository::class);
        $customMailer = new CustomMailer($mailer, $manager, $userRepository);

        $user = (new User())
            ->setEmail('sacha@mail.com')
            ->setUsername('Sacha');

        $manager
            ->expects($this->once())
            ->method('flush');

        $mailer
            ->expects($this->once())
            ->method('send');

        $customMailer->sendMailAfterRegistration($user);
    }

    public function testSendMailToResetPassword()
    {
        $mailer = $this->createMock(MailerInterface::class);
        $manager = $this->createMock(EntityManagerInterface::class);
        $userRepository = $this->createMock(UserRepository::class);
        $customMailer = new CustomMailer($mailer, $manager, $userRepository);

        $user = (new User())
            ->setEmail('sacha@mail.com')
            ->setUsername('Sacha');

        $manager
            ->expects($this->once())
            ->method('flush');

        $mailer
            ->expects($this->once())
            ->method('send');

        $customMailer->sendMailToResetPassword($user);
    }

    public function testSendMailToConfirmResetPassword()
    {
        $mailer = $this->createMock(MailerInterface::class);
        $manager = $this->createMock(EntityManagerInterface::class);
        $userRepository = $this->createMock(UserRepository::class);
        $customMailer = new CustomMailer($mailer, $manager, $userRepository);

        $user = (new User())
            ->setEmail('sacha@mail.com')
            ->setUsername('Sacha')
            ->setToken(Uuid::v4())
            ->setTokenCreatedAt(new \DateTime('now'))
        ;

        $mailer
            ->expects($this->once())
            ->method('send');

        $customMailer->sendMailToConfirmResetPassword($user);
    }

    public function testSendMailToAdminForNewMessage()
    {
        $mailer = $this->createMock(MailerInterface::class);
        $manager = $this->createMock(EntityManagerInterface::class);
        $userRepository = $this->createMock(UserRepository::class);
        $customMailer = new CustomMailer($mailer, $manager, $userRepository);

        $userRepository
            ->expects($this->once())
            ->method('findAllAdmin');

        $mailer
            ->expects($this->any())
            ->method('send');

        $contactMessage = new ContactMessage();
        $customMailer->sendMailToAdminForNewMessage($contactMessage);
    }

    public function testSendMailForNewPokemonExchange()
    {
        $mailer = $this->createMock(MailerInterface::class);
        $manager = $this->createMock(EntityManagerInterface::class);
        $userRepository = $this->createMock(UserRepository::class);
        $customMailer = new CustomMailer($mailer, $manager, $userRepository);

        $mailer
            ->expects($this->once())
            ->method('send');

        $user = (new User())
            ->setEmail('sacha@mail.com')
            ->setUsername('Sacha');
        $exchange = new PokemonExchange();

        $customMailer->sendMailForNewPokemonExchange($user, $exchange);
    }

    public function testSendMailForEditPokemonExchange()
    {
        $mailer = $this->createMock(MailerInterface::class);
        $manager = $this->createMock(EntityManagerInterface::class);
        $userRepository = $this->createMock(UserRepository::class);
        $customMailer = new CustomMailer($mailer, $manager, $userRepository);

        $mailer
            ->expects($this->once())
            ->method('send');

        $user = (new User())
            ->setEmail('sacha@mail.com')
            ->setUsername('Sacha');
        $exchange = new PokemonExchange();

        $customMailer->sendMailForEditPokemonExchange($user, $exchange);
    }

    public function testSendMailForRefusePokemonExchange()
    {
        $mailer = $this->createMock(MailerInterface::class);
        $manager = $this->createMock(EntityManagerInterface::class);
        $userRepository = $this->createMock(UserRepository::class);
        $customMailer = new CustomMailer($mailer, $manager, $userRepository);

        $mailer
            ->expects($this->once())
            ->method('send');

        $user = (new User())
            ->setEmail('sacha@mail.com')
            ->setUsername('Sacha');
        $exchange = new PokemonExchange();

        $customMailer->sendMailForRefusePokemonExchange($user, $exchange);
    }

    public function testSendMailForAcceptPokemonExchange()
    {
        $mailer = $this->createMock(MailerInterface::class);
        $manager = $this->createMock(EntityManagerInterface::class);
        $userRepository = $this->createMock(UserRepository::class);
        $customMailer = new CustomMailer($mailer, $manager, $userRepository);

        $mailer
            ->expects($this->once())
            ->method('send');

        $user = (new User())
            ->setEmail('sacha@mail.com')
            ->setUsername('Sacha');
        $exchange = new PokemonExchange();

        $customMailer->sendMailForAcceptPokemonExchange($user, $exchange);
    }
}
