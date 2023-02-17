<?php

namespace App\Tests\Utils;

use App\Common\Password;
use App\Entity\Roles;
use App\Entity\User;
use Doctrine\ORM\EntityManager;

class FixtureHelper {
    /**
     * @param EntityManager $entityManager
     * @return User|null
     */
    public static function addUser(EntityManager $entityManager): ?User {
        $expectedRoles = [Roles::ROLE_USER, Roles::ROLE_ADMIN];

        $user = new User(
            ConstHelper::USER_NAME_TEST,
            ConstHelper::USER_EMAIL_TEST,
            ConstHelper::USER_PASSWORD_TEST,
            ConstHelper::USER_PHONE_TEST,
            $expectedRoles,
        );
        $user->setCreatedAt(new \DateTime());
        $user->setUpdatedAt(new \DateTime());

        $hasher = Password::autoUserHasher();
        $hashedPasswod = $hasher->hashPassword($user, ConstHelper::USER_PASSWORD_TEST);
        $user->setPassword($hashedPasswod);

        return $entityManager
            ->getRepository(User::class)
            ->save($user, true);
    }

    /**
     * @param EntityManager $entityManager
     * @param User $user
     * @return void
     */
    public static function removeUser(EntityManager $entityManager, User $user): void {
        $entityManager
            ->getRepository(User::class)
            ->remove($user, true);
    }
}