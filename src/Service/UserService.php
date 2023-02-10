<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\UserCreate;
use App\Exception\UserNotFoundException;
use App\Repository\UserRepositoryInterface;

class UserService implements UserServiceInterface {
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

    public function removeUser(User $user): void {
        $this->userRepository->remove($user, true);
    }

    public function createUser(UserCreate $userCreate): User {
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