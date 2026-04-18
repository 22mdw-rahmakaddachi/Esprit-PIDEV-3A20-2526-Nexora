<?php

namespace App\Security;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Authorization\AccessDeniedHandlerInterface;
use Twig\Environment;

class AccessDeniedHandler implements AccessDeniedHandlerInterface
{
    public function __construct(
        private RouterInterface $router,
        private TokenStorageInterface $tokenStorage,
        private Environment $twig,
    ) {}

    public function handle(Request $request, AccessDeniedException $accessDeniedException): ?Response
    {
        $token = $this->tokenStorage->getToken();

        // Non connecté → login
        if (!$token || !$token->getUser()) {
            return new RedirectResponse($this->router->generate('app_login'));
        }

        // Connecté sans droits → 404
        $html = $this->twig->render('bundles/TwigBundle/Exception/error404.html.twig');
        return new Response($html, Response::HTTP_NOT_FOUND);
    }
}
