<?php

namespace App\Service;

use App\DTO\UserDTO;
use App\Entity\User;
use App\Exception\InvalidRequestException;
use App\Exception\UserNotFoundException;
use App\Repository\UserRepositoryInterface;
use Exception;

interface UserServiceInterface
{
    /**
     * UserRepositoryInterface constructor.
     * @param UserRepositoryInterface $userRepository
     */
    public function __construct(UserRepositoryInterface $userRepository);

    /**
     * @throws UserNotFoundException
     */
    public function findUser(int $userId): User;

    /**
     * @return array
     */
    public function findAllUsers(): array;

    /**
     * @param string $email
     * @return User
     * @throws UserNotFoundException
     * @throws Exception
     */
    public function findUserByEmail(string $email): User;

    /**
     * @throws UserNotFoundException
     * @throws Exception
     */
    public function removeUser(int $userId): void;

    /**
     * @throws InvalidRequestException
     * @throws Exception
     */
    public function createUser(UserDTO $userCreate): User;

    /**
     * @throws InvalidRequestException
     * @throws UserNotFoundException
     * @throws Exception
     */
    public function updateUser(int $userId, UserDTO $userCreate): User;
}