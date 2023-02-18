<?php

namespace App\Tests\Unit\Service;

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
use Symfony\Component\Uid\Uuid;

class UserServiceTest extends TestCase
{
    public function testFindUserSuccess(): void
    {
        $userUuid = Uuid::v4();
        $user = new User(
            ConstHelper::USER_NAME_TEST,
            ConstHelper::USER_EMAIL_TEST,
            ConstHelper::USER_PASSWORD_TEST,
            ConstHelper::USER_PHONE_TEST,
        );
        $user->setExternalId($userUuid);
        $userDto = UserMapper::entityToDto($user);

        $userRepository = $this->createMock(UserRepository::class);
        $userRepository->expects(self::once())
            ->method('findOneBy')
            ->willReturn($user);

        $userService = new UserService($userRepository);

        $response = $userService->findUser($userUuid);

        $this->assertEquals($userDto, $response);
    }

    public function testFindUserNotFound(): void
    {
        $userUuid = Uuid::v4();

        $userRepository = $this->createMock(UserRepository::class);
        $userRepository->expects(self::once())
            ->method('findOneBy')
            ->willReturn(null);

        $userService = new UserService($userRepository);

        $this->expectException(UserNotFoundException::class);

        $userService->findUser($userUuid);
    }

    public function testFindUserRepositoryError(): void
    {
        $userUuid = Uuid::v4();

        $userRepository = $this->createMock(UserRepository::class);
        $userRepository->expects(self::once())
            ->method('findOneBy')
            ->willThrowException(new \Exception());

        $userService = new UserService($userRepository);

        $this->expectException(\Exception::class);

        $userService->findUser($userUuid);
    }

    public function testFindAllUsersSuccess(): void
    {
        $userUuid = Uuid::v4();

        $user = new User(
            ConstHelper::USER_NAME_TEST,
            ConstHelper::USER_EMAIL_TEST,
            ConstHelper::USER_PASSWORD_TEST,
            ConstHelper::USER_PHONE_TEST,
        );
        $user->setExternalId($userUuid);
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
        $userUuid = Uuid::v4();
        $user = new User(
            ConstHelper::USER_NAME_TEST,
            ConstHelper::USER_EMAIL_TEST,
            ConstHelper::USER_PASSWORD_TEST,
            ConstHelper::USER_PHONE_TEST,
        );
        $user->setExternalId($userUuid);

        $userRepository = $this->createMock(UserRepository::class);
        $userRepository->expects(self::once())
            ->method('findOneBy')
            ->willReturn($user);
        $userRepository->expects(self::once())
            ->method('remove');

        $userService = new UserService($userRepository);
        $userService->removeUser($userUuid);
    }

    public function testRemoveUserRepositoryFindError(): void
    {
        $userUuid = Uuid::v4();
        $user = new User(
            ConstHelper::USER_NAME_TEST,
            ConstHelper::USER_EMAIL_TEST,
            ConstHelper::USER_PASSWORD_TEST,
            ConstHelper::USER_PHONE_TEST,
        );
        $user->setExternalId($userUuid);

        $userRepository = $this->createMock(UserRepository::class);
        $userRepository->expects(self::once())
            ->method('findOneBy')
            ->willReturn($user);
        $userRepository->expects(self::once())
            ->method('remove')
            ->willThrowException(new \Exception());

        $userService = new UserService($userRepository);

        $this->expectException(\Exception::class);

        $userService->removeUser($userUuid);
    }

    public function testRemoveUserRepositoryRemoveError(): void
    {
        $userUuid = Uuid::v4();
        $user = new User(
            ConstHelper::USER_NAME_TEST,
            ConstHelper::USER_EMAIL_TEST,
            ConstHelper::USER_PASSWORD_TEST,
            ConstHelper::USER_PHONE_TEST,
        );
        $user->setExternalId($userUuid);

        $userRepository = $this->createMock(UserRepository::class);
        $userRepository->expects(self::once())
            ->method('findOneBy')
            ->willReturn($user);
        $userRepository->expects(self::once())
            ->method('remove')
            ->willThrowException(new \Exception());

        $userService = new UserService($userRepository);

        $this->expectException(\Exception::class);

        $userService->removeUser($userUuid);
    }

