<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'users')]
#[ORM\Entity(repositoryClass: UserRepository::class)]
class User
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    private ?string $password = null;

    #[ORM\Column(length: 20)]
    private ?string $phone = null;

    #[ORM\Column(length: 255)]
    private \DateTime $createdAt;

    #[ORM\Column(length: 255)]
    private ?\DateTime $updatedAt = null;

    public function __construct(string $name, string $email, string $password, string $phone) {
        $this->name = $name;
        $this->email = $email;
        $this->password = $password;
        $this->phone = $phone;

        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int {
        return $this->id;
    }

    public function getName(): string {
        return $this->name;
    }

    public function setName(string $name): void {
        $this->name = $name;
    }

    public function getEmail(): string {
        return $this->email;
    }

    public function setEmail(string $email): void {
        $this->email = $email;
    }

    public function getPhone(): string {
        return $this->phone;
    }

    public function setPhone(string $phone): void {
        $this->phone = $phone;
    }

    public function getPassword(): string {
        return $this->password;
    }

    public function setPassword(string $password): void {
        $this->password = $password;
    }

    public function getCreatedAt(): \DateTime {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime|string $createdAt): void {
        // Fix deserialize error
        if(gettype($createdAt) == "string") {
            $this->createdAt = date_create_from_format(\DateTimeInterface::RFC3339, $createdAt);
            return;
        }

        $this->createdAt = $createdAt;
    }

    public function getUpdatedAt(): \DateTime {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTime|string $updatedAt): void {
        // Fix deserialize error
        if(gettype($updatedAt) == "string") {
            $this->createdAt = date_create_from_format(\DateTimeInterface::RFC3339, $updatedAt);
            return;
        }

        $this->updatedAt = $updatedAt;
    }
}
