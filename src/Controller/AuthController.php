<?php

namespace App\Controller;

use App\DTO\Http\Request\Auth\PasswordRestoreConfirm;
use App\DTO\Http\Request\Auth\PasswordRestoreSendCode;
use App\DTO\Http\Request\Auth\RegisterRequest;
use App\Exception\RateLimitException;
use App\OpenApi\Schema\FieldErrorResponse;
use App\OpenApi\Schema\RateLimitErrorResponse;
use App\OpenApi\Schema\RegisterResponse;
use App\OpenApi\Schema\ValidationErrorResponse;
use App\Service\PasswordService;
use App\Service\UserService;
use Doctrine\DBAL\Schema\Exception\ColumnAlreadyExists;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Translation\Exception\NotFoundResourceException;
use Symfony\Component\Validator\Exception\ValidatorException;

#[Route('/api/v1/auth/', name: 'api.v1.auth.')]
#[OA\Tag(name: 'Auth')]
final class AuthController extends AbstractController
{
    function __construct(
        private UserService $userService,
        private PasswordService $passwordService,
    )
    {
    }

    #[Route('register/', name: 'register', methods: ['POST'])]
    #[OA\Post(
        operationId: 'authRegister',
        summary: 'Регистрация пользователя',
        description: 'Создаёт нового пользователя и сразу возвращает JWT-токен для авторизации.',
        security: [],
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(ref: new Model(type: RegisterRequest::class)),
    )]
    #[OA\Response(
        response: 200,
        description: 'Пользователь успешно зарегистрирован',
        content: new OA\JsonContent(ref: new Model(type: RegisterResponse::class)),
    )]
    #[OA\Response(
        response: 400,
        description: 'Email уже занят',
        content: new OA\JsonContent(ref: new Model(type: FieldErrorResponse::class)),
    )]
    #[OA\Response(
        response: 422,
        description: 'Ошибка валидации',
        content: new OA\JsonContent(ref: new Model(type: ValidationErrorResponse::class)),
    )]
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
    #[OA\Post(
        operationId: 'authPasswordSendCode',
        summary: 'Отправка кода восстановления пароля',
        description: 'Отправляет одноразовый токен на email пользователя. При превышении лимита запросов возвращает 429.',
        security: [],
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(ref: new Model(type: PasswordRestoreSendCode::class)),
    )]
    #[OA\Response(response: 204, description: 'Код отправлен (тело ответа пустое)')]
    #[OA\Response(
        response: 429,
        description: 'Превышен лимит запросов',
        content: new OA\JsonContent(ref: new Model(type: RateLimitErrorResponse::class)),
    )]
    #[OA\Response(
        response: 422,
        description: 'Ошибка валидации',
        content: new OA\JsonContent(ref: new Model(type: ValidationErrorResponse::class)),
    )]
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
    #[OA\Post(
        operationId: 'authPasswordConfirm',
        summary: 'Подтверждение восстановления пароля',
        description: 'Устанавливает новый пароль по токену из email.',
        security: [],
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(ref: new Model(type: PasswordRestoreConfirm::class)),
    )]
    #[OA\Response(response: 204, description: 'Пароль успешно изменён (тело ответа пустое)')]
    #[OA\Response(
        response: 400,
        description: 'Невалидный или просроченный токен',
        content: new OA\JsonContent(ref: new Model(type: FieldErrorResponse::class)),
    )]
    #[OA\Response(
        response: 422,
        description: 'Ошибка валидации',
        content: new OA\JsonContent(ref: new Model(type: ValidationErrorResponse::class)),
    )]
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
