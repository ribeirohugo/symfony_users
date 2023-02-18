<?php

namespace App\Tests\Unit\Mapper;

use App\Dto\UserEditableDto;
use App\Entity\Roles;
use App\Entity\User;
use App\Mapper\UserMapper;
use App\Tests\Utils\ConstHelper;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

class UserMapperTest extends TestCase{
    public function testEntityToDto() {
        $userUuid = Uuid::v4();
        $expectedRoles = [Roles::ROLE_USER, Roles::ROLE_ADMIN];
        $user = new User(
            ConstHelper::USER_NAME_TEST,
            ConstHelper::USER_EMAIL_TEST,
            ConstHelper::USER_PASSWORD_TEST,
            ConstHelper::USER_PHONE_TEST,
            $expectedRoles,
        );
        $user->setExternalId($userUuid);

        $dto = UserMapper::entityToDto($user);

        $this->assertEquals($user->getName(), $dto->getName());
        $this->assertEquals($user->getEmail(), $dto->getEmail());
        $this->assertEquals($user->getPhone(), $dto->getPhone());
        $this->assertEquals($user->getCreatedAt(), $dto->getCreatedAt());
        $this->assertEquals($user->getUpdatedAt(), $dto->getUpdatedAt());
        $this->assertEquals($expectedRoles, $dto->getRoles());
    }

    public function testEntityToDtoWithoutRoles() {
        $userUuid = Uuid::v4();
        $user = new User(
            ConstHelper::USER_NAME_TEST,
            ConstHelper::USER_EMAIL_TEST,
            ConstHelper::USER_PASSWORD_TEST,
            ConstHelper::USER_PHONE_TEST,
        );
        $user->setExternalId($userUuid);

        $dto = UserMapper::entityToDto($user);

        $this->assertEquals($user->getExternalId(), $dto->getId());
        $this->assertEquals($user->getName(), $dto->getName());
        $this->assertEquals($user->getEmail(), $dto->getEmail());
        $this->assertEquals($user->getPhone(), $dto->getPhone());
        $this->assertEquals($user->getCreatedAt(), $dto->getCreatedAt());
        $this->assertEquals($user->getUpdatedAt(), $dto->getUpdatedAt());
        $this->assertEquals([Roles::ROLE_USER], $dto->getRoles());
    }

    public function testEntityToDtoArray() {
        $userUuid = Uuid::v4();
        $expectedRoles = [Roles::ROLE_USER, Roles::ROLE_ADMIN];
        $user = new User(
            ConstHelper::USER_NAME_TEST,
            ConstHelper::USER_EMAIL_TEST,
            ConstHelper::USER_PASSWORD_TEST,
            ConstHelper::USER_PHONE_TEST,
            $expectedRoles,
        );
        $user->setExternalId($userUuid);
        $users[] = $user;

        $dto = UserMapper::entityToDtoArray($users);

        $this->assertNotEmpty($dto);
        $this->assertEquals($user->getExternalId(), $dto[0]->getId());
        $this->assertEquals($user->getName(), $dto[0]->getName());
        $this->assertEquals($user->getEmail(), $dto[0]->getEmail());
        $this->assertEquals($user->getPhone(), $dto[0]->getPhone());
        $this->assertEquals($user->getCreatedAt(), $dto[0]->getCreatedAt());
        $this->assertEquals($user->getUpdatedAt(), $dto[0]->getUpdatedAt());
        $this->assertEquals($user->getRoles(), $dto[0]->getRoles());
    }

    public function testEntityToDtoArrayWithoutRoles() {
        $userUuid = Uuid::v4();
        $user = new User(
            ConstHelper::USER_NAME_TEST,
            ConstHelper::USER_EMAIL_TEST,
            ConstHelper::USER_PASSWORD_TEST,
            ConstHelper::USER_PHONE_TEST,
        );
        $user->setExternalId($userUuid);
        $users[] = $user;

        $dto = UserMapper::entityToDtoArray($users);

        $this->assertNotEmpty($dto);
        $this->assertEquals($user->getExternalId(), $dto[0]->getId());
        $this->assertEquals($user->getName(), $dto[0]->getName());
        $this->assertEquals($user->getEmail(), $dto[0]->getEmail());
        $this->assertEquals($user->getPhone(), $dto[0]->getPhone());
        $this->assertEquals($user->getCreatedAt(), $dto[0]->getCreatedAt());
        $this->assertEquals($user->getUpdatedAt(), $dto[0]->getUpdatedAt());
        $this->assertEquals($user->getRoles(), $dto[0]->getRoles());
    }

    public function testUserEditableDtoToEntity() {
        $expectedRoles = [Roles::ROLE_USER, Roles::ROLE_ADMIN];
        $editableUser = new UserEditableDto(
            ConstHelper::USER_NAME_TEST,
            ConstHelper::USER_EMAIL_TEST,
            ConstHelper::USER_PASSWORD_TEST,
            ConstHelper::USER_PHONE_TEST,
            $expectedRoles,
        );

        $user = UserMapper::userEditableDtoToEntity($editableUser);

        $this->assertEquals($editableUser->getName(), $user->getName());
        $this->assertEquals($editableUser->getEmail(), $user->getEmail());
        $this->assertEquals($editableUser->getPassword(), $user->getPassword());
        $this->assertEquals($editableUser->getPhone(), $user->getPhone());
        $this->assertEquals($editableUser->getRoles(), $user->getRoles());
    }

    public function testUserEditableDtoToEntityWithoutRoles() {
        $editableUser = new UserEditableDto(
            ConstHelper::USER_NAME_TEST,
            ConstHelper::USER_EMAIL_TEST,
            ConstHelper::USER_PASSWORD_TEST,
            ConstHelper::USER_PHONE_TEST,
        );

        $user = UserMapper::userEditableDtoToEntity($editableUser);

        $this->assertEquals($editableUser->getName(), $user->getName());
        $this->assertEquals($editableUser->getEmail(), $user->getEmail());
        $this->assertEquals($editableUser->getPassword(), $user->getPassword());
        $this->assertEquals($editableUser->getPhone(), $user->getPhone());
        $this->assertEquals([Roles::ROLE_USER], $user->getRoles());
    }
}
