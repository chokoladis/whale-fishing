<?php

namespace App\Entity;

use App\Repository\WalletRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: WalletRepository::class)]
class Wallet
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 64, unique: true)]
    private string $address;

    /**
     * @var Collection<int, WalletCoin> $walletCoins
     */
    #[ORM\OneToMany(mappedBy: 'wallet', targetEntity: WalletCoin::class, cascade: ['persist', 'remove'])]
    private Collection $walletCoins;


    /**
     * @var Collection<int, Transaction>
     */
    #[ORM\JoinColumn(nullable: false)]
    #[ORM\OneToMany(mappedBy: 'wallet', targetEntity: Transaction::class, cascade: ['persist', 'remove'])]
    private Collection $transactions;

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection<int, Transaction>
     */
    public function getTransactions(): Collection
    {
        return $this->transactions;
    }

    public function addTransaction(Transaction $transaction): static
    {
        if (!$this->transactions->contains($transaction)) {
            $this->transactions->add($transaction);
            $transaction->setWallet($this);
        }

        return $this;
    }

    public function removeTransaction(Transaction $transaction): static
    {
        if ($this->transactions->removeElement($transaction)) {
            // set the owning side to null (unless already changed)
            if ($transaction->getWallet() === $this) {
                $transaction->setWallet(null);
            }
        }

        return $this;
    }

    public function getAddress(): string
    {
        return $this->address;
    }

    public function setAddress(string $address): void
    {
        $this->address = $address;
    }

    public function __construct()
    {
        $this->walletCoins = new ArrayCollection;
    }
}
