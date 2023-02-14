<?php

namespace App\Tests\Unit\Mapper;

use App\Entity\User;
use App\Mapper\UserMapper;
use App\Tests\Utils\ConstHelper;
use PHPUnit\Framework\TestCase;

class UserMapperTest extends TestCase{
    public function testEntityToDto() {
        $user = new User(
            ConstHelper::USER_NAME_TEST,
            ConstHelper::USER_EMAIL_TEST,
            ConstHelper::USER_PASSWORD_TEST,
            ConstHelper::USER_PHONE_TEST,
        );

        $dto = UserMapper::EntityToDto($user);

        $this->assertEquals($user->getName(), $dto->getName());
        $this->assertEquals($user->getEmail(), $dto->getEmail());
        $this->assertEquals($user->getPhone(), $dto->getPhone());
        $this->assertEquals($user->getCreatedAt(), $dto->getCreatedAt());
        $this->assertEquals($user->getUpdatedAt(), $dto->getUpdatedAt());
    }

    public function testEntityToDtoArray() {
        $user = new User(
            ConstHelper::USER_NAME_TEST,
            ConstHelper::USER_EMAIL_TEST,
            ConstHelper::USER_PASSWORD_TEST,
            ConstHelper::USER_PHONE_TEST,
        );
        $users[] = $user;

        $dto = UserMapper::EntityToDtoArray($users);

        $this->assertNotEmpty($dto);
        $this->assertEquals($user->getName(), $dto[0]->getName());
        $this->assertEquals($user->getEmail(), $dto[0]->getEmail());
        $this->assertEquals($user->getPhone(), $dto[0]->getPhone());
        $this->assertEquals($user->getCreatedAt(), $dto[0]->getCreatedAt());
        $this->assertEquals($user->getUpdatedAt(), $dto[0]->getUpdatedAt());
    }
}
