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
    const INVALID_JSON_FORMAT = "invalid json format request";
    const EMPTY_EMAIL = "no email was found";

    /**
     * @param Exception $error
     * @param SerializerInterface $serializer
     * @return string
     */
    public static function generateJSON(Exception $error, SerializerInterface $serializer): string {
        $errorDTO = new ErrorDto($error);
        return $serializer->serialize($errorDTO, JsonEncoder::FORMAT);
    }

    /**
     * @param SerializerInterface $serializer
     * @return string
     */
    public static function invalidFormatJSON(SerializerInterface $serializer): string {
        $exception = new Exception(
            self::INVALID_JSON_FORMAT
        );

        $errorDTO = new ErrorDto($exception);
        return $serializer->serialize($errorDTO, JsonEncoder::FORMAT);
    }

    /**
     * @param SerializerInterface $serializer
     * @return string
     */
    public static function emptyEmailJSON(SerializerInterface $serializer): string {
        $exception = new Exception(
            self::EMPTY_EMAIL
        );

        $errorDTO = new ErrorDto($exception);
        return $serializer->serialize($errorDTO, JsonEncoder::FORMAT);
    }
}
