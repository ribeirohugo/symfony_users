<?php

namespace App\Tests\Unit\DTO;

use App\DTO\ErrorDTO;
use PHPUnit\Framework\TestCase;

class ErrorDTOTest extends TestCase{
    const DEFAULT_ERROR_MESSAGE = "error message";
    const DEFAULT_ERROR_CODE = 20;

    public function testErrorDTOConstruct() {
        $exception = new \Exception(self::DEFAULT_ERROR_MESSAGE, self::DEFAULT_ERROR_CODE);

        $user = new ErrorDTO($exception);

        $this->assertIsObject($user);
        $this->assertEquals(self::DEFAULT_ERROR_MESSAGE, $user->getMessage());
        $this->assertEquals(self::DEFAULT_ERROR_CODE, $user->getCode());
    }

    public function testErrorDTOMessage() {
        $testException = new \Exception();

        $user = new ErrorDTO($testException);

        $user->setMessage(self::DEFAULT_ERROR_MESSAGE);

        $this->assertEquals(self::DEFAULT_ERROR_MESSAGE, $user->getMessage());
    }

    public function testErrorDTOCode() {
        $testException = new \Exception();

        $user = new ErrorDTO($testException);

        $user->setCode(self::DEFAULT_ERROR_CODE);

        $this->assertEquals(self::DEFAULT_ERROR_CODE, $user->getCode());
    }
}
