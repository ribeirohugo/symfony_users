<?php

namespace App\Tests\Unit\Dto;

use App\DTO\UserDTO;
use App\Entity\Roles;
use App\Tests\Utils\ConstHelper;
use DateTime;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

class UserDtoTest extends TestCase{
    public function testUserDtoConstruct() {
        $userUuid = Uuid::v4();
        $timestamp = new DateTime();
        $expectedRoles = [Roles::ROLE_USER];

        $user = new UserDto(
            $userUuid,
            ConstHelper::USER_NAME_TEST,
            ConstHelper::USER_EMAIL_TEST,
            ConstHelper::USER_PHONE_TEST,
            $timestamp,
            $timestamp,
            $expectedRoles,
        );
        $user->setId($userUuid);

        $this->assertIsObject($user);
        $this->assertEquals($userUuid, $user->getId());
        $this->assertEquals(ConstHelper::USER_NAME_TEST, $user->getName());
        $this->assertEquals(ConstHelper::USER_EMAIL_TEST, $user->getEmail());
        $this->assertEquals(ConstHelper::USER_PHONE_TEST, $user->getPhone());
        $this->assertEquals($timestamp, $user->getCreatedAt());
        $this->assertEquals($timestamp, $user->getUpdatedAt());
        $this->assertEquals($expectedRoles, $user->getRoles());
    }

    public function testUserDtoId() {
        $userUuid = Uuid::v4();
        $newUserUuid = Uuid::v4();
        $user = new UserDto(
            $userUuid,
            ConstHelper::USER_NAME_TEST,
            ConstHelper::USER_EMAIL_TEST,
            ConstHelper::USER_PHONE_TEST,
        );

        $user->setId($newUserUuid);

        $this->assertIsObject($user);
        $this->assertEquals($newUserUuid, $user->getId());
    }

    public function testUserDtoName() {
        $userUuid = Uuid::v4();
        $user = new UserDto(
            $userUuid,
            ConstHelper::USER_NAME_TEST,
            ConstHelper::USER_EMAIL_TEST,
            ConstHelper::USER_PHONE_TEST,
        );

        $user->setName(ConstHelper::NEW_USER_NAME_TEST);

        $this->assertIsObject($user);
        $this->assertEquals(ConstHelper::NEW_USER_NAME_TEST, $user->getName());
    }

    public function testUserDtoEmail() {
        $userUuid = Uuid::v4();
        $user = new UserDto(
            $userUuid,
            ConstHelper::USER_NAME_TEST,
            ConstHelper::USER_EMAIL_TEST,
            ConstHelper::USER_PHONE_TEST,
        );

        $user->setEmail(ConstHelper::NEW_USER_EMAIL_TEST);

        $this->assertIsObject($user);
        $this->assertEquals(ConstHelper::NEW_USER_EMAIL_TEST, $user->getEmail());
    }

    public function testUserDtoPhone() {
        $userUuid = Uuid::v4();
        $user = new UserDto(
            $userUuid,
            ConstHelper::USER_NAME_TEST,
            ConstHelper::USER_EMAIL_TEST,
            ConstHelper::USER_PHONE_TEST,
        );

        $user->setPhone(ConstHelper::NEW_USER_PHONE_TEST);

        $this->assertIsObject($user);
        $this->assertEquals(ConstHelper::NEW_USER_PHONE_TEST, $user->getPhone());
    }

    public function testUserDtoCreatedAt() {
        $userUuid = Uuid::v4();
        $user = new UserDto(
            $userUuid,
            ConstHelper::USER_NAME_TEST,
            ConstHelper::USER_EMAIL_TEST,
            ConstHelper::USER_PHONE_TEST,
        );
        $timestamp = new DateTime();

        $user->setCreatedAt($timestamp);

        $this->assertIsObject($user);
        $this->assertEquals($timestamp, $user->getCreatedAt());
    }

    public function testUserDtoUpdatedAt() {
        $userUuid = Uuid::v4();
        $user = new UserDto(
            $userUuid,
            ConstHelper::USER_NAME_TEST,
            ConstHelper::USER_EMAIL_TEST,
            ConstHelper::USER_PHONE_TEST,
        );
        $timestamp = new DateTime();

        $user->setUpdatedAt($timestamp);

        $this->assertIsObject($user);
        $this->assertEquals($timestamp, $user->getUpdatedAt());
    }

    public function testUserDtoRoles() {
        $userUuid = Uuid::v4();
        $user = new UserDto(
            $userUuid,
            ConstHelper::USER_NAME_TEST,
            ConstHelper::USER_EMAIL_TEST,
            ConstHelper::USER_PHONE_TEST,
        );
        $expectedRoles = [Roles::ROLE_USER, Roles::ROLE_ADMIN];

        $user->setRoles($expectedRoles);

        $this->assertIsObject($user);
        $this->assertEquals($expectedRoles, $user->getRoles());
    }
}
