<?php

namespace App\Entity;

use App\Repository\CoinRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CoinRepository::class)]
class Coin
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private string $contractAddress;

    #[ORM\Column(length: 50)]
    private string $network = 'native'; //'eth-mainnet'

    #[ORM\Column(length: 20)]
    private string $symbol;

    #[ORM\Column(length: 64)]
    private string $name;

    #[ORM\Column(nullable: true)]
    private ?float $price = null;

    #[ORM\Column(nullable: false)]
    private int $decimal;

    /**
     * @var Collection<int, CoinLink>
     */
    #[ORM\OneToMany(targetEntity: CoinLink::class, mappedBy: 'coin')]
    private Collection $links;

    public function __construct()
    {
        $this->links = new ArrayCollection();
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

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): static
    {
        $this->price = $price;

        return $this;
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
}
