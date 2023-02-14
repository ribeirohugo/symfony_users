<?php

namespace App\Controller;

use App\Dto\LoginDto;
use App\Service\AuthenticationServiceInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


use App\Service\UserServiceInterface;
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

    function __construct(AuthenticationServiceInterface $authenticationService)
    {
        $this->authenticationService = $authenticationService;
    }

    #[Route('/login', name: 'login', methods: [Request::METHOD_GET])]
    public function login(Request $request, SerializerInterface $serializer): Response
    {
        try {
            $loginDto = $serializer->deserialize($request->getContent(), LoginDto::class, JsonEncoder::FORMAT);
        } catch (\Exception $e) {
            error_log($e);

            return new Response("", Response::HTTP_BAD_REQUEST);
        }

        try {
            $user = $this->authenticationService->login($loginDto);
        } catch (\Exception $e) {
            error_log($e);
            return new Response("", Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new Response(
            $serializer->serialize($user, JsonEncoder::FORMAT),
            Response::HTTP_OK,
            ['Content-Type' => 'application/json;charset=UTF-8']
        );
    }
}