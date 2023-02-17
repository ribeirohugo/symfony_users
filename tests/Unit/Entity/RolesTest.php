<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Roles;
use PHPUnit\Framework\TestCase;

class RolesTest extends TestCase
{
    public function testIsValidAdmin()
    {
        $response = Roles::isValid(Roles::ROLE_ADMIN);

        $this->assertTrue($response);
    }

    public function testIsValidUser()
    {
        $response = Roles::isValid(Roles::ROLE_USER);

        $this->assertTrue($response);
    }
    public function testIsValidFail()
    {
        $response = Roles::isValid("");

        $this->assertFalse($response);
    }
}