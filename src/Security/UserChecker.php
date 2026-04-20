<?php

namespace App\Security;

use App\Entity\Users;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof Users) return;

        $remaining = $user->getBlockUntil() - time();
        if ($remaining > 0) {
            $msg = $remaining >= 60
                ? sprintf('Compte temporairement bloqué. Réessayez dans %d minute(s).', ceil($remaining / 60))
                : sprintf('Compte temporairement bloqué. Réessayez dans %d seconde(s).', $remaining);
            throw new CustomUserMessageAccountStatusException($msg);
        }
    }

    public function checkPostAuth(UserInterface $user): void {}
}
