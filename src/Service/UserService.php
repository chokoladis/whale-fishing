<?php

namespace App\Service;

use App\DTO\Http\Request\Auth\RegisterRequest;
use App\DTO\Http\Response\Auth\RegisterDTO;
use App\Repository\UserRepository;
use App\Resource\ProfileResource;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpKernel\Attribute\RateLimit;

class UserService
{
    public function __construct(
        private UserRepository $userRepository,
        private ProfileResource $profileResource,
        private JWTTokenManagerInterface $JWTTokenManager,
    )
    {
    }

    #[RateLimit('login_register')]
    public function register(RegisterRequest $request) : RegisterDTO
    {
        $user = $this->userRepository->add($request);

        return new RegisterDTO(
            $this->profileResource->profile($user),
            $this->JWTTokenManager->create($user),
        );
    }
}
