<?php

namespace App\Mapper;

use App\Dto\UserDto;
use App\Dto\UserEditableDto;
use App\Entity\User;

class UserMapper {
    /**
     * @param User $entity
     * @return UserDto
     */
    public static function entityToDto(User $entity): UserDto {
        return new UserDto(
            $entity->getName(),
            $entity->getEmail(),
            $entity->getPhone(),
            $entity->getCreatedAt(),
            $entity->getUpdatedAt(),
        );
    }

    /**
     * @param User[] $entities
     * @return UserDto[]
     */
    public static function entityToDtoArray(array $entities): array {
        $values = [];

        foreach($entities as $entity) {
            $values[] = self::entityToDto($entity);
        }

        return $values;
    }

    /**
     * @param UserEditableDto $userEditableDto
     * @return User
     */
    public static function userEditableDtoToEntity(UserEditableDto $userEditableDto): User {
        return new User(
            $userEditableDto->getName(),
            $userEditableDto->getEmail(),
            $userEditableDto->getPassword(),
            $userEditableDto->getPhone(),
        );
    }
}