<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Auth', description: 'Регистрация, вход и восстановление пароля')]
#[OA\Tag(name: 'Profile', description: 'Профиль текущего пользователя')]
#[OA\Tag(name: 'Coin', description: 'Справочник монет и цены')]
#[OA\Tag(name: 'Transaction', description: 'Транзакции китов')]
#[OA\Tag(name: 'Wallet', description: 'Кошельки и топ-холдеры')]
final class OpenApiSpec
{
}
