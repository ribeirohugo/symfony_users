<?php

namespace App\Controller;

use App\Common\ErrorMessage;
use App\Dto\UserEditableDto;
use App\Exception\InvalidRequestException;
use App\Exception\UserNotFoundException;
use App\Service\UserServiceInterface;
use Psr\Log\LoggerInterface;
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
    /**
     * @var UserServiceInterface
     */
    private UserServiceInterface $userService;

    /**
     * @var SerializerInterface
     */
    private SerializerInterface $serializer;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @param UserServiceInterface $userService
     * @param SerializerInterface $serializer
     * @param LoggerInterface $logger
     */
    function __construct(UserServiceInterface $userService, SerializerInterface $serializer, LoggerInterface $logger)
    {
        $this->userService = $userService;
        $this->serializer = $serializer;
        $this->logger = $logger;
    }

    /**
     * Returns all persisted users.
     *
     * @return Response
     */
    #[Route('/users', name: 'listUsers', methods: [Request::METHOD_GET])]
    public function listUsers(): Response
    {
        try {
            $users = $this->userService->findAllUsers();
        } catch (\Exception $e) {
            $this->logger->error($e);
            return new Response(ErrorMessage::internalError($this->serializer), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new Response(
            $this->serializer->serialize($users, JsonEncoder::FORMAT),
            Response::HTTP_OK,
            ['Content-Type' => 'application/json;charset=UTF-8']
        );
    }

    /**
     * Returns a user, for a given user ID persisted users.
     * Returns a not found response if user doesn't exist.
     *
     * @param int $userId
     * @return Response
     */
    #[Route('/users/{userId}', name: 'singleUser', methods: [Request::METHOD_GET])]
    public function singleUser(int $userId): Response
    {
        try {
            $user = $this->userService->findUser($userId);
        } catch(UserNotFoundException $e) {
            $this->logger->warning($e);
            return new Response(ErrorMessage::generateJSON($e, $this->serializer), Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            $this->logger->error($e);
            return new Response(ErrorMessage::internalError($this->serializer), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new Response(
            $this->serializer->serialize($user, JsonEncoder::FORMAT),
            Response::HTTP_OK,
            ['Content-Type' => 'application/json;charset=UTF-8']
        );
    }

    /**
     * Returns a user for a given email.
     * Returns user not found error if the user doesn't exist for the given email.
     *
     * @param Request $request
     * @return Response
     */
    #[Route('/users/email', name: 'findUserByEmail', methods: [Request::METHOD_GET])]
    public function findUserByEmail(Request $request): Response
    {
        $email = $request->query->get('email');
        if($email == "") {
            return new Response(ErrorMessage::emptyEmailJSON($this->serializer), Response::HTTP_BAD_REQUEST);
        }

        try {
            $user = $this->userService->findUserByEmail($email);
        } catch(UserNotFoundException $e) {
            $errorResponse = ErrorMessage::generateJSON($e, $this->serializer);
            return new Response($errorResponse, Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            $this->logger->error($e);
            return new Response(ErrorMessage::internalError($this->serializer), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new Response(
            $this->serializer->serialize($user, JsonEncoder::FORMAT),
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
        } catch(UserNotFoundException $e) {
            return new Response(ErrorMessage::generateJSON($e, $this->serializer), Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            $this->logger->error($e);
            return new Response(ErrorMessage::internalError($this->serializer), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new Response("", Response::HTTP_NO_CONTENT);
    }

    /**
     * Creates a new user for a given user data through the body request.
     * Returns bad request response when the user data isn't valid.
     *
     * @param Request $request
     * @return Response
     */
    #[Route('/users', name: 'createUser', methods: [Request::METHOD_POST])]
    public function createUser(Request $request): Response
    {
        try {
            $userCreate = $this->serializer->deserialize($request->getContent(), UserEditableDto::class, JsonEncoder::FORMAT);
        } catch (\Exception $e) {
            $this->logger->error($e);
            return new Response(ErrorMessage::invalidFormatJSON($this->serializer), Response::HTTP_BAD_REQUEST);
        }

        try {
            $user = $this->userService->createUser($userCreate);
        } catch (InvalidRequestException $e) {
            $errorResponse = ErrorMessage::generateJSON($e, $this->serializer);

            return new Response($errorResponse, Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            $this->logger->error($e);
            return new Response(ErrorMessage::internalError($this->serializer), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new Response(
            $this->serializer->serialize($user, JsonEncoder::FORMAT),
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
     * @return Response
     */
    #[Route('/users/{userId}', name: 'updateUser', methods: [Request::METHOD_PUT])]
    public function updateUser(int $userId, Request $request): Response
    {
        try {
            $userCreate = $this->serializer->deserialize($request->getContent(), UserEditableDto::class, JsonEncoder::FORMAT);
        } catch (\Exception $e) {
            $this->logger->error($e);
            return new Response(ErrorMessage::invalidFormatJSON($this->serializer), Response::HTTP_BAD_REQUEST);
        }

        try {
            $user = $this->userService->updateUser($userId, $userCreate);
        } catch (InvalidRequestException $e) {
            $errorResponse = ErrorMessage::generateJSON($e, $this->serializer);

            return new Response($errorResponse,Response::HTTP_BAD_REQUEST);
        } catch (UserNotFoundException $e) {
            return new Response(ErrorMessage::generateJSON($e, $this->serializer), Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            $this->logger->error($e);
            return new Response(ErrorMessage::internalError($this->serializer), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new Response(
            $this->serializer->serialize($user, JsonEncoder::FORMAT),
            Response::HTTP_OK,
            ['Content-Type' => 'application/json;charset=UTF-8']
        );
    }
}