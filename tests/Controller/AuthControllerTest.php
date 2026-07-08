<?php

namespace App\Tests\Controller;

use App\Repository\PasswordRestoreRepository;
use Symfony\Component\HttpFoundation\Response;

final class AuthControllerTest extends BaseControllerTest
{
    public PasswordRestoreRepository $passwordRestoreRepository;

    /**
     * @param PasswordRestoreRepository $passwordRestoreRepository
     * @return void
     */
    public function test__construct(
        PasswordRestoreRepository $passwordRestoreRepository
    )
    {
        $this->passwordRestoreRepository = $passwordRestoreRepository;

    }

    public function testRegisterSuccess(): void
    {
        $this->client->request('POST', '/api/v1/auth/register/', [], [], ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => 'emailtest' . rand(1000, 9999) . '@gmail.com',
                'password' => self::TEST_USER_PASSWORD,
                'password_confirm' => self::TEST_USER_PASSWORD,
            ]));

        self::assertResponseIsSuccessful();
    }

    public function testRegisterAlreadyExists(): void
    {

        $this->client->request('POST', '/api/v1/auth/register/', [], [], ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => self::TEST_USER_EMAIL,
                'password' => self::TEST_USER_PASSWORD,
                'password_confirm' => self::TEST_USER_PASSWORD,
            ]));

        $this->client->request('POST', '/api/v1/auth/register/', [], [], ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => self::TEST_USER_EMAIL,
                'password' => self::TEST_USER_PASSWORD,
                'password_confirm' => self::TEST_USER_PASSWORD,
            ]));

        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    public function testLoginSuccess(): void
    {
//        $this->client->request('POST', '/api/v1/auth/register', [], [], ['CONTENT_TYPE' => 'application/json'],
//            json_encode([
//                'email' => self::TEST_USER_EMAIL,
//                'password' => self::TEST_USER_PASSWORD,
//                'password_confirm' => self::TEST_USER_PASSWORD,
//            ]));

        $this->client->request('POST', '/api/v1/auth/login/', [], [], ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => self::TEST_USER_EMAIL,
                'password' => self::TEST_USER_PASSWORD,
            ]));

        self::assertResponseIsSuccessful();
    }

    public function testPasswordRestoreSendSuccess(): void
    {
        $this->client->request('POST', '/api/v1/auth/password/send/', [], [], ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => self::TEST_USER_EMAIL,
            ]));

        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
    }

    public function testPasswordRestoreSendErrorUserNotFound(): void
    {
        $this->client->request('POST', '/api/v1/auth/password/send/', [], [], ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => 'test'.rand(1000, 9999).'@gmail.com',
            ]));

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testPasswordRestoreConfirmSuccess(): void
    {
        $passwordRestoreObj = $this->passwordRestoreRepository->getLastByUserEmail(self::TEST_USER_EMAIL);

        $this->client->request('POST', '/api/v1/auth/password/confirm/', [], [], ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => self::TEST_USER_EMAIL,
                'token' => $passwordRestoreObj->getToken(),
                'password' => self::TEST_USER_PASSWORD,
                'password_confirm' => self::TEST_USER_PASSWORD,
            ]));

        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
    }

    public function testPasswordRestoreConfirmError(): void
    {
        $passwordRestoreObj = $this->passwordRestoreRepository->getNotActiveTokenByEmail(self::TEST_USER_EMAIL);

        $this->client->request('POST', '/api/v1/auth/password/confirm/', [], [], ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => self::TEST_USER_EMAIL,
                'token' => $passwordRestoreObj->getToken(),
                'password' => self::TEST_USER_PASSWORD,
                'password_confirm' => self::TEST_USER_PASSWORD,
            ]));

        $json = json_decode($this->client->getResponse()->getContent(), 1);

        self::assertEquals(['errors' => ['token']], $json);
    }


}
