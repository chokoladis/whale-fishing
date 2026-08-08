<?php

namespace App\Tests\Service\Auth;

use App\DTO\Http\Request\Token\RefreshRequest;
use App\Entity\RefreshToken;
use App\Entity\User;
use App\Exception\Auth\RefreshTokenInvalid;
use App\Repository\RefreshTokenRepository;
use App\Service\Auth\TokenService;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;

class TokenServiceTest extends TestCase
{
    const string DEFAULT_REFRESH_TOKEN = '019f9f23-37e3-7d1a-aeaa-b3be31e212f4';
    const string DEFAULT_ACCESS_TOKEN = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJpYXQiOjE3ODUwODEzMTMsImV4cCI6MTc4NTA4NDkxMywicm9sZXMiOlsiVVNFUiJdLCJlbWFpbCI6Imd1c3RhdmZvcmVsaUBnbWFpbC5jb20ifQ.iaGcJgk_t9UJN_efKqvGjlQ8G-zapE4m9Prz8Y1XCO39PGdWtLfFialujS4gZmSFH0yW3ANXuDYUtjnlv_S_bb6HPhV92D56WOtfIj4AEqR-fe1RIKhRxtTWJFXNuD2pcCUshwCDpQU17gwNgdA5DcsPSXR6QIgY3ZPmzkSNbrjIpbQP1zNuKprXqonXlGJj_oAPbGs_8wlBw1BkfG9gZ7HzRKJCf1xPW-YavBZpzHQyLMHhSfY8evXvES49XoAlXx1B0_19NLgnkHJ6WYiaD06fYj-PPG5Hb_qVUExISXAUTusT4_4ptKe1sCLCm_GSJ4WcwuOh1ovnMRfuAEWj-Q';
    const string DEFAULT_EMAIL = 'gustavforeli@gmail.com';


    private TokenService $service;

    private ContainerBagInterface $params;
    private RefreshTokenRepository $refreshTokenRepository;
    private JWTTokenManagerInterface $jwtTokenManager;
    private RefreshToken $refreshToken;
    private User $user;

    protected function setUp(): void
    {
        //or createStub()
        $this->params = $this->createMock(ContainerBagInterface::class);
        $this->refreshTokenRepository = $this->createMock(
            RefreshTokenRepository::class
        );
        $this->jwtTokenManager = $this->createMock(
            JWTTokenManagerInterface::class
        );

        $this->service = new TokenService(
            secret: $_ENV['APP_SECRET'],
            params: $this->params,
            refreshTokenRepository: $this->refreshTokenRepository,
            JWTTokenManager: $this->jwtTokenManager,
//            logger: new NullLogger() //new Logger('services'),
        );
        $this->refreshToken = $this->createMock(RefreshToken::class);
        $this->setUser();
    }

    public function test_getNewAccessToken_success()
    {
        $tokenHash = hash('sha256', self::DEFAULT_REFRESH_TOKEN . $_ENV['APP_SECRET']);

        $this->refreshTokenRepository->expects(self::once())
            ->method('findOneBy')
            ->with(['tokenHash' => $tokenHash])
            ->willReturn($this->getCorrectRefreshToken());

        $this->jwtTokenManager
            ->expects(self::once())
            ->method('create')
            ->with($this->user)
            ->willReturn(self::DEFAULT_ACCESS_TOKEN);

        $result = $this->service->getNewAccessToken(
            new RefreshRequest(self::DEFAULT_REFRESH_TOKEN)
        );

        $this->assertEquals(self::DEFAULT_ACCESS_TOKEN, $result);
    }

    public function test_getNewAccessToken_errorTokenNotFound()
    {
        $tokenHash = hash('sha256', self::DEFAULT_REFRESH_TOKEN . $_ENV['APP_SECRET']);

        $this->refreshTokenRepository->expects(self::once())
            ->method('findOneBy')
            ->with(['tokenHash' => $tokenHash])
            ->willReturn(null);

        $this->expectException(RefreshTokenInvalid::class);
        $this->expectExceptionMessageIs('Данный refresh token не был найден');

        $this->service->getNewAccessToken(
            new RefreshRequest(self::DEFAULT_REFRESH_TOKEN)
        );
    }

    public function test_getNewAccessToken_errorTokenExpired()
    {
        $tokenHash = hash('sha256', self::DEFAULT_REFRESH_TOKEN . $_ENV['APP_SECRET']);

        $this->refreshTokenRepository->expects(self::once())
            ->method('findOneBy')
            ->with(['tokenHash' => $tokenHash])
            ->willReturn($this->getCorrectRefreshToken()
                ->setExpiredAt(new \DateTimeImmutable('-1 second'))
            );

        $this->expectException(RefreshTokenInvalid::class);
        $this->expectExceptionMessageIs('Ваш refresh token уже не действителен');

        $this->service->getNewAccessToken(
            new RefreshRequest(self::DEFAULT_REFRESH_TOKEN)
        );
    }

    private function getCorrectRefreshToken() {
        $obj = new RefreshToken();
        $obj->setUser($this->user);
        $obj->setTokenHash(hash('sha256', self::DEFAULT_REFRESH_TOKEN . $_ENV['APP_SECRET']));
        $obj->setExpiredAt(new \DateTimeImmutable('+1 day'));
        $obj->setIsRevoked(false);
        return $obj;
    }

    private function setUser()
    {
        $this->user = $this->createMock(User::class);
        $this->user->setEmail(self::DEFAULT_EMAIL);
        $this->user->setUpdatedAt(new \DateTimeImmutable());
    }
}