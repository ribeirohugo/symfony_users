<?php

namespace App\Dto;

use Exception;

/**
 * ErrorDto holds basic error data.
 */
class ErrorDto
{
    /**
     * @var string
     */
    private string $message;

    /**
     * @var int|mixed
     */
    private int $code;

    /**
     * @param Exception $exception
     */
    public function __construct(Exception $exception) {
        $this->message = $exception->getMessage();
        $this->code = $exception->getCode();
    }

    /**
     * @return string
     */
    public function getMessage(): string {
        return $this->message;
    }

    /**
     * @param string $message
     * @return void
     */
    public function setMessage(string $message): void {
        $this->message = $message;
    }

    /**
     * @return int
     */
    public function getCode(): int {
        return $this->code;
    }

    /**
     * @param string $code
     * @return void
     */
    public function setCode(string $code): void {
        $this->code = $code;
    }
}
