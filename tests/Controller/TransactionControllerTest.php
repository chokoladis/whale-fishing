<?php

namespace App\Tests\Controller;

final class TransactionControllerTest extends BaseControllerTest
{
    public function testListSuccess() : void
    {
        $headers = $this->getAuthHeaders();
        $this->client->request('GET', '/api/v1/transaction/', server: $headers);

        self::assertResponseIsSuccessful();
    }

    public function testTopHourly(): void
    {
        $headers = $this->getAuthHeaders();
        $this->client->request('GET', '/api/v1/transaction/topHourly', server: $headers);

        self::assertResponseIsSuccessful();
    }

    public function testTopDaily(): void
    {
        $headers = $this->getAuthHeaders();
        $this->client->request('GET', '/api/v1/transaction/topDaily', server: $headers);

        self::assertResponseIsSuccessful();
    }
}
