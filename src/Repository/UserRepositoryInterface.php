<?php

namespace App\Repository;

use App\Entity\User;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepositoryInterface;

interface UserRepositoryInterface extends ServiceEntityRepositoryInterface
{
    /**
     * @param int $userId
     * @return mixed
     */
    function find(int $userId);

    /**
     * @return mixed
     */
    function findAll();

    /**
     * @param User $user
     * @param bool $flush
     * @return User
     */
    function save(User $user, bool $flush = false): User;

    /**
     * @param User $user
     * @param bool $flush
     * @return void
     */
    public function remove(User $user, bool $flush = false): void;
}
