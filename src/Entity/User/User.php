<?php

declare(strict_types=1);

namespace App\Entity\User;

use App\Enum\User\Role;
use App\Enum\User\Status;
use App\Enum\User\Channel;
use App\Repository\User\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\OneToMany;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_PHONE', fields: ['phone'])]
#[ORM\HasLifecycleCallbacks]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    const IDENTITY_FIELD = 'phone';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $name = null;

    #[ORM\Column(length: 180, nullable: true)]
    private ?string $email = null;

    // todo check by regexp \d{11,15}
    #[ORM\Column(length: 15, unique: true)]
    private string $phone;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [Role::USER->value];

    #[ORM\Column(length: 10)]
    private Status $status = Status::BLOCKED;

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(updatable: false)]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column()]
    private \DateTimeImmutable $updatedAt;

    /**
     * @var Collection<int, PasswordRestore>|null
     */
    #[OneToMany(mappedBy: 'user', targetEntity: PasswordRestore::class)]
    private ?Collection $passwordRestores;

    #[ORM\OneToMany(mappedBy: 'user', cascade: ['persist', 'remove'], targetEntity: UserVerification::class)]
    private ?Collection $userVerifications = null;

    #[ORM\OneToOne(targetEntity: TelegramRequest::class, mappedBy: 'user')]
    private ?TelegramRequest $telegramRequest = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
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
        return $this->phone;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = Role::USER->value;

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Ensure the session doesn't contain actual password hashes by CRC32C-hashing them, as supported since Symfony 7.3.
     */
    public function __serialize(): array
    {
        $data = (array)$this;
        $data["\0" . self::class . "\0password"] = hash('Argon2', $this->password);

        return $data;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name) : static
    {
        $this->name = $name;

        return $this;
    }

    public function __construct()
    {
        $this->passwordRestores = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    /**
     * @return Collection<int, PasswordRestore>
     */
    public function getPasswordRestores(): Collection
    {
        return $this->passwordRestores;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getStatus(): Status
    {
        return $this->status;
    }

    public function setStatus(Status $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function getUserVerifications(): ?Collection
    {
        return $this->userVerifications;
    }

    public function setUserVerification(?UserVerification $userVerification): static
    {
        if (!$this->userVerifications->contains($userVerification)) {
            $this->userVerifications->add($userVerification);
            $userVerification->setUser($this);
        }

        return $this;
    }

    public function getPhone(): string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): static
    {
        $this->phone = $phone;
        return $this;
    }

    // как вариант сделать запрос в репе
    public function isTelegramVerified(): bool
    {
        return $this->getUserVerifications()->exists(
            static fn (int $key, UserVerification $userVerification): bool
                => $userVerification->getType() === Channel::TELEGRAM && $userVerification->getVerifiedAt()
        );
    }

    public function getTelegramRequest(): ?TelegramRequest
    {
        return $this->telegramRequest;
    }
}
