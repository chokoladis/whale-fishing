<?php

namespace App\Entity;

use App\Entity\User\User;
use App\Repository\TelegramLinkRequestRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: TelegramLinkRequestRepository::class)]
#[ORM\HasLifecycleCallbacks]
class TelegramLinkRequest
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'uuid')]
    private ?Uuid $uuid = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?User $userId = null;


    #[ORM\Column(nullable: true)]
    private ?int $chatId = null;

    #[ORM\Column]
    private string $ipAddress;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $activatedAt = null;

    #[ORM\Column]
    private \DateTimeImmutable $expiredAt;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUuid(): ?Uuid
    {
        return $this->uuid;
    }

    public function setUuid(Uuid $uuid): static
    {
        $this->uuid = $uuid;

        return $this;
    }

    public function getUserId(): ?User
    {
        return $this->userId;
    }

    public function setUserId(?User $userId): static
    {
        $this->userId = $userId;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getActivatedAt(): ?\DateTimeImmutable
    {
        return $this->activatedAt;
    }

    public function setActivatedAt(?\DateTimeImmutable $activatedAt): static
    {
        $this->activatedAt = $activatedAt;

        return $this;
    }

    public function getChatId(): ?int
    {
        return $this->chatId;
    }

    public function setChatId(?int $chatId): static
    {
        $this->chatId = $chatId;

        return $this;
    }

    public function getExpiredAt(): ?\DateTimeImmutable
    {
        return $this->expiredAt;
    }

    public function setExpiredAt(\DateTimeImmutable $expiredAt): static
    {
        $this->expiredAt = $expiredAt;

        return $this;
    }

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->expiredAt = new \DateTimeImmutable('+1 hour');
    }

    public function getIpAddress(): string
    {
        return $this->ipAddress;
    }

    public function setIpAddress(string $ipAddress): static
    {
        $this->ipAddress = $ipAddress;
        return $this;
    }
}
