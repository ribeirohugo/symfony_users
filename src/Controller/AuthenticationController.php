<?php

namespace App\Controller;

use App\Common\ErrorMessage;
use App\Dto\LoginDto;
use App\Exception\UserNotFoundException;
use App\Service\AuthenticationServiceInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * UserController holds user related controller methods and routing configs.
 */
#[AsController]
class AuthenticationController extends AbstractController
{
    /**
     * @var AuthenticationServiceInterface
     */
    private AuthenticationServiceInterface $authenticationService;

    /**
     * @var SerializerInterface
     */
    private SerializerInterface $serializer;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @param AuthenticationServiceInterface $authenticationService
     * @param SerializerInterface $serializer
     * @param LoggerInterface $logger
     */
    function __construct(
        AuthenticationServiceInterface $authenticationService,
        SerializerInterface $serializer,
        LoggerInterface $logger,
    )
    {
        $this->authenticationService = $authenticationService;
        $this->serializer = $serializer;
        $this->logger = $logger;
    }

    #[Route('/login', name: 'login', methods: [Request::METHOD_POST])]
    public function login(Request $request): Response
    {
        try {
            $loginDto = $this->serializer->deserialize($request->getContent(), LoginDto::class, JsonEncoder::FORMAT);
        } catch (Exception $e) {
            $this->logger->error($e);
            return new Response(ErrorMessage::invalidFormatJSON($this->serializer), Response::HTTP_BAD_REQUEST);
        }

        try {
            $user = $this->authenticationService->login($loginDto);
        } catch (UserNotFoundException) {
            return new Response(ErrorMessage::authenticationFailed($this->serializer), Response::HTTP_UNAUTHORIZED);
        } catch (Exception $e) {
            $this->logger->error($e);
            return new Response(ErrorMessage::internalError($this->serializer), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        if($user) {
            return new Response(
                $this->serializer->serialize($user, JsonEncoder::FORMAT),
                Response::HTTP_OK,
                ['Content-Type' => 'application/json;charset=UTF-8']
            );
        }

        return new Response(
            ErrorMessage::authenticationFailed($this->serializer),
            Response::HTTP_UNAUTHORIZED,
            ['Content-Type' => 'application/json;charset=UTF-8']
        );
    }
}