    public function testCreateUserSuccess(): void
    {
        $userUuid = Uuid::v4();
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
        $user->setExternalId($userUuid);
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
        $externalUuid = Uuid::v4();
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
        $user->setExternalId($externalUuid);
        $userDto = UserMapper::entityToDto($user);

        $userRepository = $this->createMock(UserRepository::class);
        $userRepository->expects(self::once())
            ->method('findOneBy')
            ->willReturn($user);
        $userRepository->expects(self::once())
            ->method('save')
            ->willReturn($user);

        $userService = new UserService($userRepository);

        $response = $userService->updateUser($externalUuid, $userCreate);

        // Set current timestamp to update date
        $userDto->setUpdatedAt($response->getUpdatedAt());

        $this->assertEquals($userDto, $response);
    }

    public function testUpdateUserEmptyName(): void
    {
        $uuid = Uuid::v4();
        $userCreate = new UserEditableDto(
            "",
            ConstHelper::USER_EMAIL_TEST,
            ConstHelper::USER_PASSWORD_TEST,
            ConstHelper::USER_PHONE_TEST,
        );

        $userRepository = $this->createMock(UserRepository::class);
        $userService = new UserService($userRepository);

        $this->expectException(InvalidRequestException::class);

        $userService->updateUser($uuid, $userCreate);
    }

    public function testUpdateUserEmptyEmail(): void
    {
        $uuid = Uuid::v4();
        $userCreate = new UserEditableDto(
            ConstHelper::USER_NAME_TEST,
            "",
            ConstHelper::USER_PASSWORD_TEST,
            ConstHelper::USER_PHONE_TEST,
        );

        $userRepository = $this->createMock(UserRepository::class);
        $userService = new UserService($userRepository);

        $this->expectException(InvalidRequestException::class);

        $userService->updateUser($uuid, $userCreate);
    }

    public function testUpdateUserNotFound(): void
    {
        $userUuid = Uuid::v4();
        $userCreate = new UserEditableDto(
            ConstHelper::USER_NAME_TEST,
            ConstHelper::USER_EMAIL_TEST,
            ConstHelper::USER_PASSWORD_TEST,
            ConstHelper::USER_PHONE_TEST,
        );

        $userRepository = $this->createMock(UserRepository::class);
        $userRepository->expects(self::once())
            ->method('findOneBy')
            ->willReturn(null);

        $userService = new UserService($userRepository);

        $this->expectException(\Exception::class);

        $userService->updateUser($userUuid, $userCreate);
    }

    public function testUpdateUserRepositoryFindError(): void
    {
        $uuid = Uuid::v4();
        $userCreate = new UserEditableDto(
            ConstHelper::USER_NAME_TEST,
            ConstHelper::USER_EMAIL_TEST,
            ConstHelper::USER_PASSWORD_TEST,
            ConstHelper::USER_PHONE_TEST,
        );

        $userRepository = $this->createMock(UserRepository::class);
        $userRepository->expects(self::once())
            ->method('findOneBy')
            ->willThrowException(new \Exception());

        $userService = new UserService($userRepository);

        $this->expectException(\Exception::class);

        $userService->updateUser($uuid, $userCreate);
    }

    public function testUpdateUserRepositorySaveError(): void
    {
        $userUuid = Uuid::v4();
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
        $user->setExternalId($userUuid);

        $userRepository = $this->createMock(UserRepository::class);
        $userRepository->expects(self::once())
            ->method('findOneBy')
            ->willReturn($user);
        $userRepository->expects(self::once())
            ->method('save')
            ->willThrowException(new \Exception());

        $userService = new UserService($userRepository);

        $this->expectException(\Exception::class);

        $userService->updateUser($userUuid, $userCreate);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    protected function createRequest(string $uri, string $method, string $content) {
        return Request::create($uri, $method, [], [], [], [], $content);
    }
}
