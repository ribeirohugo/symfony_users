<?php

namespace App\Tests\Controller;

use App\Controller\UserController;
use App\Entity\User;
use App\Repository\UserRepositoryInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class UserControllerTest extends TestCase
{
    const USER_NAME_TEST = "name";
    const USER_EMAIL_TEST = "email@domain.com";
    const USER_PASSWORD_TEST = "password";
    const USER_PHONE_TEST = "910123123";

    private $userController;

    private $serializer;

    protected function setUp(): void
    {
        $this->userController = new UserController();

        $encoders = array(new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());

        $this->serializer = new Serializer($normalizers, $encoders);
    }

    public function testListUsersSuccess(): void
    {
        $user = new User(
            self::USER_NAME_TEST,
            self::USER_EMAIL_TEST,
            self::USER_PASSWORD_TEST,
            self::USER_PHONE_TEST,
        );

        $userRepository = $this->createMock(UserRepositoryInterface::class);
        $userRepository->expects(self::once())
            ->method('findAll')
            ->willReturn([
                $user
            ]);

        $response = $this->userController->listUsers($userRepository, $this->serializer);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertEquals($this->serializer->serialize([$user], JsonEncoder::FORMAT), $response->getContent());
    }

    public function testSingleUserSuccess(): void
    {
        $userId = 1;
        $user = new User(
            self::USER_NAME_TEST,
            self::USER_EMAIL_TEST,
            self::USER_PASSWORD_TEST,
            self::USER_PHONE_TEST,
        );

        $userRepository = $this->createMock(UserRepositoryInterface::class);
        $userRepository->expects(self::once())
            ->method('find')
            ->willReturn($user);

        $response = $this->userController->singleUser($userId, $userRepository, $this->serializer);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertEquals($this->serializer->serialize($user, JsonEncoder::FORMAT), $response->getContent());
    }

    public function testSingleUserNotFound(): void
    {
        $userId = 1;

        $userRepository = $this->createMock(UserRepositoryInterface::class);
        $userRepository->expects(self::once())
            ->method('find')
            ->willReturn(null);

        $response = $this->userController->singleUser($userId, $userRepository, $this->serializer);

        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}