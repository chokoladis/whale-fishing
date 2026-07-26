<?php

namespace App\Service;

use App\DTO\Http\Request\Auth\RegisterRequest;
use App\DTO\Http\Response\Auth\RegisterDTO;
use App\Repository\UserRepository;
use App\Resource\ProfileResource;
use App\Service\Auth\TokenService;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\RateLimit;

class UserService
{
    public function __construct(
        private UserRepository           $userRepository,
        private ProfileResource          $profileResource,
        private JWTTokenManagerInterface $JWTTokenManager,
        private TokenService             $tokenService,
    )
    {
    }

    #[RateLimit('login_register')]
    public function register(RegisterRequest $registerRequest, Request $request): RegisterDTO
    {
        $user = $this->userRepository->add($registerRequest);

        return new RegisterDTO(
            $this->profileResource->profile($user),
            $this->JWTTokenManager->create($user),
            $this->tokenService->createRefreshToken($request, $user)
        );
    }
}
