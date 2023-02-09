<?php

namespace App\Tests\Unit\Service;

use App\Entity\User;
use App\Entity\UserCreate;
use App\Exception\UserNotFoundException;
use App\Repository\UserRepository;
use App\Service\UserService;
use App\Service\UserServiceInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Encoder\JsonEncoder;

class UserServiceTest extends TestCase
{
    const USER_NAME_TEST = "name";
    const USER_EMAIL_TEST = "email@domain.com";
    const USER_PASSWORD_TEST = "password";
    const USER_PHONE_TEST = "910123123";

    protected function setUp(): void
    {

    }

    public function testUpdateUserSuccess(): void
    {
        $userId = 1;
        $userCreate = new UserCreate(
            self::USER_NAME_TEST,
            self::USER_EMAIL_TEST,
            self::USER_PASSWORD_TEST,
            self::USER_PHONE_TEST,
        );
        $user = new User(
            self::USER_NAME_TEST,
            self::USER_EMAIL_TEST,
            self::USER_PASSWORD_TEST,
            self::USER_PHONE_TEST,
        );

        $userRepository = $this->createMock(UserRepository::class);
        $userRepository->expects(self::once())
            ->method('find')
            ->willReturn($user);
        $userRepository->expects(self::once())
            ->method('save')
            ->willReturn($user);

        $userService = new UserService($userRepository);

        $response = $userService->updateUser($userId, $userCreate);

        $this->assertEquals($user, $response);
    }

    public function testUpdateUserNotFound(): void
    {
        $userId = 1;
        $userCreate = new UserCreate(
            self::USER_NAME_TEST,
            self::USER_EMAIL_TEST,
            self::USER_PASSWORD_TEST,
            self::USER_PHONE_TEST,
        );
        $expectedException = new UserNotFoundException($userId);

        $userRepository = $this->createMock(UserRepository::class);
        $userRepository->expects(self::once())
            ->method('find')
            ->willReturn(null);

        $userService = new UserService($userRepository);

        try {
            $userService->updateUser($userId, $userCreate);
        } catch(\Exception $e) {
            $this->assertEquals($expectedException, $e);
        }
    }

    public function testUpdateUserRepositoryFindError(): void
    {
        $userId = 1;
        $userCreate = new UserCreate(
            self::USER_NAME_TEST,
            self::USER_EMAIL_TEST,
            self::USER_PASSWORD_TEST,
            self::USER_PHONE_TEST,
        );
        $user = new User(
            self::USER_NAME_TEST,
            self::USER_EMAIL_TEST,
            self::USER_PASSWORD_TEST,
            self::USER_PHONE_TEST,
        );

        $expectedException = new \Exception();

        $userRepository = $this->createMock(UserRepository::class);
        $userRepository->expects(self::once())
            ->method('find')
            ->willThrowException($expectedException);

        $userService = new UserService($userRepository);

        try {
            $userService->updateUser($userId, $userCreate);
        } catch(\Exception $e) {
            $this->assertEquals($expectedException, $e);
        }
    }

    public function testUpdateUserRepositorySaveError(): void
    {
        $userId = 1;
        $userCreate = new UserCreate(
            self::USER_NAME_TEST,
            self::USER_EMAIL_TEST,
            self::USER_PASSWORD_TEST,
            self::USER_PHONE_TEST,
        );
        $user = new User(
            self::USER_NAME_TEST,
            self::USER_EMAIL_TEST,
            self::USER_PASSWORD_TEST,
            self::USER_PHONE_TEST,
        );

        $expectedException = new \Exception();

        $userRepository = $this->createMock(UserRepository::class);
        $userRepository->expects(self::once())
            ->method('find')
            ->willReturn($user);
        $userRepository->expects(self::once())
            ->method('save')
            ->willThrowException($expectedException);

        $userService = new UserService($userRepository);

        try {
            $userService->updateUser($userId, $userCreate);
        } catch(\Exception $e) {
            $this->assertEquals($expectedException, $e);
        }
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    protected function createRequest(string $uri, string $method, string $content) {
        return Request::create($uri, $method, [], [], [], [], $content);
    }
}
