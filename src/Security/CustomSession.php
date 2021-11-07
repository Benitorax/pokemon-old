<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class CustomSession
{
    private SessionInterface $session;
    public function __construct(RequestStack $requestStack)
    {
        $this->session = $requestStack->getSession();
    }

    public function add(string $type, string $message)
    {
        $this->session->getFlashBag()->add($type, $message);
    }
}
