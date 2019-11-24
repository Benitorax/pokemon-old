<?php

namespace App\Controller;

use App\Entity\User;
use App\Handler\UserHandler;
use App\Entity\ContactMessage;
use App\Repository\UserRepository;
use App\Repository\ContactMessageRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class AdminController extends AbstractController
{
    /**
     * @Route("/admin/messages/new", name="admin_messages_new", methods={"GET"})
     */
    public function showNewMessages(ContactMessageRepository $messageRepository)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $messages = $messageRepository->findNewMessages();
        $csrfToken = $this->getUser()->getId()->toString();

        return $this->render('admin/show_messages.html.twig', [
            'messages' => $messages,
            'title' => 'New messages',
            'csrfToken' => $csrfToken
        ]);
    }

    /**
     * @Route("/admin/messages/archived", name="admin_messages_archived", methods={"GET"})
     */
    public function showArchivedMessages(ContactMessageRepository $messageRepository)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $messages = $messageRepository->findReadMessages();
        $csrfToken = $this->getUser()->getId()->toString();

        return $this->render('admin/show_messages.html.twig', [
            'messages' => $messages,
            'title' => 'Archived messages',
            'csrfToken' => $csrfToken
        ]);
    }

    /**
     * @Route("/admin/messages/new/count", name="admin_message_new_count", methods={"GET"})
     */
    public function getNewContactMessageCount(ContactMessageRepository $contactMessageRepository)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $newContactMessageCount = count($contactMessageRepository->findNewMessages());

        return $this->json([
            'count' => $newContactMessageCount
        ]);
    }  

    /**
     * @Route("/admin/messages/{id}/archive/{csrfToken}", name="admin_messages_archive", methods={"GET"})
     */
    public function archiveMessage(ContactMessage $message, ObjectManager $manager, $csrfToken)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if (!$this->isCsrfTokenValid($this->getUser()->getId()->toString(), $csrfToken)) {
            throw new AccessDeniedException('Forbidden.');
        }

        $message->setIsRead(true);
        $manager->flush();
        $this->addFlash('success', 'The message has been archived.');

        return $this->redirectToRoute('admin_messages_new');
    }

    /**
     * @Route("/admin/messages/{id}/delete/{csrfToken}", name="admin_messages_delete", methods={"GET"})
     */
    public function deleteMessage(Request $request, ContactMessage $message, ObjectManager $manager, $csrfToken)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if (!$this->isCsrfTokenValid($this->getUser()->getId()->toString(), $csrfToken)) {
            throw new AccessDeniedException('Forbidden.');
        }
        $isRead = $message->getIsRead();
        $manager->remove($message);
        $manager->flush();
        $this->addFlash('success', 'The message has been deleted.');

        if($isRead) {
            return $this->redirectToRoute('admin_messages_archived');
        } else {
            return $this->redirectToRoute('admin_messages_new');
        }
    }

    /**
     * @Route("/admin/users/activated", name="admin_users_activated", methods={"GET"})
     */
    public function showActivatedUsers(UserRepository $userRepository)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $csrfToken = $this->getUser()->getId()->toString();

        $users = $userRepository->findAllActivated();

        return $this->render('admin/users_activated.html.twig', [
            'users' => $users,
            "csrfToken" => $csrfToken
        ]);
    }

    /**
     * @Route("/admin/user/inactivated", name="admin_users_not_activated", methods={"GET"})
     */
    public function showInactivatedUsers(UserRepository $userRepository)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $csrfToken = $this->getUser()->getId()->toString();

        $users = $userRepository->findAllInactivated();
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
    public function deleteAllInactivatedUsers(ObjectManager $manager, UserRepository $userRepository)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $users = $userRepository->findAllInactivated();
        $onlyRealUsers = [];
        foreach($users as $user) {
            if(is_int(strpos($user->getEmail(), '@')) && $user->getCreatedAt() < new \DateTime('- 1 month')) {
                $onlyRealUsers[] = $user;
            }
        }

        if(count($onlyRealUsers) === 0) {
            $this->addFlash('danger', 'No accounts have been created one month ago.');
            return $this->redirectToRoute('admin_users_not_activated');    
        }

        foreach($onlyRealUsers as $user) {
            $manager->remove($user);
            $manager->flush();
        }
        
        $this->addFlash('success', 'All accounts created one month ago have been deleted with success.');
        return $this->redirectToRoute('admin_users_not_activated');
    }

    /**
     * @Route("/admin/user/inactivated/{id}/delete/{csrfToken}", name="admin_user_inactivated_delete", methods={"GET"})
     */
    public function deleteInactivatedUser(User $user, ObjectManager $manager, $csrfToken)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if (!$this->isCsrfTokenValid($this->getUser()->getId()->toString(), $csrfToken)) {
            throw new AccessDeniedException('Forbidden.');
        }

        $manager->remove($user);
        $manager->flush();
        $this->addFlash('success', 'The account has been deleted with success.');

        return $this->redirectToRoute('admin_users_not_activated');
    }

    /**
     * @Route("/admin/user/{id}/delete/{csrfToken}", name="admin_user_delete", methods={"GET"})
     */
    public function deleteUser(User $user, $csrfToken, UserHandler $userHandler)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if (!$this->isCsrfTokenValid($this->getUser()->getId()->toString(), $csrfToken)) {
            throw new AccessDeniedException('Forbidden.');
        }

        $userHandler->deleteUser($user);
        $this->addFlash('success', 'The account has been deleted with success.');

        return $this->redirectToRoute('admin_users_not_activated');
    }  
}