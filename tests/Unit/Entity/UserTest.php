<?php

namespace App\Tests\Unit\Entity;

use App\Entity\User;
use App\Tests\Utils\ConstHelper;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase{
    public function testUserCreate() {
        $user = new User(
            ConstHelper::USER_NAME_TEST,
            ConstHelper::USER_EMAIL_TEST,
            ConstHelper::USER_PASSWORD_TEST,
            ConstHelper::USER_PHONE_TEST,
        );

        $this->assertIsObject($user);
        $this->assertEquals(ConstHelper::USER_NAME_TEST, $user->getName());
        $this->assertEquals(ConstHelper::USER_EMAIL_TEST, $user->getEmail());
        $this->assertEquals(ConstHelper::USER_PASSWORD_TEST, $user->getPassword());
        $this->assertEquals(ConstHelper::USER_PHONE_TEST, $user->getPhone());
    }

    public function testUserId() {
        $user = new User("", "", "", "");

        $user->setId(ConstHelper::USER_ID_TEST);

        $this->assertEquals(ConstHelper::USER_ID_TEST, $user->getId());
    }

    public function testUserName() {
        $user = new User("", "", "", "");

        $user->setName(ConstHelper::USER_NAME_TEST);

        $this->assertEquals(ConstHelper::USER_NAME_TEST, $user->getName());
    }

    public function testUserEmail() {
        $user = new User("", "", "", "");

        $user->setEmail(ConstHelper::USER_EMAIL_TEST);

        $this->assertEquals(ConstHelper::USER_EMAIL_TEST, $user->getEmail());    }

    public function testUserPassword() {
        $user = new User("", "", "", "");

        $user->setPassword(ConstHelper::USER_PASSWORD_TEST);

        $this->assertEquals(ConstHelper::USER_PASSWORD_TEST, $user->getPassword());
    }

    public function testUserPhone() {
        $user = new User("", "", "", "");

        $user->setPhone(ConstHelper::USER_PHONE_TEST);

        $this->assertEquals(ConstHelper::USER_PHONE_TEST, $user->getPhone());
    }

    public function testUserCreatedAt() {
        $user = new User("", "", "", "");

        $createdAt = new \DateTime();

        $user->setCreatedAt($createdAt);

        $this->assertEquals($createdAt, $user->getCreatedAt());
    }

    public function testUserUpdatedAt() {
        $user = new User("", "", "", "");

        $updatedAt = new \DateTime();

        $user->setUpdatedAt($updatedAt);

        $this->assertEquals($updatedAt, $user->getUpdatedAt());
    }
}
