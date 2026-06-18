<?php

namespace App\Controller;

use App\DTO\Http\Request\ListRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/v1/transaction/', name: 'api_transaction_')]
final class TransactionController extends AbstractController
{
    public function __construct(
        private \App\Service\Wallet\TransactionService $transactionService,
    )
    {
    }

    #[Route('topHourly/', name: 'topHourly', methods: ['GET'])]
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

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(
        #[MapQueryString] ?ListRequest $listRequest,
    ) : JsonResponse
    {
        try {
            return $this->json(['data' => $this->transactionService->getList($listRequest)], Response::HTTP_OK);
        } catch (\HttpException $exception) {
            return $this->json(['errors' => [$exception->getMessage()]], Response::HTTP_BAD_REQUEST);
        }
    }
}
