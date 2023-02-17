<?php

namespace App\Tests\Unit\Dto;

use App\Dto\LoginDto;
use App\Tests\Utils\ConstHelper;
use PHPUnit\Framework\TestCase;

class LoginDtoTest extends TestCase{
    public function testLoginDtoConstruct() {
        $loginDto = new LoginDto(ConstHelper::USER_EMAIL_TEST, ConstHelper::USER_PASSWORD_TEST);

        $this->assertIsObject($loginDto);
        $this->assertEquals(ConstHelper::USER_EMAIL_TEST, $loginDto->getEmail());
        $this->assertEquals(ConstHelper::USER_PASSWORD_TEST, $loginDto->getPassword());
    }

    public function testLoginEmail() {
        $loginDto = new LoginDto("", "");

        $loginDto->setEmail(ConstHelper::USER_EMAIL_TEST);

        $this->assertEquals(ConstHelper::USER_EMAIL_TEST, $loginDto->getEmail());
    }

    public function testLoginPassword() {
        $loginDto = new LoginDto("", "");

        $loginDto->setPassword(ConstHelper::USER_PASSWORD_TEST);

        $this->assertEquals(ConstHelper::USER_PASSWORD_TEST, $loginDto->getPassword());
    }
}
