<?php

namespace App\Tests\Unit\Service;

use App\Dto\UserDto;
use App\Dto\UserEditableDto;
use App\Entity\User;
use App\Exception\InvalidRequestException;
use App\Exception\UserNotFoundException;
use App\Mapper\UserMapper;
use App\Repository\UserRepository;
use App\Service\UserService;
use App\Tests\Utils\ConstHelper;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class UserServiceTest extends TestCase
{
    public function testFindUserSuccess(): void
    {
        $user = new User(
            ConstHelper::USER_NAME_TEST,
            ConstHelper::USER_EMAIL_TEST,
            ConstHelper::USER_PASSWORD_TEST,
            ConstHelper::USER_PHONE_TEST,
        );
        $user->setId(ConstHelper::USER_ID_TEST);
        $userDto = UserMapper::entityToDto($user);

        $userRepository = $this->createMock(UserRepository::class);
        $userRepository->expects(self::once())
            ->method('find')
            ->willReturn($user);

        $userService = new UserService($userRepository);

        $response = $userService->findUser(ConstHelper::USER_ID_TEST);

        $this->assertEquals($userDto, $response);
    }

    public function testFindUserNotFound(): void
    {
        $userRepository = $this->createMock(UserRepository::class);
        $userRepository->expects(self::once())
            ->method('find')
            ->willReturn(null);

        $userService = new UserService($userRepository);

        $this->expectException(UserNotFoundException::class);

        $userService->findUser(ConstHelper::USER_ID_TEST);
    }

    public function testFindUserRepositoryError(): void
    {
        $userRepository = $this->createMock(UserRepository::class);
        $userRepository->expects(self::once())
            ->method('find')
            ->willThrowException(new \Exception());

        $userService = new UserService($userRepository);

        $this->expectException(\Exception::class);

        $userService->findUser(ConstHelper::USER_ID_TEST);
    }

    public function testFindAllUsersSuccess(): void
    {
        $user = new User(
            ConstHelper::USER_NAME_TEST,
            ConstHelper::USER_EMAIL_TEST,
            ConstHelper::USER_PASSWORD_TEST,
            ConstHelper::USER_PHONE_TEST,
        );
        $user->setId(ConstHelper::USER_ID_TEST);
        $userDto = UserMapper::entityToDto($user);

        $userRepository = $this->createMock(UserRepository::class);
        $userRepository->expects(self::once())
            ->method('findAll')
            ->willReturn([$user]);

        $userService = new UserService($userRepository);

        $response = $userService->findAllUsers();

        $this->assertEquals([$userDto], $response);
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
        $user = new User(
            ConstHelper::USER_NAME_TEST,
            ConstHelper::USER_EMAIL_TEST,
            ConstHelper::USER_PASSWORD_TEST,
            ConstHelper::USER_PHONE_TEST,
        );

        $userRepository = $this->createMock(UserRepository::class);
        $userRepository->expects(self::once())
            ->method('find')
            ->willReturn($user);
        $userRepository->expects(self::once())
            ->method('remove');

        $userService = new UserService($userRepository);
        $userService->removeUser(ConstHelper::USER_ID_TEST);
    }

    public function testRemoveUserRepositoryFindError(): void
    {
        $userId = 1;
        $user = new User(
            ConstHelper::USER_NAME_TEST,
            ConstHelper::USER_EMAIL_TEST,
            ConstHelper::USER_PASSWORD_TEST,
            ConstHelper::USER_PHONE_TEST,
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
            ConstHelper::USER_NAME_TEST,
            ConstHelper::USER_EMAIL_TEST,
            ConstHelper::USER_PASSWORD_TEST,
            ConstHelper::USER_PHONE_TEST,
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
            ConstHelper::USER_NAME_TEST,
            ConstHelper::USER_EMAIL_TEST,
            ConstHelper::USER_PASSWORD_TEST,
            ConstHelper::USER_PHONE_TEST,
        );
        $user = new User(
            ConstHelper::USER_NAME_TEST,
            ConstHelper::USER_EMAIL_TEST,
            ConstHelper::USER_PASSWORD_TEST,
            ConstHelper::USER_PHONE_TEST,
        );
        $user->setId(ConstHelper::USER_ID_TEST);
        $userDto = UserMapper::entityToDto($user);

        $userRepository = $this->createMock(UserRepository::class);
        $userRepository->expects(self::once())
            ->method('save')
            ->willReturn($user);

        $userService = new UserService($userRepository);

        $response = $userService->createUser($userCreate);

        $this->assertEquals($userDto, $response);
    }

    public function testCreateUserEmptyName(): void
    {
        $userCreate = new UserEditableDto(
            "",
            ConstHelper::USER_EMAIL_TEST,
            ConstHelper::USER_PASSWORD_TEST,
            ConstHelper::USER_PHONE_TEST,
        );

        $userRepository = $this->createMock(UserRepository::class);
        $userService = new UserService($userRepository);

        $this->expectException(InvalidRequestException::class);

        $userService->createUser($userCreate);
    }

    public function testCreateUserEmptyEmail(): void
    {
        $userCreate = new UserEditableDto(
            ConstHelper::USER_NAME_TEST,
            "",
            ConstHelper::USER_PASSWORD_TEST,
            ConstHelper::USER_PHONE_TEST,
        );

        $userRepository = $this->createMock(UserRepository::class);
        $userService = new UserService($userRepository);

        $this->expectException(InvalidRequestException::class);

        $userService->createUser($userCreate);
    }

    public function testCreateUserEmptyPassword(): void
    {
        $userCreate = new UserEditableDto(
            ConstHelper::USER_NAME_TEST,
            ConstHelper::USER_EMAIL_TEST,
            "",
            ConstHelper::USER_PHONE_TEST,
        );

        $userRepository = $this->createMock(UserRepository::class);
        $userService = new UserService($userRepository);

        $this->expectException(InvalidRequestException::class);

        $userService->createUser($userCreate);
    }

    public function testCreateUserRepositorySaveError(): void
    {
        $userCreate = new UserEditableDto(
            ConstHelper::USER_NAME_TEST,
            ConstHelper::USER_EMAIL_TEST,
            ConstHelper::USER_PASSWORD_TEST,
            ConstHelper::USER_PHONE_TEST,
        );
        $user = new User(
            ConstHelper::USER_NAME_TEST,
            ConstHelper::USER_EMAIL_TEST,
            ConstHelper::USER_PASSWORD_TEST,
            ConstHelper::USER_PHONE_TEST,
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
            ConstHelper::USER_NAME_TEST,
            ConstHelper::USER_EMAIL_TEST,
            ConstHelper::USER_PASSWORD_TEST,
            ConstHelper::USER_PHONE_TEST,
        );
        $user = new User(
            ConstHelper::USER_NAME_TEST,
            ConstHelper::USER_EMAIL_TEST,
            ConstHelper::USER_PASSWORD_TEST,
            ConstHelper::USER_PHONE_TEST,
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
            ConstHelper::USER_EMAIL_TEST,
            ConstHelper::USER_PASSWORD_TEST,
            ConstHelper::USER_PHONE_TEST,
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
            ConstHelper::USER_NAME_TEST,
            "",
            ConstHelper::USER_PASSWORD_TEST,
            ConstHelper::USER_PHONE_TEST,
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
            ConstHelper::USER_NAME_TEST,
            ConstHelper::USER_EMAIL_TEST,
            ConstHelper::USER_PASSWORD_TEST,
            ConstHelper::USER_PHONE_TEST,
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
            ConstHelper::USER_NAME_TEST,
            ConstHelper::USER_EMAIL_TEST,
            ConstHelper::USER_PASSWORD_TEST,
            ConstHelper::USER_PHONE_TEST,
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
            ConstHelper::USER_NAME_TEST,
            ConstHelper::USER_EMAIL_TEST,
            ConstHelper::USER_PASSWORD_TEST,
            ConstHelper::USER_PHONE_TEST,
        );
        $user = new User(
            ConstHelper::USER_NAME_TEST,
            ConstHelper::USER_EMAIL_TEST,
            ConstHelper::USER_PASSWORD_TEST,
            ConstHelper::USER_PHONE_TEST,
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
