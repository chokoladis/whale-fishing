<?php

namespace App\Tests\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;


abstract class BaseControllerTest extends WebTestCase
{
    const string TEST_USER_EMAIL = 'test@mail.ru';
    const string TEST_PHONE = '79228282828';
    const string TEST_USER_PASSWORD = 'Pdn$192_swC';

    protected ?KernelBrowser $client = null;
    protected ?EntityManagerInterface $em = null;
    protected ?UrlGeneratorInterface $urlBuild = null;

    /**
     * @param array<mixed> $claims
     * @return array<string>
     * @throws \Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTEncodeFailureException
     */
    protected function getAuthHeaders(array $claims = ['phone' => self::TEST_PHONE]): array
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

        $this->client = self::$kernel ? $this->getClient() : $this->createClient();
        $this->urlBuild = static::getContainer()->get(UrlGeneratorInterface::class);

        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

        $this->em = static::getContainer()->get(EntityManagerInterface::class);
        $this->em->getConnection()->setAutoCommit(false);
        $this->em->beginTransaction();
    }

    protected function tearDown(): void
    {
        if ($this->em) {
            $conn = $this->em->getConnection();
            if ($conn->isTransactionActive()) {
                $conn->rollBack();
                $this->em->close();
            }

            $this->em = null;
        }

        $this->client = null;
        $this->urlBuild = null;

        parent::tearDown();
    }
}
