<?php

namespace App\Tests\Integration\Service;

use App\Dto\LoginDto;
use App\Entity\User;
use App\Exception\UserNotFoundException;
use App\Mapper\UserMapper;
use App\Service\AuthenticationService;
use App\Tests\Utils\ConstHelper;
use App\Tests\Utils\FixtureHelper;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class AuthenticationServiceTest extends KernelTestCase
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

    public function testLoginSuccess(): void
    {
        $user = FixtureHelper::addUser($this->entityManager);

        $loginDto = new LoginDto(
            ConstHelper::USER_EMAIL_TEST,
            ConstHelper::USER_PASSWORD_TEST,
        );

        $userRepository = $this->entityManager->getRepository(User::class);
        $loginService = new AuthenticationService($userRepository);

        $response = $loginService->login($loginDto);
        $userDto = UserMapper::entityToDto($user);

        $this->assertEquals($userDto, $response);

        FixtureHelper::removeUser($this->entityManager, $user);
    }

    public function testLoginUserNotFound(): void
    {
        $loginDto = new LoginDto(
            ConstHelper::USER_EMAIL_TEST,
            ConstHelper::USER_PASSWORD_TEST,
        );

        $userRepository = $this->entityManager->getRepository(User::class);
        $loginService = new AuthenticationService($userRepository);

        $this->expectException(UserNotFoundException::class);

        $loginService->login($loginDto);
    }

    public function testLoginInvalidPassword(): void
    {
        $user = FixtureHelper::addUser($this->entityManager);

        $loginDto = new LoginDto(
            ConstHelper::USER_EMAIL_TEST,
            ConstHelper::NEW_USER_PASSWORD_TEST,
        );

        $userRepository = $this->entityManager->getRepository(User::class);
        $loginService = new AuthenticationService($userRepository);

        $response = $loginService->login($loginDto);

        $this->assertFalse($response);

        FixtureHelper::removeUser($this->entityManager, $user);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // doing this is recommended to avoid memory leaks
        $this->entityManager->close();
        $this->entityManager = null;
    }
}