<?php

namespace App\Controller;

use App\Common\ErrorMessage;
use App\Entity\UserCreate;
use App\Exception\InvalidRequestException;
use App\Exception\UserNotFoundException;
use App\Service\UserServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\SerializerInterface;

#[AsController]
class UserController extends AbstractController
{
    const INVALID_JSON_FORMAT = "invalid json format request";

    private UserServiceInterface $userService;

    function __construct(UserServiceInterface $userService)
    {
        $this->userService = $userService;
    }

    #[Route('/users', name: 'listUsers', methods: [Request::METHOD_GET])]
    public function listUsers(SerializerInterface $serializer): Response
    {
        try {
            $users = $this->userService->findAllUsers();
        } catch (\Exception $e) {
            error_log($e);
            return new Response("",Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new Response(
            $serializer->serialize($users, JsonEncoder::FORMAT),
            Response::HTTP_OK,
            ['Content-Type' => 'application/json;charset=UTF-8']
        );
    }

    #[Route('/users/{userId}', name: 'singleUser', methods: [Request::METHOD_GET])]
    public function singleUser(int $userId, SerializerInterface $serializer): Response
    {
        try {
            $user = $this->userService->findUser($userId);
        } catch(UserNotFoundException) {
            return new Response("", Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            error_log($e);
            return new Response("",Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new Response(
            $serializer->serialize($user, JsonEncoder::FORMAT),
            Response::HTTP_OK,
            ['Content-Type' => 'application/json;charset=UTF-8']
        );
    }

    #[Route('/users/{userId}', name: 'removeUser', methods: [Request::METHOD_DELETE])]
    public function removeUser(int $userId): Response
    {
        try {
            $this->userService->removeUser($userId);
        } catch(UserNotFoundException) {
            return new Response("", Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            error_log($e);
            return new Response("",Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new Response("",Response::HTTP_NO_CONTENT);
    }

    #[Route('/users', name: 'createUser', methods: [Request::METHOD_POST])]
    public function createUser(Request $request, SerializerInterface $serializer): Response
    {
        try {
            $userCreate = $serializer->deserialize($request->getContent(), UserCreate::class, JsonEncoder::FORMAT);
        } catch (\Exception $e) {
            error_log($e);
            return new Response(self::INVALID_JSON_FORMAT,Response::HTTP_BAD_REQUEST);
        }

        try {
            $user = $this->userService->createUser($userCreate);
        } catch (InvalidRequestException $e) {
            $errorResponse = ErrorMessage::generate($e, $serializer);

            return new Response($errorResponse,Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            error_log($e);
            return new Response("",Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new Response(
            $serializer->serialize($user, JsonEncoder::FORMAT),
            Response::HTTP_OK,
            ['Content-Type' => 'application/json;charset=UTF-8']
        );
    }

    #[Route('/users/{userId}', name: 'updateUser', methods: [Request::METHOD_PUT])]
    public function updateUser(int $userId, Request $request, SerializerInterface $serializer): Response
    {
        try {
            $userCreate = $serializer->deserialize($request->getContent(), UserCreate::class, JsonEncoder::FORMAT);
        } catch (InvalidRequestException $e) {
            $errorResponse = ErrorMessage::generate($e, $serializer);

            return new Response($errorResponse,Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            error_log($e);

            return new Response("",Response::HTTP_BAD_REQUEST);
        }

        try {
            $user = $this->userService->updateUser($userId, $userCreate);
        } catch (UserNotFoundException $e) {
            error_log($e);
            return new Response("",Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            error_log($e);
            return new Response("",Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new Response(
            $serializer->serialize($user, JsonEncoder::FORMAT),
            Response::HTTP_OK,
            ['Content-Type' => 'application/json;charset=UTF-8']
        );
    }
}