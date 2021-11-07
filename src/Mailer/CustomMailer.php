<?php

namespace App\Mailer;

use App\Entity\User;
use App\Entity\ContactMessage;
use App\Entity\PokemonExchange;
use Symfony\Component\Uid\Uuid;
use App\Repository\UserRepository;
use Symfony\Component\Mime\Address;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;

class CustomMailer
{
    private MailerInterface $mailer;
    private EntityManagerInterface $manager;
    private UserRepository $userRepository;

    public function __construct(
        MailerInterface $mailer,
        EntityManagerInterface $manager,
        UserRepository $userRepository
    ) {
        $this->mailer = $mailer;
        $this->manager = $manager;
        $this->userRepository = $userRepository;
    }

    public function sendMailAfterRegistration(User $user): void
    {
        $this->setToken($user);
        $message = $this->prepareMessage('after_registration', 'Thank you for registration', $user);
        $this->mailer->send($message);
    }

    public function sendMailToResetPassword(User $user): void
    {
        $this->setToken($user);
        $message = $this->prepareMessage('password_reset', 'Reset your password', $user);
        $this->mailer->send($message);
    }

    public function sendMailToConfirmResetPassword(User $user): void
    {
        $message = $this->prepareMessage('confirm_password_reset', 'Your password has been modified', $user);
        $this->mailer->send($message);
    }

    public function sendMailToAdminForNewMessage(ContactMessage $cMessage): void
    {
        $users = $this->userRepository->findAllAdmin();

        if (!$users) {
            return;
        }

        foreach ($users as $user) {
            $message = $this->prepareMessageToAdmin(
                'admin_message_new',
                'You have received a new message',
                $user,
                $cMessage
            );
            $this->mailer->send($message);
        }
    }

    public function prepareMessage(string $template, string $subject, User $user): TemplatedEmail
    {
        return (new TemplatedEmail())
            ->from(new Address('contact@pokemon.com', 'Pokemon'))
            ->to($user->getEmail())
            ->subject($subject)
            ->htmlTemplate('email/' . $template . '.html.twig')
            ->context([
                'username' => $user->getUsername(),
                'token' =>  $user->getToken() ? $user->getToken()->__toString() : null
            ]);
    }

    public function prepareMessageToAdmin(
        string $template,
        string $subject,
        User $user,
        ContactMessage $message
    ): TemplatedEmail {
        return (new TemplatedEmail())
            ->from(new Address('contact@pokemon.com', 'Pokemon'))
            ->to($user->getEmail())
            ->subject($subject)
            ->htmlTemplate('email/' . $template . '.html.twig')
            ->context([
                'username' => $user->getUsername(),
                'token' =>  $user->getToken() ? $user->getToken()->__toString() : null,
                'message' => $message
            ]);
    }

    public function setToken(User $user): void
    {
        $user->setToken(Uuid::v4());
        $user->setTokenCreatedAt(new \DateTime('now'));
        $this->manager->flush();
    }

    public function sendMailForNewPokemonExchange(User $user, PokemonExchange $exchange): void
    {
        $message = $this->prepareMessageForPokemonExchange(
            'pokemon_exchange_new',
            'You have received a request for pokemons exchange',
            $user,
            $exchange
        );
        $this->mailer->send($message);
    }

    public function sendMailForEditPokemonExchange(User $user, PokemonExchange $exchange): void
    {
        $message = $this->prepareMessageForPokemonExchange(
            'pokemon_exchange_edit',
            'A request for pokemons exchange has been modified',
            $user,
            $exchange
        );
        $this->mailer->send($message);
    }

    public function sendMailForRefusePokemonExchange(User $user, PokemonExchange $exchange): void
    {
        $message = $this->prepareMessageForPokemonExchange(
            'pokemon_exchange_refuse',
            'A request for pokemons exchange has been refused or withdrawn',
            $user,
            $exchange
        );
        $this->mailer->send($message);
    }

    public function sendMailForAcceptPokemonExchange(User $user, PokemonExchange $exchange): void
    {
        $message = $this->prepareMessageForPokemonExchange(
            'pokemon_exchange_accept',
            'A request for pokemons exchange has been accepted',
            $user,
            $exchange
        );
        $this->mailer->send($message);
    }

    public function prepareMessageForPokemonExchange(
        string $template,
        string $subject,
        User $user,
        PokemonExchange $exchange
    ): TemplatedEmail {
        return (new TemplatedEmail())
            ->from(new Address('contact@pokemon.com', 'Pokemon'))
            ->to($user->getEmail())
            ->subject($subject)
            ->htmlTemplate('email/pokemon_exchange/' . $template . '.html.twig')
            ->context([
                'username' => $user->getUsername(),
                'ex' => $exchange,
                'title' => $subject
            ]);
    }
}
