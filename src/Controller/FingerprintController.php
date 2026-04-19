<?php

namespace App\Controller;

use App\Entity\Fingerprint;
use App\Entity\Users;
use App\Repository\FingerprintRepository;
use App\Service\FingerprintBridgeService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/fingerprint')]
class FingerprintController extends AbstractController
{
    // ── Page de login par empreinte ──
    #[Route('/login', name: 'fingerprint_login_page')]
    public function loginPage(): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_redirect_after_login');
        }
        return $this->render('security/fingerprint_login.html.twig');
    }

    // ── Page d'enregistrement d'empreinte (profil) ──
    #[Route('/register', name: 'fingerprint_register_page')]
    public function registerPage(FingerprintRepository $repo): Response
    {
        $user = $this->getUser();
        if (!$user instanceof Users) {
            return $this->redirectToRoute('app_login');
        }

        $fingerprints = $repo->findBy(['user' => $user]);

        return $this->render('security/fingerprint_register.html.twig', [
            'fingerprints' => $fingerprints,
            'user'         => $user,
        ]);
    }
}
