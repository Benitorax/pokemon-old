<?php

namespace App\Controller;

use App\Form\DeleteAccountType;
use App\Form\ModifyPasswordFormType;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class AppController extends AbstractController
{
    /**
     * @Route("/", name="app_redirect_index")
     */
    public function redirectIndex()
    {
        return $this->redirectToRoute('app_index');
    }
    
    /**
     * @Route("/index/", name="app_index")
     */
    public function index()
    {
        return $this->render('app/index.html.twig', [
        ]);
    }

    /**
     * @Route("/account/", name="app_account")
     */
    public function showAccount()
    {
        return $this->render('app/showAccount.html.twig', [
        ]);
    }

    /**
     * @Route("/account/password", name="app_modify_password")
     */
    public function modifyPassword(Request $request, UserPasswordEncoderInterface $passwordEncoder, ObjectManager $manager)
    {
        $passwordForm = $this->createForm(ModifyPasswordFormType::class);
        $passwordForm->handleRequest($request);

        if($passwordForm->isSubmitted()) {
            if($passwordForm->isValid()) {
                $data = $passwordForm->getData();
                $user = $this->getUser();
    
                if($passwordEncoder->isPasswordValid($user, $data['password'])) {
                    $encodedPassword = $passwordEncoder->encodePassword($user, $data['newPassword']);
                    $user->setPassword($encodedPassword);
                    $manager->flush();
                    $this->addFlash('success', 'Your password has been modified.');
                    return $this->redirectToRoute('app_account');
                } else {
                    $this->addFlash('danger', 'Password invalid.');
                }
    
            } else {
                $this->addFlash('danger', 'The new password doesn\'t match in both fields.');
            }
        }

        return $this->render('app/modify_password.html.twig', [
            'passwordForm' => $passwordForm->createView()
        ]);
    }

    /**
     * @Route("/account/delete", name="app_account_delete")
     */
    public function deleteAccount(Request $request, ObjectManager $manager, TokenStorageInterface $tokenStorage)
    {
        $deleteAccountForm = $this->createForm(DeleteAccountType::class);
        $deleteAccountForm->handleRequest($request);

        if($deleteAccountForm->isSubmitted() && $deleteAccountForm->isValid()) {
            $manager->remove($this->getUser());
            $manager->flush();
            $tokenStorage->setToken(null);
            $request->getSession()->invalidate();
            $this->addFlash('success', 'Your account has been deleted.');

            return $this->redirectToRoute('app_index');
        }

        return $this->render('app/deleteAccount.html.twig', [
            'deleteAccountForm' => $deleteAccountForm->createView()
        ]);
    }
}
