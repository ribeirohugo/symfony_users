<?php

namespace App\Tests\Integration\Controller;

use App\Controller\UserController;
use App\Entity\User;
use App\Entity\UserCreate;
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

class UserControllerTest extends KernelTestCase
{
    const USER_NAME_TEST = "name";
    const USER_EMAIL_TEST = "email@domain.com";
    const USER_PASSWORD_TEST = "password";
    const USER_PHONE_TEST = "910123123";

    /**
     * @var EntityManager
     */
    private EntityManager $entityManager;

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

        $userRepository = $this->entityManager
            ->getRepository(User::class);
        ;
        $userController = new UserController();

        $response = $userController->listUsers($userRepository, $this->serializer);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertEquals($this->serializer->serialize([$user], JsonEncoder::FORMAT), $response->getContent());

        $this->removeUser($user);
    }

    public function testSingleUserSuccess(): void
    {
        $user = $this->addUser();

        $userRepository = $this->entityManager
            ->getRepository(User::class);
        ;
        $userController = new UserController();

        $response = $userController->singleUser($user->getId(), $userRepository, $this->serializer);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertEquals($this->serializer->serialize($user, JsonEncoder::FORMAT), $response->getContent());

        $this->removeUser($user);
    }

    public function testSingleUserNotFound(): void
    {
        $userRepository = $this->entityManager
            ->getRepository(User::class);
        ;
        $userController = new UserController();

        $response = $userController->singleUser(1, $userRepository, $this->serializer);

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

        $userRepository = $this->entityManager
            ->getRepository(User::class);

        $userController = new UserController();

        $content = $this->serializer->serialize($userCreate, JsonEncoder::FORMAT);
        $request = $this->createRequest("/users", Request::METHOD_POST, $content);

        $response = $userController->createUser($request, $userRepository, $this->serializer);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $createdUser = $this->serializer->deserialize($response->getContent(), User::class, "json");

        $this->removeUser($createdUser);
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

        $userRepository = $this->entityManager
            ->getRepository(User::class);

        $userController = new UserController();

        $content = $this->serializer->serialize($userCreate, JsonEncoder::FORMAT);
        $request = $this->createRequest("/users", Request::METHOD_PUT, $content);

        $response = $userController->updateUser($existingUser->getId(), $request, $userRepository, $this->serializer);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $createdUser = $this->serializer->deserialize($response->getContent(), User::class, "json");

        $this->removeUser($createdUser);
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

    protected function createRequest(string $uri, string $method, string $content) {
        return Request::create($uri, $method, [], [], [], [], $content);
    }
}
