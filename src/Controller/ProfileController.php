<?php

namespace App\Controller;

use App\OpenApi\Schema\ProfileResponse;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/v1/profile/', name: 'api.v1.profile.')]
#[OA\Tag(name: 'Profile')]
final class ProfileController extends AbstractController
{
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
            'user' => $this->getUser(),
        ]);
    }
}
