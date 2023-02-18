<?php

namespace App\Tests\Integration\Controller;

use App\Common\ErrorMessage;
use App\Controller\UserController;
use App\Dto\UserEditableDto;
use App\Entity\Roles;
use App\Entity\User;
use App\Exception\InvalidRequestException;
use App\Exception\UserNotFoundException;
use App\Mapper\UserMapper;
use App\Service\UserService;
use App\Tests\Utils\ConstHelper;
use App\Tests\Utils\FixtureHelper;
use App\Tests\Utils\RequestHelper;
use DateTime;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManager;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\UidNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Uid\Uuid;

class UserControllerTest extends KernelTestCase
{
    /**
     * @var EntityManager
     */
    private EntityManager $entityManager;

    /**
     * @var Serializer
     */
    private Serializer $serializer;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        $application = new Application($kernel);

        $command = $application->find('doctrine:migrations:migrate');
        $commandTester = new CommandTester($command);
        $commandTester->execute(['n']);

        $commandTester->assertCommandIsSuccessful();

        $encoders = array(new JsonEncoder());
        $normalizers = array(new DateTimeNormalizer(), new UidNormalizer(), new ObjectNormalizer());

        $this->serializer = new Serializer($normalizers, $encoders);

        $this->logger = new Logger("test");
    }

    public function testListUsersSuccess(): void
    {
        $user = FixtureHelper::addUser($this->entityManager);
        $userDto = UserMapper::entityToDto($user);

        $userRepository = $this->entityManager->getRepository(User::class);
        $userService = new UserService($userRepository);
        $userController = new UserController($userService, $this->serializer, $this->logger);

        $response = $userController->listUsers();

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertEquals($this->serializer->serialize([$userDto], JsonEncoder::FORMAT), $response->getContent());

        FixtureHelper::removeUser($this->entityManager, $user);
    }

    public function testSingleUserSuccess(): void
    {
        $user = FixtureHelper::addUser($this->entityManager);
        $userDto = UserMapper::entityToDto($user);

        $userRepository = $this->entityManager->getRepository(User::class);
        $userService = new UserService($userRepository);
        $userController = new UserController($userService, $this->serializer, $this->logger);

        $response = $userController->singleUser($user->getExternalId());

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertEquals($this->serializer->serialize($userDto, JsonEncoder::FORMAT), $response->getContent());

        FixtureHelper::removeUser($this->entityManager, $user);
    }

    public function testSingleUserNotFound(): void
    {
        $userUuid = Uuid::v4();

        $userRepository = $this->entityManager->getRepository(User::class);
        $userService = new UserService($userRepository);
        $userController = new UserController($userService, $this->serializer, $this->logger);

        $response = $userController->singleUser($userUuid);

        $expectedError = new UserNotFoundException($userUuid);

        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        $this->assertEquals(ErrorMessage::generateJSON($expectedError, $this->serializer), $response->getContent());
    }

    public function testFindUserByEmailSuccess(): void
    {
        $user = FixtureHelper::addUser($this->entityManager);
        $userDto = UserMapper::entityToDto($user);

        $userRepository = $this->entityManager->getRepository(User::class);
        $userService = new UserService($userRepository);
        $userController = new UserController($userService, $this->serializer, $this->logger);

        $parameters = ["email" => ConstHelper::USER_EMAIL_TEST];
        $request = RequestHelper::createRequest("/users/email", Request::METHOD_GET, "", $parameters);
        $response = $userController->findUserByEmail($request);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertEquals($this->serializer->serialize($userDto, JsonEncoder::FORMAT), $response->getContent());

        FixtureHelper::removeUser($this->entityManager, $user);
    }

    public function testFindUserByEmailEmptyEmail(): void
    {
        $userRepository = $this->entityManager->getRepository(User::class);
        $userService = new UserService($userRepository);
        $userController = new UserController($userService, $this->serializer, $this->logger);

        $request = RequestHelper::createRequest("/users/email", Request::METHOD_GET, "");
        $response = $userController->findUserByEmail($request);

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertEquals(ErrorMessage::emptyEmailJSON($this->serializer), $response->getContent());
    }

    public function testFindUserByEmailNotFound(): void
    {
        $userRepository = $this->entityManager->getRepository(User::class);
        $userService = new UserService($userRepository);
        $userController = new UserController($userService, $this->serializer, $this->logger);

        $parameters = ["email" => ConstHelper::USER_EMAIL_TEST];
        $request = RequestHelper::createRequest("/users/email", Request::METHOD_GET, "", $parameters);
        $response = $userController->findUserByEmail($request);

        $exception = new UserNotFoundException(ConstHelper::USER_EMAIL_TEST);

        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        $this->assertEquals(ErrorMessage::generateJSON($exception, $this->serializer), $response->getContent());
    }

    public function testRemoveUserSuccess(): void
    {
        $user = FixtureHelper::addUser($this->entityManager);

        $userRepository = $this->entityManager->getRepository(User::class);
        $userService = new UserService($userRepository);
        $userController = new UserController($userService, $this->serializer, $this->logger);

        $response = $userController->removeUser($user->getExternalId());

        $this->assertEquals(Response::HTTP_NO_CONTENT, $response->getStatusCode());

        FixtureHelper::removeUser($this->entityManager, $user);
    }

    public function testRemoveUserNotFound(): void
    {
        $userUuid = Uuid::v4();

        $userRepository = $this->entityManager->getRepository(User::class);
        $userService = new UserService($userRepository);
        $userController = new UserController($userService, $this->serializer, $this->logger);

        $response = $userController->removeUser($userUuid);

        $exception = new UserNotFoundException($userUuid);

        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        $this->assertEquals(ErrorMessage::generateJSON($exception, $this->serializer), $response->getContent());
    }

    public function testCreateUserSuccess(): void
    {
        $userCreate = new UserEditableDto(
            ConstHelper::USER_NAME_TEST,
            ConstHelper::USER_EMAIL_TEST,
            ConstHelper::USER_PASSWORD_TEST,
            ConstHelper::USER_PHONE_TEST,
        );

        $userRepository = $this->entityManager->getRepository(User::class);
        $userService = new UserService($userRepository);
        $userController = new UserController($userService, $this->serializer, $this->logger);

        $content = $this->serializer->serialize($userCreate, JsonEncoder::FORMAT);
        $request = RequestHelper::createRequest("/users", Request::METHOD_POST, $content);

        $response = $userController->createUser($request);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        // Fix: get user from response
        $normalizedUser = json_decode($response->getContent(), true);
        if($normalizedUser["id"] != null) {
            $newUser = $userRepository->findOneBy(["externalId" => $normalizedUser["id"]]);
        }

        FixtureHelper::removeUser($this->entityManager, $newUser);
    }

    public function testCreateUserSuccessWithRoles(): void
    {
        $expectedRoles = [Roles::ROLE_ADMIN, Roles::ROLE_USER];
        $userCreate = new UserEditableDto(
            ConstHelper::USER_NAME_TEST,
            ConstHelper::USER_EMAIL_TEST,
            ConstHelper::USER_PASSWORD_TEST,
            ConstHelper::USER_PHONE_TEST,
            $expectedRoles,
        );

        $userRepository = $this->entityManager->getRepository(User::class);
        $userService = new UserService($userRepository);
        $userController = new UserController($userService, $this->serializer, $this->logger);

        $content = $this->serializer->serialize($userCreate, JsonEncoder::FORMAT);
        $request = RequestHelper::createRequest("/users", Request::METHOD_POST, $content);

        $response = $userController->createUser($request);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        // Fix: get user from response
        $normalizedUser = json_decode($response->getContent(), true);
        if($normalizedUser["id"] != null) {
            $newUser = $userRepository->findOneBy(["externalId" => $normalizedUser["id"]]);
        }

        $this->assertEquals($expectedRoles, $newUser->getRoles());

        FixtureHelper::removeUser($this->entityManager, $newUser);
    }

    public function testCreateUserDuplicatedEmail(): void
    {
        FixtureHelper::addUser($this->entityManager);

        $userCreate = new UserEditableDto(
            ConstHelper::USER_NAME_TEST,
            ConstHelper::USER_EMAIL_TEST,
            ConstHelper::USER_PASSWORD_TEST,
            ConstHelper::USER_PHONE_TEST,
        );

        $userRepository = $this->entityManager->getRepository(User::class);
        $userService = new UserService($userRepository);
        $userController = new UserController($userService, $this->serializer, $this->logger);

        $content = $this->serializer->serialize($userCreate, JsonEncoder::FORMAT);
        $request = RequestHelper::createRequest("/users", Request::METHOD_POST, $content);

        $response = $userController->createUser($request);

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertEquals(ErrorMessage::duplicatedEmailJSON($this->serializer), $response->getContent());
    }

    public function testCreateUserEmptyName(): void
    {
        $userCreate = new UserEditableDto(
            "",
            ConstHelper::USER_EMAIL_TEST,
            ConstHelper::USER_PASSWORD_TEST,
            ConstHelper::USER_PHONE_TEST,
        );
        $exception = new InvalidRequestException(UserService::ERROR_EMPTY_USER_NAME);
        $errorMessage = ErrorMessage::generateJSON($exception, $this->serializer);

        $userRepository = $this->entityManager->getRepository(User::class);
        $userService = new UserService($userRepository);
        $userController = new UserController($userService, $this->serializer, $this->logger);

        $content = $this->serializer->serialize($userCreate, JsonEncoder::FORMAT);
        $request = RequestHelper::createRequest("/users", Request::METHOD_POST, $content);

        $response = $userController->createUser($request);

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertEquals($errorMessage, $response->getContent());
    }

    public function testCreateUserEmptyEmail(): void
    {
        $userCreate = new UserEditableDto(
            ConstHelper::USER_NAME_TEST,
            "",
            ConstHelper::USER_PASSWORD_TEST,
            ConstHelper::USER_PHONE_TEST,
        );
        $exception = new InvalidRequestException(UserService::ERROR_EMPTY_USER_EMAIL);

        $userRepository = $this->entityManager->getRepository(User::class);
        $userService = new UserService($userRepository);
        $userController = new UserController($userService, $this->serializer, $this->logger);

        $content = $this->serializer->serialize($userCreate, JsonEncoder::FORMAT);
        $request = RequestHelper::createRequest("/users", Request::METHOD_POST, $content);

        $response = $userController->createUser($request);

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertEquals(ErrorMessage::generateJSON($exception, $this->serializer), $response->getContent());
    }

    public function testCreateUserEmptyPassword(): void
    {
        $userCreate = new UserEditableDto(
            ConstHelper::USER_NAME_TEST,
            ConstHelper::USER_EMAIL_TEST,
            "",
            ConstHelper::USER_PHONE_TEST,
        );
        $exception = new InvalidRequestException(UserService::ERROR_EMPTY_USER_PASSWORD);

        $userRepository = $this->entityManager->getRepository(User::class);
        $userService = new UserService($userRepository);
        $userController = new UserController($userService, $this->serializer, $this->logger);

        $content = $this->serializer->serialize($userCreate, JsonEncoder::FORMAT);
        $request = RequestHelper::createRequest("/users", Request::METHOD_POST, $content);

        $response = $userController->createUser($request);

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertEquals(ErrorMessage::generateJSON($exception, $this->serializer), $response->getContent());
    }

    public function testUpdateUserSuccess(): void
    {
        $existingUser = FixtureHelper::addUser($this->entityManager);

        $userCreate = new UserEditableDto(
            ConstHelper::NEW_USER_NAME_TEST,
            ConstHelper::NEW_USER_EMAIL_TEST,
            ConstHelper::NEW_USER_PASSWORD_TEST,
            ConstHelper::NEW_USER_PHONE_TEST,
        );

        $userRepository = $this->entityManager->getRepository(User::class);
        $userService = new UserService($userRepository);
        $userController = new UserController($userService, $this->serializer, $this->logger);

        $content = $this->serializer->serialize($userCreate, JsonEncoder::FORMAT);
        $request = RequestHelper::createRequest("/users", Request::METHOD_PUT, $content);

        $response = $userController->updateUser($existingUser->getExternalId(), $request);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        FixtureHelper::removeUser($this->entityManager, $existingUser);
    }

    public function testUpdateUserSuccessWithRoles(): void
    {
        $existingUser = FixtureHelper::addUser($this->entityManager);

        $expectedRoles = [Roles::ROLE_USER, Roles::ROLE_ADMIN];
        $userCreate = new UserEditableDto(
            ConstHelper::NEW_USER_NAME_TEST,
            ConstHelper::NEW_USER_EMAIL_TEST,
            ConstHelper::NEW_USER_PASSWORD_TEST,
            ConstHelper::NEW_USER_PHONE_TEST,
            $expectedRoles
        );

        $userRepository = $this->entityManager->getRepository(User::class);
        $userService = new UserService($userRepository);
        $userController = new UserController($userService, $this->serializer, $this->logger);

        $content = $this->serializer->serialize($userCreate, JsonEncoder::FORMAT);
        $request = RequestHelper::createRequest("/users", Request::METHOD_PUT, $content);

        $response = $userController->updateUser($existingUser->getExternalId(), $request);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        // Check if roles were properly updated
        $existingUser = $userRepository->find($existingUser->getId());
        $this->assertEquals($expectedRoles, $existingUser->getRoles());

        FixtureHelper::removeUser($this->entityManager, $existingUser);
    }

    public function testUpdateUserDuplicatedEmail(): void
    {
        $existingUser = FixtureHelper::addUser($this->entityManager);
        FixtureHelper::addUser($this->entityManager, ConstHelper::NEW_USER_EMAIL_TEST);

        $userCreate = new UserEditableDto(
            ConstHelper::NEW_USER_NAME_TEST,
            ConstHelper::NEW_USER_EMAIL_TEST,
            ConstHelper::NEW_USER_PASSWORD_TEST,
            ConstHelper::NEW_USER_PHONE_TEST,
        );

        $userRepository = $this->entityManager->getRepository(User::class);
        $userService = new UserService($userRepository);
        $userController = new UserController($userService, $this->serializer, $this->logger);

        $content = $this->serializer->serialize($userCreate, JsonEncoder::FORMAT);
        $request = RequestHelper::createRequest("/users", Request::METHOD_PUT, $content);

        $response = $userController->updateUser($existingUser->getExternalId(), $request);

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertEquals(ErrorMessage::duplicatedEmailJSON($this->serializer), $response->getContent());
    }

    public function testUpdateUserEmptyName(): void
    {
        $existingUser = FixtureHelper::addUser($this->entityManager);

        $userCreate = new UserEditableDto(
            "",
            ConstHelper::NEW_USER_EMAIL_TEST,
            ConstHelper::NEW_USER_PASSWORD_TEST,
            ConstHelper::NEW_USER_PHONE_TEST,
        );
        $exception = new InvalidRequestException(UserService::ERROR_EMPTY_USER_NAME);

        $userRepository = $this->entityManager->getRepository(User::class);
        $userService = new UserService($userRepository);
        $userController = new UserController($userService, $this->serializer, $this->logger);

        $content = $this->serializer->serialize($userCreate, JsonEncoder::FORMAT);
        $request = RequestHelper::createRequest("/users", Request::METHOD_PUT, $content);

        $response = $userController->updateUser($existingUser->getExternalId(), $request);

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertEquals(ErrorMessage::generateJSON($exception, $this->serializer), $response->getContent());

        FixtureHelper::removeUser($this->entityManager, $existingUser);
    }

    public function testUpdateUserEmptyEmail(): void
    {
        $existingUser = FixtureHelper::addUser($this->entityManager);

        $userCreate = new UserEditableDto(
            ConstHelper::NEW_USER_NAME_TEST,
            "",
            ConstHelper::NEW_USER_PASSWORD_TEST,
            ConstHelper::NEW_USER_PHONE_TEST,
        );
        $exception = new InvalidRequestException(UserService::ERROR_EMPTY_USER_EMAIL);

        $userRepository = $this->entityManager->getRepository(User::class);
        $userService = new UserService($userRepository);
        $userController = new UserController($userService, $this->serializer, $this->logger);

        $content = $this->serializer->serialize($userCreate, JsonEncoder::FORMAT);
        $request = RequestHelper::createRequest("/users", Request::METHOD_PUT, $content);

        $response = $userController->updateUser($existingUser->getExternalId(), $request);

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertEquals(ErrorMessage::generateJSON($exception, $this->serializer), $response->getContent());

        FixtureHelper::removeUser($this->entityManager, $existingUser);
    }

    public function testUpdateUserNotFound(): void
    {
        $userUuid = Uuid::v4();
        $userCreate = new UserEditableDto(
            ConstHelper::NEW_USER_NAME_TEST,
            ConstHelper::NEW_USER_EMAIL_TEST,
            ConstHelper::NEW_USER_PASSWORD_TEST,
            ConstHelper::NEW_USER_PHONE_TEST,
        );
        $exception = new UserNotFoundException($userUuid);

        $userRepository = $this->entityManager->getRepository(User::class);
        $userService = new UserService($userRepository);
        $userController = new UserController($userService, $this->serializer, $this->logger);

        $content = $this->serializer->serialize($userCreate, JsonEncoder::FORMAT);
        $request = RequestHelper::createRequest("/users/1", Request::METHOD_PUT, $content);

        $response = $userController->updateUser($userUuid, $request);

        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        $this->assertEquals(ErrorMessage::generateJSON($exception, $this->serializer), $response->getContent());
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // doing this is recommended to avoid memory leaks
        $this->entityManager->close();
        $this->entityManager = null;
    }
}
