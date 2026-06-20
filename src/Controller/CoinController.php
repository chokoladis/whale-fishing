<?php

namespace App\Controller;

use App\DTO\Http\Request\ListRequest;
use App\Exception\Coin\InvalidCoinSymbolException;
use App\Service\Coin\CoinService;
use App\Service\Coin\PriceService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/v1/coin/', name: 'api_coin_')]
final class CoinController extends AbstractController
{
    public function __construct(
        private CoinService $coinService,
        private PriceService $priceService
    )
    {
    }

//    todo разрешить без входа
    #[Route('', name: 'list', methods: ['GET'])]
    public function index(
        #[MapQueryString] ?ListRequest $listRequest,
    ): Response
    {
        $result = $this->coinService->getCoins($listRequest);

        if ($result->total) {
            return $this->json([
                'data' => $result
            ], Response::HTTP_OK);
        } else {
            return $this->json([
                'data' => []
            ], Response::HTTP_NOT_FOUND);
        }
    }

    // todo rate limit or http request to rabbitMQ
    #[Route('{symbol}', name: 'app_coin_price', methods: ['GET'])]
    public function getPrice(
        string $symbol,
    ) : JsonResponse
    {
        try {
            return $this->json(['data' => [
                'coin' => $this->coinService->getCoin($symbol)
            ]], Response::HTTP_OK);
        } catch (HttpException|InvalidCoinSymbolException $exception) {
            return $this->json(['errors' => [$exception->getMessage()]], Response::HTTP_BAD_REQUEST);
        }
    }
}
