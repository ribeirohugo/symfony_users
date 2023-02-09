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

        $this->userRepository->save($user);

        return $user;
    }
}