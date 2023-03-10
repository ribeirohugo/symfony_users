<?php

namespace App\Tests\Utils;

use App\Common\Password;
use App\Entity\Roles;
use App\Entity\User;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Uid\Uuid;

class FixtureHelper {
    /**
     * @param EntityManager $entityManager
     * @return User|null
     */
    public static function addUser(EntityManager $entityManager, $email = ConstHelper::USER_EMAIL_TEST): ?User {
        $expectedRoles = [Roles::ROLE_USER, Roles::ROLE_ADMIN];

        $user = new User(
            ConstHelper::USER_NAME_TEST,
            $email,
            ConstHelper::USER_PASSWORD_TEST,
            ConstHelper::USER_PHONE_TEST,
            $expectedRoles,
        );
        $user->setCreatedAt(new \DateTime());
        $user->setUpdatedAt(new \DateTime());
        $user->setExternalId(Uuid::v4());

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