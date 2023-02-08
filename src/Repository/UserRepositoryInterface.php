<?php

namespace App\Repository;

use App\Entity\User;

interface UserRepositoryInterface
{
    function find(int $userId);
    function findAll();
    function save(User $user, bool $flush = false): User;
    public function remove(User $user, bool $flush = false): void;
}
