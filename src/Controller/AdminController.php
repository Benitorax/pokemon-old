<?php

namespace App\Controller;

use App\Entity\User;
use App\Handler\UserHandler;
use App\Entity\ContactMessage;
use App\Repository\UserRepository;
use App\Repository\ContactMessageRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class AdminController extends AbstractController
{
    #[Route(path: '/admin/messages/new', name: 'admin_messages_new', methods: ['GET'])]
    public function showNewMessages(ContactMessageRepository $messageRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $messages = $messageRepository->findNewMessages();
        /** @var User */
        $user = $this->getUser();
        $csrfToken = $user->getUuid()->__toString();

        return $this->render('admin/show_messages.html.twig', [
            'messages' => $messages,
            'title' => 'New messages',
            'csrfToken' => $csrfToken
        ]);
    }

    #[Route(path: '/admin/messages/archived', name: 'admin_messages_archived', methods: ['GET'])]
    public function showArchivedMessages(ContactMessageRepository $messageRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $messages = $messageRepository->findReadMessages();
        /** @var User */
        $user = $this->getUser();
        $csrfToken = $user->getUuid()->__toString();

        return $this->render('admin/show_messages.html.twig', [
            'messages' => $messages,
            'title' => 'Archived messages',
            'csrfToken' => $csrfToken
        ]);
    }

    #[Route(path: '/admin/messages/new/count', name: 'admin_message_new_count', methods: ['GET'])]
    public function getNewContactMessageCount(ContactMessageRepository $contactMessageRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $newContactMessageCount = count($contactMessageRepository->findNewMessages());

        return $this->json([
            'count' => $newContactMessageCount
        ]);
    }

    #[Route(path: '/admin/messages/{id}/archive', name: 'admin_messages_archive', methods: ['POST'])]
    public function archiveMessage(Request $request, ContactMessage $message, EntityManagerInterface $manager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $csrfToken = (string) $request->request->get('token');

        /** @var User */
        $user = $this->getUser();

        if (!$this->isCsrfTokenValid($user->getUuid()->__toString(), $csrfToken)) {
            throw new AccessDeniedException('Forbidden.');
        }

        $message->setIsRead(true);
        $manager->flush();
        $this->addFlash('success', 'The message has been archived.');

        return $this->redirectToRoute('admin_messages_new');
    }

    #[Route(path: '/admin/messages/{id}/delete', name: 'admin_messages_delete', methods: ['POST'])]
    public function deleteMessage(Request $request, ContactMessage $message, EntityManagerInterface $manager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $csrfToken = (string) $request->request->get('token');

        /** @var User */
        $user = $this->getUser();

        if (!$this->isCsrfTokenValid($user->getUuid()->__toString(), $csrfToken)) {
            throw new AccessDeniedException('Forbidden.');
        }

        $isRead = $message->getIsRead();
        $manager->remove($message);
        $manager->flush();
        $this->addFlash('success', 'The message has been deleted.');

        if ($isRead) {
            return $this->redirectToRoute('admin_messages_archived');
        } else {
            return $this->redirectToRoute('admin_messages_new');
        }
    }

    #[Route(path: '/admin/users/activated', name: 'admin_users_activated', methods: ['GET'])]
    public function showActivatedUsers(UserRepository $userRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        /** @var User */
        $user = $this->getUser();
        $csrfToken = $user->getUuid()->__toString();
        $users = $userRepository->findAllActivated();

        return $this->render('admin/users_activated.html.twig', [
            'users' => $users,
            "csrfToken" => $csrfToken
        ]);
    }

    #[Route(path: '/admin/user/inactivated', name: 'admin_users_not_activated', methods: ['GET'])]
    public function showInactivatedUsers(UserRepository $userRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        /** @var User */
        $user = $this->getUser();
        $csrfToken = $user->getUuid()->__toString();
        $users = $userRepository->findAllInactivated();
        $onlyRealUsers = [];

        foreach ($users as $user) {
            if (is_int(strpos($user->getEmail(), '@'))) {
                $onlyRealUsers[] = $user;
            }
        }
        return $this->render('admin/users_not_activated.html.twig', [
            'users' => $onlyRealUsers,
            "csrfToken" => $csrfToken
        ]);
    }

    #[Route(path: '/admin/user/expired-1-month/delete', name: 'admin_users_not_activated_delete', methods: ['GET'])]
    public function deleteAllInactivatedUsers(EntityManagerInterface $manager, UserRepository $userRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $users = $userRepository->findAllInactivated();
        $onlyRealUsers = [];
        foreach ($users as $user) {
            if (is_int(strpos($user->getEmail(), '@')) && $user->getCreatedAt() < new \DateTime('- 1 month')) {
                $onlyRealUsers[] = $user;
            }
        }

        if ($onlyRealUsers === [] || $onlyRealUsers === null) {
            $this->addFlash('danger', 'No accounts have been created one month ago.');
            return $this->redirectToRoute('admin_users_not_activated');
        }

        foreach ($onlyRealUsers as $user) {
            $manager->remove($user);
            $manager->flush();
        }

        $this->addFlash('success', 'All accounts created one month ago have been deleted with success.');
        return $this->redirectToRoute('admin_users_not_activated');
    }

    #[Route(path: '/admin/user/inactivated/{id}/delete', name: 'admin_user_inactivated_delete', methods: ['POST'])]
    public function deleteInactivatedUser(Request $request, User $user, EntityManagerInterface $manager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $csrfToken = (string) $request->request->get('token');

        /** @var User */
        $adminUser = $this->getUser();

        if (!$this->isCsrfTokenValid($adminUser->getUuid()->__toString(), $csrfToken)) {
            throw new AccessDeniedException('Forbidden.');
        }

        $manager->remove($user);
        $manager->flush();
        $this->addFlash('success', 'The account has been deleted with success.');

        return $this->redirectToRoute('admin_users_not_activated');
    }

    #[Route(path: '/admin/user/{id}/delete}', name: 'admin_user_delete', methods: ['POST'])]
    public function deleteUser(Request $request, User $user, UserHandler $userHandler): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $csrfToken = (string) $request->request->get('token');

        /** @var User */
        $adminUser = $this->getUser();

        if (!$this->isCsrfTokenValid($adminUser->getUuid()->__toString(), $csrfToken)) {
            throw new AccessDeniedException('Forbidden.');
        }

        $userHandler->deleteUser($user);
        $this->addFlash('success', 'The account has been deleted with success.');

        return $this->redirectToRoute('admin_users_activated');
    }
}
