<?php
namespace App\Mailer;

use App\Entity\User;
use \Twig\Environment;
use App\Entity\ContactMessage;
use App\Entity\PokemonExchange;
use Symfony\Component\Uid\Uuid;
use App\Repository\UserRepository;
use Symfony\Component\Mime\Address;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class CustomMailer
{
    private $mailer;
    private $twig;
    private $manager;
    private $userRepository;

    public function __construct(MailerInterface $mailer, Environment $twig, 
                                EntityManagerInterface $manager, UserRepository $userRepository
    ) {
        $this->mailer = $mailer;
        $this->twig = $twig;
        $this->manager = $manager;
        $this->userRepository = $userRepository;
    }

    public function sendMailAfterRegistration(UserInterface $user)
    {
        $this->setToken($user);
        $message = $this->prepareMessage('after_registration', 'Thank you for registration', $user);
        $this->mailer->send($message);
    }

    public function sendMailToResetPassword(UserInterface $user)
    {
        $this->setToken($user);
        $message = $this->prepareMessage('password_reset', 'Reset your password', $user);
        $this->mailer->send($message);
    }

    public function sendMailToConfirmResetPassword(UserInterface $user)
    {
        $message = $this->prepareMessage('confirm_password_reset', 'Your password has been modified', $user);
        $this->mailer->send($message);
    }

    public function sendMailToAdminForNewMessage(ContactMessage $cMessage) 
    {
        $users = $this->userRepository->findAllAdmin();
        
        if(!$users) { return; }

        foreach($users as $user) {
            $message = $this->prepareMessageToAdmin('admin_message_new', 'You have received a new message', $user, $cMessage);
            $this->mailer->send($message);
        }
    }

    public function prepareMessage(string $template, string $subject, User $user)
    {
        return (new TemplatedEmail())
            ->from(new Address('contact@pokemon.com', 'Pokemon'))
            ->to($user->getEmail())
            ->subject($subject)
            ->htmlTemplate('email/'.$template.'.html.twig')
            ->context([
                'username' => $user->getUsername(),
                'token' =>  $user->getToken() ? $user->getToken()->__toString() : null
            ]);
    }

    public function prepareMessageToAdmin(string $template, string $subject,  User $user, ContactMessage $message)
    {
        return (new TemplatedEmail())
            ->from(new Address('contact@pokemon.com', 'Pokemon'))
            ->to($user->getEmail())
            ->subject($subject)
            ->htmlTemplate('email/'.$template.'.html.twig')
            ->context([
                'username' => $user->getUsername(),
                'token' =>  $user->getToken() ? $user->getToken()->_toString() : null,
                'message' => $message
            ]);
    }

    public function setToken($user)
    {
        $user->setToken(Uuid::v4());
        $user->setTokenCreatedAt(new \DateTime('now'));
        $this->manager->flush();
    }

    public function sendMailForNewPokemonExchange(UserInterface $user, PokemonExchange $exchange)
    {
        $message = $this->prepareMessageForPokemonExchange('pokemon_exchange_new', 'You have received a request for pokemons exchange', $user, $exchange);
        $this->mailer->send($message);
    }

    public function sendMailForEditPokemonExchange(UserInterface $user, PokemonExchange $exchange)
    {
        $message = $this->prepareMessageForPokemonExchange('pokemon_exchange_edit', 'A request for pokemons exchange has been modified', $user, $exchange);
        $this->mailer->send($message);
    }

    public function sendMailForRefusePokemonExchange(UserInterface $user, PokemonExchange $exchange)
    {
        $message = $this->prepareMessageForPokemonExchange('pokemon_exchange_refuse', 'A request for pokemons exchange has been refused or withdrawn', $user, $exchange);
        $this->mailer->send($message);
    }

    public function sendMailForAcceptPokemonExchange(UserInterface $user, PokemonExchange $exchange)
    {
        $message = $this->prepareMessageForPokemonExchange('pokemon_exchange_accept', 'A request for pokemons exchange has been accepted', $user, $exchange);
        $this->mailer->send($message);
    }

    public function prepareMessageForPokemonExchange(string $template, string $subject, User $user, PokemonExchange $exchange)
    {
        return (new TemplatedEmail())
            ->from(new Address('contact@pokemon.com', 'Pokemon'))
            ->to($user->getEmail())
            ->subject($subject)
            ->htmlTemplate('email/pokemon_exchange/'.$template.'.html.twig')
            ->context([
                'username' => $user->getUsername(),
                'ex' => $exchange,
                'title' => $subject
            ]);
    }
}