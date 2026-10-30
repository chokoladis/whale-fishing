<?php

namespace App\Tests\Controller;

final class ProfileControllerTest extends BaseControllerTest
{
    public function testIndex(): void
    {
        $headers = $this->getAuthHeaders();
        $this->getClient()->request('GET', '/api/v1/profile/', server: $headers);

        self::assertResponseIsSuccessful();
    }
}
