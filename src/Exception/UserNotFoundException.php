<?php

namespace App\Exception;

use Exception;

/**
 * UserNotFoundException is thrown when a user there is no user matched with a given a search criteria.
 */
final class UserNotFoundException extends Exception
{
    const DEFAULT_MESSAGE = 'The user with ID "%d" does not exist.';

    /**
     * @param string $userId
     */
    public function __construct(string $userId)
    {
        parent::__construct(sprintf(self::DEFAULT_MESSAGE, $userId));
    }
}
