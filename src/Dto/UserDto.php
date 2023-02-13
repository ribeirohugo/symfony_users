<?php

namespace App\Dto;

use DateTimeInterface;

/**
 * UserDTO holds returnable data for a user.
 */
class UserDto
{
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
     * @param string $name
     * @param string $email
     * @param string $phone
     * @param DateTimeInterface|null $createdAt
     * @param DateTimeInterface|null $updatedAt
     */
    public function __construct(
        string             $name,
        string             $email,
        string             $phone,
        ?DateTimeInterface $createdAt = null,
        ?DateTimeInterface $updatedAt = null,
    ) {
        $this->name = $name;
        $this->email = $email;
        $this->phone = $phone;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
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
     * @return DateTimeInterface
     */
    public function getCreatedAt(): DateTimeInterface {
        return $this->createdAt;
    }

    /**
     * @param DateTimeInterface|string $createdAt
     * @return void
     */
    public function setCreatedAt(DateTimeInterface|string $createdAt): void {
        // Fix: deserialize set datetime error
        if(gettype($createdAt) == "string") {
            $this->createdAt = date_create_from_format(DateTimeInterface::RFC3339, $createdAt);
            return;
        }

        $this->createdAt = $createdAt;
    }

    /**
     * @return DateTimeInterface|null
     */
    public function getUpdatedAt(): ?DateTimeInterface {
        return $this->updatedAt;
    }

    /**
     * @param DateTimeInterface|string|null $updatedAt
     * @return void
     */
    public function setUpdatedAt(DateTimeInterface|string|null $updatedAt): void {
        // Fix: deserialize set datetime error
        if(gettype($updatedAt) == "string") {
            $this->createdAt = date_create_from_format(DateTimeInterface::RFC3339, $updatedAt);
            return;
        }

        $this->updatedAt = $updatedAt;
    }
}
