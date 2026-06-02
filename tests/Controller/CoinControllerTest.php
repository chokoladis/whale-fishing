<?php

namespace App\Tests\Controller;

final class CoinControllerTest extends BaseControllerTest
{
    public function testBtcPriceSuccess() : void
    {
        $headers = $this->getAuthHeaders();
        $this->client->request('GET', '/api/v1/coin/btc/price', server: $headers);

        self::assertResponseIsSuccessful();
    }

    public function testListSuccess(): void
    {
        $headers = $this->getAuthHeaders();
        $this->client->request('GET', '/api/v1/coin/', server: $headers);

        self::assertResponseIsSuccessful();
    }
}
