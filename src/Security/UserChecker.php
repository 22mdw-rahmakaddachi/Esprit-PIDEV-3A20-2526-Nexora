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

        if ($user->getBlockUntil() > time()) {
            throw new CustomUserMessageAccountStatusException(
                'Votre compte est bloqué. Veuillez contacter l\'administrateur.'
            );
        }
    }

    public function checkPostAuth(UserInterface $user): void {}
}
