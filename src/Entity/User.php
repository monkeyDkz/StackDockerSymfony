<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Uid\UuidV7;
use Serializable;
use Symfony\Component\Validator\Constraints as Assert;



#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface, Serializable
{
    public const ROLES = [
        'ROLE_USER' => 'ROLE_USER',
        'ROLE_ADMIN' => 'ROLE_ADMIN',
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private ?UuidV7 $id = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Assert\NotBlank(message: "Veuillez renseigner votre email")]
    #[Assert\Email(message: "Veuillez renseigner un email valide")]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $lastname = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $firstname = null;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $externalId = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $password = null;

    #[ORM\Column]
    private bool $isVerified = false;



    public function getId(): ?UuidV7
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        return $this->roles ?? ['ROLE_USER'];
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): self
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(?string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getFullName(bool $revert = false): string
    {
        if (!$this->firstname && !$this->lastname) {
            return "({$this->email})";
        }
        if ($revert) {
            return $this->lastname . ' ' . $this->firstname;
        }
        return $this->firstname . ' ' . $this->lastname;
    }

    public function getExternalId(): ?array
    {
        return $this->externalId;
    }

    public function setExternalId(?array $externalId): self
    {
        $this->externalId = $externalId;

        return $this;
    }

    public function addExternalId(string $provider, string $externalId): self
    {
        $externalId = $provider . ':' . $externalId;
        if (!$this->externalId) {
            $this->externalId = [];
        }

        if (!in_array($externalId, $this->externalId)) {
            $this->externalId[] = $externalId;
        }

        return $this;
    }

    public function hasExternalId(string $provider): bool
    {
        if (!$this->externalId) {
            return false;
        }
        return count(array_filter($this->externalId, fn (string $externalId) => str_starts_with($externalId, $provider))) > 0;
    }

    public function removeExternalId(string $provider): self
    {
        $this->externalId = array_values(array_filter($this->externalId, fn (string $externalId) => !str_starts_with($externalId, $provider)));

        return $this;
    }

    public function serialize()
    {
        return serialize([
            "id" => $this->id,
            "email" => $this->email,
            "externalId" => $this->externalId,
            "roles" => $this->roles,
            "lastname" => $this->lastname,
            "firstname" => $this->firstname,
            "password" => $this->password,

        ]);
    }

    public function unserialize(string $stringData)
    {
        $data = unserialize($stringData);

        $this->id = $data["id"] ?? null;
        $this->email = $data["email"] ?? null;
        $this->externalId = $data["externalId"] ?? null;
        $this->roles = $data["roles"] ?? null;
        $this->lastname = $data["lastname"] ?? null;
        $this->firstname = $data["firstname"] ?? null;
        $this->password = $data["password"] ?? null;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): static
    {
        $this->isVerified = $isVerified;

        return $this;
    }
}
