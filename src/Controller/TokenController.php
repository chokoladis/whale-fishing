<?php

namespace App\Controller;

use App\DTO\Http\Request\Auth\PasswordRestoreSendToken;
use App\DTO\Http\Request\Token\RefreshRequest;
use App\Exception\Auth\RefreshTokenInvalid;
use App\Service\Auth\TokenService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/v1/token/', name: 'api.v1.token.')]
final class TokenController extends AbstractController
{
    public function __construct(
        private TokenService $tokenService,
    )
    {

    }

    #[Route('refresh', name: 'refresh', methods: ['POST'])]
    public function index(
        #[MapRequestPayload] RefreshRequest $request,
    ): Response
    {
        //in progress
        try {
            $this->tokenService->refresh($request);
        } catch (RefreshTokenInvalid $e) {

        }

        return $this->json([]);
    }
}
