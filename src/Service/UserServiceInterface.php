<?php

namespace App\Service;

use App\Dto\UserDto;
use App\Dto\UserEditableDto;
use App\Exception\EmailAlreadyInUseException;
use App\Exception\InvalidRequestException;
use App\Exception\UserNotFoundException;
use App\Repository\UserRepositoryInterface;
use Exception;
use Symfony\Component\Uid\Uuid;

interface UserServiceInterface
{
    /**
     * UserRepositoryInterface constructor.
     * @param UserRepositoryInterface $userRepository
     */
    public function __construct(UserRepositoryInterface $userRepository);

    /**
     * @param Uuid $userId
     * @return UserDto
     *
     * @throws UserNotFoundException
     */
    public function findUser(Uuid $userId): UserDto;

    /**
     * @return UserDto[]
     */
    public function findAllUsers(): array;

    /**
     * @param string $email
     * @return UserDto
     *
     * @throws UserNotFoundException|Exception
     */
    public function findUserByEmail(string $email): UserDto;

    /**
     * @param Uuid $userId
     * @return void
     *
     * @throws UserNotFoundException|Exception
     */
    public function removeUser(Uuid $userId): void;

    /**
     * @param UserEditableDto $userEditable
     * @return UserDto
     *
     * @throws InvalidRequestException|EmailAlreadyInUseException|Exception
     */
    public function createUser(UserEditableDto $userEditable): UserDto;

    /**
     * @param Uuid $userId
     * @param UserEditableDto $userEditable
     * @return UserDto
     *
     * @throws InvalidRequestException|UserNotFoundException|EmailAlreadyInUseException|Exception
     */
    public function updateUser(Uuid $userId, UserEditableDto $userEditable): UserDto;
}
