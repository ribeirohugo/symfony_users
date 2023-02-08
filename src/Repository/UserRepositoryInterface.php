<?php

namespace App\Repository;

use App\Entity\User;

interface UserRepositoryInterface extends RepositoryInterface
{
    function find(int $userId);
    function findAll();
    function save(User $user, bool $flush = false): User;
    public function remove(User $user, bool $flush = false): void;
}
