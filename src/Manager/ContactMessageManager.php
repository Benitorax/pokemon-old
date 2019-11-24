<?php
namespace App\Manager;

use App\Entity\ContactMessage;
use App\Repository\ContactMessageRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Security;

class ContactMessageManager
{
    private $manager;
    private $messageRepository;
    private $user;

    public function __construct(ObjectManager $manager, ContactMessageRepository $messageRepository, Security $security)
    {
        $this->manager = $manager;
        $this->messageRepository = $messageRepository;
        $this->user = $security->getUser();
    }

    public function createContactMessage(ContactMessage $message)
    {
        $message->setAuthorName($this->user->getUsername())
                ->setAuthorEmail($this->user->getEmail())
                ->setCreatedAt(new \DateTime('now'));
        $this->manager->persist($message);
        $this->manager->flush();
        
        return $message;
    }
}