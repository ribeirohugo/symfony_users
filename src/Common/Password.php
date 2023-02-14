<?php

namespace App\Common;

use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasher;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

/**
 * Password holds common password methods to be used across packages.
 */
class Password {
    /**
     * autoUserHasher generates a UserPasswordHasher based on auto password hashing configs.
     *
     * @return UserPasswordHasherInterface
     */
    public static function autoUserHasher(): UserPasswordHasherInterface
    {
        $passwordHasherFactory = new PasswordHasherFactory([
            PasswordAuthenticatedUserInterface::class => ['algorithm' => 'auto'],
        ]);

        return new UserPasswordHasher($passwordHasherFactory);
    }
}