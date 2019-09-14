<?php
namespace App\Mailer;

use Symfony\Component\Security\Core\User\UserInterface;
use Twig\Environment;

class CustomMailer
{
    private $mailer;
    private $twig;

    public function __construct(\Swift_Mailer $mailer, Environment $twig) {
        $this->mailer = $mailer;
        $this->twig = $twig;
    }

    public function sendMailAfterRegistration(UserInterface $user)
    {
        $message = (new \Swift_Message('Confirm your registration'))
            ->setFrom('send@example.com')
            ->setTo($user->getEmail())
            ->setBody(
                $this->twig->render(
                    // templates/hello/email.txt.twig
                    'email/after_registration.html.twig',
                    ['username' => $user->getUsername()]
                )
            )
        ;
        $this->mailer->send($message);
    }
}