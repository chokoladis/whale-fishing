<?php

namespace App\Controller;

use App\DTO\Http\Request\ListRequest;
use App\OpenApi\Schema\ErrorResponse;
use App\OpenApi\Schema\TransactionEmptyResponse;
use App\OpenApi\Schema\TransactionListResponse;
use App\OpenApi\Schema\TransactionTopResponse;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/v1/transaction/', name: 'api.v1.transaction.')]
#[OA\Tag(name: 'Transaction')]
final class TransactionController extends AbstractController
{
    public function __construct(
        private \App\Service\Wallet\TransactionService $transactionService,
    )
    {
    }

    #[Route('', name: 'list', methods: ['GET'])]
    #[OA\Get(
        operationId: 'transactionList',
        summary: 'Список транзакций',
        description: 'Пагинированный список транзакций, отсортированный по дате (DESC). Требует JWT.',
    )]
    #[OA\Parameter(
        name: 'page',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'integer', minimum: 1, default: 1),
    )]
    #[OA\Parameter(
        name: 'perPage',
        in: 'query',
        required: false,
        schema: new OA\Schema(type: 'integer', minimum: 5, maximum: 30, default: 20),
    )]
    #[OA\Response(
        response: 200,
        description: 'Список транзакций',
        content: new OA\JsonContent(ref: new Model(type: TransactionListResponse::class)),
    )]
    #[OA\Response(
        response: 400,
        description: 'Ошибка запроса',
        content: new OA\JsonContent(ref: new Model(type: ErrorResponse::class)),
    )]
    #[OA\Response(response: 401, description: 'Не авторизован')]
    public function list(
        #[MapQueryString] ?ListRequest $listRequest,
    ) : JsonResponse
    {
        try {
            return $this->json(['data' => $this->transactionService->getList($listRequest)], Response::HTTP_OK);
        } catch (HttpException $exception) {
            return $this->json(['errors' => [$exception->getMessage()]], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('topHourly/', name: 'topHourly', methods: ['GET'])]
    #[OA\Get(
        operationId: 'transactionTopHourly',
        summary: 'Топ транзакций за последний час',
        description: 'Крупнейшие транзакции за последние 60 минут. Публичный эндпоинт.',
        security: [],
    )]
    #[OA\Response(
        response: 200,
        description: 'Топ транзакций',
        content: new OA\JsonContent(ref: new Model(type: TransactionTopResponse::class)),
    )]
    #[OA\Response(
        response: 404,
        description: 'Транзакции не найдены',
        content: new OA\JsonContent(ref: new Model(type: TransactionEmptyResponse::class)),
    )]
    public function getTopHourly(): Response
    {
        $result = $this->transactionService->getTopHourly();
        if (!empty($result)) {
            return $this->json([
                'data' => $result
            ], Response::HTTP_OK);
        } else {
            return $this->json([
                'data' => []
            ], Response::HTTP_NOT_FOUND);
        }
    }

    #[Route('topDaily/', name: 'topDaily', methods: ['GET'])]
    #[OA\Get(
        operationId: 'transactionTopDaily',
        summary: 'Топ транзакций за последние 24 часа',
        description: 'Крупнейшие транзакции за сутки. Публичный эндпоинт.',
        security: [],
    )]
    #[OA\Response(
        response: 200,
        description: 'Топ транзакций',
        content: new OA\JsonContent(ref: new Model(type: TransactionTopResponse::class)),
    )]
    #[OA\Response(
        response: 404,
        description: 'Транзакции не найдены',
        content: new OA\JsonContent(ref: new Model(type: TransactionEmptyResponse::class)),
    )]
    public function getTopDaily(): Response
    {
        $result = $this->transactionService->getTopDaily();
        if (!empty($result)) {
            return $this->json([
                'data' => $result
            ], Response::HTTP_OK);
        } else {
            return $this->json([
                'data' => []
            ], Response::HTTP_NOT_FOUND);
        }
    }
}
