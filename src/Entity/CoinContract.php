<?php

namespace App\Entity;

use App\Repository\CoinRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CoinRepository::class)]
#[ORM\HasLifecycleCallbacks]
class CoinContract
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private string $contractAddress;

    #[ORM\Column(length: 50)]
    private string $network = 'native';

    #[ORM\Column(nullable: true)]
    private ?string $localPrice = null;

    #[ORM\Column]
    private int $decimal;

    #[ORM\Column(updatable: false)]
    private \DateTimeImmutable $createdAt;
    #[ORM\Column]
    private \DateTimeImmutable $updatedAt;

    #[ORM\ManyToOne(targetEntity: Coin::class, inversedBy: 'coinContract', cascade: ['persist'])]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Coin $coin;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCoin(): Coin
    {
        return $this->coin;
    }

    public function setCoin(Coin $coin): static
    {
        $this->coin = $coin;

        return $this;
    }

    public function getLocalPrice(): ?string
    {
        return $this->localPrice;
    }

    public function setLocalPrice(string $price): static
    {
        $this->localPrice = $price;

        return $this;
    }

    public function getContractAddress(): ?string
    {
        return $this->contractAddress;
    }

    public function setContractAddress(?string $contractAddress): void
    {
        $this->contractAddress = $contractAddress;
    }

    public function getNetwork(): string
    {
        return $this->network;
    }

    public function setNetwork(string $network): void
    {
        $this->network = $network;
    }

    public function getDecimal(): int
    {
        return $this->decimal;
    }

    public function setDecimal(int $decimal): void
    {
        $this->decimal = $decimal;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    #[ORM\PreUpdate]
    public function autoUpdateUpdatedAt() : void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }
}
