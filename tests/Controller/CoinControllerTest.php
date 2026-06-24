<?php

namespace App\Tests\Controller;

use Symfony\Component\HttpFoundation\Response;

final class CoinControllerTest extends BaseControllerTest
{
    public function testListSuccess(): void
    {
        $headers = $this->getAuthHeaders();
        $this->client->request('GET', '/api/v1/coin/', server: $headers);

        self::assertResponseIsSuccessful();
    }

//    public function testListNotFound(): void
//    {
//        $headers = $this->getAuthHeaders();
//        $this->client->request('GET', '/api/v1/coin/', server: $headers);
//
//        self::assertResponseStatusCodeSame(\Symfony\Component\HttpFoundation\Response::HTTP_NOT_FOUND);
//    }

    public function testGetCoinEthSuccess(): void
    {
        $headers = $this->getAuthHeaders();
        $this->client->request('GET', '/api/v1/coin/eth/', server: $headers);

        self::assertResponseIsSuccessful();
    }

    public function testGetCoinRandomNotFound(): void
    {
        $headers = $this->getAuthHeaders();
        $this->client->request('GET', sprintf('/api/v1/coin/%s/', rand(1, 9999)), server: $headers);

        self::assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }
}
