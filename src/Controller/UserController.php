<?php

namespace App\Controller;

use App\Common\ErrorMessage;
use App\DTO\UserDTO;
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

/**
 * UserController holds user related controller methods and routing configs.
 */
#[AsController]
class UserController extends AbstractController
{
    const INVALID_JSON_FORMAT = "invalid json format request";
    const EMPTY_EMAIL = "no email was found";

    /**
     * @var UserServiceInterface
     */
    private UserServiceInterface $userService;

    /**
     * @param UserServiceInterface $userService
     */
    function __construct(UserServiceInterface $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Returns all persisted users.
     *
     * @param SerializerInterface $serializer
     * @return Response
     */
    #[Route('/users', name: 'listUsers', methods: [Request::METHOD_GET])]
    public function listUsers(SerializerInterface $serializer): Response
    {
        try {
            $users = $this->userService->findAllUsers();
        } catch (\Exception $e) {
            error_log($e);
            return new Response("", Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new Response(
            $serializer->serialize($users, JsonEncoder::FORMAT),
            Response::HTTP_OK,
            ['Content-Type' => 'application/json;charset=UTF-8']
        );
    }

    /**
     * Returns a user, for a given user ID persisted users.
     * Returns a not found response if user doesn't exist.
     *
     * @param int $userId
     * @param SerializerInterface $serializer
     * @return Response
     */
    #[Route('/users/{userId}', name: 'singleUser', methods: [Request::METHOD_GET])]
    public function singleUser(int $userId, SerializerInterface $serializer): Response
    {
        try {
            $user = $this->userService->findUser($userId);
        } catch(UserNotFoundException) {
            return new Response("", Response::HTTP_NOT_FOUND);
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

    /**
     * Returns a user for a given email.
     * Returns user not found error if the user doesn't exist for the given email.
     *
     * @param Request $request
     * @param SerializerInterface $serializer
     * @return Response
     */
    #[Route('/users/email', name: 'findUserByEmail', methods: [Request::METHOD_GET])]
    public function findUserByEmail(Request $request, SerializerInterface $serializer): Response
    {
        $email = $request->query->get('email');
        if($email == "") {
            return new Response(self::EMPTY_EMAIL, Response::HTTP_BAD_REQUEST);
        }

        try {
            $user = $this->userService->findUserByEmail($email);
        } catch(UserNotFoundException $e) {
            $errorResponse = ErrorMessage::generate($e, $serializer);

            return new Response($errorResponse, Response::HTTP_NOT_FOUND);
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

    /**
     * Removes a user for a given user ID.
     * Returns user not found response if user doesn't exist.
     *
     * @param int $userId
     * @return Response
     */
    #[Route('/users/{userId}', name: 'removeUser', methods: [Request::METHOD_DELETE])]
    public function removeUser(int $userId): Response
    {
        try {
            $this->userService->removeUser($userId);
        } catch(UserNotFoundException) {
            return new Response("", Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            error_log($e);
            return new Response("", Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new Response("", Response::HTTP_NO_CONTENT);
    }

    /**
     * Creates a new user for a given user data through the body request.
     * Returns bad request response when the user data isn't valid.
     *
     * @param Request $request
     * @param SerializerInterface $serializer
     * @return Response
     */
    #[Route('/users', name: 'createUser', methods: [Request::METHOD_POST])]
    public function createUser(Request $request, SerializerInterface $serializer): Response
    {
        try {
            $userCreate = $serializer->deserialize($request->getContent(), UserDTO::class, JsonEncoder::FORMAT);
        } catch (\Exception $e) {
            error_log($e);
            return new Response(self::INVALID_JSON_FORMAT, Response::HTTP_BAD_REQUEST);
        }

        try {
            $user = $this->userService->createUser($userCreate);
        } catch (InvalidRequestException $e) {
            $errorResponse = ErrorMessage::generate($e, $serializer);

            return new Response($errorResponse, Response::HTTP_BAD_REQUEST);
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

    /**
     * Updates an existing user for a given user ID, with a given user data.
     * Returns not found status if the user doesn't exist.
     * Returns bad request response when the user data isn't valid.
     *
     * @param int $userId
     * @param Request $request
     * @param SerializerInterface $serializer
     * @return Response
     */
    #[Route('/users/{userId}', name: 'updateUser', methods: [Request::METHOD_PUT])]
    public function updateUser(int $userId, Request $request, SerializerInterface $serializer): Response
    {
        try {
            $userCreate = $serializer->deserialize($request->getContent(), UserDTO::class, JsonEncoder::FORMAT);
        } catch (\Exception $e) {
            error_log($e);

            return new Response(self::INVALID_JSON_FORMAT, Response::HTTP_BAD_REQUEST);
        }

        try {
            $user = $this->userService->updateUser($userId, $userCreate);
        } catch (InvalidRequestException $e) {
            $errorResponse = ErrorMessage::generate($e, $serializer);

            return new Response($errorResponse,Response::HTTP_BAD_REQUEST);
        } catch (UserNotFoundException $e) {
            error_log($e);
            return new Response("", Response::HTTP_NOT_FOUND);
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