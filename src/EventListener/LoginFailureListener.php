<?php

namespace App\EventListener;

use App\Entity\Users;
use App\Repository\UsersRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Security\Http\Event\LoginFailureEvent;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

#[AsEventListener(event: LoginFailureEvent::class)]
#[AsEventListener(event: LoginSuccessEvent::class)]
final class LoginFailureListener
{
    // Durées de blocage en secondes par niveau
    // Niveau 1 = 3 fautes → 30s, Niveau 2 = 6 fautes → 60s, etc.
    private const BLOCK_DURATIONS = [1 => 30, 2 => 60, 3 => 120, 4 => 300, 5 => 600];

    public function __construct(
        private UsersRepository $usersRepo,
        private EntityManagerInterface $em
    ) {}

    public function __invoke(LoginFailureEvent|LoginSuccessEvent $event): void
    {
        if ($event instanceof LoginSuccessEvent) {
            $this->onSuccess($event);
        } else {
            $this->onFailure($event);
        }
    }

    private function onFailure(LoginFailureEvent $event): void
    {
        $email = $event->getRequest()->request->get('email', '');
        if (!$email) return;

        $user = $this->usersRepo->findOneBy(['email' => $email]);
        if (!$user instanceof Users) return;

        // Si encore bloqué, ne pas incrémenter
        if ($user->getBlockUntil() > time()) return;

        $tentatives = $user->getTentative() + 1;
        $user->setTentative($tentatives);

        // Tous les 3 échecs → bloquer et monter de niveau
        if ($tentatives % 3 === 0) {
            $level    = $user->getBlockLevel() + 1;
            $duration = self::BLOCK_DURATIONS[min($level, 5)];
            $user->setBlockLevel($level);
            $user->setBlockUntil(time() + $duration);
        }

        $this->em->flush();

        // Stocker en session le nombre de tentatives pour l'afficher dans le template
        $remaining = 3 - ($tentatives % 3);
        if ($remaining === 3) $remaining = 0; // vient d'être bloqué
        $event->getRequest()->getSession()->set('login_tentatives', $tentatives);
        $event->getRequest()->getSession()->set('login_remaining', $remaining);
    }

    private function onSuccess(LoginSuccessEvent $event): void
    {
        $user = $event->getUser();
        if (!$user instanceof Users) return;

        // Réinitialiser les compteurs après connexion réussie
        $user->setTentative(0);
        $user->setBlockLevel(0);
        $user->setBlockUntil(0);
        $this->em->flush();
    }
}
