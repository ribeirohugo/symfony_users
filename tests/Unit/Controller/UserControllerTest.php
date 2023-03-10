<?php

namespace App\Tests\Unit\Controller;

use App\Common\ErrorMessage;
use App\Controller\UserController;
use App\Dto\UserDto;
use App\Dto\UserEditableDto;
use App\Exception\EmailAlreadyInUseException;
use App\Exception\InvalidRequestException;
use App\Exception\UserNotFoundException;
use App\Service\UserService;
use App\Service\UserServiceInterface;
use App\Tests\Utils\ConstHelper;
use App\Tests\Utils\RequestHelper;
use Exception;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Uid\Uuid;

class UserControllerTest extends TestCase
{
    /**
     * @var Serializer
     */
    private Serializer $serializer;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var Uuid
     */
    private Uuid $userUuidTest;

    protected function setUp(): void
    {
        $encoders = array(new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());

        $this->serializer = new Serializer($normalizers, $encoders);

        $this->logger = new Logger("test");

        $this->userUuidTest = Uuid::v4();
    }

    public function testListUsersSuccess(): void
    {
        $userDto = new UserDto(
            $this->userUuidTest,
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
        $userController = new UserController($userService, $this->serializer, $this->logger);

        $response = $userController->listUsers();

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertEquals($this->serializer->serialize([$userDto], JsonEncoder::FORMAT), $response->getContent());
    }

    public function testSingleUserSuccess(): void
    {
        $userDto = new UserDto(
            $this->userUuidTest,
            ConstHelper::USER_NAME_TEST,
            ConstHelper::USER_EMAIL_TEST,
            ConstHelper::USER_PHONE_TEST,
        );

        $userService = $this->createMock(UserServiceInterface::class);
        $userService->expects(self::once())
            ->method('findUser')
            ->willReturn($userDto);
        $userController = new UserController($userService, $this->serializer, $this->logger);

        $response = $userController->singleUser($this->userUuidTest);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertEquals($this->serializer->serialize($userDto, JsonEncoder::FORMAT), $response->getContent());
    }

    public function testSingleUserNotFound(): void
    {
        $exception = new UserNotFoundException($this->userUuidTest);

        $userService = $this->createMock(UserServiceInterface::class);
        $userService->expects(self::once())
            ->method('findUser')
            ->willThrowException($exception);

        $userController = new UserController($userService, $this->serializer, $this->logger);

        $response = $userController->singleUser($this->userUuidTest);

        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        $this->assertEquals(ErrorMessage::generateJSON($exception, $this->serializer), $response->getContent());
    }

    public function testFindUserByEmailSuccess(): void
    {
        $userDto = new UserDto(
            $this->userUuidTest,
            ConstHelper::USER_NAME_TEST,
            ConstHelper::USER_EMAIL_TEST,
            ConstHelper::USER_PASSWORD_TEST,
        );

        $userService = $this->createMock(UserServiceInterface::class);
        $userService->expects(self::once())
            ->method('findUserByEmail')
            ->willReturn($userDto);
        $userController = new UserController($userService, $this->serializer, $this->logger);

        $parameters = ["email" => ConstHelper::USER_EMAIL_TEST];
        $request = RequestHelper::createRequest("users/email", Request::METHOD_GET, "", $parameters);

        $response = $userController->findUserByEmail($request);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertEquals($this->serializer->serialize($userDto, JsonEncoder::FORMAT), $response->getContent());
    }

    public function testFindUserByEmailEmptyEmail(): void
    {
        $userService = $this->createMock(UserServiceInterface::class);
        $userController = new UserController($userService, $this->serializer, $this->logger);

        $request = RequestHelper::createRequest("users/email", Request::METHOD_GET, "");

        $response = $userController->findUserByEmail($request);

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertEquals(ErrorMessage::emptyEmailJSON($this->serializer), $response->getContent());
    }

    public function testFindUserByEmailUserNotFound(): void
    {
        $exception = new UserNotFoundException($this->userUuidTest);

        $userService = $this->createMock(UserServiceInterface::class);
        $userService->expects(self::once())
            ->method('findUserByEmail')
            ->willThrowException($exception);
        $userController = new UserController($userService, $this->serializer, $this->logger);

        $parameters = ["email" => ConstHelper::USER_EMAIL_TEST];
        $request = RequestHelper::createRequest("users/email", Request::METHOD_GET, "", $parameters);

        $response = $userController->findUserByEmail($request);

        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        $this->assertEquals(ErrorMessage::generateJSON($exception, $this->serializer), $response->getContent());
    }

    public function testRemoveUserSuccess(): void
    {
        $userService = $this->createMock(UserServiceInterface::class);
        $userService->expects(self::once())
            ->method('removeUser');
        $userController = new UserController($userService, $this->serializer, $this->logger);

        $response = $userController->removeUser($this->userUuidTest);

        $this->assertEquals(Response::HTTP_NO_CONTENT, $response->getStatusCode());
    }

    public function testRemoveUserNotFound(): void
    {
        $exception = new UserNotFoundException($this->userUuidTest);

        $userService = $this->createMock(UserServiceInterface::class);
        $userService->expects(self::once())
            ->method('removeUser')
            ->willThrowException($exception);
        $userController = new UserController($userService, $this->serializer, $this->logger);

        $response = $userController->removeUser($this->userUuidTest);

        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        $this->assertEquals(ErrorMessage::generateJSON($exception, $this->serializer), $response->getContent());
    }

    public function testRemoveUserServiceError(): void
    {
        $userService = $this->createMock(UserServiceInterface::class);
        $userService->expects(self::once())
            ->method('removeUser')
            ->willThrowException(new Exception());
        $userController = new UserController($userService, $this->serializer, $this->logger);

        $response = $userController->removeUser($this->userUuidTest);

        $this->assertEquals(Response::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
        $this->assertEquals(ErrorMessage::internalError($this->serializer), $response->getContent());
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
            $this->userUuidTest,
            ConstHelper::USER_NAME_TEST,
            ConstHelper::USER_EMAIL_TEST,
            ConstHelper::USER_PHONE_TEST,
        );

        $userService = $this->createMock(UserServiceInterface::class);
        $userService->expects(self::once())
            ->method('createUser')
            ->willReturn($userDto);
        $userController = new UserController($userService, $this->serializer, $this->logger);

        $content = $this->serializer->serialize($userCreate, JsonEncoder::FORMAT);
        $request = RequestHelper::createRequest("users", Request::METHOD_POST, $content);

        $response = $userController->createUser($request);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertEquals($this->serializer->serialize($userDto, JsonEncoder::FORMAT), $response->getContent());
    }

    public function testCreateUserDuplicatedEmail(): void
    {
        $userCreate = new UserEditableDto(
            ConstHelper::USER_NAME_TEST,
            ConstHelper::USER_EMAIL_TEST,
            ConstHelper::USER_PASSWORD_TEST,
            ConstHelper::USER_PHONE_TEST,
        );
        $exception = new EmailAlreadyInUseException();

        $userService = $this->createMock(UserServiceInterface::class);
        $userService->expects(self::once())
            ->method('createUser')
            ->willThrowException($exception);
        $userController = new UserController($userService, $this->serializer, $this->logger);

        $content = $this->serializer->serialize($userCreate, JsonEncoder::FORMAT);
        $request = RequestHelper::createRequest("users", Request::METHOD_POST, $content);

        $response = $userController->createUser($request);

        $this->assertEquals(Response::HTTP_CONFLICT, $response->getStatusCode());
        $this->assertEquals(ErrorMessage::duplicatedEmailJSON($this->serializer), $response->getContent());
    }

    public function testCreateUserInvalidBody(): void
    {
        $userService = $this->createMock(UserServiceInterface::class);
        $userController = new UserController($userService, $this->serializer, $this->logger);

        $request = Request::createFromGlobals();

        $response = $userController->createUser($request);

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertEquals(ErrorMessage::invalidFormatJSON($this->serializer), $response->getContent());
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
        $userController = new UserController($userService, $this->serializer, $this->logger);

        $content = $this->serializer->serialize($userCreate, JsonEncoder::FORMAT);
        $request = RequestHelper::createRequest("users", Request::METHOD_POST, $content);

        $response = $userController->createUser($request);

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertEquals(ErrorMessage::generateJSON($expectedException, $this->serializer), $response->getContent());
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
            ->willThrowException(new Exception());
        $userController = new UserController($userService, $this->serializer, $this->logger);

        $content = $this->serializer->serialize($userCreate, JsonEncoder::FORMAT);
        $request = RequestHelper::createRequest("users", Request::METHOD_POST, $content);

        $response = $userController->createUser($request);

        $this->assertEquals(Response::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
        $this->assertEquals(ErrorMessage::internalError($this->serializer), $response->getContent());
    }

    public function testUpdateUserSuccess(): void
    {
        $externalId = Uuid::v4();
        $userCreate = new UserEditableDto(
            ConstHelper::USER_NAME_TEST,
            ConstHelper::USER_EMAIL_TEST,
            ConstHelper::USER_PASSWORD_TEST,
            ConstHelper::USER_PHONE_TEST,
        );
        $userDto = new UserDto(
            $externalId,
            ConstHelper::USER_NAME_TEST,
            ConstHelper::USER_EMAIL_TEST,
            ConstHelper::USER_PHONE_TEST,
        );

        $userService = $this->createMock(UserService::class);
        $userService->expects(self::once())
            ->method('updateUser')
            ->willReturn($userDto);

        $userController = new UserController($userService, $this->serializer, $this->logger);

        $content = $this->serializer->serialize($userCreate, JsonEncoder::FORMAT);
        $request = RequestHelper::createRequest("users/1", Request::METHOD_PUT, $content);

        $response = $userController->updateUser(Uuid::v4(), $request);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertEquals($this->serializer->serialize($userDto, JsonEncoder::FORMAT), $response->getContent());
    }

    public function testUpdateUserDuplicatedEmail(): void
    {
        $userCreate = new UserEditableDto(
            ConstHelper::USER_NAME_TEST,
            ConstHelper::USER_EMAIL_TEST,
            ConstHelper::USER_PASSWORD_TEST,
            ConstHelper::USER_PHONE_TEST,
        );
        $exception = new EmailAlreadyInUseException();

        $userService = $this->createMock(UserService::class);
        $userService->expects(self::once())
            ->method('updateUser')
            ->willThrowException($exception);

        $userController = new UserController($userService, $this->serializer, $this->logger);

        $content = $this->serializer->serialize($userCreate, JsonEncoder::FORMAT);
        $request = RequestHelper::createRequest("users/1", Request::METHOD_PUT, $content);

        $response = $userController->updateUser(Uuid::v4(), $request);

        $this->assertEquals(Response::HTTP_CONFLICT, $response->getStatusCode());
        $this->assertEquals(ErrorMessage::duplicatedEmailJSON($this->serializer), $response->getContent());
    }

    public function testUpdateUserNotfound(): void
    {
        $userCreate = new UserEditableDto(
            ConstHelper::USER_NAME_TEST,
            ConstHelper::USER_EMAIL_TEST,
            ConstHelper::USER_PASSWORD_TEST,
            ConstHelper::USER_PHONE_TEST,
        );

        $exception = new UserNotFoundException($this->userUuidTest);

        $userService = $this->createMock(UserServiceInterface::class);
        $userService->expects(self::once())
            ->method('updateUser')
            ->willThrowException($exception);
        $userController = new UserController($userService, $this->serializer, $this->logger);

        $content = $this->serializer->serialize($userCreate, JsonEncoder::FORMAT);
        $request = RequestHelper::createRequest("users/1", Request::METHOD_PUT, $content);

        $response = $userController->updateUser($this->userUuidTest, $request);

        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        $this->assertEquals(ErrorMessage::generateJSON($exception, $this->serializer), $response->getContent());
    }

    public function testUpdateUserError(): void
    {
        $userCreate = new UserEditableDto(
            ConstHelper::USER_NAME_TEST,
            ConstHelper::USER_EMAIL_TEST,
            ConstHelper::USER_PASSWORD_TEST,
            ConstHelper::USER_PHONE_TEST,
        );
        $exception = new Exception();

        $userService = $this->createMock(UserServiceInterface::class);
        $userService->expects(self::once())
            ->method('updateUser')
            ->willThrowException($exception);
        $userController = new UserController($userService, $this->serializer, $this->logger);

        $content = $this->serializer->serialize($userCreate, JsonEncoder::FORMAT);
        $request = RequestHelper::createRequest("users/1", Request::METHOD_PUT, $content);

        $response = $userController->updateUser($this->userUuidTest, $request);

        $this->assertEquals(Response::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
        $this->assertEquals(ErrorMessage::internalError($this->serializer), $response->getContent());

    }
}