<?php

namespace App\Controller;

use App\Entity\Users;
use App\Repository\UsersRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class GoogleAuthController extends AbstractController
{
    public function __construct(
        private string                   $googleClientId,
        private string                   $googleClientSecret,
        private HttpClientInterface      $httpClient,
        private UsersRepository          $usersRepo,
        private EntityManagerInterface   $em,
        private TokenStorageInterface    $tokenStorage,
        private EventDispatcherInterface $dispatcher,
    ) {}

    // ── Étape 1 : Rediriger vers Google ──────────────────────────────────────
    #[Route('/auth/google', name: 'auth_google_start')]
    public function start(Request $request): RedirectResponse
    {
        $state = bin2hex(random_bytes(16));
        $request->getSession()->set('oauth_state', $state);

        $callbackUrl = $this->generateUrl('auth_google_callback', [], UrlGeneratorInterface::ABSOLUTE_URL);

        $params = http_build_query([
            'client_id'     => $this->googleClientId,
            'redirect_uri'  => $callbackUrl,
            'response_type' => 'code',
            'scope'         => 'openid email profile',
            'state'         => $state,
            'access_type'   => 'online',
            'prompt'        => 'select_account',
        ]);

        return new RedirectResponse('https://accounts.google.com/o/oauth2/v2/auth?' . $params);
    }

    // ── Étape 2 : Callback Google ─────────────────────────────────────────────
    #[Route('/auth/google/callback', name: 'auth_google_callback')]
    public function callback(Request $request): Response
    {
        // Vérifier le state CSRF
        $sessionState = $request->getSession()->get('oauth_state');
        $returnedState = $request->query->get('state');

        if (!$sessionState || $sessionState !== $returnedState) {
            $this->addFlash('danger', 'Erreur de sécurité OAuth. Veuillez réessayer.');
            return $this->redirectToRoute('app_login');
        }

        $code = $request->query->get('code');
        if (!$code) {
            $error = $request->query->get('error', 'Accès refusé');
            $this->addFlash('danger', 'Connexion Google annulée : ' . $error);
            return $this->redirectToRoute('app_login');
        }

        // ── Échanger le code contre un access_token ──
        try {
            $callbackUrl = $this->generateUrl('auth_google_callback', [], UrlGeneratorInterface::ABSOLUTE_URL);

            $tokenResponse = $this->httpClient->request('POST', 'https://oauth2.googleapis.com/token', [
                'body' => [
                    'code'          => $code,
                    'client_id'     => $this->googleClientId,
                    'client_secret' => $this->googleClientSecret,
                    'redirect_uri'  => $callbackUrl,
                    'grant_type'    => 'authorization_code',
                ],
            ]);

            $statusCode = $tokenResponse->getStatusCode();
            $tokenData  = $tokenResponse->toArray(false); // false = ne pas lever d'exception sur erreur HTTP

            if ($statusCode !== 200 || empty($tokenData['access_token'])) {
                $errorDesc = $tokenData['error_description'] ?? ($tokenData['error'] ?? 'Erreur inconnue');
                throw new \RuntimeException('Google token error: ' . $errorDesc);
            }

            $accessToken = $tokenData['access_token'];

            // ── Récupérer les infos utilisateur ──
            $userInfoResponse = $this->httpClient->request('GET', 'https://www.googleapis.com/oauth2/v3/userinfo', [
                'headers' => ['Authorization' => 'Bearer ' . $accessToken],
            ]);

            $googleUser = $userInfoResponse->toArray(false);

        } catch (\Throwable $e) {
            $this->addFlash('danger', 'Erreur Google : ' . $e->getMessage());
            return $this->redirectToRoute('app_login');
        }

        $email  = $googleUser['email']      ?? null;
        $prenom = $googleUser['given_name']  ?? ($googleUser['name'] ?? 'Utilisateur');
        $nom    = $googleUser['family_name'] ?? 'Google';

        if (!$email) {
            $this->addFlash('danger', 'Impossible de récupérer votre email Google.');
            return $this->redirectToRoute('app_login');
        }

        // ── Trouver ou créer l'utilisateur ──
        $user = $this->usersRepo->findOneBy(['email' => $email]);

        if (!$user) {
            // Créer un nouveau compte
            $user = new Users();
            $user->setEmail($email);
            $user->setPrenom($prenom);
            $user->setNom($nom);
            $user->setRole('participant');
            $user->setMdp(''); // Pas de mot de passe pour les comptes Google
            $user->setValidation(1);
            $user->setTentative(0);
            $user->setBlockUntil(0);
            $user->setBlockLevel(0);
            $user->setNum(0);

            $this->em->persist($user);
            $this->em->flush();

            $this->addFlash('success', '🎉 Compte créé avec succès via Google ! Bienvenue ' . $prenom . ' !');
        } else {
            // Vérifier si le compte est bloqué
            if ($user->getBlockUntil() > time()) {
                $remaining = $user->getBlockUntil() - time();
                $this->addFlash('danger', "Votre compte est temporairement bloqué. Réessayez dans {$remaining} secondes.");
                return $this->redirectToRoute('app_login');
            }
        }

        // ── Connecter l'utilisateur manuellement ──
        $token = new UsernamePasswordToken($user, 'main', $user->getRoles());
        $this->tokenStorage->setToken($token);
        $request->getSession()->set('_security_main', serialize($token));

        $loginEvent = new InteractiveLoginEvent($request, $token);
        $this->dispatcher->dispatch($loginEvent, 'security.interactive_login');

        return $this->redirectToRoute('app_redirect_after_login');
    }
}
