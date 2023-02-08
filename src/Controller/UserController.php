<?php

namespace App\Controller;

use App\Repository\UserRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\SerializerInterface;

#[AsController]
class UserController extends AbstractController
{
    #[Route('/users', name: 'listUsers', methods: ['GET'])]
    public function listUsers(UserRepositoryInterface $userRepository, SerializerInterface $serializer): Response
    {
        $users = $userRepository->findAll();

        return new Response(
            $serializer->serialize($users, JsonEncoder::FORMAT),
            Response::HTTP_OK,
            ['Content-Type' => 'application/json;charset=UTF-8']
        );
    }

    #[Route('/users/{userId}', name: 'singleUser', methods: ['GET'])]
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

    #[Route('/users/{userId}', name: 'removeUser', methods: ['DELETE'])]
    public function removeUser(int $userId, UserRepositoryInterface $userRepository): Response
    {
        $user = $userRepository->find($userId);

        if(empty($user)) {
            return new Response("", Response::HTTP_NOT_FOUND);
        }

        $userRepository->remove($user);

        return new Response("",Response::HTTP_NO_CONTENT);
    }
}