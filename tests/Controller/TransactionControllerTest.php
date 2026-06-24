<?php

namespace App\Tests\Controller;

final class TransactionControllerTest extends BaseControllerTest
{
    public function testListSuccess(): void
    {
        $headers = $this->getAuthHeaders();
        $this->client->request('GET', '/api/v1/transaction/', server: $headers);

        self::assertResponseIsSuccessful();
    }

    // todo?
    public function testTopHourlySuccess(): void
    {
        $headers = $this->getAuthHeaders();
        $this->client->request('GET', '/api/v1/transaction/topHourly/', server: $headers);

        $json = json_decode($this->client->getResponse()->getContent(), true);

        self::assertEquals(['data' => []], $json);
    }

    public function testTopDailySuccess(): void
    {
        $headers = $this->getAuthHeaders();
        $this->client->request('GET', '/api/v1/transaction/topDaily/', server: $headers);

        $json = json_decode($this->client->getResponse()->getContent(), true);

        self::assertEquals(['data' => []], $json);
    }
}
