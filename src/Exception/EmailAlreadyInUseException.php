<?php

namespace App\Exception;

use Exception;

/**
 * EmailAlreadyInUseException is thrown when there is an e-mail unique key constraint infringement.
 */
final class EmailAlreadyInUseException extends Exception
{
    const DEFAULT_MESSAGE = 'The inserted e-mail address is already in use.';

    public function __construct()
    {
        parent::__construct(self::DEFAULT_MESSAGE);
    }
}
