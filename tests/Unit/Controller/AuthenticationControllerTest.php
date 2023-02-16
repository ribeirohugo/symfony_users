<?php

namespace App\Tests\Unit\Controller;

use App\Common\ErrorMessage;
use App\Controller\AuthenticationController;
use App\Dto\LoginDto;
use App\Dto\UserDto;
use App\Exception\UserNotFoundException;
use App\Service\AuthenticationServiceInterface;
use App\Tests\Utils\ConstHelper;
use App\Tests\Utils\RequestHelper;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class AuthenticationControllerTest extends TestCase
{
    /**
     * @var Serializer
     */
    private Serializer $serializer;

    protected function setUp(): void
    {
        $encoders = array(new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());

        $this->serializer = new Serializer($normalizers, $encoders);
    }

    public function testLoginSuccess(): void
    {
        $userDto = new UserDto(
            ConstHelper::USER_ID_TEST,
            ConstHelper::USER_NAME_TEST,
            ConstHelper::USER_EMAIL_TEST,
            ConstHelper::USER_PHONE_TEST,
        );
        $loginDto = new LoginDto(
            ConstHelper::USER_EMAIL_TEST,
            ConstHelper::USER_PASSWORD_TEST,
        );

        $authService = $this->createMock(AuthenticationServiceInterface::class);
        $authService->expects(self::once())
            ->method('login')
            ->willReturn($userDto);
        $userController = new AuthenticationController($authService, $this->serializer);

        $content = $this->serializer->serialize($loginDto, JsonEncoder::FORMAT);
        $request = RequestHelper::createRequest("login", Request::METHOD_PUT, $content);

        $response = $userController->login($request);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $this->assertEquals($this->serializer->serialize($userDto, JsonEncoder::FORMAT), $response->getContent());
    }

    public function testLoginUserNotFound(): void
    {
        $userDto = new UserDto(
            ConstHelper::USER_ID_TEST,
            ConstHelper::USER_NAME_TEST,
            ConstHelper::USER_EMAIL_TEST,
            ConstHelper::USER_PHONE_TEST,
        );
        $loginDto = new LoginDto(
            ConstHelper::USER_EMAIL_TEST,
            ConstHelper::USER_PASSWORD_TEST,
        );

        $authService = $this->createMock(AuthenticationServiceInterface::class);
        $authService->expects(self::once())
            ->method('login')
            ->willThrowException(new UserNotFoundException(ConstHelper::USER_EMAIL_TEST));

        $userController = new AuthenticationController($authService, $this->serializer);

        $content = $this->serializer->serialize($loginDto, JsonEncoder::FORMAT);
        $request = RequestHelper::createRequest("login", Request::METHOD_POST, $content);

        $response = $userController->login($request);

        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
        $this->assertEquals(ErrorMessage::authenticationFailed($this->serializer), $response->getContent());
    }

    public function testLoginUnauthorized(): void
    {
        $loginDto = new LoginDto(
            ConstHelper::USER_EMAIL_TEST,
            ConstHelper::USER_PASSWORD_TEST,
        );

        $authService = $this->createMock(AuthenticationServiceInterface::class);
        $authService->expects(self::once())
            ->method('login')
            ->willReturn(false);
        $userController = new AuthenticationController($authService, $this->serializer);

        $content = $this->serializer->serialize($loginDto, JsonEncoder::FORMAT);
        $request = RequestHelper::createRequest("login", Request::METHOD_PUT, $content);

        $response = $userController->login($request);

        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }
}