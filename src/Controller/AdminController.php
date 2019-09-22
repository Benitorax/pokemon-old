<?php

namespace App\Controller;

use App\Entity\ContactMessage;
use App\Repository\ContactMessageRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminController extends AbstractController
{
    /**
     * @Route("/admin/messages/new", name="admin_messages_new")
     */
    public function showNewMessages(ContactMessageRepository $messageRepository)
    {
        $messages = $messageRepository->findNewMessages();
        return $this->render('admin/show_messages.html.twig', [
            'messages' => $messages,
            'title' => 'New messages'
        ]);
    }

    /**
     * @Route("/admin/messages/archive", name="admin_messages_archive")
     */
    public function showReadMessages(ContactMessageRepository $messageRepository)
    {
        $messages = $messageRepository->findReadMessages();
        return $this->render('admin/show_messages.html.twig', [
            'messages' => $messages,
            'title' => 'Archives messages'
        ]);
    }

    /**
     * @Route("/admin/messages/{id}/read", name="admin_messages_read")
     */
    public function readMessage(ContactMessage $message, ObjectManager $manager)
    {
        $message->setIsRead(true);
        $manager->flush();
        $this->addFlash('success', 'The message has been archived.');

        return $this->redirectToRoute('admin_messages_new');
    }
}