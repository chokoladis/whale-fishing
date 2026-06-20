<?php

namespace App\Controller;

use App\DTO\Http\Request\User\RegisterRequest;
use App\Service\UserService;
use Doctrine\DBAL\Schema\Exception\ColumnAlreadyExists;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

final class AuthController extends AbstractController
{
    function __construct(
        private UserService $userService,
    )
    {
    }

    #[Route('/api/v1/auth/register', name: 'api.v1.auth.register', methods: ['POST'])]
    public function register(
        #[MapRequestPayload] RegisterRequest $request,
    ): Response
    {
        try {
            return $this->json($this->userService->register($request));
        } catch (ColumnAlreadyExists $e) {
            return $this->json([
                'errors' => [
                    'email' => $e->getMessage(),
                ]
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}
