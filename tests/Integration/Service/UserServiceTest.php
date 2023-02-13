<?php

namespace App\Tests\Integration\Service;

use App\Controller\UserController;
use App\DTO\UserDTO;
use App\Entity\User;
use App\Exception\InvalidRequestException;
use App\Exception\UserNotFoundException;
use App\Service\UserService;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class UserServiceTest extends KernelTestCase
{
    const USER_NAME_TEST = "name";
    const USER_EMAIL_TEST = "email@domain.com";
    const USER_PASSWORD_TEST = "password";
    const USER_PHONE_TEST = "910123123";
    const NEW_USER_NAME_TEST = "new name";
    const NEW_USER_EMAIL_TEST = "new_email@domain.com";
    const NEW_USER_PASSWORD_TEST = "password";
    const NEW_USER_PHONE_TEST = "910123123";
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

        $this->expectException(UserNotFoundException::class);

        $userService->findUser($userId);
    }

    public function testFindUserByEmailSuccess(): void
    {
        $user = $this->addUser();

        $userRepository = $this->entityManager->getRepository(User::class);
        $userService = new UserService($userRepository);

        $response = $userService->findUserByEmail($user->getEmail());

        $this->assertEquals($user, $response);

        $this->removeUser($user);
    }

    public function testFindUserByEmailNotFound(): void
    {
        $userRepository = $this->entityManager->getRepository(User::class);
        $userService = new UserService($userRepository);

        $this->expectException(UserNotFoundException::class);

        $userService->findUserByEmail(self::USER_EMAIL_TEST);
    }

    public function testRemoveUserSuccess(): void
    {
        $user = $this->addUser();

        $userRepository = $this->entityManager->getRepository(User::class);
        $userService = new UserService($userRepository);
        $userController = new UserController($userService);

        $userController->removeUser($user->getId());
    }

    public function testRemoveUserNotFound(): void
    {
        $userId = 1;
        $userRepository = $this->entityManager->getRepository(User::class);
        $userService = new UserService($userRepository);

        $this->expectException(UserNotFoundException::class);

        $userService->removeUser($userId);
    }

    public function testCreateUserSuccess(): void
    {
        $userCreate = new UserDTO(
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

        $userCreate = new UserDTO(
            self::NEW_USER_NAME_TEST,
            self::NEW_USER_EMAIL_TEST,
            self::NEW_USER_PASSWORD_TEST,
            self::NEW_USER_PHONE_TEST,
        );

        $userRepository = $this->entityManager->getRepository(User::class);
        $userService = new UserService($userRepository);

        $response = $userService->updateUser($existingUser->getId(), $userCreate);

        $this->assertEquals($userCreate->getName(), $response->getName());
        $this->assertEquals($userCreate->getEmail(), $response->getEmail());
        $this->assertEquals($userCreate->getPassword(), $response->getPassword());
        $this->assertEquals($userCreate->getPhone(), $response->getPhone());

        $this->removeUser($response);
    }

    public function testUpdateUserEmptyName(): void
    {
        $existingUser = $this->addUser();

        $userCreate = new UserDTO(
            "",
            self::NEW_USER_EMAIL_TEST,
            self::NEW_USER_PASSWORD_TEST,
            self::NEW_USER_PHONE_TEST,
        );

        $userRepository = $this->entityManager->getRepository(User::class);
        $userService = new UserService($userRepository);

        $this->expectException(InvalidRequestException::class);

        $response = $userService->updateUser($existingUser->getId(), $userCreate);

        $this->removeUser($response);
    }

    public function testUpdateUserEmptyEmail(): void
    {
        $existingUser = $this->addUser();

        $userCreate = new UserDTO(
            self::NEW_USER_NAME_TEST,
            "",
            self::NEW_USER_PASSWORD_TEST,
            self::NEW_USER_PHONE_TEST,
        );

        $userRepository = $this->entityManager->getRepository(User::class);
        $userService = new UserService($userRepository);

        $this->expectException(InvalidRequestException::class);

        $response = $userService->updateUser($existingUser->getId(), $userCreate);

        $this->removeUser($response);
    }

    public function testUpdateUserNotFound(): void
    {
        $userId = 1;
        $userCreate = new UserDTO(
            "new name",
            "new_email@domain.com",
            "new password",
            "123",
        );

        $userRepository = $this->entityManager->getRepository(User::class);
        $userService = new UserService($userRepository);

        $this->expectException(UserNotFoundException::class);

        $userService->updateUser($userId, $userCreate);

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
