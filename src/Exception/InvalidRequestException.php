<?php

namespace App\Exception;

use Exception;

/**
 * InvalidRequestException is thrown when an HTTP request doesn't fulfill business logic or syntax requirements.
 */
final class InvalidRequestException extends Exception
{
    /**
     * @param string $message
     */
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}
