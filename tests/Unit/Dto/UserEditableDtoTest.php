<?php

namespace App\Tests\Unit\Dto;

use App\Dto\UserEditableDto;
use App\Entity\Roles;
use App\Tests\Utils\ConstHelper;
use PHPUnit\Framework\TestCase;

class UserEditableDtoTest extends TestCase{
    public function testUserEditableDtoConstruct() {
        $expectedRoles = [Roles::ROLE_USER, Roles::ROLE_ADMIN];

        $user = new UserEditableDto(
            ConstHelper::USER_NAME_TEST,
            ConstHelper::USER_EMAIL_TEST,
            ConstHelper::USER_PASSWORD_TEST,
            ConstHelper::USER_PHONE_TEST,
            $expectedRoles,
        );

        $this->assertIsObject($user);
        $this->assertEquals(ConstHelper::USER_NAME_TEST, $user->getName());
        $this->assertEquals(ConstHelper::USER_EMAIL_TEST, $user->getEmail());
        $this->assertEquals(ConstHelper::USER_PASSWORD_TEST, $user->getPassword());
        $this->assertEquals(ConstHelper::USER_PHONE_TEST, $user->getPhone());
        $this->assertEquals($expectedRoles, $user->getRoles());
    }

    public function testUserEditableDtoConstructWithoutRoles() {
        $user = new UserEditableDto(
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
        $this->assertEmpty($user->getRoles());
    }

    public function testUserEditableDtoName() {
        $user = new UserEditableDto(
            ConstHelper::USER_NAME_TEST,
            ConstHelper::USER_EMAIL_TEST,
            ConstHelper::USER_PASSWORD_TEST,
            ConstHelper::USER_PHONE_TEST,
        );

        $user->setName(ConstHelper::NEW_USER_NAME_TEST);

        $this->assertIsObject($user);
        $this->assertEquals(ConstHelper::NEW_USER_NAME_TEST, $user->getName());
    }

    public function testUserEditableDtoEmail() {
        $user = new UserEditableDto(
            ConstHelper::USER_NAME_TEST,
            ConstHelper::USER_EMAIL_TEST,
            ConstHelper::USER_PASSWORD_TEST,
            ConstHelper::USER_PHONE_TEST,
        );

        $user->setEmail(ConstHelper::NEW_USER_EMAIL_TEST);

        $this->assertIsObject($user);
        $this->assertEquals(ConstHelper::NEW_USER_EMAIL_TEST, $user->getEmail());
    }

    public function testUserEditableDtoPassword() {
        $user = new UserEditableDto(
            ConstHelper::USER_NAME_TEST,
            ConstHelper::USER_EMAIL_TEST,
            ConstHelper::USER_PASSWORD_TEST,
            ConstHelper::USER_PHONE_TEST,
        );

        $user->setPassword(ConstHelper::NEW_USER_PASSWORD_TEST);

        $this->assertIsObject($user);
        $this->assertEquals(ConstHelper::NEW_USER_PASSWORD_TEST, $user->getPassword());
    }

    public function testUserEditableDtoPhone() {
        $user = new UserEditableDto(
            ConstHelper::USER_NAME_TEST,
            ConstHelper::USER_EMAIL_TEST,
            ConstHelper::USER_PASSWORD_TEST,
            ConstHelper::USER_PHONE_TEST,
        );

        $user->setPhone(ConstHelper::NEW_USER_PHONE_TEST);

        $this->assertIsObject($user);
        $this->assertEquals(ConstHelper::NEW_USER_PHONE_TEST, $user->getPhone());
    }
}
