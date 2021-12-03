<?php

namespace App\Manager;

use App\Entity\User;
use App\Entity\ContactMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;

class ContactMessageManager
{
    private EntityManagerInterface $manager;
    private User $user;

    public function __construct(
        EntityManagerInterface $manager,
        Security $security
    ) {
        /** @var User */
        $user = $security->getUser();
        $this->user = $user;
        $this->manager = $manager;
    }

    public function createContactMessage(ContactMessage $message): ContactMessage
    {
        $message->setAuthorName($this->user->getUsername())
                ->setAuthorEmail($this->user->getEmail())
                ->setCreatedAt(new \DateTime('now'));
        $this->manager->persist($message);
        $this->manager->flush();

        return $message;
    }
}
