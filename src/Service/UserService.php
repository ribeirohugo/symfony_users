<?php

namespace App\Service;

use App\Dto\UserEditableDto;
use App\Entity\User;
use App\Exception\InvalidRequestException;
use App\Exception\UserNotFoundException;
use App\Repository\UserRepositoryInterface;

/**
 * UserService holds user service layer and parses data from controller into the repository layer.
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
     * @return User
     * @throws UserNotFoundException
     */
    public function findUser(int $userId): User {
        $user = $this->userRepository->find($userId);
        if(empty($user)) {
            throw new UserNotFoundException($userId);
        }

        return $user;
    }

    /**
     * @param string $email
     * @return User
     * @throws UserNotFoundException
     */
    public function findUserByEmail(string $email): User {
        $user = $this->userRepository->findOneBy(["email" => $email]);

        if(empty($user)) {
            throw new UserNotFoundException($email);
        }

        return $user;
    }

    /**
     * @return array
     */
    public function findAllUsers(): array {
        return $this->userRepository->findAll();
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
     * @param \App\Dto\UserEditableDto $userCreate
     * @return User
     * @throws InvalidRequestException
     */
    public function createUser(UserEditableDto $userCreate): User {
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

    /**
     * @param int $userId
     * @param \App\Dto\UserEditableDto $userCreate
     * @return User
     * @throws InvalidRequestException
     * @throws UserNotFoundException
     */
    public function updateUser(int $userId, UserEditableDto $userCreate): User {
        if($userCreate->getName() == "") {
            throw new InvalidRequestException(self::ERROR_EMPTY_USER_NAME);
        }
        if($userCreate->getEmail() == "") {
            throw new InvalidRequestException(self::ERROR_EMPTY_USER_EMAIL);
        }

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