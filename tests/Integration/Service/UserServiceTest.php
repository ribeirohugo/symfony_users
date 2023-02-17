<?php

namespace App\Tests\Integration\Service;

use App\Dto\UserEditableDto;
use App\Entity\User;
use App\Exception\InvalidRequestException;
use App\Exception\UserNotFoundException;
use App\Mapper\UserMapper;
use App\Service\UserService;
use App\Tests\Utils\ConstHelper;
use App\Tests\Utils\FixtureHelper;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class UserServiceTest extends KernelTestCase
{
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
        $user = FixtureHelper::addUser($this->entityManager);
        $userDto = UserMapper::entityToDto($user);

        $userRepository = $this->entityManager->getRepository(User::class);
        $userService = new UserService($userRepository);

        $response = $userService->findAllUsers();

        $this->assertEquals([$userDto], $response);

        FixtureHelper::removeUser($this->entityManager, $user);
    }

    public function testFindUserSuccess(): void
    {
        $user = FixtureHelper::addUser($this->entityManager);
        $userDto = UserMapper::entityToDto($user);

        $userRepository = $this->entityManager->getRepository(User::class);
        $userService = new UserService($userRepository);

        $response = $userService->findUser($user->getId());

        $this->assertEquals($userDto, $response);

        FixtureHelper::removeUser($this->entityManager, $user);
    }

    public function testSingleUserNotFound(): void
    {
        $userRepository = $this->entityManager->getRepository(User::class);
        $userService = new UserService($userRepository);

        $this->expectException(UserNotFoundException::class);

        $userService->findUser(ConstHelper::USER_ID_TEST);
    }

    public function testFindUserByEmailSuccess(): void
    {
        $user = FixtureHelper::addUser($this->entityManager);
        $userDto = UserMapper::entityToDto($user);

        $userRepository = $this->entityManager->getRepository(User::class);
        $userService = new UserService($userRepository);

        $response = $userService->findUserByEmail($user->getEmail());

        $this->assertEquals($userDto, $response);

        FixtureHelper::removeUser($this->entityManager, $user);
    }

    public function testFindUserByEmailNotFound(): void
    {
        $userRepository = $this->entityManager->getRepository(User::class);
        $userService = new UserService($userRepository);

        $this->expectException(UserNotFoundException::class);

        $userService->findUserByEmail(ConstHelper::USER_EMAIL_TEST);
    }

    public function testRemoveUserSuccess(): void
    {
        $user = FixtureHelper::addUser($this->entityManager);

        $userRepository = $this->entityManager->getRepository(User::class);
        $userService = new UserService($userRepository);

        $userService->removeUser($user->getId());
    }

    public function testRemoveUserNotFound(): void
    {
        $userRepository = $this->entityManager->getRepository(User::class);
        $userService = new UserService($userRepository);

        $this->expectException(UserNotFoundException::class);

        $userService->removeUser(ConstHelper::USER_ID_TEST);
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

        $response = $userService->createUser($userCreate);

        $this->assertEquals($userCreate->getName(), $response->getName());
        $this->assertEquals($userCreate->getEmail(), $response->getEmail());
        $this->assertEquals($userCreate->getPhone(), $response->getPhone());
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

        $response = $userService->updateUser($existingUser->getId(), $userCreate);

        $this->assertEquals($userCreate->getName(), $response->getName());
        $this->assertEquals($userCreate->getEmail(), $response->getEmail());
        $this->assertEquals($userCreate->getPhone(), $response->getPhone());

        FixtureHelper::removeUser($this->entityManager, $existingUser);
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

        $userRepository = $this->entityManager->getRepository(User::class);
        $userService = new UserService($userRepository);

        $this->expectException(InvalidRequestException::class);

        $userService->updateUser($existingUser->getId(), $userCreate);

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

        $userRepository = $this->entityManager->getRepository(User::class);
        $userService = new UserService($userRepository);

        $this->expectException(InvalidRequestException::class);

        $userService->updateUser($existingUser->getId(), $userCreate);

        FixtureHelper::removeUser($this->entityManager, $existingUser);
    }

    public function testUpdateUserNotFound(): void
    {
        $userCreate = new UserEditableDto(
            ConstHelper::NEW_USER_NAME_TEST,
            ConstHelper::USER_EMAIL_TEST,
            ConstHelper::USER_PASSWORD_TEST,
            ConstHelper::USER_PHONE_TEST,
        );

        $userRepository = $this->entityManager->getRepository(User::class);
        $userService = new UserService($userRepository);

        $this->expectException(UserNotFoundException::class);

        $userService->updateUser(ConstHelper::USER_ID_TEST, $userCreate);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // doing this is recommended to avoid memory leaks
        $this->entityManager->close();
        $this->entityManager = null;
    }
}
