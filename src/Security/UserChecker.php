<?php

namespace App\Security;

use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;

class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        if (method_exists($user, 'isProfileCompleted') && !$user->isProfileCompleted()) {
            throw new CustomUserMessageAuthenticationException('Vous devez compléter votre profil avant de vous connecter.');

        }
    }

    public function checkPostAuth(UserInterface $user): void
    {

    }
}