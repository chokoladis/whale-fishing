<?php

namespace App\Controller;

use App\Service\Coin\WalletService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class WalletController extends AbstractController
{
    function __construct(
        private WalletService $walletService,
    )
    {
    }

    #[Route('/api/v1/wallets/{symbol}', name: 'api_wallets', methods: ['GET'])]
    public function index(
        string $symbol,
    ): Response
    {
        $result = $this->walletService->getTopHolders($symbol);
        dd($result);

        return $this->json([

        ]);
    }
}
