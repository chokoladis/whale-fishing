<?php

namespace App\Tests\Controller;

use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\ORM\EntityManagerInterface;


abstract class BaseControllerTest extends WebTestCase
{
    const string TEST_USER_EMAIL = 'test@mail.ru';

    protected ?KernelBrowser $client = null;
    protected ?EntityManagerInterface $em = null;
    protected function getAuthHeaders(array $claims = ['email' => self::TEST_USER_EMAIL]): array
    {
        $container = static::getContainer();

        /** @var JWTEncoderInterface $encoder */
        $encoder = $container->get(JWTEncoderInterface::class);
        $token = $encoder->encode($claims);

        return [
            'HTTP_Authorization' => sprintf('Bearer %s', $token),
            'CONTENT_TYPE' => 'application/json',
        ];
    }

    /**
     * Вызывается перед каждым тестом
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->client = static::createClient();

        $this->em = static::getContainer()->get(EntityManagerInterface::class);
        $this->em->getConnection()->beginTransaction();
    }

    protected function tearDown(): void
    {
        if ($this->em) {
            $this->em->getConnection()->rollBack();
            $this->em->close();
            $this->em = null;
        }

        $this->client = null;

        parent::tearDown();
    }
}
