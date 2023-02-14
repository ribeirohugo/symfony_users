<?php

namespace App\Tests\Unit\Service;

use App\Common\Password;
use App\Dto\LoginDto;
use App\Entity\User;
use App\Exception\UserNotFoundException;
use App\Mapper\UserMapper;
use App\Repository\UserRepository;
use App\Service\AuthenticationService;
use App\Tests\Utils\ConstHelper;
use PHPUnit\Framework\TestCase;

class LoginServiceTest extends TestCase
{
    public function testLoginSuccess(): void
    {
        $loginDto = new LoginDto(
            ConstHelper::USER_EMAIL_TEST,
            ConstHelper::USER_PASSWORD_TEST,
        );
        $user = new User(
            ConstHelper::USER_NAME_TEST,
            ConstHelper::USER_EMAIL_TEST,
            "",
            ConstHelper::USER_PHONE_TEST,
        );
        $user->setId(ConstHelper::USER_ID_TEST);

        $hasher = Password::autoUserHasher();
        $user->setPassword($hasher->hashPassword($user, ConstHelper::USER_PASSWORD_TEST));

        $userDto = UserMapper::entityToDto($user);

        $userRepository = $this->createMock(UserRepository::class);
        $userRepository->expects(self::once())
            ->method('findOneBy')
            ->willReturn($user);

        $loginService = new AuthenticationService($userRepository);

        $response = $loginService->login($loginDto);

        $this->assertEquals($userDto, $response);
    }

    public function testLoginInvalidPassword(): void
    {
        $loginDto = new LoginDto(
            ConstHelper::USER_EMAIL_TEST,
            ConstHelper::USER_PASSWORD_TEST,
        );
        $user = new User(
            ConstHelper::USER_NAME_TEST,
            ConstHelper::USER_EMAIL_TEST,
            "",
            ConstHelper::USER_PHONE_TEST,
        );
        $user->setId(ConstHelper::USER_ID_TEST);

        $userRepository = $this->createMock(UserRepository::class);
        $userRepository->expects(self::once())
            ->method('findOneBy')
            ->willReturn($user);

        $loginService = new AuthenticationService($userRepository);

        $response = $loginService->login($loginDto);

        $this->assertFalse($response);
    }

    public function testLoginUserNotFound(): void
    {
        $loginDto = new LoginDto(
            ConstHelper::USER_EMAIL_TEST,
            ConstHelper::USER_PASSWORD_TEST,
        );

        $userRepository = $this->createMock(UserRepository::class);
        $userRepository->expects(self::once())
            ->method('findOneBy')
            ->willReturn(null);

        $loginService = new AuthenticationService($userRepository);

        $this->expectException(UserNotFoundException::class);

        $loginService->login($loginDto);
    }
}
