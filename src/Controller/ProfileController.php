<?php

namespace App\Controller;

use App\Exception\User\UserStatusException;
use App\OpenApi\Schema\ProfileResponse;
use App\Resource\ProfileResource;
use App\Service\UserService;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\RateLimit;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/v1/profile/', name: 'api.v1.profile.')]
#[OA\Tag(name: 'Profile')]
final class ProfileController extends AbstractController
{
    public function __construct(
        private UserService $userService,
        private ProfileResource $profileResource,
    )
    {
    }

    #[Route('', name: 'index', methods: ['GET'])]
    #[OA\Get(
        operationId: 'profileGet',
        summary: 'Профиль текущего пользователя',
        description: 'Возвращает данные авторизованного пользователя. Требует JWT-токен.',
    )]
    #[OA\Response(
        response: 200,
        description: 'Данные профиля',
        content: new OA\JsonContent(ref: new Model(type: ProfileResponse::class)),
    )]
    #[OA\Response(response: 401, description: 'Не авторизован')]
    public function profile(): Response
    {
        return $this->json([
            'user' => $this->profileResource->fullData($this->getUser()),
        ]);
    }

    #[RateLimit('user_delete_restore')]
    #[Route('', name: 'delete', methods: ['DELETE'])]
    public function delete() : Response
    {
        try {
            $this->userService->delete($this->getUser());
            return $this->json([
                'result' => 'Ваш аккаунт удален (данные будут хранится ещё 30дней)',
            ]);
        } catch (UserStatusException $e) {
            return $this->json([
                'errors' => [$e->getMessage()],
            ],Response::HTTP_BAD_REQUEST);
        }
    }

    #[RateLimit('user_delete_restore')]
    #[Route('restore/', name: 'restore', methods: ['POST'])]
    public function restore() : Response
    {
        $this->userService->restore($this->getUser());

        return $this->json([]);
    }
}
