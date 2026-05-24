<?php

namespace App\Entity;

use App\Enum\Coin\TransactionType;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Transaction
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private int $id;

    #[ORM\ManyToOne(inversedBy: 'transactions')]
    #[ORM\JoinColumn(nullable: false)]
    private Wallet $wallet;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private User $user;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private Coin $coin;

    #[ORM\Column(enumType: TransactionType::class)]
    private TransactionType $type;

    #[ORM\Column(type: 'decimal', precision: 28, scale: 8)]
    private string $price;

    #[ORM\Column(type: 'decimal', precision: 28, scale: 8)]
    private string $qty;

    #[ORM\Column(type: 'decimal', precision: 28, scale: 8)]
    private string $gas = '0';

    #[ORM\Column(type: 'decimal', precision: 28, scale: 8)]
    private string $fee = '0';

    #[ORM\Column(length: 128, nullable: true)]
    private ?string $txHash = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $note = null;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;
}
