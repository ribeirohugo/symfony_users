<?php

namespace App\Tests\Integration\Controller;

use App\Common\ErrorMessage;
use App\Controller\UserController;
use App\Dto\UserEditableDto;
use App\Entity\User;
use App\Exception\InvalidRequestException;
use App\Mapper\UserMapper;
use App\Service\UserService;
use App\Tests\Utils\ConstHelper;
use App\Tests\Utils\RequestHelper;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

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
        $normalizers = array(new DateTimeNormalizer(), new ObjectNormalizer());

        $this->serializer = new Serializer($normalizers, $encoders);
    }

    public function testListUsersSuccess(): void
    {
        $user = $this->addUser();
        $userDto = UserMapper::entityToDto($user);

        $userRepository = $this->entityManager->getRepository(User::class);
        $userService = new UserService($userRepository);
        $userController = new UserController($userService);

        $response = $userController->listUsers($this->serializer);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertEquals($this->serializer->serialize([$userDto], JsonEncoder::FORMAT), $response->getContent());

        $this->removeUser($user);
    }

    public function testSingleUserSuccess(): void
    {
        $user = $this->addUser();
        $userDto = UserMapper::entityToDto($user);

        $userRepository = $this->entityManager->getRepository(User::class);
        $userService = new UserService($userRepository);
        $userController = new UserController($userService);

        $response = $userController->singleUser($user->getId(), $this->serializer);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertEquals($this->serializer->serialize($userDto, JsonEncoder::FORMAT), $response->getContent());

        $this->removeUser($user);
    }

    public function testSingleUserNotFound(): void
    {
        $userRepository = $this->entityManager->getRepository(User::class);
        $userService = new UserService($userRepository);
        $userController = new UserController($userService);

        $response = $userController->singleUser(1, $this->serializer);

        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public function testFindUserByEmailSuccess(): void
    {
        $user = $this->addUser();
        $userDto = UserMapper::entityToDto($user);

        $userRepository = $this->entityManager->getRepository(User::class);
        $userService = new UserService($userRepository);
        $userController = new UserController($userService);

        $parameters = ["email" => ConstHelper::USER_EMAIL_TEST];
        $request = RequestHelper::createRequest("/users/email", Request::METHOD_GET, "", $parameters);
        $response = $userController->findUserByEmail($request, $this->serializer);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertEquals($this->serializer->serialize($userDto, JsonEncoder::FORMAT), $response->getContent());

        $this->removeUser($user);
    }

    public function testFindUserByEmailEmptyEmail(): void
    {
        $userRepository = $this->entityManager->getRepository(User::class);
        $userService = new UserService($userRepository);
        $userController = new UserController($userService);

        $request = RequestHelper::createRequest("/users/email", Request::METHOD_GET, "");
        $response = $userController->findUserByEmail($request, $this->serializer);

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }

    public function testFindUserByEmailNotFound(): void
    {
        $userRepository = $this->entityManager->getRepository(User::class);
        $userService = new UserService($userRepository);
        $userController = new UserController($userService);

        $parameters = ["email" => ConstHelper::USER_EMAIL_TEST];
        $request = RequestHelper::createRequest("/users/email", Request::METHOD_GET, "", $parameters);
        $response = $userController->findUserByEmail($request, $this->serializer);

        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public function testRemoveUserSuccess(): void
    {
        $user = $this->addUser();

        $userRepository = $this->entityManager->getRepository(User::class);
        $userService = new UserService($userRepository);
        $userController = new UserController($userService);

        $response = $userController->removeUser($user->getId(), $this->serializer);

        $this->assertEquals(Response::HTTP_NO_CONTENT, $response->getStatusCode());
    }

    public function testRemoveUserNotFound(): void
    {
        $userRepository = $this->entityManager->getRepository(User::class);
        $userService = new UserService($userRepository);
        $userController = new UserController($userService);

        $response = $userController->removeUser(1);

        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
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
        $userController = new UserController($userService);

        $content = $this->serializer->serialize($userCreate, JsonEncoder::FORMAT);
        $request = $this->createRequest("/users", Request::METHOD_POST, $content);

        $response = $userController->createUser($request, $this->serializer);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        // Fix: get user from response
        $normalizedUser = json_decode($response->getContent(), true);
        if($normalizedUser["id"] != null) {
            $newUser = $userRepository->find($normalizedUser["id"]);
        }

        $this->removeUser($newUser);
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
        $errorMessage = ErrorMessage::generate($exception, $this->serializer);

        $userRepository = $this->entityManager->getRepository(User::class);
        $userService = new UserService($userRepository);
        $userController = new UserController($userService);

        $content = $this->serializer->serialize($userCreate, JsonEncoder::FORMAT);
        $request = $this->createRequest("/users", Request::METHOD_POST, $content);

        $response = $userController->createUser($request, $this->serializer);

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
        $errorMessage = ErrorMessage::generate($exception, $this->serializer);

        $userRepository = $this->entityManager->getRepository(User::class);
        $userService = new UserService($userRepository);
        $userController = new UserController($userService);

        $content = $this->serializer->serialize($userCreate, JsonEncoder::FORMAT);
        $request = $this->createRequest("/users", Request::METHOD_POST, $content);

        $response = $userController->createUser($request, $this->serializer);

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertEquals($errorMessage, $response->getContent());
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
        $errorMessage = ErrorMessage::generate($exception, $this->serializer);

        $userRepository = $this->entityManager->getRepository(User::class);
        $userService = new UserService($userRepository);
        $userController = new UserController($userService);

        $content = $this->serializer->serialize($userCreate, JsonEncoder::FORMAT);
        $request = $this->createRequest("/users", Request::METHOD_POST, $content);

        $response = $userController->createUser($request, $this->serializer);

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertEquals($errorMessage, $response->getContent());
    }

    public function testUpdateUserSuccess(): void
    {
        $existingUser = $this->addUser();

        $userCreate = new UserEditableDto(
            ConstHelper::NEW_USER_NAME_TEST,
            ConstHelper::NEW_USER_EMAIL_TEST,
            ConstHelper::NEW_USER_PASSWORD_TEST,
            ConstHelper::NEW_USER_PHONE_TEST,
        );

        $userRepository = $this->entityManager->getRepository(User::class);
        $userService = new UserService($userRepository);
        $userController = new UserController($userService);

        $content = $this->serializer->serialize($userCreate, JsonEncoder::FORMAT);
        $request = $this->createRequest("/users", Request::METHOD_PUT, $content);

        $response = $userController->updateUser($existingUser->getId(), $request, $this->serializer);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $this->removeUser($existingUser);
    }

    public function testUpdateUserEmptyName(): void
    {
        $existingUser = $this->addUser();

        $userCreate = new UserEditableDto(
            "",
            ConstHelper::NEW_USER_EMAIL_TEST,
            ConstHelper::NEW_USER_PASSWORD_TEST,
            ConstHelper::NEW_USER_PHONE_TEST,
        );
        $exception = new InvalidRequestException(UserService::ERROR_EMPTY_USER_NAME);
        $errorMessage = ErrorMessage::generate($exception, $this->serializer);

        $userRepository = $this->entityManager->getRepository(User::class);
        $userService = new UserService($userRepository);
        $userController = new UserController($userService);

        $content = $this->serializer->serialize($userCreate, JsonEncoder::FORMAT);
        $request = $this->createRequest("/users", Request::METHOD_PUT, $content);

        $response = $userController->updateUser($existingUser->getId(), $request, $this->serializer);

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertEquals($errorMessage, $response->getContent());

        $this->removeUser($existingUser);
    }

    public function testUpdateUserEmptyEmail(): void
    {
        $existingUser = $this->addUser();

        $userCreate = new UserEditableDto(
            ConstHelper::NEW_USER_NAME_TEST,
            "",
            ConstHelper::NEW_USER_PASSWORD_TEST,
            ConstHelper::NEW_USER_PHONE_TEST,
        );
        $exception = new InvalidRequestException(UserService::ERROR_EMPTY_USER_EMAIL);
        $errorMessage = ErrorMessage::generate($exception, $this->serializer);

        $userRepository = $this->entityManager->getRepository(User::class);
        $userService = new UserService($userRepository);
        $userController = new UserController($userService);

        $content = $this->serializer->serialize($userCreate, JsonEncoder::FORMAT);
        $request = $this->createRequest("/users", Request::METHOD_PUT, $content);

        $response = $userController->updateUser($existingUser->getId(), $request, $this->serializer);

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertEquals($errorMessage, $response->getContent());

        $this->removeUser($existingUser);
    }

    public function testUpdateUserNotFound(): void
    {
        $userCreate = new UserEditableDto(
            ConstHelper::NEW_USER_NAME_TEST,
            ConstHelper::NEW_USER_EMAIL_TEST,
            ConstHelper::NEW_USER_PASSWORD_TEST,
            ConstHelper::NEW_USER_PHONE_TEST,
        );

        $userRepository = $this->entityManager->getRepository(User::class);
        $userService = new UserService($userRepository);
        $userController = new UserController($userService);

        $content = $this->serializer->serialize($userCreate, JsonEncoder::FORMAT);
        $request = $this->createRequest("/users/1", Request::METHOD_PUT, $content);

        $response = $userController->updateUser(1, $request, $this->serializer);

        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public function testSaveUserFailWithDuplicatedEmail() {
        $user = $this->addUser();

        $conflictingUser = new User(
            ConstHelper::USER_NAME_TEST,
            ConstHelper::USER_EMAIL_TEST,
            ConstHelper::USER_PASSWORD_TEST,
            ConstHelper::USER_PHONE_TEST,
        );
        $conflictingUser->setCreatedAt(new \DateTime());
        $conflictingUser->setUpdatedAt(new \DateTime());

        $this->expectException(UniqueConstraintViolationException::class);

        $this->entityManager
            ->getRepository(User::class)
            ->save($conflictingUser, true)
        ;
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // doing this is recommended to avoid memory leaks
        $this->entityManager->close();
        $this->entityManager = null;
    }

    protected function addUser(): ?User {
        $user = new User(
            ConstHelper::USER_NAME_TEST,
            ConstHelper::USER_EMAIL_TEST,
            ConstHelper::USER_PASSWORD_TEST,
            ConstHelper::USER_PHONE_TEST,
        );
        $user->setCreatedAt(new \DateTime());
        $user->setUpdatedAt(new \DateTime());

        return $this->entityManager
            ->getRepository(User::class)
            ->save($user, true)
            ;
    }

    protected function removeUser(User $user): void {
        $this->entityManager
            ->getRepository(User::class)
            ->remove($user, true)
        ;
    }

    protected function createRequest(string $uri, string $method, string $content) {
        return Request::create($uri, $method, [], [], [], [], $content);
    }
}
