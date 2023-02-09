<?php

namespace App\Tests\Unit\Controller;

use App\Controller\UserController;
use App\Entity\User;
use App\Entity\UserCreate;
use App\Repository\UserRepositoryInterface;
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

    private UserController $userController;

    private Serializer $serializer;

    protected function setUp(): void
    {
        $this->userController = new UserController();

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

        $userRepository = $this->createMock(UserRepositoryInterface::class);
        $userRepository->expects(self::once())
            ->method('findAll')
            ->willReturn([
                $user
            ]);

        $response = $this->userController->listUsers($userRepository, $this->serializer);

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

        $userRepository = $this->createMock(UserRepositoryInterface::class);
        $userRepository->expects(self::once())
            ->method('find')
            ->willReturn($user);

        $response = $this->userController->singleUser($userId, $userRepository, $this->serializer);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertEquals($this->serializer->serialize($user, JsonEncoder::FORMAT), $response->getContent());
    }

    public function testSingleUserNotFound(): void
    {
        $userId = 1;

        $userRepository = $this->createMock(UserRepositoryInterface::class);
        $userRepository->expects(self::once())
            ->method('find')
            ->willReturn(null);

        $response = $this->userController->singleUser($userId, $userRepository, $this->serializer);

        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
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

        $userRepository = $this->createMock(UserRepositoryInterface::class);
        $userRepository->expects(self::once())
            ->method('find')
            ->willReturn($user);
        $userRepository->expects(self::once())
            ->method('remove');

        $response = $this->userController->removeUser($userId, $userRepository);

        $this->assertEquals(Response::HTTP_NO_CONTENT, $response->getStatusCode());
    }

    public function testRemoveUserNotFound(): void
    {
        $userId = 1;

        $userRepository = $this->createMock(UserRepositoryInterface::class);
        $userRepository->expects(self::once())
            ->method('find')
            ->willReturn(null);

        $response = $this->userController->removeUser($userId, $userRepository);

        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
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

        $userRepository = $this->createMock(UserRepositoryInterface::class);
        $userRepository->expects(self::once())
            ->method('save')
            ->willReturn($user);

        $content = $this->serializer->serialize($userCreate, JsonEncoder::FORMAT);
        $request = $this->createRequest("users", Request::METHOD_POST, $content);

        $response = $this->userController->createUser($request, $userRepository, $this->serializer);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertEquals($this->serializer->serialize($user, JsonEncoder::FORMAT), $response->getContent());
    }

    public function testCreateUserInvalidBody(): void
    {
        $user = new User(
            self::USER_NAME_TEST,
            self::USER_EMAIL_TEST,
            self::USER_PASSWORD_TEST,
            self::USER_PHONE_TEST,
        );

        $userRepository = $this->createMock(UserRepositoryInterface::class);

        $request = Request::createFromGlobals();

        $response = $this->userController->createUser($request, $userRepository, $this->serializer);

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

        $userRepository = $this->createMock(UserRepositoryInterface::class);
        $userRepository->expects(self::once())
            ->method('save')
            ->willThrowException(new \Exception());


        $content = $this->serializer->serialize($userCreate, JsonEncoder::FORMAT);
        $request = $this->createRequest("users", Request::METHOD_POST, $content);

        $response = $this->userController->createUser($request, $userRepository, $this->serializer);

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

        $userRepository = $this->createMock(UserRepositoryInterface::class);
        $userRepository->expects(self::once())
            ->method('find')
            ->willReturn($user);
        $userRepository->expects(self::once())
            ->method('save')
            ->willReturn($user);

        $content = $this->serializer->serialize($userCreate, JsonEncoder::FORMAT);
        $request = $this->createRequest("users/1", Request::METHOD_PUT, $content);

        $response = $this->userController->updateUser($userId, $request, $userRepository, $this->serializer);

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

        $userRepository = $this->createMock(UserRepositoryInterface::class);
        $userRepository->expects(self::once())
            ->method('find');

        $content = $this->serializer->serialize($userCreate, JsonEncoder::FORMAT);
        $request = $this->createRequest("users/1", Request::METHOD_PUT, $content);

        $response = $this->userController->updateUser($userId, $request, $userRepository, $this->serializer);

        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
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

        $userRepository = $this->createMock(UserRepositoryInterface::class);
        $userRepository->expects(self::once())
            ->method('find')
            ->willThrowException(new \Exception());

        $content = $this->serializer->serialize($userCreate, JsonEncoder::FORMAT);
        $request = $this->createRequest("users/1", Request::METHOD_PUT, $content);

        $response = $this->userController->updateUser($userId, $request, $userRepository, $this->serializer);

        $this->assertEquals(Response::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
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

        $userRepository = $this->createMock(UserRepositoryInterface::class);
        $userRepository->expects(self::once())
            ->method('find')
            ->willReturn($user);
        $userRepository->expects(self::once())
            ->method('save')
            ->willThrowException(new \Exception());

        $content = $this->serializer->serialize($userCreate, JsonEncoder::FORMAT);
        $request = $this->createRequest("users/1", Request::METHOD_PUT, $content);

        $response = $this->userController->updateUser($userId, $request, $userRepository, $this->serializer);

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