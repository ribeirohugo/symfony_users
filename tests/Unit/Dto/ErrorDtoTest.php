<?php

namespace App\Tests\Unit\Dto;

use App\Dto\ErrorDto;
use Exception;
use PHPUnit\Framework\TestCase;

class ErrorDtoTest extends TestCase{
    const DEFAULT_ERROR_MESSAGE = "error message";
    const DEFAULT_ERROR_CODE = 20;

    public function testErrorDtoConstruct() {
        $exception = new Exception(self::DEFAULT_ERROR_MESSAGE, self::DEFAULT_ERROR_CODE);

        $user = new ErrorDto($exception);

        $this->assertIsObject($user);
        $this->assertEquals(self::DEFAULT_ERROR_MESSAGE, $user->getMessage());
        $this->assertEquals(self::DEFAULT_ERROR_CODE, $user->getCode());
    }

    public function testErrorDtoMessage() {
        $testException = new Exception();

        $user = new ErrorDto($testException);

        $user->setMessage(self::DEFAULT_ERROR_MESSAGE);

        $this->assertEquals(self::DEFAULT_ERROR_MESSAGE, $user->getMessage());
    }

    public function testErrorDtoCode() {
        $testException = new Exception();

        $user = new ErrorDto($testException);

        $user->setCode(self::DEFAULT_ERROR_CODE);

        $this->assertEquals(self::DEFAULT_ERROR_CODE, $user->getCode());
    }
}
