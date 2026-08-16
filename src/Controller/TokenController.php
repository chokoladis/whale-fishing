<?php

namespace App\Controller;

use App\DTO\Http\Request\Token\RefreshRequest;
use App\Exception\Auth\RefreshTokenInvalid;
use App\Service\Auth\TokenService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Attribute\RateLimit;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/v1/token/', name: 'api.v1.token.')]
final class TokenController extends AbstractController
{
    public function __construct(
        private TokenService $tokenService,
    )
    {

    }

    #[RateLimit('token_refresher')]
    #[Route('refresh/', name: 'refresh', methods: ['POST'])]
    public function index(
        #[MapRequestPayload] RefreshRequest $request,
    ): Response
    {
        try {
            return $this->json([
                'access_token' => $this->tokenService->getNewAccessToken($request),
            ]);
        } catch (RefreshTokenInvalid $e) {
            return $this->json([
                'errors' => [$e->getMessage()]
            ], Response::HTTP_BAD_REQUEST);
        }

    }
}
