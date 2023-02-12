<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\UserCreate;
use App\Exception\InvalidRequestException;
use App\Exception\UserNotFoundException;
use App\Repository\UserRepositoryInterface;

class UserService implements UserServiceInterface {
    const ERROR_EMPTY_USER_NAME = "user name should not be empty";
    const ERROR_EMPTY_USER_EMAIL = "user e-mail should not be empty";
    const ERROR_EMPTY_USER_PASSWORD = "user password should not be empty";

    private UserRepositoryInterface $userRepository;
    function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function findUser(int $userId): User {
        $user = $this->userRepository->find($userId);
        if(empty($user)) {
            throw new UserNotFoundException($userId);
        }

        return $user;
    }

    public function findAllUsers(): array {
        return $this->userRepository->findAll();
    }

    public function removeUser(int $userId): void {
        $user = $this->userRepository->find($userId);
        if(empty($user)) {
            throw new UserNotFoundException($userId);
        }

        $this->userRepository->remove($user, true);
    }

    public function createUser(UserCreate $userCreate): User {
        if($userCreate->getName() == "") {
            throw new InvalidRequestException(self::ERROR_EMPTY_USER_NAME);
        }
        if($userCreate->getEmail() == "") {
            throw new InvalidRequestException(self::ERROR_EMPTY_USER_EMAIL);
        }
        if($userCreate->getPassword() == "") {
            throw new InvalidRequestException(self::ERROR_EMPTY_USER_PASSWORD);
        }

        $user = new User(
            $userCreate->getName(),
            $userCreate->getEmail(),
            $userCreate->getPassword(),
            $userCreate->getPhone(),
        );

        return $this->userRepository->save($user, true);
    }

    public function updateUser(int $userId, UserCreate $userCreate): User {
        $user = $this->userRepository->find($userId);
        if(empty($user)) {
            throw new UserNotFoundException($userId);
        }

        $user->setName($userCreate->getName());
        $user->setEmail($userCreate->getEmail());
        $user->setPhone($userCreate->getPhone());
        $user->setUpdatedAt(new \DateTime());

        if($userCreate->getPassword()!="") {
            $user->setPassword($userCreate->getPassword());
        }

        $this->userRepository->save($user, true);

        return $user;
    }
}