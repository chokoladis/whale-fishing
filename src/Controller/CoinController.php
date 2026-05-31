<?php

namespace App\Controller;

use App\Service\Coin\CoinService;
use App\Service\Coin\PriceService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CoinController extends AbstractController
{

    public function __construct(
        private CoinService $coinService,
        private PriceService $priceService
    )
    {
    }

    #[Route('/coin', name: 'app_coin')]
    public function index(): Response
    {
        $this->coinService->getCoins('');

        return $this->render('coin/index.html.twig', [
            'controller_name' => 'CoinController',
        ]);
    }

    #[Route('/api/v1/coin/{symbol}/price', name: 'app_coin_price', methods: ['GET'])]
    public function getPrice(
        string $symbol,
    )
    {
        try {
            return $this->json(['data' => [
                'coin' => $this->priceService->getPriceBySymbol($symbol)
            ]], Response::HTTP_OK);
        } catch (\HttpException $exception) {
            return $this->json(['errors' => [$exception->getMessage()]], Response::HTTP_BAD_REQUEST);
        }
    }
}
