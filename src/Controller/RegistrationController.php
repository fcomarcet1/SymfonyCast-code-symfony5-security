<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Symfony\Component\Security\Http\Authenticator\FormLoginAuthenticator;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

class RegistrationController extends AbstractController
{
    public const VERIFY_EMAIL_ROUTE = 'app_verify_email';

    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $entityManager,
        UserAuthenticatorInterface $userAuthenticator,
        FormLoginAuthenticator $formLoginAuthenticator,
        VerifyEmailHelperInterface $verifyEmailHelper
    ): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
            $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            // logged in automatically after registration
            //$userAuthenticator->authenticateUser($user, $formLoginAuthenticator, $request);

            $entityManager->persist($user);
            $entityManager->flush();

            // do anything else you need here, like send an email
            $signatureComponents = $verifyEmailHelper->generateSignature(
                self::VERIFY_EMAIL_ROUTE,
                $user->getId(),
                $user->getEmail(),
                ['id' => $user->getId()]
            );

            // TODO: send email
            //$this->addFlash('success', 'Registration successful. Please check your email to verify your account.');

            // TODO: in a real app, send this as an email!
            $this->addFlash('success', 'Your account has been created!.Confirm your email at: '.$signatureComponents->getSignedUrl());
            //https://127.0.0.1:8000/verify?expires=1668074669&id=56&signature=pTzq8zmXaYvZ5EhqcP5Kn2%2BDHbUlUd%2FKPU7ilmSzH6s%3D&token=dqhaDgFovnc4mFtgIXOM6XzUbECVgXhQrxQsnkAl%2F4g%3D

            return $this->redirectToRoute('app_login');
            //return $this->redirectToRoute('app_homepage');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }


    /**
     * @Route("/verify", name="app_verify_email")
     */
    public function verifyUserEmail(
        Request $request,
        VerifyEmailHelperInterface $verifyEmailHelper,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager
    ): Response
    {
        $user = $userRepository->find($request->query->get('id'));
        if (!$user) {
            throw $this->createNotFoundException();
        }

        try {
            $verifyEmailHelper->validateEmailConfirmation(
                $request->getUri(),
                $user->getId(),
                $user->getEmail(),
            );
        } catch (VerifyEmailExceptionInterface $e) {
            $this->addFlash('error', $e->getReason());
            return $this->redirectToRoute('app_register');
        }

        $user->setIsVerified(true);
        $entityManager->flush();

        $this->addFlash('success', 'Your email address has been verified.');

        return $this->redirectToRoute('app_login');

    }
}
