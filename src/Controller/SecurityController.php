<?php

namespace App\Controller;

use App\Entity\RegisterUserDTO;
use App\Form\RegisterType;
use App\Form\CheckEmailType;
use App\Handler\UserHandler;
use App\Mailer\CustomMailer;
use App\Form\ResetPasswordType;
use App\ReCaptcha\ReCaptchaService;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login', methods: ['GET', 'POST'])]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->isGranted('ROLE_USER')) {
            return $this->redirectToRoute('app_index');
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    #[Route(path: '/logout', name: 'app_logout', methods: ['GET'])]
    public function logout(): void
    {
        throw new \Exception('This method can be blank - it will be intercepted by the logout key on your firewall');
    }

    #[Route(path: '/register', name: 'app_register', methods: ['GET', 'POST'])]
    public function register(
        Request $request,
        UserHandler $userHandler,
        CustomMailer $mailer,
        UserRepository $userRepository,
        ReCaptchaService $reCaptcha
    ): Response {
        if ($this->isGranted('ROLE_USER')) {
            return $this->redirectToRoute('app_index');
        }

        $registerUserDTO = new RegisterUserDTO($userRepository);
        $form = $this->createForm(RegisterType::class, $registerUserDTO);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $reCaptcha->handleResponse($request->get('g-recaptcha-response'));

            if ($reCaptcha->isResponseValid(0.5)) {
                $user = $userHandler->handle($form->getData());
                $mailer->sendMailAfterRegistration($user);
                $this->addFlash(
                    'success',
                    'Congrats, you have been registered with success!'
                    . ' You will receive an email to confirm your address.'
                );

                return $this->redirectToRoute('app_index');
            } else {
                $this->addFlash('danger', 'Sorry, robots are not allowed. If you\'re human, try it again.');
            }
        }

        return $this->render('security/register.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/email_confirm/', name: 'app_email_confirm', methods: ['GET'])]
    public function confirmEmailAddress(
        Request $request,
        UserRepository $userRepository,
        EntityManagerInterface $manager
    ): Response {
        $token = $request->query->get('token');
        $user = $userRepository->findOneBy(['token' => $token]);

        if (null !== $user) {
            $user->setToken(null);
            $user->setTokenCreatedAt(null);
            $user->setIsActivated(true);
            $manager->flush();
            $this->addFlash('success', 'Thank you, your account is now activated.');
        } else {
            $this->addFlash('danger', 'Your account has been deleted, you need to register again');
        }

        return $this->redirectToRoute('app_login');
    }

    #[Route(path: '/password/forgotten/', name: 'app_password_forgotten', methods: ['GET', 'POST'])]
    public function passwordForgotten(
        Request $request,
        UserRepository $userRepository,
        CustomMailer $mailer,
        ReCaptchaService $reCaptcha
    ): Response {
        $emailForm = $this->createForm(CheckEmailType::class);
        $emailForm->handleRequest($request);

        if ($emailForm->isSubmitted() && $emailForm->isValid()) {
            $reCaptcha->handleResponse($request->get('g-recaptcha-response'));

            if ($reCaptcha->isResponseValid(0.5)) {
                $email = $emailForm->getData()['email'];
                $user = $userRepository->findOneIsActivatedByEmail($email);

                if (null !== $user) {
                    $mailer->sendMailToResetPassword($user);
                    $this->addFlash('success', 'You will receive an email to reset your password.');

                    return $this->redirectToRoute('app_login');
                } else {
                    $this->addFlash('danger', 'This email is not registered.');
                }
            } else {
                $this->addFlash('danger', 'Sorry, robots are not allowed. If you\'re human, try it again.');
            }
        }

        return $this->render('security/password_forgotten.html.twig', [
            'emailForm' => $emailForm->createView()
        ]);
    }

    #[Route(path: '/password/reset/', name: 'app_password_reset', methods: ['GET', 'POST'])]
    public function resetPasswordForgotten(
        Request $request,
        UserRepository $userRepository,
        UserHandler $userHandler,
        EntityManagerInterface $manager,
        CustomMailer $mailer
    ): Response {
        $token = $request->query->get('token');

        if (!$token) {
            throw new \ErrorException('This page does\'t exist.');
        }

        $user = $userRepository->findOneBy(['token' => $token]);

        if (null !== $user) {
            /** @var \DateTime */
            $tokenDatetime = $user->getTokenCreatedAt();
            $interval = (new \DateTime('now'))->diff($tokenDatetime);

            if ($interval->format('%a') >= 1) {
                $this->addFlash('danger', 'Your request has expired. Make a new request again.');
                return $this->redirectToRoute('app_password_forgotten');
            }

            $resetPasswordForm = $this->createForm(ResetPasswordType::class);
            $resetPasswordForm->handleRequest($request);

            if ($resetPasswordForm->isSubmitted() && $resetPasswordForm->isValid()) {
                $user->setToken(null);
                $user->setTokenCreatedAt(null);
                $userHandler->modifyPassword($user, $resetPasswordForm->getData()['newPassword']);
                $manager->flush();
                $mailer->sendMailToConfirmResetPassword($user);
                $this->addFlash('success', 'Your password has been modified with success.');
                return $this->redirectToRoute('app_login');
            }
        }

        return $this->render('security/password_reset.html.twig', [
            'resetPasswordForm' => isset($resetPasswordForm) ? $resetPasswordForm->createView() : null,
        ]);
    }
}
