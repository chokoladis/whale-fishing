<?php

namespace App\Controller;

use App\DTO\Http\Request\ListRequest;
use App\Exception\Coin\InvalidCoinSymbolException;
use App\OpenApi\Schema\CoinDetailResponse;
use App\OpenApi\Schema\CoinEmptyListResponse;
use App\OpenApi\Schema\CoinListResponse;
use App\OpenApi\Schema\ErrorResponse;
use App\Service\Coin\CoinService;
use Doctrine\ORM\EntityNotFoundException;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Attribute\RateLimit;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/v1/coin/', name: 'api.v1.coin.')]
#[OA\Tag(name: 'Coin')]
final class CoinController extends AbstractController
{
    public function __construct(
        private CoinService $coinService,
    )
    {
    }

    #[Route('', name: 'list', methods: ['GET'])]
    #[OA\Get(
        operationId: 'coinList',
        summary: 'Список монет',
        description: 'Возвращает пагинированный список монет, отсортированный по цене (DESC). Публичный эндпоинт.',
        security: [],
    )]
    #[OA\Parameter(
        name: 'page',
        description: 'Номер страницы',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'integer', minimum: 1, default: 1, example: 1),
    )]
    #[OA\Parameter(
        name: 'perPage',
        description: 'Количество элементов на странице (5–30, по умолчанию 20)',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'integer', minimum: 5, maximum: 30, default: 20, example: 20),
    )]
    #[OA\Parameter(
        name: 'sort',
        description: 'Поле сортировки (TODO: не реализовано)',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'string'),
    )]
    #[OA\Parameter(
        name: 'order',
        description: 'Направление сортировки: asc или desc (TODO: не реализовано)',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'string', enum: ['asc', 'desc']),
    )]
    #[OA\Response(
        response: 200,
        description: 'Список монет',
        content: new OA\JsonContent(ref: new Model(type: CoinListResponse::class)),
    )]
    #[OA\Response(
        response: 404,
        description: 'Монеты не найдены',
        content: new OA\JsonContent(ref: new Model(type: CoinEmptyListResponse::class)),
    )]
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

    #[Route('{symbol}/', name: 'detail', methods: ['GET'])]
    #[OA\Get(
        operationId: 'coinDetail',
        summary: 'Детали монеты по символу',
        description: 'Возвращает подробную информацию о монете: цена, контракт, ссылки. Публичный эндпоинт.',
        security: [],
    )]
    #[OA\Parameter(
        name: 'symbol',
        description: 'Символ монеты (например: eth, usdt, btc)',
        in: 'path',
        required: true,
        schema: new OA\Schema(type: 'string', example: 'eth'),
    )]
    #[OA\Response(
        response: 200,
        description: 'Детали монеты',
        content: new OA\JsonContent(ref: new Model(type: CoinDetailResponse::class)),
    )]
    #[OA\Response(
        response: 400,
        description: 'Невалидный символ',
        content: new OA\JsonContent(ref: new Model(type: ErrorResponse::class)),
    )]
    #[OA\Response(
        response: 404,
        description: 'Монета не найдена',
        content: new OA\JsonContent(ref: new Model(type: ErrorResponse::class)),
    )]
    #[RateLimit('coin_get', 'ip')]
    public function get(
        string $symbol,
    ) : JsonResponse
    {
        try {
            return $this->json(['data' => [
                'coin' => $this->coinService->getCoin($symbol)
            ]], Response::HTTP_OK);
        } catch (HttpException|InvalidCoinSymbolException $exception) {
            return $this->json(['errors' => [$exception->getMessage()]], Response::HTTP_BAD_REQUEST);
        } catch (EntityNotFoundException $e) {
            return $this->json(['errors' => [$e->getMessage()]], Response::HTTP_NOT_FOUND);
        }
    }
}
