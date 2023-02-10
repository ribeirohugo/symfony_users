<?php

namespace App\Tests\Integration\Service;

use App\Controller\UserController;
use App\Entity\User;
use App\Entity\UserCreate;
use App\Exception\UserNotFoundException;
use App\Service\UserService;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;

class UserServiceTest extends KernelTestCase
{
    const USER_NAME_TEST = "name";
    const USER_EMAIL_TEST = "email@domain.com";
    const USER_PASSWORD_TEST = "password";
    const USER_PHONE_TEST = "910123123";

    /**
     * @var EntityManager
     */
    private EntityManager $entityManager;

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
    }

    public function testListUsersSuccess(): void
    {
        $user = $this->addUser();

        $userRepository = $this->entityManager->getRepository(User::class);
        $userService = new UserService($userRepository);

        $response = $userService->findAllUsers();

        $this->assertEquals([$user], $response);

        $this->removeUser($user);
    }

    public function testFindUserSuccess(): void
    {
        $user = $this->addUser();

        $userRepository = $this->entityManager->getRepository(User::class);
        $userService = new UserService($userRepository);

        $response = $userService->findUser($user->getId());

        $this->assertEquals($user, $response);

        $this->removeUser($user);
    }

    public function testSingleUserNotFound(): void
    {
        $userId = 1;

        $userRepository = $this->entityManager->getRepository(User::class);
        $userService = new UserService($userRepository);

        try {
            $userService->findUser($userId);
        } catch (\Exception $e) {
            $this->assertEquals(new UserNotFoundException($userId), $e);
        }
    }

    public function testRemoveUserSuccess(): void
    {
        $user = $this->addUser();

        $userRepository = $this->entityManager->getRepository(User::class);
        $userService = new UserService($userRepository);
        $userController = new UserController($userService);

        $response = $userController->removeUser($user->getId());

        $this->assertEquals(Response::HTTP_NO_CONTENT, $response->getStatusCode());
    }

    public function testRemoveUserNotFound(): void
    {
        $userId = 1;
        $userRepository = $this->entityManager->getRepository(User::class);
        $userService = new UserService($userRepository);

        try {
            $userService->removeUser($userId);
        } catch (\Exception $e) {
            $this->assertEquals(new UserNotFoundException($userId), $e);
        }
    }

    public function testCreateUserSuccess(): void
    {
        $userCreate = new UserCreate(
            self::USER_NAME_TEST,
            self::USER_EMAIL_TEST,
            self::USER_PASSWORD_TEST,
            self::USER_PHONE_TEST,
        );

        $userRepository = $this->entityManager->getRepository(User::class);
        $userService = new UserService($userRepository);

        $response = $userService->createUser($userCreate);

        $this->assertEquals($userCreate->getName(), $response->getName());
        $this->assertEquals($userCreate->getEmail(), $response->getEmail());
        $this->assertEquals($userCreate->getPassword(), $response->getPassword());
        $this->assertEquals($userCreate->getPhone(), $response->getPhone());

        $this->removeUser($response);
    }

    public function testUpdateUserSuccess(): void
    {
        $existingUser = $this->addUser();

        $userCreate = new UserCreate(
            "new name",
            "new_email@domain.com",
            "new password",
            "123",
        );

        $userRepository = $this->entityManager->getRepository(User::class);
        $userService = new UserService($userRepository);

        $response = $userService->updateUser($existingUser->getId(), $userCreate);

        $this->assertEquals($userCreate->getName(), $response->getName());
        $this->assertEquals($userCreate->getEmail(), $response->getEmail());
        $this->assertEquals($userCreate->getPassword(), $response->getPassword());
        $this->assertEquals($userCreate->getPhone(), $response->getPhone());

        $this->removeUser($existingUser);
    }

    public function testUpdateUserNotFound(): void
    {
        $userId = 1;
        $userCreate = new UserCreate(
            "new name",
            "new_email@domain.com",
            "new password",
            "123",
        );

        $userRepository = $this->entityManager->getRepository(User::class);
        $userService = new UserService($userRepository);

        try {
            $userService->updateUser($userId, $userCreate);
        } catch (\Exception $e) {
            $this->assertEquals(new UserNotFoundException($userId), $e);
        }
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
            self::USER_NAME_TEST,
            self::USER_EMAIL_TEST,
            self::USER_PASSWORD_TEST,
            self::USER_PHONE_TEST,
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
}
