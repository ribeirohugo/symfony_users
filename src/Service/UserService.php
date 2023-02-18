<?php

namespace App\Service;

use App\Common\Password;
use App\Dto\UserDto;
use App\Dto\UserEditableDto;
use App\Exception\EmailAlreadyInUseException;
use App\Exception\InvalidRequestException;
use App\Exception\UserNotFoundException;
use App\Mapper\UserMapper;
use App\Repository\UserRepositoryInterface;
use DateTime;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Component\Uid\Uuid;

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
     * @param Uuid $userId
     * @return UserDto
     * @throws UserNotFoundException
     */
    public function findUser(Uuid $userId): UserDto {
        $user = $this->userRepository->findOneBy(["externalId" => $userId]);
        if(empty($user)) {
            throw new UserNotFoundException($userId->toRfc4122());
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
     * @param Uuid $userId
     * @return void
     *
     * @throws UserNotFoundException
     */
    public function removeUser(Uuid $userId): void {
        $user = $this->userRepository->findOneBy(["externalId" => $userId]);
        if(empty($user)) {
            throw new UserNotFoundException($userId->toRfc4122());
        }

        $this->userRepository->remove($user, true);
    }

    /**
     * @param UserEditableDto $userEditable
     * @return UserDto
     *
     * @throws InvalidRequestException|EmailAlreadyInUseException
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
        $user->setExternalId(Uuid::v4());

        $passwordHasher = Password::autoUserHasher();
        $hashedPassword = $passwordHasher->hashPassword($user, $user->getPassword());
        $user->setPassword($hashedPassword);

        try {
            $newUser = $this->userRepository->save($user, true);
        } catch (UniqueConstraintViolationException) {
            throw new EmailAlreadyInUseException();
        }

        return UserMapper::entityToDto($newUser);
    }

    /**
     * @param Uuid $userId
     * @param UserEditableDto $userEditable
     * @return UserDto
     *
     * @throws UserNotFoundException|InvalidRequestException|EmailAlreadyInUseException
     */
    public function updateUser(Uuid $userId, UserEditableDto $userEditable): UserDto {
        if($userEditable->getName() == "") {
            throw new InvalidRequestException(self::ERROR_EMPTY_USER_NAME);
        }
        if($userEditable->getEmail() == "") {
            throw new InvalidRequestException(self::ERROR_EMPTY_USER_EMAIL);
        }

        $user = $this->userRepository->findOneBy(["externalId" => $userId]);
        if(empty($user)) {
            throw new UserNotFoundException($userId->toRfc4122());
        }

        $user->setName($userEditable->getName());
        $user->setEmail($userEditable->getEmail());
        $user->setPhone($userEditable->getPhone());
        $user->setRoles($userEditable->getRoles());
        $user->setUpdatedAt(new DateTime());

        if($userEditable->getPassword()!="") {
            $user->setPassword($userEditable->getPassword());
        }

        $passwordHasher = Password::autoUserHasher();

        $hashedPassword = $passwordHasher->hashPassword($user, $user->getPassword());
        $user->setPassword($hashedPassword);

        try {
            $updatedUser = $this->userRepository->save($user, true);
        } catch (UniqueConstraintViolationException) {
            throw new EmailAlreadyInUseException();
        }

        return UserMapper::entityToDto($updatedUser);
    }
}