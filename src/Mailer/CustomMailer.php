<?php
namespace App\Mailer;

use DateTime;
use Doctrine\Common\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Security\Core\User\UserInterface;
use \Twig\Environment;

class CustomMailer
{
    private $mailer;
    private $twig;
    private $manager;

    public function __construct(\Swift_Mailer $mailer, Environment $twig, ObjectManager $manager) {
        $this->mailer = $mailer;
        $this->twig = $twig;
        $this->manager = $manager;
    }

    public function sendMailAfterRegistration(UserInterface $user)
    {
        $this->setToken($user);
        $message = $this->prepareMessage($user, 'after_registration');
        $this->mailer->send($message);
    }

    public function sendMailToResetPassword(UserInterface $user)
    {
        $this->setToken($user);
        $message = $this->prepareMessage($user, 'password_reset');
        $this->mailer->send($message);
    }

    public function sendMailToConfirmResetPassword(UserInterface $user)
    {
        $message = $this->prepareMessage($user, 'confirm_password_reset');
        $this->mailer->send($message);
    }

    public function prepareMessage(UserInterface $user, string $template)
    {
        return (new \Swift_Message('Reset password request'))
            ->setFrom('send@example.com')
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

    public function setToken($user)
    {
        $user->setToken(Uuid::uuid4());
        $user->setTokenCreatedAt(new \DateTime('now'));
        $this->manager->flush();
    }
}