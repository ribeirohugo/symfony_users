<?php

namespace App\Tests\Unit\Common;

use App\Common\Password;
use App\Entity\User;
use App\Tests\Utils\ConstHelper;
use PHPUnit\Framework\TestCase;

class PasswordTest extends TestCase{
    public function testAutoUserHasherSuccess() {
        $user = new User(
            ConstHelper::USER_NAME_TEST,
            ConstHelper::USER_EMAIL_TEST,
            ConstHelper::USER_PASSWORD_TEST,
            ConstHelper::USER_PHONE_TEST
        );

        $hasher = Password::autoUserHasher();

        $hashedPassword = $hasher->hashPassword($user, $user->getPassword());
        $user->setPassword($hashedPassword);

        $result = $hasher->isPasswordValid($user, ConstHelper::USER_PASSWORD_TEST);

        $this->assertTrue($result);
    }

    public function testAutoUserHasherInvalid() {
        $user = new User(
            ConstHelper::USER_NAME_TEST,
            ConstHelper::USER_EMAIL_TEST,
            ConstHelper::USER_PASSWORD_TEST,
            ConstHelper::USER_PHONE_TEST
        );

        $hasher = Password::autoUserHasher();

        $hashedPassword = $hasher->hashPassword($user, $user->getPassword());
        $user->setPassword($hashedPassword);

        $result = $hasher->isPasswordValid($user, ConstHelper::NEW_USER_PASSWORD_TEST);

        $this->assertFalse($result);
    }
}
