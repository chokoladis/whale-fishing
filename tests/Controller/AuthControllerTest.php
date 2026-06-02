<?php

namespace App\Tests\Controller;

use Symfony\Component\HttpFoundation\Response;

final class AuthControllerTest extends BaseControllerTest
{
    public function testRegisterSuccess(): void
    {
        $this->client->request('POST', '/api/v1/auth/register', [], [], ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => self::TEST_USER_EMAIL,
                'password' => 'Pdn$192_swC',
                'password_confirm' => 'Pdn$192_swC',
            ]));

        self::assertResponseIsSuccessful();
    }

    public function testRegisterAlreadyExists(): void
    {

        $this->client->request('POST', '/api/v1/auth/register', [], [], ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => self::TEST_USER_EMAIL,
                'password' => 'Pdn$192_swC',
                'password_confirm' => 'Pdn$192_swC',
            ]));

        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    public function testLoginSuccess(): void
    {
//        $this->client->request('POST', '/api/v1/auth/register', [], [], ['CONTENT_TYPE' => 'application/json'],
//            json_encode([
//                'email' => self::TEST_USER_EMAIL,
//                'password' => 'Pdn$192_swC',
//                'password_confirm' => 'Pdn$192_swC',
//            ]));

        $this->client->request('POST', '/api/v1/auth/login', [], [], ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => self::TEST_USER_EMAIL,
                'password' => 'Pdn$192_swC',
            ]));

        self::assertResponseIsSuccessful();
    }


}
