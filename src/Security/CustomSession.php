<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;

class CustomSession
{
    private Session $session;

    public function __construct(RequestStack $requestStack)
    {
        /** @var Session */
        $session = $requestStack->getSession();
        $this->session = $session;
    }

    public function add(string $type, string $message): void
    {
        $this->session->getFlashBag()->add($type, $message);
    }
}
