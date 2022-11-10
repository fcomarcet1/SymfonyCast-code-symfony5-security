<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Totp\TotpAuthenticatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    /**
     * @Route("/login", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_homepage');
        }

        return $this->render('security/login.html.twig', [
            'error' => $authenticationUtils->getLastAuthenticationError(),
            'last_username' => $authenticationUtils->getLastUsername(),
        ]);
    }

    /**
     * @Route("/admin/login", name="app_admin_login")
     *
     */
    public function adminLogin()
    {
        return new Response('Pretend admin login page, that should be public');
    }

    /**
     * @Route("/logout", name="app_logout")
     * @throws \Exception
     */
    public function logout()
    {
        throw new \Exception('logout() should never be reached');
    }

    /**
     * @Route("/authentication/2fa/enable", name="app_2fa_enable")
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function enable2fa(
        TotpAuthenticatorInterface $totpAuthenticator,
        EntityManagerInterface $entityManager
    )
    {
        $user = $this->getUser();
        if (!$user->isTotpAuthenticationEnabled()) {
            $user->setTotpSecret($totpAuthenticator->generateSecret());
            $entityManager->flush();
        }
        dd($user);

    }
}
