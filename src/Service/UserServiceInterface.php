<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\UserCreate;
use App\Exception\UserNotFoundException;
use App\Repository\UserRepositoryInterface;

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

    public function findAllUsers(): array;

    public function removeUser(User $user): void;

    public function createUser(UserCreate $userCreate): User;

    /**
     * @throws UserNotFoundException
     */
    public function updateUser(int $userId, UserCreate $userCreate): User;
}