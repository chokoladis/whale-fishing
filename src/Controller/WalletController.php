<?php

namespace App\Controller;

use App\OpenApi\Schema\ErrorResponse;
use App\OpenApi\Schema\WalletDetailResponse;
use App\OpenApi\Schema\WalletTopHoldersResponse;
use App\Service\Wallet\WalletService;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/v1/wallet/', name: 'api.v1.wallet.')]
#[OA\Tag(name: 'Wallet')]
final class WalletController extends AbstractController
{
    function __construct(
        private WalletService $walletService,
    )
    {
    }

    #[Route('detail/{address}', name: 'detail', methods: ['GET'])]
    #[OA\Get(
        operationId: 'walletDetail',
        summary: 'Детали кошелька',
        description: 'Возвращает адрес кошелька, балансы по монетам, P&L и среднюю цену входа. Требует JWT.',
    )]
    #[OA\Parameter(
        name: 'address',
        description: 'Ethereum-адрес кошелька (0x...)',
        in: 'path',
        required: true,
        schema: new OA\Schema(type: 'string', example: '0x742d35Cc6634C0532925a3b844Bc9e7595f0bEb'),
    )]
    #[OA\Response(
        response: 200,
        description: 'Детали кошелька',
        content: new OA\JsonContent(ref: new Model(type: WalletDetailResponse::class)),
    )]
    #[OA\Response(response: 401, description: 'Не авторизован')]
    public function detail(
        string $address,
    ): Response
    {
        return $this->json([
            'wallet' => $this->walletService->getDetail($address)
        ]);
    }

    #[Route('topHoldersCoin/{symbol}', name: 'top_holders_coin', methods: ['GET'])]
    #[OA\Get(
        operationId: 'walletTopHolders',
        summary: 'Топ-холдеры монеты',
        description: 'Кошельки с наибольшим балансом указанной монеты (мин. $100 000). Требует JWT.',
    )]
    #[OA\Parameter(
        name: 'symbol',
        description: 'Символ монеты (например: USDT, ETH)',
        in: 'path',
        required: true,
        schema: new OA\Schema(type: 'string', example: 'USDT'),
    )]
    #[OA\Response(
        response: 200,
        description: 'Список топ-холдеров',
        content: new OA\JsonContent(ref: new Model(type: WalletTopHoldersResponse::class)),
    )]
    #[OA\Response(
        response: 400,
        description: 'Невалидный символ',
        content: new OA\JsonContent(ref: new Model(type: ErrorResponse::class)),
    )]
    #[OA\Response(response: 401, description: 'Не авторизован')]
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
