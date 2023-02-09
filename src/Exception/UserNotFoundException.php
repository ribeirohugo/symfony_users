<?php

namespace App\Exception;

final class UserNotFoundException extends \Exception
{
    const DEFAULT_MESSAGE = 'The user with ID "%d" does not exist.';

    public function __construct(string $userId)
    {
        parent::__construct(sprintf(self::DEFAULT_MESSAGE, $userId));
    }
}
