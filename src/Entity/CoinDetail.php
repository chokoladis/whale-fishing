<?php

namespace App\Entity;

use App\Repository\CoinDetailRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CoinDetailRepository::class)]
class CoinDetail
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'coinDetail', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Coin $coin = null;

    #[ORM\Column(nullable: true)]
    private ?array $investors = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 24, scale: 10)]
    private ?string $marketCap = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 24, scale: 10)]
    private ?string $liquidity = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 22, scale: 10)]
    private ?string $volume = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 14, scale: 0)]
    private ?string $totalSupply = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 24, scale: 10)]
    private ?string $circulationSupply = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 14, scale: 0)]
    private ?string $maxSupply = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $listedAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCoin(): ?Coin
    {
        return $this->coin;
    }

    public function setCoin(Coin $coin): static
    {
        $this->coin = $coin;

        return $this;
    }

    public function getinvestors(): ?array
    {
        return $this->investors;
    }

    public function setinvestors(?array $investors): static
    {
        $this->investors = $investors;

        return $this;
    }

    public function getMarketCap(): ?string
    {
        return $this->marketCap;
    }

    public function setMarketCap(string $marketCap): static
    {
        $this->marketCap = $marketCap;

        return $this;
    }

    public function getLiquidity(): ?string
    {
        return $this->liquidity;
    }

    public function setLiquidity(string $liquidity): static
    {
        $this->liquidity = $liquidity;

        return $this;
    }

    public function getvolume(): ?string
    {
        return $this->volume;
    }

    public function setvolume(string $volume): static
    {
        $this->volume = $volume;

        return $this;
    }

    public function getTotalSupply(): ?string
    {
        return $this->totalSupply;
    }

    public function setTotalSupply(string $totalSupply): static
    {
        $this->totalSupply = $totalSupply;

        return $this;
    }

    public function getCirculationSupply(): ?string
    {
        return $this->circulationSupply;
    }

    public function setCirculationSupply(string $circulationSupply): static
    {
        $this->circulationSupply = $circulationSupply;

        return $this;
    }

    public function getMaxSupply(): ?string
    {
        return $this->maxSupply;
    }

    public function setMaxSupply(string $maxSupply): static
    {
        $this->maxSupply = $maxSupply;

        return $this;
    }

    public function getListedAt(): ?\DateTimeImmutable
    {
        return $this->listedAt;
    }

    public function setListedAt(\DateTimeImmutable $listedAt): static
    {
        $this->listedAt = $listedAt;

        return $this;
    }
}
