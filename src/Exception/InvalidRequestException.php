<?php

namespace App\Exception;

final class InvalidRequestException extends \Exception
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}
