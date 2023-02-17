<?php

namespace App\Entity;

/**
 * Roles class that holds existing role constants.
 */
class Roles {
    const ROLE_USER = "ROLE_USER";
    const ROLE_ADMIN = "ROLE_ADMIN";

    public static function isValid(string $role): bool {
        if($role == self::ROLE_USER || $role == self::ROLE_ADMIN) {
            return true;
        }

        return false;
    }
}