<?php

namespace App\Service;

use App\Common\Password;
use App\Dto\LoginDto;
use App\Dto\UserDto;
use App\Dto\UserEditableDto;
use App\Exception\InvalidRequestException;
use App\Exception\UserNotFoundException;
use App\Mapper\UserMapper;
use App\Repository\UserRepositoryInterface;

/**
 * AuthenticationService holds user service layer and parses data from controller into the repository layer.
 */
class AuthenticationService implements AuthenticationInterface
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