<?php

namespace App\Entity;

use App\Repository\WalletRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: WalletRepository::class)]
class Wallet
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'wallets')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'wallets')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Coin $coin = null;

    #[ORM\Column(type: 'decimal', precision: 28, scale: 8)]
    private ?string $qty = null;

    #[ORM\Column(type: 'decimal', precision: 28, scale: 8)]
    private ?string $priceAvg = null;

    /**
     * @var Collection<int, Transaction>
     */
    #[ORM\JoinColumn(nullable: false)]
    #[ORM\OneToMany(mappedBy: 'wallet', targetEntity: Transaction::class, cascade: ['persist', 'remove'])]
    private Collection $transactions;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column]
    private \DateTimeImmutable $updatedAt;

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

    public function getCoin(): ?Coin
    {
        return $this->coin;
    }

    public function setCoin(?Coin $coin): static
    {
        $this->coin = $coin;

        return $this;
    }

    public function getQty(): ?string
    {
        return $this->qty;
    }

    public function setQty(string $qty): static
    {
        $this->qty = $qty;

        return $this;
    }

    public function getPriceAvg(): ?string
    {
        return $this->priceAvg;
    }

    public function setPriceAvg(string $priceAvg): static
    {
        $this->priceAvg = $priceAvg;

        return $this;
    }

//    /**
//     * @return Collection<int, Transaction>
//     */
//    public function getTransactions(): Collection
//    {
//        return $this->transactions;
//    }
//
//    public function addTransaction(Transaction $transaction): static
//    {
//        if (!$this->transactions->contains($transaction)) {
//            $this->transactions->add($transaction);
//            $transaction->setWallet($this);
//        }
//
//        return $this;
//    }
//
//    public function removeTransaction(Transaction $transaction): static
//    {
//        if ($this->transactions->removeElement($transaction)) {
//            // set the owning side to null (unless already changed)
//            if ($transaction->getWallet() === $this) {
//                $transaction->setWallet(null);
//            }
//        }
//
//        return $this;
//    }

    public function getTotalValue(float $currentPrice): float
    {
        return (float)$this->qty * $currentPrice;
    }

    public function getPnl(float $currentPrice): float
    {
        return ((float)$currentPrice - (float)$this->avgPrice) * (float)$this->qty;
    }
}
