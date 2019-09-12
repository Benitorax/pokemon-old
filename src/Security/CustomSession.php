<?php
namespace App\Security;

use Symfony\Component\HttpFoundation\Session\SessionInterface;

class CustomSession
{
    private $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    public function add(string $type, string $message)
    {
        $this->session->getFlashBag()->add($type, $message);
    }
}