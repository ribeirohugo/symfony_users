<?php

namespace App\Tests\Unit\Entity;

use App\Entity\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase{
    const USER_NAME_TEST = "name";
    const USER_EMAIL_TEST = "email@domain.com";
    const USER_PASSWORD_TEST = "password";
    const USER_PHONE_TEST = "910123123";

    public function testUserCreate() {
        $user = new User(self::USER_NAME_TEST, self::USER_EMAIL_TEST, self::USER_PASSWORD_TEST, self::USER_PHONE_TEST);

        $this->assertIsObject($user);
        $this->assertEquals(self::USER_NAME_TEST, $user->getName());
        $this->assertEquals(self::USER_EMAIL_TEST, $user->getEmail());
        $this->assertEquals(self::USER_PASSWORD_TEST, $user->getPassword());
        $this->assertEquals(self::USER_PHONE_TEST, $user->getPhone());
    }

    public function testUserName() {
        $user = new User("test", "test", "test", "test");

        $user->setName(self::USER_NAME_TEST);

        $this->assertEquals(self::USER_NAME_TEST, $user->getName());
    }

    public function testUserEmail() {
        $user = new User("test", "test", "test", "test");

        $user->setEmail(self::USER_EMAIL_TEST);

        $this->assertEquals(self::USER_EMAIL_TEST, $user->getEmail());    }

    public function testUserPassword() {
        $user = new User("test", "test", "test", "test");

        $user->setPassword(self::USER_PASSWORD_TEST);

        $this->assertEquals(self::USER_PASSWORD_TEST, $user->getPassword());
    }

    public function testUserPhone() {
        $user = new User("test", "test", "test", "test");

        $user->setPhone(self::USER_PHONE_TEST);

        $this->assertEquals(self::USER_PHONE_TEST, $user->getPhone());
    }

    public function testUserCreatedAt() {
        $user = new User("test", "test", "test", "test");

        $createdAt = new \DateTime();

        $user->setCreatedAt($createdAt);

        $this->assertEquals($createdAt, $user->getCreatedAt());
    }

    public function testUserUpdatedAt() {
        $user = new User("test", "test", "test", "test");

        $updatedAt = new \DateTime();

        $user->setUpdatedAt($updatedAt);

        $this->assertEquals($updatedAt, $user->getUpdatedAt());
    }
}
