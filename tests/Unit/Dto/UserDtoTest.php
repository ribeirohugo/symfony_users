<?php

namespace App\Tests\Unit\Dto;

use App\DTO\UserDTO;
use App\Entity\Roles;
use App\Tests\Utils\ConstHelper;
use PHPUnit\Framework\TestCase;

class UserDtoTest extends TestCase{
    public function testUserDtoConstruct() {
        $timestamp = new \DateTime();
        $expectedRoles = [Roles::ROLE_USER, Roles::ROLE_ADMIN];

        $user = new UserDto(
            ConstHelper::USER_ID_TEST,
            ConstHelper::USER_NAME_TEST,
            ConstHelper::USER_EMAIL_TEST,
            ConstHelper::USER_PHONE_TEST,
            $timestamp,
            $timestamp,
        );
        $user->setId(ConstHelper::USER_ID_TEST);

        $this->assertIsObject($user);
        $this->assertEquals(ConstHelper::USER_ID_TEST, $user->getId());
        $this->assertEquals(ConstHelper::USER_NAME_TEST, $user->getName());
        $this->assertEquals(ConstHelper::USER_EMAIL_TEST, $user->getEmail());
        $this->assertEquals(ConstHelper::USER_PHONE_TEST, $user->getPhone());
        $this->assertEquals($timestamp, $user->getCreatedAt());
        $this->assertEquals($timestamp, $user->getUpdatedAt());
        $this->assertEquals($expectedRoles, $user->getRoles());
    }

    public function testUserDtoId() {
        $user = new UserDto(
            ConstHelper::USER_ID_TEST,
            ConstHelper::USER_NAME_TEST,
            ConstHelper::USER_EMAIL_TEST,
            ConstHelper::USER_PHONE_TEST,
        );

        $user->setId(ConstHelper::NEW_USER_ID_TEST);

        $this->assertIsObject($user);
        $this->assertEquals(ConstHelper::NEW_USER_ID_TEST, $user->getId());
    }

    public function testUserDtoName() {
        $user = new UserDto(
            ConstHelper::USER_ID_TEST,
            ConstHelper::USER_NAME_TEST,
            ConstHelper::USER_EMAIL_TEST,
            ConstHelper::USER_PHONE_TEST,
        );

        $user->setName(ConstHelper::NEW_USER_NAME_TEST);

        $this->assertIsObject($user);
        $this->assertEquals(ConstHelper::NEW_USER_NAME_TEST, $user->getName());
    }

    public function testUserDtoEmail() {
        $user = new UserDto(
            ConstHelper::USER_ID_TEST,
            ConstHelper::USER_NAME_TEST,
            ConstHelper::USER_EMAIL_TEST,
            ConstHelper::USER_PHONE_TEST,
        );

        $user->setEmail(ConstHelper::NEW_USER_EMAIL_TEST);

        $this->assertIsObject($user);
        $this->assertEquals(ConstHelper::NEW_USER_EMAIL_TEST, $user->getEmail());
    }

    public function testUserDtoPhone() {
        $user = new UserDto(
            ConstHelper::USER_ID_TEST,
            ConstHelper::USER_NAME_TEST,
            ConstHelper::USER_EMAIL_TEST,
            ConstHelper::USER_PHONE_TEST,
        );

        $user->setPhone(ConstHelper::NEW_USER_PHONE_TEST);

        $this->assertIsObject($user);
        $this->assertEquals(ConstHelper::NEW_USER_PHONE_TEST, $user->getPhone());
    }

    public function testUserDtoCreatedAt() {
        $user = new UserDto(
            ConstHelper::USER_ID_TEST,
            ConstHelper::USER_NAME_TEST,
            ConstHelper::USER_EMAIL_TEST,
            ConstHelper::USER_PHONE_TEST,
        );
        $timestamp = new \DateTime();

        $user->setCreatedAt($timestamp);

        $this->assertIsObject($user);
        $this->assertEquals($timestamp, $user->getCreatedAt());
    }

    public function testUserDtoUpdatedAt() {
        $user = new UserDto(
            ConstHelper::USER_ID_TEST,
            ConstHelper::USER_NAME_TEST,
            ConstHelper::USER_EMAIL_TEST,
            ConstHelper::USER_PHONE_TEST,
        );
        $timestamp = new \DateTime();

        $user->setUpdatedAt($timestamp);

        $this->assertIsObject($user);
        $this->assertEquals($timestamp, $user->getUpdatedAt());
    }
}
