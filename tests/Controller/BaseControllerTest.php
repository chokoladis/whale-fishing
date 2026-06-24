<?php

namespace App\Tests\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;


abstract class BaseControllerTest extends WebTestCase
{
    const string TEST_USER_EMAIL = 'test@mail.ru';
    const string TEST_USER_PASSWORD = 'Pdn$192_swC';

    protected ?KernelBrowser $client = null;
    protected ?EntityManagerInterface $em = null;

    /**
     * @param array<mixed> $claims
     * @return array<string>
     * @throws \Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTEncodeFailureException
     */
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

        $this->client = $this->createClient();

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
