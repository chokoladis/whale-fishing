<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\UniqueConstraint(columns: ['wallet_id', 'coin_id'])]
#[ORM\HasLifecycleCallbacks]
class WalletCoin
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Wallet::class, inversedBy: 'walletCoins')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Wallet $wallet;

    #[ORM\ManyToOne(targetEntity: Coin::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Coin $coin;

    #[ORM\Column(type: 'decimal', precision: 28, scale: 8)]
    private string $balance = '0';

    #[ORM\Column(type: 'decimal', precision: 28, scale: 8)]
    private string $avgPrice = '0';

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;
    #[ORM\Column]
    private \DateTimeImmutable $updatedAt;

    public function __construct()
    {
        $this->createdAt   = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function setUpdatedAtValue(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getWallet(): Wallet
    {
        return $this->wallet;
    }

    public function setWallet(Wallet $wallet): void
    {
        $this->wallet = $wallet;
    }

    public function getCoin(): Coin
    {
        return $this->coin;
    }

    public function setCoin(Coin $coin): void
    {
        $this->coin = $coin;
    }

    public function getBalance(): string
    {
        return $this->balance;
    }

    public function setBalance(string $balance): void
    {
        $this->balance = $balance;
    }

    public function getAvgPrice(): string
    {
        return $this->avgPrice;
    }

    public function setAvgPrice(string $avgPrice): void
    {
        $this->avgPrice = $avgPrice;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getTotalValue(float $currentPrice): float
    {
        return (float)$this->balance * $currentPrice;
    }

    public function getPnl(float $currentPrice): float
    {
        return ($currentPrice - (float)$this->avgPrice) * (float)$this->balance;
    }
}
