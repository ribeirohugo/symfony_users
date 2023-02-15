<?php

namespace App\Tests\Integration\Controller;

use App\Controller\AuthenticationController;
use App\Dto\LoginDto;
use App\Entity\User;
use App\Mapper\UserMapper;
use App\Service\AuthenticationService;
use App\Tests\Utils\ConstHelper;
use App\Tests\Utils\FixtureHelper;
use App\Tests\Utils\RequestHelper;
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

class AuthenticationControllerTest extends KernelTestCase
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

    public function testLoginSuccess(): void
    {
        $user = FixtureHelper::addUser($this->entityManager);
        $userDto = UserMapper::entityToDto($user);

        $loginDto = new LoginDto(
            ConstHelper::USER_EMAIL_TEST,
            ConstHelper::USER_PASSWORD_TEST,
        );

        $userRepository = $this->entityManager->getRepository(User::class);
        $authService = new AuthenticationService($userRepository);
        $authController = new AuthenticationController($authService, $this->serializer);

        $content = $this->serializer->serialize($loginDto, JsonEncoder::FORMAT);
        $request = RequestHelper::createRequest("login", Request::METHOD_PUT, $content);

        $response = $authController->login($request);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertEquals($this->serializer->serialize($userDto, JsonEncoder::FORMAT), $response->getContent());

        FixtureHelper::removeUser($this->entityManager, $user);
    }

    public function testLoginUserNotFound(): void
    {
        $loginDto = new LoginDto(
            ConstHelper::USER_EMAIL_TEST,
            ConstHelper::USER_PASSWORD_TEST,
        );

        $userRepository = $this->entityManager->getRepository(User::class);
        $authService = new AuthenticationService($userRepository);
        $authController = new AuthenticationController($authService, $this->serializer);

        $content = $this->serializer->serialize($loginDto, JsonEncoder::FORMAT);
        $request = RequestHelper::createRequest("login", Request::METHOD_PUT, $content);

        $response = $authController->login($request);

        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    public function testLoginUserUnauthorized(): void
    {
        $user = FixtureHelper::addUser($this->entityManager);

        $loginDto = new LoginDto(
            ConstHelper::USER_EMAIL_TEST,
            ConstHelper::NEW_USER_PASSWORD_TEST,
        );

        $userRepository = $this->entityManager->getRepository(User::class);
        $authService = new AuthenticationService($userRepository);
        $authController = new AuthenticationController($authService, $this->serializer);

        $content = $this->serializer->serialize($loginDto, JsonEncoder::FORMAT);
        $request = RequestHelper::createRequest("login", Request::METHOD_PUT, $content);

        $response = $authController->login($request);

        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());

        FixtureHelper::removeUser($this->entityManager, $user);
    }
}