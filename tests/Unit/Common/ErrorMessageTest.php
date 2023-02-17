<?php

namespace App\Tests\Unit\Common;

use App\Common\ErrorMessage;
use App\Dto\ErrorDto;
use Exception;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class ErrorMessageTest extends TestCase
{
    /**
     * @var Serializer
     */
    private Serializer $serializer;

    protected function setUp(): void
    {
        $encoders = array(new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());

        $this->serializer = new Serializer($normalizers, $encoders);
    }

    public function testGenerateJSON() {
        $exception = new Exception(
            ErrorMessage::INVALID_JSON_FORMAT
        );

        $errorDTO = new ErrorDto($exception);
        $expectedJSON = $this->serializer->serialize($errorDTO, JsonEncoder::FORMAT);

        $response = ErrorMessage::generateJSON($exception, $this->serializer);

        $this->assertEquals($expectedJSON, $response);
    }

    public function testInvalidFormatJSON() {
        $exception = new Exception(
            ErrorMessage::INVALID_JSON_FORMAT
        );

        $errorDTO = new ErrorDto($exception);
        $expectedJSON = $this->serializer->serialize($errorDTO, JsonEncoder::FORMAT);

        $response = ErrorMessage::invalidFormatJSON($this->serializer);

        $this->assertEquals($expectedJSON, $response);
    }

    public function testEmptyEmailJSON() {
        $exception = new Exception(
            ErrorMessage::EMPTY_EMAIL
        );

        $errorDTO = new ErrorDto($exception);
        $expectedJSON = $this->serializer->serialize($errorDTO, JsonEncoder::FORMAT);

        $response = ErrorMessage::emptyEmailJSON($this->serializer);

        $this->assertEquals($expectedJSON, $response);
    }

    public function testAuthenticationFailed() {
        $exception = new Exception(
            ErrorMessage::INVALID_AUTHENTICATION
        );

        $errorDTO = new ErrorDto($exception);
        $expectedJSON = $this->serializer->serialize($errorDTO, JsonEncoder::FORMAT);

        $response = ErrorMessage::authenticationFailed($this->serializer);

        $this->assertEquals($expectedJSON, $response);
    }

    public function testInternalError() {
        $exception = new Exception(
            ErrorMessage::INTERNAL_ERROR
        );

        $errorDTO = new ErrorDto($exception);
        $expectedJSON = $this->serializer->serialize($errorDTO, JsonEncoder::FORMAT);

        $response = ErrorMessage::internalError($this->serializer);

        $this->assertEquals($expectedJSON, $response);
    }
}