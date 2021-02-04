<?php

namespace App\Tests\Mailer;

use App\Entity\ContactMessage;
use App\Entity\PokemonExchange;
use App\Entity\User;
use App\Mailer\CustomMailer;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mailer\MailerInterface;
use Twig\Environment;

class CustomMailerTest extends TestCase
{
    public function testSendMailAfterRegistration()
    {
        $mailer = $this->createMock(MailerInterface::class);
        $twig = $this->createMock(Environment::class);
        $manager = $this->createMock(EntityManagerInterface::class);
        $userRepository = $this->createMock(UserRepository::class);
        $customMailer = new CustomMailer($mailer, $twig, $manager, $userRepository);

        $user = (new User())
            ->setEmail('sacha@mail.com')
            ->setUsername('Sacha');

        $manager
            ->expects($this->once())
            ->method('flush');

        $twig
            ->expects($this->once())
            ->method('render');

        $mailer
            ->expects($this->once())
            ->method('send');

        $customMailer->sendMailAfterRegistration($user);
    }

    public function testSendMailToResetPassword()
    {
        $mailer = $this->createMock(MailerInterface::class);
        $twig = $this->createMock(Environment::class);
        $manager = $this->createMock(EntityManagerInterface::class);
        $userRepository = $this->createMock(UserRepository::class);
        $customMailer = new CustomMailer($mailer, $twig, $manager, $userRepository);

        $user = (new User())
            ->setEmail('sacha@mail.com')
            ->setUsername('Sacha');

        $manager
            ->expects($this->once())
            ->method('flush');

        $twig
            ->expects($this->once())
            ->method('render');

        $mailer
            ->expects($this->once())
            ->method('send');

        $customMailer->sendMailToResetPassword($user);

    }

    public function testSendMailToConfirmResetPassword()
    {
        $mailer = $this->createMock(MailerInterface::class);
        $twig = $this->createMock(Environment::class);
        $manager = $this->createMock(EntityManagerInterface::class);
        $userRepository = $this->createMock(UserRepository::class);
        $customMailer = new CustomMailer($mailer, $twig, $manager, $userRepository);

        $user = (new User())
            ->setEmail('sacha@mail.com')
            ->setUsername('Sacha');

        $twig
            ->expects($this->once())
            ->method('render');

        $mailer
            ->expects($this->once())
            ->method('send');

        $customMailer->sendMailToConfirmResetPassword($user);
    }

    public function testSendMailToAdminForNewMessage()
    {
        $mailer = $this->createMock(MailerInterface::class);
        $twig = $this->createMock(Environment::class);
        $manager = $this->createMock(EntityManagerInterface::class);
        $userRepository = $this->createMock(UserRepository::class);
        $customMailer = new CustomMailer($mailer, $twig, $manager, $userRepository);

        $userRepository
            ->expects($this->once())
            ->method('findAllAdmin');

        $twig
            ->expects($this->any())
            ->method('render');

        $mailer
            ->expects($this->any())
            ->method('send');

        $contactMessage = new ContactMessage();
        $customMailer->sendMailToAdminForNewMessage($contactMessage);

    }

    public function testSendMailForNewPokemonExchange()
    {
        $mailer = $this->createMock(MailerInterface::class);
        $twig = $this->createMock(Environment::class);
        $manager = $this->createMock(EntityManagerInterface::class);
        $userRepository = $this->createMock(UserRepository::class);
        $customMailer = new CustomMailer($mailer, $twig, $manager, $userRepository);
        $user = (new User())
            ->setEmail('sacha@mail.com')
            ->setUsername('Sacha');
        $exchange = new PokemonExchange();
        
        $twig
            ->expects($this->any())
            ->method('render');

        $mailer
            ->expects($this->once())
            ->method('send');

        $customMailer->sendMailForNewPokemonExchange($user, $exchange);
    }

    public function testSendMailForEditPokemonExchange()
    {
        $mailer = $this->createMock(MailerInterface::class);
        $twig = $this->createMock(Environment::class);
        $manager = $this->createMock(EntityManagerInterface::class);
        $userRepository = $this->createMock(UserRepository::class);
        $customMailer = new CustomMailer($mailer, $twig, $manager, $userRepository);
        $user = (new User())
            ->setEmail('sacha@mail.com')
            ->setUsername('Sacha');
        $exchange = new PokemonExchange();
        
        $twig
            ->expects($this->any())
            ->method('render');

        $mailer
            ->expects($this->once())
            ->method('send');

        $customMailer->sendMailForEditPokemonExchange($user, $exchange);

    }

    public function testSendMailForRefusePokemonExchange()
    {
        $mailer = $this->createMock(MailerInterface::class);
        $twig = $this->createMock(Environment::class);
        $manager = $this->createMock(EntityManagerInterface::class);
        $userRepository = $this->createMock(UserRepository::class);
        $customMailer = new CustomMailer($mailer, $twig, $manager, $userRepository);
        $user = (new User())
            ->setEmail('sacha@mail.com')
            ->setUsername('Sacha');
        $exchange = new PokemonExchange();
        
        $twig
            ->expects($this->any())
            ->method('render');

        $mailer
            ->expects($this->once())
            ->method('send');

        $customMailer->sendMailForRefusePokemonExchange($user, $exchange);

    }

    public function testSendMailForAcceptPokemonExchange()
    {
        $mailer = $this->createMock(MailerInterface::class);
        $twig = $this->createMock(Environment::class);
        $manager = $this->createMock(EntityManagerInterface::class);
        $userRepository = $this->createMock(UserRepository::class);
        $customMailer = new CustomMailer($mailer, $twig, $manager, $userRepository);
        $user = (new User())
            ->setEmail('sacha@mail.com')
            ->setUsername('Sacha');
        $exchange = new PokemonExchange();
        
        $twig
            ->expects($this->any())
            ->method('render');

        $mailer
            ->expects($this->once())
            ->method('send');

        $customMailer->sendMailForAcceptPokemonExchange($user, $exchange);

    }
}
