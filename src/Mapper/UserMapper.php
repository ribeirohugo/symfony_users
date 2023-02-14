<?php

namespace App\Mapper;

use App\Dto\UserDto;
use App\Entity\User;

class UserMapper {
    /**
     * @param User $entity
     * @return UserDto
     */
    public static function EntityToDto(User $entity): UserDto {
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
    public static function EntityToDtoArray(array $entities): array {
        $values = [];

        foreach($entities as $entity) {
            $values[] = self::EntityToDto($entity);
        }

        return $values;
    }
}