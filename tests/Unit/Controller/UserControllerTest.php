<?php

namespace App\Tests\Unit\Controller;

use App\Controller\UserController;
use App\Entity\User;
use App\Entity\UserCreate;
use App\Exception\UserNotFoundException;
use App\Service\UserService;
use App\Service\UserServiceInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class UserControllerTest extends TestCase
{
    const USER_NAME_TEST = "name";
    const USER_EMAIL_TEST = "email@domain.com";
    const USER_PASSWORD_TEST = "password";
    const USER_PHONE_TEST = "910123123";

    private Serializer $serializer;

    protected function setUp(): void
    {
        $encoders = array(new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());

        $this->serializer = new Serializer($normalizers, $encoders);
    }

    public function testListUsersSuccess(): void
    {
        $user = new User(
            self::USER_NAME_TEST,
            self::USER_EMAIL_TEST,
            self::USER_PASSWORD_TEST,
            self::USER_PHONE_TEST,
        );

        $userService = $this->createMock(UserServiceInterface::class);
        $userService->expects(self::once())
            ->method('findAllUsers')
            ->willReturn([
                $user
            ]);
        $userController = new UserController($userService);

        $response = $userController->listUsers($this->serializer);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertEquals($this->serializer->serialize([$user], JsonEncoder::FORMAT), $response->getContent());
    }

    public function testSingleUserSuccess(): void
    {
        $userId = 1;
        $user = new User(
            self::USER_NAME_TEST,
            self::USER_EMAIL_TEST,
            self::USER_PASSWORD_TEST,
            self::USER_PHONE_TEST,
        );

        $userService = $this->createMock(UserServiceInterface::class);
        $userService->expects(self::once())
            ->method('findUser')
            ->willReturn($user);
        $userController = new UserController($userService);

        $response = $userController->singleUser($userId, $this->serializer);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertEquals($this->serializer->serialize($user, JsonEncoder::FORMAT), $response->getContent());
    }

    public function testSingleUserNotFound(): void
    {
        $userId = 1;

        $userService = $this->createMock(UserServiceInterface::class);
        $userService->expects(self::once())
            ->method('findUser')
            ->willThrowException(new UserNotFoundException($userId));
        $userController = new UserController($userService);

        $response = $userController->singleUser($userId, $this->serializer);

        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public function testRemoveUserSuccess(): void
    {
        $userId = 1;

        $userService = $this->createMock(UserServiceInterface::class);
        $userService->expects(self::once())
            ->method('removeUser');
        $userController = new UserController($userService);

        $response = $userController->removeUser($userId);

        $this->assertEquals(Response::HTTP_NO_CONTENT, $response->getStatusCode());
    }

    public function testRemoveUserNotFound(): void
    {
        $userId = 1;

        $userService = $this->createMock(UserServiceInterface::class);
        $userService->expects(self::once())
            ->method('removeUser')
            ->willThrowException(new UserNotFoundException($userId));
        $userController = new UserController($userService);

        $response = $userController->removeUser($userId);

        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public function testRemoveUserServiceError(): void
    {
        $userId = 1;

        $userService = $this->createMock(UserServiceInterface::class);
        $userService->expects(self::once())
            ->method('removeUser')
            ->willThrowException(new \Exception());
        $userController = new UserController($userService);

        $response = $userController->removeUser($userId);

        $this->assertEquals(Response::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
    }

    public function testCreateUserSuccess(): void
    {
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

        $userService = $this->createMock(UserServiceInterface::class);
        $userService->expects(self::once())
            ->method('createUser')
            ->willReturn($user);
        $userController = new UserController($userService);

        $content = $this->serializer->serialize($userCreate, JsonEncoder::FORMAT);
        $request = $this->createRequest("users", Request::METHOD_POST, $content);

        $response = $userController->createUser($request, $this->serializer);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertEquals($this->serializer->serialize($user, JsonEncoder::FORMAT), $response->getContent());
    }

    public function testCreateUserInvalidBody(): void
    {
        $userService = $this->createMock(UserServiceInterface::class);
        $userController = new UserController($userService);

        $request = Request::createFromGlobals();

        $response = $userController->createUser($request, $this->serializer);

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }

    public function testCreateUserRepositoryError(): void
    {
        $userCreate = new UserCreate(
            self::USER_NAME_TEST,
            self::USER_EMAIL_TEST,
            self::USER_PASSWORD_TEST,
            self::USER_PHONE_TEST,
        );

        $userService = $this->createMock(UserServiceInterface::class);
        $userService->expects(self::once())
            ->method('createUser')
            ->willThrowException(new \Exception());
        $userController = new UserController($userService);

        $content = $this->serializer->serialize($userCreate, JsonEncoder::FORMAT);
        $request = $this->createRequest("users", Request::METHOD_POST, $content);

        $response = $userController->createUser($request, $this->serializer);

        $this->assertEquals(Response::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
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

        $userService = $this->createMock(UserService::class);
        $userService->expects(self::once())
            ->method('updateUser')
            ->willReturn($user);
        $userController = new UserController($userService);

        $content = $this->serializer->serialize($userCreate, JsonEncoder::FORMAT);
        $request = $this->createRequest("users/1", Request::METHOD_PUT, $content);

        $response = $userController->updateUser($userId, $request, $this->serializer);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertEquals($this->serializer->serialize($user, JsonEncoder::FORMAT), $response->getContent());
    }

    public function testUpdateUserNotfound(): void
    {
        $userId = 1;
        $userCreate = new UserCreate(
            self::USER_NAME_TEST,
            self::USER_EMAIL_TEST,
            self::USER_PASSWORD_TEST,
            self::USER_PHONE_TEST,
        );

        $userService = $this->createMock(UserServiceInterface::class);
        $userService->expects(self::once())
            ->method('updateUser')
            ->willThrowException(new UserNotFoundException($userId));
        $userController = new UserController($userService);

        $content = $this->serializer->serialize($userCreate, JsonEncoder::FORMAT);
        $request = $this->createRequest("users/1", Request::METHOD_PUT, $content);

        $response = $userController->updateUser($userId, $request, $this->serializer);

        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public function testUpdateUserError(): void
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

        $userService = $this->createMock(UserServiceInterface::class);
        $userService->expects(self::once())
            ->method('updateUser')
            ->willThrowException(new \Exception());
        $userController = new UserController($userService);

        $content = $this->serializer->serialize($userCreate, JsonEncoder::FORMAT);
        $request = $this->createRequest("users/1", Request::METHOD_PUT, $content);

        $response = $userController->updateUser($userId, $request, $this->serializer);

        $this->assertEquals(Response::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    protected function createRequest(string $uri, string $method, string $content) {
        return Request::create($uri, $method, [], [], [], [], $content);
    }
}