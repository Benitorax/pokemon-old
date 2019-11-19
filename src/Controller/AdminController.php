<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\ContactMessage;
use App\Repository\UserRepository;
use App\Repository\ContactMessageRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class AdminController extends AbstractController
{
    /**
     * @Route("/admin/messages/new", name="admin_messages_new", methods={"GET"})
     */
    public function showNewMessages(ContactMessageRepository $messageRepository)
    {
        $messages = $messageRepository->findNewMessages();
        $csrfToken = $this->getUser()->getId()->toString();

        return $this->render('admin/show_messages.html.twig', [
            'messages' => $messages,
            'title' => 'New messages',
            'csrfToken' => $csrfToken
        ]);
    }

    /**
     * @Route("/admin/messages/archive", name="admin_messages_archived", methods={"GET"})
     */
    public function showArchivedMessages(ContactMessageRepository $messageRepository)
    {
        $messages = $messageRepository->findReadMessages();
        $csrfToken = $this->getUser()->getId()->toString();

        return $this->render('admin/show_messages.html.twig', [
            'messages' => $messages,
            'title' => 'Archives messages',
            'csrfToken' => $csrfToken
        ]);
    }

    /**
     * @Route("/admin/messages/{id}/archive/{csrfToken}", name="admin_messages_archive", methods={"GET"})
     */
    public function archiveMessage(ContactMessage $message, ObjectManager $manager, $csrfToken)
    {
        if (!$this->isCsrfTokenValid($this->getUser()->getId()->toString(), $csrfToken)) {
            throw new AccessDeniedException('Forbidden.');
        }

        $message->setIsRead(true);
        $manager->flush();
        $this->addFlash('success', 'The message has been archived.');

        return $this->redirectToRoute('admin_messages_new');
    }

    /**
     * @Route("/admin/user/not-activated", name="admin_users_not_activated", methods={"GET"})
     */
    public function showNotActivatedUsers(UserRepository $userRepository)
    {
        $csrfToken = $this->getUser()->getId()->toString();

        $users = $userRepository->findAllNotActivated();
        $onlyRealUsers = [];
        foreach($users as $user) {
            if(is_int(strpos($user->getEmail(), '@'))) {
                $onlyRealUsers[] = $user;
            }
        }
        return $this->render('admin/users_not_activated.html.twig', [
            'users' => $onlyRealUsers,
            "csrfToken" => $csrfToken
        ]);
    }

    /**
     * @Route("/admin/user/expired-1-month/delete", name="admin_users_not_activated_delete", methods={"GET"})
     */
    public function deleteAllNotActivatedUsers(ObjectManager $manager, UserRepository $userRepository)
    {
        $users = $userRepository->findAllNotActivated();
        $onlyRealUsers = [];
        foreach($users as $user) {
            if(is_int(strpos($user->getEmail(), '@')) && $user->getCreatedAt() < new \DateTime('- 1 month')) {
                $onlyRealUsers[] = $user;
            }
        }

        foreach($onlyRealUsers as $user) {
            $manager->remove($user);
            $manager->flush();
        }
        
        $this->addFlash('success', 'All accounts created from 1 month ago have been deleted with success.');

        return $this->redirectToRoute('admin_users_not_activated');
    }

    /**
     * @Route("/admin/user/{id}/delete/{csrfToken}", name="admin_user_delete", methods={"GET"})
     */
    public function deleteUser(User $user, ObjectManager $manager, $csrfToken)
    {
        if (!$this->isCsrfTokenValid($this->getUser()->getId()->toString(), $csrfToken)) {
            throw new AccessDeniedException('Forbidden.');
        }

        $manager->remove($user);
        $manager->flush();
        $this->addFlash('success', 'The account has been deleted with success.');

        return $this->redirectToRoute('admin_users_not_activated');
    }
}