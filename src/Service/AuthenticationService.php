<?php

namespace App\Service;

use App\Common\Password;
use App\Dto\LoginDto;
use App\Dto\UserDto;
use App\Exception\UserNotFoundException;
use App\Mapper\UserMapper;
use App\Repository\UserRepositoryInterface;

/**
 * AuthenticationService holds login service logic and maps data between controller and repository.
 */
class AuthenticationService implements AuthenticationServiceInterface
{
    /**
     * @var UserRepositoryInterface
     */
    private UserRepositoryInterface $userRepository;

    /**
     * @param UserRepositoryInterface $userRepository
     */
    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @param LoginDto $loginDto
     * @return UserDto|bool
     * @throws UserNotFoundException
     */
    public function login(LoginDto $loginDto): UserDto|bool
    {
        $user = $this->userRepository->findOneBy(["email" => $loginDto->getEmail()]);
        if(empty($user)) {
            throw new UserNotFoundException($loginDto->getEmail());
        }

        $hasher = Password::autoUserHasher();

        $isValid = $hasher->isPasswordValid($user, $loginDto->getPassword());
        if($isValid) {
            return UserMapper::entityToDto($user);
        }

        return false;
    }
}