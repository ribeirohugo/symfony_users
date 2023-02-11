<?php

namespace App\DTO;

class ErrorDTO
{
    private string $message = "";

    private int $code;

    public function __construct(\Exception $exception) {
        $this->message = $exception->getMessage();
        $this->code = $exception->getCode();
    }

    public function getMessage(): string {
        return $this->message;
    }

    public function setMessage(string $message): void {
        $this->message = $message;
    }

    public function getCode(): int {
        return $this->code;
    }

    public function setCode(string $code): void {
        $this->code = $code;
    }
}
