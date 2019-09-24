<?php
namespace App\Mailer;

use DateTime;
use Ramsey\Uuid\Uuid;
use \Twig\Environment;
use App\Entity\ContactMessage;
use App\Entity\PokemonExchange;
use App\Repository\UserRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\User\UserInterface;

class CustomMailer
{
    private $mailer;
    private $twig;
    private $manager;
    private $userRepository;

    public function __construct(\Swift_Mailer $mailer, Environment $twig, ObjectManager $manager, UserRepository $userRepository) {
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
        foreach($users as $user) {
            $message = $this->prepareMessageToAdmin('admin_message_new', 'You have received a new message', $user, $cMessage);
            $this->mailer->send($message);
        }
    }

    public function prepareMessage(string $template, string $subject, UserInterface $user)
    {
        return (new \Swift_Message($subject))
            ->setFrom('contact@pokemon.com')
            ->setTo($user->getEmail())
            ->setBody(
                $this->twig->render(
                    // templates/hello/email.txt.twig
                    'email/'.$template.'.html.twig',[
                        'username' => $user->getUsername(),
                        'token' =>  $user->getToken() ? $user->getToken()->toString() : null
                    ]
                    ),
                    'text/html'
            )
        ;
    }

    public function prepareMessageToAdmin(string $template, string $subject,  UserInterface $user, ContactMessage $message)
    {
        return (new \Swift_Message($subject))
            ->setFrom('contact@pokemon.com')
            ->setTo($user->getEmail())
            ->setBody(
                $this->twig->render(
                    // templates/hello/email.txt.twig
                    'email/'.$template.'.html.twig',[
                        'username' => $user->getUsername(),
                        'token' =>  $user->getToken() ? $user->getToken()->toString() : null,
                        'message' => $message
                    ]
                    ),
                    'text/html'
            )
        ;
    }

    public function setToken($user)
    {
        $user->setToken(Uuid::uuid4());
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

    public function prepareMessageForPokemonExchange(string $template, string $subject, UserInterface $user, PokemonExchange $exchange)
    {
        return (new \Swift_Message($subject))
            ->setFrom('contact@pokemon.com')
            ->setTo($user->getEmail())
            ->setBody(
                $this->twig->render(
                    // templates/hello/email.txt.twig
                    'email/pokemon_exchange/'.$template.'.html.twig',[
                        'username' => $user->getUsername(),
                        'ex' => $exchange,
                        'title' => $subject
                    ]
                    ),
                    'text/html'
            )
        ;
    }
}