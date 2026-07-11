<?php

namespace App\Entity;

use App\Repository\CoinRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CoinRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Coin
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 20)]
    private string $symbol;

    #[ORM\Column(length: 64)]
    private string $name;

    #[ORM\Column(nullable: true)]
    private ?float $avgPrice = null;

    /**
     * @var Collection<int, CoinLink>
     */
    #[ORM\OneToMany(targetEntity: CoinLink::class, mappedBy: 'coin')]
    private Collection $links;

    #[ORM\Column(updatable: false)]
    private \DateTimeImmutable $createdAt;
    #[ORM\Column]
    private \DateTimeImmutable $updatedAt;

    #[ORM\OneToOne(mappedBy: 'Coin', cascade: ['persist', 'remove'])]
    private ?CoinDetail $coinDetail = null;

    /**
     * @var Collection<CoinContract> $coinContract
     */
    #[ORM\OneToMany(mappedBy: 'Coin', targetEntity: CoinContract::class, cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Collection $coinContract;

    public function __construct()
    {
        $this->links = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSymbol(): ?string
    {
        return $this->symbol;
    }

    public function setSymbol(string $symbol): static
    {
        $this->symbol = $symbol;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getAvgPrice(): ?float
    {
        return $this->avgPrice;
    }

    public function setAvgPrice(float $avgPrice): static
    {
        $this->avgPrice = $avgPrice;

        return $this;
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

    /**
     * @return Collection<int, CoinLink>
     */
    public function getLinks(): Collection
    {
        return $this->links;
    }

    public function addLink(CoinLink $link): static
    {
        if (!$this->links->contains($link)) {
            $this->links->add($link);
            $link->setCoin($this);
        }

        return $this;
    }

    public function removeLink(CoinLink $link): static
    {
        if ($this->links->removeElement($link)) {
            // set the owning side to null (unless already changed)
            if ($link->getCoin() === $this) {
                $link->setCoin(null);
            }
        }

        return $this;
    }

    public function getCoinDetail(): ?CoinDetail
    {
        return $this->coinDetail;
    }

    public function setCoinDetail(CoinDetail $coinDetail): static
    {
        // set the owning side of the relation if necessary
        if ($coinDetail->getCoin() !== $this) {
            $coinDetail->setCoin($this);
        }

        $this->coinDetail = $coinDetail;

        return $this;
    }

    public function getCoinContract(): Collection
    {
        return $this->coinContract;
    }
}
