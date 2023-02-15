<?php

namespace App\Service;

use App\Common\Password;
use App\Dto\UserDto;
use App\Dto\UserEditableDto;
use App\Exception\InvalidRequestException;
use App\Exception\UserNotFoundException;
use App\Mapper\UserMapper;
use App\Repository\UserRepositoryInterface;

/**
 * UserService holds user service logic and maps data between controller and repository.
 */
class UserService implements UserServiceInterface {
    const ERROR_EMPTY_USER_NAME = "user name should not be empty";
    const ERROR_EMPTY_USER_EMAIL = "user e-mail should not be empty";
    const ERROR_EMPTY_USER_PASSWORD = "user password should not be empty";

    /**
     * @var UserRepositoryInterface
     */
    private UserRepositoryInterface $userRepository;

    /**
     * @param UserRepositoryInterface $userRepository
     */
    function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @param int $userId
     * @return UserDto
     * @throws UserNotFoundException
     */
    public function findUser(int $userId): UserDto {
        $user = $this->userRepository->find($userId);
        if(empty($user)) {
            throw new UserNotFoundException($userId);
        }

        return UserMapper::entityToDto($user);
    }

    /**
     * @param string $email
     * @return UserDto
     * @throws UserNotFoundException
     */
    public function findUserByEmail(string $email): UserDto {
        $user = $this->userRepository->findOneBy(["email" => $email]);

        if(empty($user)) {
            throw new UserNotFoundException($email);
        }

        return UserMapper::entityToDto($user);
    }

    /**
     * @return UserDto[]
     */
    public function findAllUsers(): array {
        $users = $this->userRepository->findAll();

        return UserMapper::entityToDtoArray($users);
    }

    /**
     * @param int $userId
     * @return void
     * @throws UserNotFoundException
     */
    public function removeUser(int $userId): void {
        $user = $this->userRepository->find($userId);
        if(empty($user)) {
            throw new UserNotFoundException($userId);
        }

        $this->userRepository->remove($user, true);
    }

    /**
     * @param UserEditableDto $userEditable
     * @return UserDto
     * @throws InvalidRequestException
     */
    public function createUser(UserEditableDto $userEditable): UserDto {
        if($userEditable->getName() == "") {
            throw new InvalidRequestException(self::ERROR_EMPTY_USER_NAME);
        }
        if($userEditable->getEmail() == "") {
            throw new InvalidRequestException(self::ERROR_EMPTY_USER_EMAIL);
        }
        if($userEditable->getPassword() == "") {
            throw new InvalidRequestException(self::ERROR_EMPTY_USER_PASSWORD);
        }

        $user = UserMapper::userEditableDtoToEntity($userEditable);

        $passwordHasher = Password::autoUserHasher();

        $hashedPassword = $passwordHasher->hashPassword($user, $user->getPassword());
        $user->setPassword($hashedPassword);

        $newUser = $this->userRepository->save($user, true);

        return UserMapper::entityToDto($newUser);
    }

    /**
     * @param int $userId
     * @param UserEditableDto $userEditable
     * @return UserDto
     * @throws InvalidRequestException
     * @throws UserNotFoundException
     */
    public function updateUser(int $userId, UserEditableDto $userEditable): UserDto {
        if($userEditable->getName() == "") {
            throw new InvalidRequestException(self::ERROR_EMPTY_USER_NAME);
        }
        if($userEditable->getEmail() == "") {
            throw new InvalidRequestException(self::ERROR_EMPTY_USER_EMAIL);
        }

        $user = $this->userRepository->find($userId);
        if(empty($user)) {
            throw new UserNotFoundException($userId);
        }

        $user->setName($userEditable->getName());
        $user->setEmail($userEditable->getEmail());
        $user->setPhone($userEditable->getPhone());
        $user->setUpdatedAt(new \DateTime());

        if($userEditable->getPassword()!="") {
            $user->setPassword($userEditable->getPassword());
        }

        $passwordHasher = Password::autoUserHasher();

        $hashedPassword = $passwordHasher->hashPassword($user, $user->getPassword());
        $user->setPassword($hashedPassword);

        $updatedUser = $this->userRepository->save($user, true);

        return UserMapper::entityToDto($updatedUser);
    }
}