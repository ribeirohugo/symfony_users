<?php

namespace App\Repository;

use App\Entity\User;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepositoryInterface;

interface UserRepositoryInterface extends ServiceEntityRepositoryInterface
{
    function find(int $userId);

    function findAll();

    function save(User $user, bool $flush = false): User;

    public function remove(User $user, bool $flush = false): void;
}
