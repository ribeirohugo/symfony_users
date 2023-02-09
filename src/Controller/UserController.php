<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\UserCreate;
use App\Exception\UserNotFoundException;
use App\Repository\UserRepositoryInterface;
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
    #[Route('/users', name: 'listUsers', methods: [Request::METHOD_GET])]
    public function listUsers(UserRepositoryInterface $userRepository, SerializerInterface $serializer): Response
    {
        $users = $userRepository->findAll();

        return new Response(
            $serializer->serialize($users, JsonEncoder::FORMAT),
            Response::HTTP_OK,
            ['Content-Type' => 'application/json;charset=UTF-8']
        );
    }

    #[Route('/users/{userId}', name: 'singleUser', methods: [Request::METHOD_GET])]
    public function singleUser(int $userId, UserRepositoryInterface $userRepository, SerializerInterface $serializer): Response
    {
        $user = $userRepository->find($userId);

        if(empty($user)) {
            return new Response("", Response::HTTP_NOT_FOUND);
        }

        return new Response(
            $serializer->serialize($user, JsonEncoder::FORMAT),
            Response::HTTP_OK,
            ['Content-Type' => 'application/json;charset=UTF-8']
        );
    }

    #[Route('/users/{userId}', name: 'removeUser', methods: [Request::METHOD_DELETE])]
    public function removeUser(int $userId, UserRepositoryInterface $userRepository): Response
    {
        $user = $userRepository->find($userId);

        if(empty($user)) {
            return new Response("", Response::HTTP_NOT_FOUND);
        }

        $userRepository->remove($user);

        return new Response("",Response::HTTP_NO_CONTENT);
    }

    #[Route('/users', name: 'createUser', methods: [Request::METHOD_POST])]
    public function createUser(Request $request, UserRepositoryInterface $userRepository, SerializerInterface $serializer): Response
    {
        try {
            $userCreate = $serializer->deserialize($request->getContent(), UserCreate::class, "json");
        } catch (\Exception $e) {
            error_log($e);

            return new Response("",Response::HTTP_BAD_REQUEST);
        };

        $user = new User(
            $userCreate->getName(),
            $userCreate->getEmail(),
            $userCreate->getPassword(),
            $userCreate->getPhone(),
        );

        try {
            $userRepository->save($user, true);
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
    public function updateUser(int $userId, Request $request, UserServiceInterface $userService, SerializerInterface $serializer): Response
    {
        try {
            $userCreate = $serializer->deserialize($request->getContent(), UserCreate::class, "json");
        } catch (\Exception $e) {
            error_log($e);

            return new Response("",Response::HTTP_BAD_REQUEST);
        }

        try {
            $user = $userService->updateUser($userId, $userCreate);
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