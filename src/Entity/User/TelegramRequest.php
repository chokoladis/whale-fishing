<?php

namespace App\Entity\User;

use App\Repository\User\TelegramRequestRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TelegramRequestRepository::class)]
#[ORM\HasLifecycleCallbacks]
class TelegramRequest
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'bigint')]
    private int $chatId;

    #[ORM\OneToOne(targetEntity: User::class, inversedBy: 'user', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn]
    private ?User $user = null;

    #[ORM\Column(length: 32)]
    private string $ipAddress;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

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

    public function getChatId(): int
    {
        return $this->chatId;
    }

    public function setChatId(int $chatId): static
    {
        $this->chatId = $chatId;

        return $this;
    }

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
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

    // todo
    public function isVerified()//: bool
    {
        // если верифицирован просто входит
        // с проверкой по телеге
        return $this->getUser()->getUserVerifications();
    }
}
