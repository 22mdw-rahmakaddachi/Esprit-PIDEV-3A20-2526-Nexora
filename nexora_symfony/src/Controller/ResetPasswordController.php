<?php

namespace App\Controller;

use App\Repository\UsersRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

final class ResetPasswordController extends AbstractController
{
    #[Route('/forgot-password', name: 'app_forgot_password', methods: ['GET', 'POST'])]
    public function forgotPassword(
        Request $request,
        UsersRepository $usersRepo,
        EntityManagerInterface $em,
        MailerInterface $mailer
    ): Response {
        $sent = false;
        $error = null;

        if ($request->isMethod('POST')) {
            $emailInput = trim($request->request->get('email', ''));
            $user = $usersRepo->findOneBy(['email' => $emailInput]);

            if ($user) {
                $code = strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
                $user->setResetCode($code);
                $user->setResetExpiration(time() + 3600); // 1h
                $em->flush();

                try {
                    $mailer->send(
                        (new Email())
                            ->from('anoir5502@gmail.com')
                            ->to($user->getEmail())
                            ->subject('🔑 Réinitialisation de votre mot de passe — Nexora')
                            ->html(
                                '<div style="font-family:Inter,Arial,sans-serif;max-width:520px;margin:auto;padding:32px;border:1px solid #ede5ff;border-radius:16px">'
                                . '<h2 style="color:#4e2d9a;font-family:Georgia,serif;margin-bottom:8px">Réinitialisation du mot de passe</h2>'
                                . '<p style="color:#555">Bonjour <strong>' . htmlspecialchars($user->getPrenom()) . '</strong>,</p>'
                                . '<p style="color:#555">Voici votre code de réinitialisation (valable <strong>1 heure</strong>) :</p>'
                                . '<div style="text-align:center;margin:28px 0">'
                                . '<span style="display:inline-block;background:linear-gradient(135deg,#6c3fc5,#9b59b6);color:#fff;font-size:2rem;font-weight:800;letter-spacing:8px;padding:16px 32px;border-radius:14px">' . $code . '</span>'
                                . '</div>'
                                . '<p style="color:#888;font-size:0.85rem">Si vous n\'avez pas demandé cette réinitialisation, ignorez cet email.</p>'
                                . '<p style="color:#aaa;font-size:0.78rem;margin-top:24px">— L\'équipe Nexora</p>'
                                . '</div>'
                            )
                    );
                } catch (\Exception $e) {
                    // silencieux — on ne révèle pas si l'email existe
                }
            }

            // Toujours afficher "envoyé" pour ne pas révéler si l'email existe
            $sent = true;
        }

        return $this->render('security/forgot_password.html.twig', [
            'sent'  => $sent,
            'error' => $error,
        ]);
    }

    #[Route('/reset-password', name: 'app_reset_password', methods: ['GET', 'POST'])]
    public function resetPassword(
        Request $request,
        UsersRepository $usersRepo,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $hasher
    ): Response {
        $error   = null;
        $success = false;
        $email   = $request->query->get('email', $request->request->get('email', ''));
        $code    = $request->query->get('code', $request->request->get('code', ''));

        if ($request->isMethod('POST')) {
            $email    = trim($request->request->get('email', ''));
            $code     = trim($request->request->get('code', ''));
            $password = $request->request->get('password', '');
            $confirm  = $request->request->get('confirm', '');

            if (strlen($password) < 6) {
                $error = 'Le mot de passe doit contenir au moins 6 caractères.';
            } elseif ($password !== $confirm) {
                $error = 'Les mots de passe ne correspondent pas.';
            } else {
                $user = $usersRepo->findOneBy(['email' => $email, 'resetCode' => strtoupper($code)]);

                if (!$user || $user->getResetExpiration() < time()) {
                    $error = 'Code invalide ou expiré.';
                } else {
                    $user->setMdp($hasher->hashPassword($user, $password));
                    $user->setResetCode(null);
                    $user->setResetExpiration(null);
                    $em->flush();
                    $success = true;
                }
            }
        }

        return $this->render('security/reset_password.html.twig', [
            'error'   => $error,
            'success' => $success,
            'email'   => $email,
            'code'    => $code,
        ]);
    }
}
