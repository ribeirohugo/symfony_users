<?php

namespace App\Service;

use App\Dto\LoginDto;
use App\Dto\UserDto;
use App\Dto\UserEditableDto;
use App\Exception\InvalidRequestException;
use App\Exception\UserNotFoundException;
use App\Repository\UserRepositoryInterface;
use Exception;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

interface AuthenticationInterface
{
    /**
     * UserRepositoryInterface constructor.
     * @param UserRepositoryInterface $userRepository
     */
    public function __construct(UserRepositoryInterface $userRepository);

    /**
     * @param LoginDto $loginDto
     * @return UserDto|bool
     * @throws UserNotFoundException
     */
    public function login(LoginDto $loginDto): UserDto|bool;
}