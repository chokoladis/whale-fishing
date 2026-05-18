<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Request\User\RegisterRequest;
use Doctrine\DBAL\Schema\Exception\ColumnAlreadyExists;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

final class AuthController extends AbstractController
{
    function __construct(
        private UserRepository $userRepository,
    )
    {
    }

    #[Route('/api/v1/auth/register', name: 'auth.register', methods: ['POST'])]
    public function index(
        #[MapRequestPayload] RegisterRequest $request,
    ): Response
    {
        try {
            return $this->json([
                'user' => $this->userRepository->add($request),
            ]);
        } catch (ColumnAlreadyExists $e) {
            return $this->json([
                'errors' => [
                    'email' => $e->getMessage(),
                ]
            ], Response::HTTP_BAD_REQUEST);
        }

    }
}
