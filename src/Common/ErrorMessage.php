<?php

namespace App\Common;

use App\Dto\ErrorDto;
use Exception;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * ErrorMessage is responsible for wrapping error messages generating logic.
 */
class ErrorMessage {
    /**
     * @param Exception $error
     * @param SerializerInterface $serializer
     * @return string
     */
    public static function generate(Exception $error, SerializerInterface $serializer): string {
        $errorDTO = new ErrorDto($error);
        return $serializer->serialize($errorDTO, JsonEncoder::FORMAT);
    }
}
