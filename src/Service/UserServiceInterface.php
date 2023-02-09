<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\UserCreate;
use App\Repository\UserRepositoryInterface;

interface UserServiceInterface
{
    /**
     * UserRepositoryInterface constructor.
     * @param UserRepositoryInterface $userRepository
     */
    public function __construct(UserRepositoryInterface $userRepository);

    public function updateUser(int $userId, UserCreate $userCreate): User;
}