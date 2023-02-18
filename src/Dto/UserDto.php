<?php

namespace App\Dto;

use DateTimeInterface;

/**
 * UserDTO holds returnable data for a user.
 */
class UserDto
{
    /**
     * @var int
     */
    private int $id;

    /**
     * @var string
     */
    private string $name;

    /**
     * @var string
     */
    private string $email;

    /**
     * @var string
     */
    private string $phone;

    /**
     * @var DateTimeInterface|null
     */
    private ?DateTimeInterface $createdAt;

    /**
     * @var DateTimeInterface|null
     */
    private ?DateTimeInterface $updatedAt;

    /**
     * @var string[]
     */
    private array $roles;

    /**
     * @param int $id
     * @param string $name
     * @param string $email
     * @param string $phone
     * @param DateTimeInterface|null $createdAt
     * @param DateTimeInterface|null $updatedAt
     * @param array $roles
     */
    public function __construct(
        int                $id,
        string             $name,
        string             $email,
        string             $phone,
        ?DateTimeInterface $createdAt = null,
        ?DateTimeInterface $updatedAt = null,
        array $roles = [],
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->email = $email;
        $this->phone = $phone;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
        $this->roles = $roles;
    }

    /**
     * @return int
     */
    public function getId(): int {
        return $this->id;
    }

    /**
     * @param int $id
     * @return void
     */
    public function setId(int $id): void {
        $this->id = $id;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string {
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
     * @return string|null
     */
    public function getPhone(): ?string {
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

        /**
     * @return DateTimeInterface
     */
    public function getCreatedAt(): DateTimeInterface {
        return $this->createdAt;
    }

    /**
     * @param DateTimeInterface $createdAt
     * @return void
     */
    public function setCreatedAt(DateTimeInterface $createdAt): void {
        $this->createdAt = $createdAt;
    }

    /**
     * @return DateTimeInterface|null
     */
    public function getUpdatedAt(): ?DateTimeInterface {
        return $this->updatedAt;
    }

    /**
     * @param DateTimeInterface|null $updatedAt
     * @return void
     */
    public function setUpdatedAt(DateTimeInterface|null $updatedAt): void {
        $this->updatedAt = $updatedAt;
    }
}
