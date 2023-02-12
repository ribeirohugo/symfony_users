<?php

namespace App\Common;

use App\DTO\ErrorDTO;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\SerializerInterface;

class ErrorMessage {
    public static function generate(\Exception $error, SerializerInterface $serializer): string {
        $errorDTO = new ErrorDTO($error);
        return $serializer->serialize($errorDTO, JsonEncoder::FORMAT);
    }
}