<?php

namespace App\Dto;

/**
 * UserEditableDto holds editable attributes for creating/updating a user.
 */
class UserEditableDto
{
    /**
     * @var string|null
     */
    private ?string $name = null;

    /**
     * @var string|null
     */
    private ?string $email = null;

    /**
     * @var string|null
     */
    private ?string $password = null;

    /**
     * @var string|null
     */
    private ?string $phone = null;

    /**
     * @param string $name
     * @param string $email
     * @param string $password
     * @param string $phone
     */
    public function __construct(string $name, string $email, string $password, string $phone) {
        $this->name = $name;
        $this->email = $email;
        $this->password = $password;
        $this->phone = $phone;
    }

    /**
     * @return string
     */
    public function getName(): string {
        return $this->name;
    }

    /**
     * @param string $name
     * @return void
     */
    public function setName(string $name): void {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getEmail(): string {
        return $this->email;
    }

    /**
     * @param string $email
     * @return void
     */
    public function setEmail(string $email): void {
        $this->email = $email;
    }

    /**
     * @return string
     */
    public function getPhone(): string {
        return $this->phone;
    }

    /**
     * @param string $phone
     * @return void
     */
    public function setPhone(string $phone): void {
        $this->phone = $phone;
    }

    /**
     * @return string
     */
    public function getPassword(): string {
        return $this->password;
    }

    /**
     * @param string $password
     * @return void
     */
    public function setPassword(string $password): void {
        $this->password = $password;
    }
}
