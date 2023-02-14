<?php

namespace App\Service;

use App\Dto\UserDto;
use App\Dto\UserEditableDto;
use App\Exception\InvalidRequestException;
use App\Exception\UserNotFoundException;
use App\Repository\UserRepositoryInterface;
use Exception;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

interface UserServiceInterface
{
    /**
     * UserRepositoryInterface constructor.
     * @param UserRepositoryInterface $userRepository
     */
    public function __construct(UserRepositoryInterface $userRepository);

    /**
     * @param int $userId
     * @return UserDto
     * @throws UserNotFoundException
     */
    public function findUser(int $userId): UserDto;

    /**
     * @return UserDto[]
     */
    public function findAllUsers(): array;

    /**
     * @param string $email
     * @return UserDto
     * @throws UserNotFoundException
     * @throws Exception
     */
    public function findUserByEmail(string $email): UserDto;

    /**
     * @throws UserNotFoundException
     * @throws Exception
     */
    public function removeUser(int $userId): void;

    /**
     * @param UserEditableDto $userEditable
     * @param UserPasswordHasherInterface $passwordHasher
     * @return UserDto
     * @throws Exception
     */
    public function createUser(UserEditableDto $userEditable): UserDto;

    /**
     * @throws InvalidRequestException
     * @throws UserNotFoundException
     * @throws Exception
     */
    public function updateUser(int $userId, UserEditableDto $userEditable): UserDto;
}