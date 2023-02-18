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
    private ?string $name;

    /**
     * @var string|null
     */
    private ?string $email;

    /**
     * @var string|null
     */
    private ?string $password;

    /**
     * @var string|null
     */
    private ?string $phone;

    /**
     * @var array
     */
    private array $roles;

    /**
     * @param string $name
     * @param string $email
     * @param string $password
     * @param string $phone
     * @param array $roles
     */
    public function __construct(string $name, string $email, string $password, string $phone, array $roles = []) {
        $this->name = $name;
        $this->email = $email;
        $this->password = $password;
        $this->phone = $phone;
        $this->roles = $roles;
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

    /**
     * @return string[]
     */
    public function getRoles(): array {
        return $this->roles;
    }

    /**
     * @param array $roles
     * @return void
     */
    public function setRoles(array $roles): void {
        $this->roles = $roles;
    }
}
