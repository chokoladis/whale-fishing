<?php

namespace App\Tests\Controller;

use App\Repository\User\PasswordRestoreRepository;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Response;

final class AuthControllerTest extends BaseControllerTest
{
    public PasswordRestoreRepository $passwordRestoreRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->passwordRestoreRepository = $this->getContainer()->get(PasswordRestoreRepository::class);
    }

    public function testRegisterSuccess(): void
    {
        $this->client->request('POST', $this->urlBuild->generate('api.v1.auth.register'), [], [], ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => self::TEST_USER_EMAIL,
                'phone' => self::TEST_PHONE,
                'password' => self::TEST_USER_PASSWORD,
                'password_confirm' => self::TEST_USER_PASSWORD,
            ]));

        self::assertResponseIsSuccessful();
    }

    public function testRegisterAlreadyExists(): void
    {
//        $container = static::getContainer();
//        $rateLimiterFactory = $container->get('framework.rate_limiter.register');
//
//        $limiter = $rateLimiterFactory->create($this->client->getRequest()->getClientIp());
//        $limiter->reset();

        $this->client->request('POST', $this->urlBuild->generate('api.v1.auth.register'), [], [], ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => self::TEST_USER_EMAIL,
                'phone' => self::TEST_PHONE,
                'password' => self::TEST_USER_PASSWORD,
                'password_confirm' => self::TEST_USER_PASSWORD,
            ]));

        $this->client->request('POST', $this->urlBuild->generate('api.v1.auth.register'), [], [], ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => self::TEST_USER_EMAIL,
                'phone' => self::TEST_PHONE,
                'password' => self::TEST_USER_PASSWORD,
                'password_confirm' => self::TEST_USER_PASSWORD,
            ]));

        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    public function testLoginSuccess(): void
    {
        $this->client->request('POST', $this->urlBuild->generate('api.v1.auth.login'), [], [], ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => self::TEST_USER_EMAIL,
                'password' => self::TEST_USER_PASSWORD,
            ]));

        self::assertResponseIsSuccessful();
    }

    public function testPasswordRestoreSendSuccess(): void
    {
        $this->client->request('POST', $this->urlBuild->generate('api.v1.auth.password.send'), [], [], ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => self::TEST_USER_EMAIL,
            ]));

        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
    }

    public function testPasswordRestoreSendErrorNoGotData(): void
    {
        self::expectException(BadRequestException::class);

        $this->client->request('POST', $this->urlBuild->generate('api.v1.auth.password.send'), [], [], ['CONTENT_TYPE' => 'application/json'],
            json_encode([]));

//        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testPasswordRestoreSendErrorUserNotFound(): void
    {
        $this->client->request('POST', $this->urlBuild->generate('api.v1.auth.password.send'), [], [], ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'email' => 'test'.rand(1000, 9999).'@gmail.com',
            ]));

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }
}
