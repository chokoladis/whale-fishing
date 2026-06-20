<?php

namespace App\Controller;

use App\Service\Wallet\WalletService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/v1/wallets/', name: 'api.v1.wallets.')]
final class WalletController extends AbstractController
{
    function __construct(
        private WalletService $walletService,
    )
    {
    }

    #[Route('detail/{address}', name: 'detail', methods: ['GET'])]
    public function detail(
        string $address,
    ): Response
    {
//        todo get transaction by wallet and transaction by wallet + symbols
//        todo realize avgPrice
        return $this->json([
            'wallet' => $this->walletService->getDetail($address)
        ]);
    }

    #[Route('topHoldersCoin/{symbol}', name: 'top_holders_coin', methods: ['GET'])]
    public function index(
        string $symbol,
    ): Response
    {
        $result = $this->walletService->getTopHolders($symbol);

        return $this->json([
            'wallets' => $result
        ]);
    }
}
