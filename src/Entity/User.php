<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * User entity class.
 */
#[ORM\Table(name: 'users')]
#[ORM\Entity(repositoryClass: UserRepository::class)]
class User
{
    /**
     * @var int|null
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * @var string
     */
    #[ORM\Column(length: 255)]
    private string $name;

    /**
     * @var string
     */
    #[ORM\Column(length: 255)]
    private string $email;

    /**
     * @var string|null
     */
    #[ORM\Column(length: 255)]
    private ?string $password = null;

    /**
     * @var string
     */
    #[ORM\Column(length: 20)]
    private string $phone;

    /**
     * @var \DateTime
     */
    #[ORM\Column(length: 255)]
    private \DateTime $createdAt;

    /**
     * @var \DateTime|null
     */
    #[ORM\Column(length: 255)]
    private ?\DateTime $updatedAt = null;

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

        $this->createdAt = new \DateTime();
    }

    /**
     * @return int|null
     */
    public function getId(): ?int {
        return $this->id;
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
     * @return \DateTime
     */
    public function getCreatedAt(): \DateTime {
        return $this->createdAt;
    }

    /**
     * @param \DateTime|string $createdAt
     * @return void
     */
    public function setCreatedAt(\DateTime|string $createdAt): void {
        // Fix: deserialize set datetime error
        if(gettype($createdAt) == "string") {
            $this->createdAt = date_create_from_format(\DateTimeInterface::RFC3339, $createdAt);
            return;
        }

        $this->createdAt = $createdAt;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getUpdatedAt(): ?\DateTimeInterface {
        return $this->updatedAt;
    }

    /**
     * @param \DateTime|string $updatedAt
     * @return void
     */
    public function setUpdatedAt(\DateTime|string $updatedAt): void {
        // Fix: deserialize set datetime error
        if(gettype($updatedAt) == "string") {
            $this->createdAt = date_create_from_format(\DateTimeInterface::RFC3339, $updatedAt);
            return;
        }

        $this->updatedAt = $updatedAt;
    }
}
