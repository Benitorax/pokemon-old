<?php

namespace App\Controller;

use App\Form\DeleteAccountType;
use App\Form\ModifyPasswordType;
use App\Entity\ModifyPasswordDTO;
use App\Form\ContactMessageType;
use App\Handler\UserHandler;
use App\Mailer\CustomMailer;
use App\Manager\ContactMessageManager;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class AppController extends AbstractController
{  
    /**
     * @Route("/", name="app_index", methods={"GET"})
     */
    public function index()
    {
        return $this->render('app/index.html.twig', [
        ]);
    }

    /**
     * @Route("/account", name="app_account", methods={"GET"})
     */
    public function showAccount()
    {
        return $this->render('app/show_account.html.twig', [
        ]);
    }

    /**
     * @Route("/account/password", name="app_modify_password", methods={"GET","POST"})
     */
    public function modifyPassword(Request $request, UserPasswordEncoderInterface $passwordEncoder, ObjectManager $manager)
    {
        $passwordForm = $this->createForm(ModifyPasswordType::class);
        $passwordForm->handleRequest($request);

        if($passwordForm->isSubmitted() && $passwordForm->isValid()) {
            /** @var ModifyPasswordDTO $data */
            $data = $passwordForm->getData();
            $user = $this->getUser();

            if($passwordEncoder->isPasswordValid($user, $data->getPassword())) {
                if($data->getPassword() === $data->getNewPassword()) {
                    $this->addFlash('danger', 'Your new password has to be different from your actual password.');
                } else {
                    $encodedPassword = $passwordEncoder->encodePassword($user, $data->getNewPassword());
                    $user->setPassword($encodedPassword);
                    $manager->flush();
                    $this->addFlash('success', 'Your password has been modified.');
                    return $this->redirectToRoute('app_account');    
                }
            }
        }

        return $this->render('app/modify_password.html.twig', [
            'passwordForm' => $passwordForm->createView()
        ]);
    }

    /**
     * @Route("/account/delete", name="app_account_delete", methods={"GET","POST"})
     */
    public function deleteAccount(Request $request, UserHandler $userHandler, TokenStorageInterface $tokenStorage, \ReCaptcha\ReCaptcha $reCaptcha)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        
        $deleteAccountForm = $this->createForm(DeleteAccountType::class);
        $deleteAccountForm->handleRequest($request);

        if($deleteAccountForm->isSubmitted() && $deleteAccountForm->isValid()) {
            $gRecaptchaResponse = $request->get('g-recaptcha-response');
            $response = $reCaptcha->verify($gRecaptchaResponse);

            if($response->getScore() > 0.5) {
                $userHandler->deleteUser($this->getUser());
                $tokenStorage->setToken(null);
                $request->getSession()->invalidate();
                $this->addFlash('success', 'Your account has been deleted.');

                return $this->redirectToRoute('app_index');
            
            } else {
                $this->addFlash('danger','Sorry, robots are not allowed. If you\'re human, try it again.'); 
            }
        }

        return $this->render('app/delete_account.html.twig', [
            'deleteAccountForm' => $deleteAccountForm->createView()
        ]);
    }

    /**
     * @Route("/contact", name="app_contact", methods={"GET","POST"})
     */
    public function sendMessageToAdmin(Request $request, ContactMessageManager $messageManager, CustomMailer $mailer, \ReCaptcha\ReCaptcha $reCaptcha)
    {
        $contactForm = $this->createForm(ContactMessageType::class);
        $contactForm->handleRequest($request);
        if($contactForm->isSubmitted() && $contactForm->isValid()) {
            $gRecaptchaResponse = $request->get('g-recaptcha-response');
            $response = $reCaptcha->verify($gRecaptchaResponse);

            if($response->getScore() > 0.5) {
                $message = $messageManager->createContactMessage($contactForm->getData());
                $this->addFlash('success', 'Your message has been sent.');
                $mailer->sendMailToAdminForNewMessage($message);

                return $this->redirectToRoute('app_index');

            } else {
                $this->addFlash('danger','Sorry, robots are not allowed. If you\'re human, try it again.'); 
            }
        }

        return $this->render('app/contact.html.twig', [
            'contactForm' => $contactForm->createView()
        ]);
    }

    /**
     * @Route("/privacy-policy", name="app_privacy_policy", methods={"GET"})
     */
    public function showPrivacyPolicy($companyName, $websiteUrl)
    {
        return $this->render('app/privacy_policy.html.twig', [
            'companyName' => $companyName,
            'websiteUrl' => $websiteUrl
        ]);
    }
}
