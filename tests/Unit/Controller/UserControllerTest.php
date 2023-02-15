<?php

namespace App\Tests\Unit\Controller;

use App\Common\ErrorMessage;
use App\Controller\UserController;
use App\Dto\UserDto;
use App\Dto\UserEditableDto;
use App\Exception\InvalidRequestException;
use App\Exception\UserNotFoundException;
use App\Service\UserService;
use App\Service\UserServiceInterface;
use App\Tests\Utils\ConstHelper;
use App\Tests\Utils\RequestHelper;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class UserControllerTest extends TestCase
{
    /**
     * @var Serializer
     */
    private Serializer $serializer;

    protected function setUp(): void
    {
        $encoders = array(new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());

        $this->serializer = new Serializer($normalizers, $encoders);
    }

    public function testListUsersSuccess(): void
    {
        $userDto = new UserDto(
            ConstHelper::USER_ID_TEST,
            ConstHelper::USER_NAME_TEST,
            ConstHelper::USER_EMAIL_TEST,
            ConstHelper::USER_PHONE_TEST,
        );

        $userService = $this->createMock(UserServiceInterface::class);
        $userService->expects(self::once())
            ->method('findAllUsers')
            ->willReturn([
                $userDto
            ]);
        $userController = new UserController($userService);

        $response = $userController->listUsers($this->serializer);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertEquals($this->serializer->serialize([$userDto], JsonEncoder::FORMAT), $response->getContent());
    }

    public function testSingleUserSuccess(): void
    {
        $userId = 1;
        $userDto = new UserDto(
            ConstHelper::USER_ID_TEST,
            ConstHelper::USER_NAME_TEST,
            ConstHelper::USER_EMAIL_TEST,
            ConstHelper::USER_PHONE_TEST,
        );

        $userService = $this->createMock(UserServiceInterface::class);
        $userService->expects(self::once())
            ->method('findUser')
            ->willReturn($userDto);
        $userController = new UserController($userService);

        $response = $userController->singleUser($userId, $this->serializer);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertEquals($this->serializer->serialize($userDto, JsonEncoder::FORMAT), $response->getContent());
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

    public function testFindUserByEmailSuccess(): void
    {
        $userDto = new UserDto(
            ConstHelper::USER_ID_TEST,
            ConstHelper::USER_NAME_TEST,
            ConstHelper::USER_EMAIL_TEST,
            ConstHelper::USER_PASSWORD_TEST,
        );

        $userService = $this->createMock(UserServiceInterface::class);
        $userService->expects(self::once())
            ->method('findUserByEmail')
            ->willReturn($userDto);
        $userController = new UserController($userService);

        $parameters = ["email" => ConstHelper::USER_EMAIL_TEST];
        $request = RequestHelper::createRequest("users/email", Request::METHOD_GET, "", $parameters);

        $response = $userController->findUserByEmail($request, $this->serializer);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertEquals($this->serializer->serialize($userDto, JsonEncoder::FORMAT), $response->getContent());
    }

    public function testFindUserByEmailEmptyEmail(): void
    {
        $userService = $this->createMock(UserServiceInterface::class);
        $userController = new UserController($userService);

        $request = RequestHelper::createRequest("users/email", Request::METHOD_GET, "");

        $response = $userController->findUserByEmail($request, $this->serializer);

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertEquals(UserController::EMPTY_EMAIL, $response->getContent());
    }

    public function testFindUserByEmailNotFound(): void
    {
        $userService = $this->createMock(UserServiceInterface::class);
        $userService->expects(self::once())
            ->method('findUserByEmail')
            ->willThrowException(new UserNotFoundException(ConstHelper::USER_EMAIL_TEST));
        $userController = new UserController($userService);

        $parameters = ["email" => ConstHelper::USER_EMAIL_TEST];
        $request = RequestHelper::createRequest("users/email", Request::METHOD_GET, "", $parameters);

        $response = $userController->findUserByEmail($request, $this->serializer);

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
        $userCreate = new UserEditableDto(
            ConstHelper::USER_NAME_TEST,
            ConstHelper::USER_EMAIL_TEST,
            ConstHelper::USER_PASSWORD_TEST,
            ConstHelper::USER_PHONE_TEST,
        );
        $userDto = new UserDto(
            ConstHelper::USER_ID_TEST,
            ConstHelper::USER_NAME_TEST,
            ConstHelper::USER_EMAIL_TEST,
            ConstHelper::USER_PHONE_TEST,
        );

        $userService = $this->createMock(UserServiceInterface::class);
        $userService->expects(self::once())
            ->method('createUser')
            ->willReturn($userDto);
        $userController = new UserController($userService);

        $content = $this->serializer->serialize($userCreate, JsonEncoder::FORMAT);
        $request = RequestHelper::createRequest("users", Request::METHOD_POST, $content);

        $response = $userController->createUser($request, $this->serializer);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertEquals($this->serializer->serialize($userDto, JsonEncoder::FORMAT), $response->getContent());
    }

    public function testCreateUserInvalidBody(): void
    {
        $userService = $this->createMock(UserServiceInterface::class);
        $userController = new UserController($userService);

        $request = Request::createFromGlobals();

        $response = $userController->createUser($request, $this->serializer);

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }

    public function testCreateUserInvalidRequestException(): void
    {
        $userCreate = new UserEditableDto(
            "",
            ConstHelper::USER_EMAIL_TEST,
            ConstHelper::USER_PASSWORD_TEST,
            ConstHelper::USER_PHONE_TEST,
        );
        $expectedException = new InvalidRequestException("request");

        $userService = $this->createMock(UserServiceInterface::class);
        $userService->expects(self::once())
            ->method('createUser')
            ->willThrowException($expectedException);
        $userController = new UserController($userService);

        $content = $this->serializer->serialize($userCreate, JsonEncoder::FORMAT);
        $request = RequestHelper::createRequest("users", Request::METHOD_POST, $content);

        $response = $userController->createUser($request, $this->serializer);

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertEquals(ErrorMessage::generate($expectedException, $this->serializer), $response->getContent());
    }

    public function testCreateUserRepositoryError(): void
    {
        $userCreate = new UserEditableDto(
            ConstHelper::USER_NAME_TEST,
            ConstHelper::USER_EMAIL_TEST,
            ConstHelper::USER_PASSWORD_TEST,
            ConstHelper::USER_PHONE_TEST,
        );

        $userService = $this->createMock(UserServiceInterface::class);
        $userService->expects(self::once())
            ->method('createUser')
            ->willThrowException(new \Exception());
        $userController = new UserController($userService);

        $content = $this->serializer->serialize($userCreate, JsonEncoder::FORMAT);
        $request = RequestHelper::createRequest("users", Request::METHOD_POST, $content);

        $response = $userController->createUser($request, $this->serializer);

        $this->assertEquals(Response::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
    }

    public function testUpdateUserSuccess(): void
    {
        $userCreate = new UserEditableDto(
            ConstHelper::USER_NAME_TEST,
            ConstHelper::USER_EMAIL_TEST,
            ConstHelper::USER_PASSWORD_TEST,
            ConstHelper::USER_PHONE_TEST,
        );
        $userDto = new UserDto(
            ConstHelper::USER_ID_TEST,
            ConstHelper::USER_NAME_TEST,
            ConstHelper::USER_EMAIL_TEST,
            ConstHelper::USER_PHONE_TEST,
        );
        $userService = $this->createMock(UserService::class);
        $userService->expects(self::once())
            ->method('updateUser')
            ->willReturn($userDto);
        $userController = new UserController($userService);

        $content = $this->serializer->serialize($userCreate, JsonEncoder::FORMAT);
        $request = RequestHelper::createRequest("users/1", Request::METHOD_PUT, $content);

        $response = $userController->updateUser(ConstHelper::USER_ID_TEST, $request, $this->serializer);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertEquals($this->serializer->serialize($userDto, JsonEncoder::FORMAT), $response->getContent());
    }

    public function testUpdateUserNotfound(): void
    {
        $userId = 1;
        $userCreate = new UserEditableDto(
            ConstHelper::USER_NAME_TEST,
            ConstHelper::USER_EMAIL_TEST,
            ConstHelper::USER_PASSWORD_TEST,
            ConstHelper::USER_PHONE_TEST,
        );

        $userService = $this->createMock(UserServiceInterface::class);
        $userService->expects(self::once())
            ->method('updateUser')
            ->willThrowException(new UserNotFoundException($userId));
        $userController = new UserController($userService);

        $content = $this->serializer->serialize($userCreate, JsonEncoder::FORMAT);
        $request = RequestHelper::createRequest("users/1", Request::METHOD_PUT, $content);

        $response = $userController->updateUser($userId, $request, $this->serializer);

        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public function testUpdateUserError(): void
    {
        $userId = 1;
        $userCreate = new UserEditableDto(
            ConstHelper::USER_NAME_TEST,
            ConstHelper::USER_EMAIL_TEST,
            ConstHelper::USER_PASSWORD_TEST,
            ConstHelper::USER_PHONE_TEST,
        );

        $userService = $this->createMock(UserServiceInterface::class);
        $userService->expects(self::once())
            ->method('updateUser')
            ->willThrowException(new \Exception());
        $userController = new UserController($userService);

        $content = $this->serializer->serialize($userCreate, JsonEncoder::FORMAT);
        $request = RequestHelper::createRequest("users/1", Request::METHOD_PUT, $content);

        $response = $userController->updateUser($userId, $request, $this->serializer);

        $this->assertEquals(Response::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}