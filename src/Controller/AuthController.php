<?php

namespace App\Controller;

use App\DTO\Http\Request\Auth\PasswordRestoreConfirm;
use App\DTO\Http\Request\Auth\PasswordRestoreSendCode;
use App\DTO\Http\Request\Auth\RegisterRequest;
use App\Exception\RateLimitException;
use App\Service\PasswordService;
use App\Service\UserService;
use Doctrine\DBAL\Schema\Exception\ColumnAlreadyExists;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Translation\Exception\NotFoundResourceException;
use Symfony\Component\Validator\Exception\ValidatorException;

#[Route('/api/v1/auth/', name: 'api.v1.auth.')]
final class AuthController extends AbstractController
{
    function __construct(
        private UserService $userService,
        private PasswordService $passwordService,
    )
    {
    }

    #[Route('register/', name: 'register', methods: ['POST'])]
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

    #[Route('password/send_code/', name: 'password.sendCode', methods: ['POST'])]
    public function passwordSendCode(
        #[MapRequestPayload] PasswordRestoreSendCode $request,
    ): Response
    {
        try {
            $this->passwordService->sendToken($request);

            return $this->json()->setStatusCode(Response::HTTP_NO_CONTENT);
        } catch (RateLimitException $e) {
            return $this->json([
                'errors' => [$e->getMessage()]
            ], Response::HTTP_TOO_MANY_REQUESTS);
        }
    }

    #[Route('password/confirm/', name: 'password.restore', methods: ['POST'])]
    public function passwordRestore(
        #[MapRequestPayload] PasswordRestoreConfirm $request,
    ): Response
    {
        try {
            $this->passwordService->restore($request);
            return $this->json()->setStatusCode(Response::HTTP_NO_CONTENT);
        } catch (ValidatorException|NotFoundResourceException $e) {
            return $this->json([
                'errors' => [
                    'token' => $e->getMessage(),
                ]
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}
