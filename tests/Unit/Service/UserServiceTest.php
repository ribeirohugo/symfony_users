<?php

namespace App\Tests\Unit\Service;

use App\Dto\UserEditableDto;
use App\Entity\User;
use App\Exception\InvalidRequestException;
use App\Exception\UserNotFoundException;
use App\Repository\UserRepository;
use App\Service\UserService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class UserServiceTest extends TestCase
{
    const USER_NAME_TEST = "name";
    const USER_EMAIL_TEST = "email@domain.com";
    const USER_PASSWORD_TEST = "password";
    const USER_PHONE_TEST = "910123123";

    public function testFindUserSuccess(): void
    {
        $userId = 1;
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

        $userService = new UserService($userRepository);

        $response = $userService->findUser($userId);

        $this->assertEquals($user, $response);
    }

    public function testFindUserNotFound(): void
    {
        $userId = 1;

        $userRepository = $this->createMock(UserRepository::class);
        $userRepository->expects(self::once())
            ->method('find')
            ->willReturn(null);

        $userService = new UserService($userRepository);

        $this->expectException(UserNotFoundException::class);

        $userService->findUser($userId);
    }

    public function testFindUserRepositoryError(): void
    {
        $userId = 1;

        $userRepository = $this->createMock(UserRepository::class);
        $userRepository->expects(self::once())
            ->method('find')
            ->willThrowException(new \Exception());

        $userService = new UserService($userRepository);

        $this->expectException(\Exception::class);

        $userService->findUser($userId);
    }

    public function testFindAllUsersSuccess(): void
    {
        $user = new User(
            self::USER_NAME_TEST,
            self::USER_EMAIL_TEST,
            self::USER_PASSWORD_TEST,
            self::USER_PHONE_TEST,
        );

        $userRepository = $this->createMock(UserRepository::class);
        $userRepository->expects(self::once())
            ->method('findAll')
            ->willReturn([$user]);

        $userService = new UserService($userRepository);

        $response = $userService->findAllUsers();

        $this->assertEquals([$user], $response);
    }

    public function testFindAllUsersRepositoryError(): void
    {
        $userRepository = $this->createMock(UserRepository::class);
        $userRepository->expects(self::once())
            ->method('findAll')
            ->willThrowException(new \Exception());

        $userService = new UserService($userRepository);

        $this->expectException(\Exception::class);

        $userService->findAllUsers();
    }

    public function testRemoveUserSuccess(): void
    {
        $userId = 1;
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
            ->method('remove');

        $userService = new UserService($userRepository);
        $userService->removeUser($userId);
    }

    public function testRemoveUserRepositoryFindError(): void
    {
        $userId = 1;
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
            ->method('remove')
            ->willThrowException(new \Exception());

        $userService = new UserService($userRepository);

        $this->expectException(\Exception::class);

        $userService->removeUser($userId);
    }

    public function testRemoveUserRepositoryRemoveError(): void
    {
        $userId = 1;
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
            ->method('remove')
            ->willThrowException(new \Exception());

        $userService = new UserService($userRepository);

        $this->expectException(\Exception::class);

        $userService->removeUser($userId);
    }

    public function testCreateUserSuccess(): void
    {
        $userCreate = new UserEditableDto(
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
            ->method('save')
            ->willReturn($user);

        $userService = new UserService($userRepository);

        $response = $userService->createUser($userCreate);

        $this->assertEquals($user, $response);
    }

    public function testCreateUserEmptyName(): void
    {
        $userCreate = new UserEditableDto(
            "",
            self::USER_EMAIL_TEST,
            self::USER_PASSWORD_TEST,
            self::USER_PHONE_TEST,
        );

        $userRepository = $this->createMock(UserRepository::class);
        $userService = new UserService($userRepository);

        $this->expectException(InvalidRequestException::class);

        $userService->createUser($userCreate);
    }

    public function testCreateUserEmptyEmail(): void
    {
        $userCreate = new UserEditableDto(
            self::USER_NAME_TEST,
            "",
            self::USER_PASSWORD_TEST,
            self::USER_PHONE_TEST,
        );

        $userRepository = $this->createMock(UserRepository::class);
        $userService = new UserService($userRepository);

        $this->expectException(InvalidRequestException::class);

        $userService->createUser($userCreate);
    }

    public function testCreateUserEmptyPassword(): void
    {
        $userCreate = new UserEditableDto(
            self::USER_NAME_TEST,
            self::USER_EMAIL_TEST,
            "",
            self::USER_PHONE_TEST,
        );

        $userRepository = $this->createMock(UserRepository::class);
        $userService = new UserService($userRepository);

        $this->expectException(InvalidRequestException::class);

        $userService->createUser($userCreate);
    }

    public function testCreateUserRepositorySaveError(): void
    {
        $userCreate = new UserEditableDto(
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
            ->method('save')
            ->willThrowException(new \Exception());

        $userService = new UserService($userRepository);

        $this->expectException(\Exception::class);

        $userService->createUser($userCreate);
    }

    public function testUpdateUserSuccess(): void
    {
        $userId = 1;
        $userCreate = new UserEditableDto(
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

    public function testUpdateUserEmptyName(): void
    {
        $userId = 1;
        $userCreate = new UserEditableDto(
            "",
            self::USER_EMAIL_TEST,
            self::USER_PASSWORD_TEST,
            self::USER_PHONE_TEST,
        );

        $userRepository = $this->createMock(UserRepository::class);
        $userService = new UserService($userRepository);

        $this->expectException(InvalidRequestException::class);

        $userService->updateUser($userId, $userCreate);
    }

    public function testUpdateUserEmptyEmail(): void
    {
        $userId = 1;
        $userCreate = new UserEditableDto(
            self::USER_NAME_TEST,
            "",
            self::USER_PASSWORD_TEST,
            self::USER_PHONE_TEST,
        );

        $userRepository = $this->createMock(UserRepository::class);
        $userService = new UserService($userRepository);

        $this->expectException(InvalidRequestException::class);

        $userService->updateUser($userId, $userCreate);
    }

    public function testUpdateUserNotFound(): void
    {
        $userId = 1;
        $userCreate = new UserEditableDto(
            self::USER_NAME_TEST,
            self::USER_EMAIL_TEST,
            self::USER_PASSWORD_TEST,
            self::USER_PHONE_TEST,
        );

        $userRepository = $this->createMock(UserRepository::class);
        $userRepository->expects(self::once())
            ->method('find')
            ->willReturn(null);

        $userService = new UserService($userRepository);

        $this->expectException(\Exception::class);

        $userService->updateUser($userId, $userCreate);
    }

    public function testUpdateUserRepositoryFindError(): void
    {
        $userId = 1;
        $userCreate = new UserEditableDto(
            self::USER_NAME_TEST,
            self::USER_EMAIL_TEST,
            self::USER_PASSWORD_TEST,
            self::USER_PHONE_TEST,
        );

        $userRepository = $this->createMock(UserRepository::class);
        $userRepository->expects(self::once())
            ->method('find')
            ->willThrowException(new \Exception());

        $userService = new UserService($userRepository);

        $this->expectException(\Exception::class);

        $userService->updateUser($userId, $userCreate);
    }

    public function testUpdateUserRepositorySaveError(): void
    {
        $userId = 1;
        $userCreate = new UserEditableDto(
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
            ->willThrowException(new \Exception());

        $userService = new UserService($userRepository);

        $this->expectException(\Exception::class);

        $userService->updateUser($userId, $userCreate);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    protected function createRequest(string $uri, string $method, string $content) {
        return Request::create($uri, $method, [], [], [], [], $content);
    }
}
