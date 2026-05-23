<?php

namespace App\Entity;

use App\Enum\Coin\LinkType;
use App\Repository\CoinLinkRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CoinLinkRepository::class)]
class CoinLink
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'string', enumType: LinkType::class, length: 32)]
    private ?LinkType $type = null;

    #[ORM\Column]
    private ?string $url = null;

    #[ORM\ManyToOne(targetEntity: Coin::class, inversedBy: 'links')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Coin $coin = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getType(): LinkType
    {
        return $this->type;
    }

    public function setType(LinkType $type): static
    {
        $this->type = $type;
        return $this;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): static
    {
        $this->url = $url;
        return $this;
    }
}
