<?php

namespace App\Service\Auth;

use App\DTO\Http\Request\Token\RefreshRequest;
use App\Entity\RefreshToken;
use App\Entity\User;
use App\Exception\Auth\RefreshTokenInvalid;
use App\Helper\SecureHelper;
use App\Repository\RefreshTokenRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Uid\Uuid;

class TokenService
{
    public function __construct(
        #[Autowire(env: 'APP_SECRET')]
        private string                   $secret,
        private ContainerBagInterface    $params,
        private RefreshTokenRepository   $refreshTokenRepository,
        private JWTTokenManagerInterface $JWTTokenManager,
        private LoggerInterface          $logger,
    )
    {
    }

    public function getNewAccessToken(RefreshRequest $request)
    {
        $tokenHash = hash('sha256', $request->refresh_token . $this->secret);

        $refreshToken = $this->refreshTokenRepository->findOneBy([
            'tokenHash' => $tokenHash
        ]);

        if (!$refreshToken)
            throw new RefreshTokenInvalid('Данный refresh token не был найден');

        if ($refreshToken->isRevoked())
            throw new RefreshTokenInvalid('Ваш refresh token уже не действителен');

        if ($refreshToken->getExpiredAt()->getTimestamp() - time() <= 0) {
            $refreshToken->setIsRevoked(true);
            $this->refreshTokenRepository->save($refreshToken);
            throw new RefreshTokenInvalid('Ваш refresh token уже не действителен');
        }

        // device_fingerprint, ip_address(смотреть подсеть) - для безопастности и 2фа

        return $this->JWTTokenManager->create($refreshToken->getUser());
    }

    //todo limit
    public function createRefreshToken(Request $request, UserInterface $user)
    {
        $lifetime = $this->params->get('jwt')['refresh_token']['lifetime'];

        $token = Uuid::v7();

        $tokenHash = hash('sha256', $token->hash() . $this->secret);

        $newRefreshToken = new RefreshToken;
        $newRefreshToken->setTokenHash($tokenHash);
        $newRefreshToken->setIpAddress(SecureHelper::ipAddress());
        $newRefreshToken->setUser($user);
        $newRefreshToken->setDeviceFingerprint(SecureHelper::getDeviceFingerprint($request));
        $newRefreshToken->setExpiredAt((new \DateTimeImmutable())->modify('+' . $lifetime . ' seconds'));

        $this->refreshTokenRepository->save($newRefreshToken);

        return $token->hash();
    }

//        boolean isDateCorrect = claims
//        .getExpiration()
//        .after(new Date());
//        var type = claims.get("type");
//
//        if (!typeToken.equals(type)) {
//            throw new JwtException("Некорректный тип токена");
//        }
//        if (!isDateCorrect) {
//            throw new ExpiredJwtException(null, claims, "Действие токена авторизации истекло");
//        }

//    todo
    public function revokeAll(User $user)
    {
        /** @var RefreshToken[] $refreshTokens */
        $refreshTokens = $this->refreshTokenRepository->findBy([
            'user' => $user
        ]);

        if (!empty($refreshTokens)) {
            foreach ($refreshTokens as $refreshToken) {
                $refreshToken->setIsRevoked(true);
            }
        }
    }
}
