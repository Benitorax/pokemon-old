<?php
namespace App\Mailer;

use DateTime;
use Ramsey\Uuid\Uuid;
use \Twig\Environment;
use App\Entity\ContactMessage;
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

    public function sentMailToAdminForNewMessage(ContactMessage $cMessage) {
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
}