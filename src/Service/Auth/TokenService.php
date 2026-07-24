<?php

namespace App\Service\Auth;

use App\DTO\Http\Request\Token\RefreshRequest;
use App\Entity\RefreshToken;
use App\Exception\Auth\RefreshTokenInvalid;
use App\Helper\SecureHelper;
use App\Helper\UserHelper;
use App\Repository\RefreshTokenRepository;
use App\Tool\Security\JWTToken;
use Psr\Log\LoggerInterface;
use Symfony\Component\Uid\Uuid;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;

class TokenService
{
    public function __construct(
        #[Autowire(env: 'APP_SECRET')]
        private string $secret,
//        #[Autowire(param: 'jwt.refresh_token.lifetime')]
//        private int $refreshTokenLifetime,
        private ContainerBagInterface $params,
        private RefreshTokenRepository $refreshTokenRepository,
        private LoggerInterface $logger,
    )
    {
    }

//    todo
    public function refresh(RefreshRequest $request)
    {
        $tokenHash = hash('argon2id', $request->refresh_token . $this->secret);

        $refreshToken = $this->refreshTokenRepository->findOneBy([
            'tokenHash' => $tokenHash
        ]);

        if (!$refreshToken)
            throw new RefreshTokenInvalid('Данный refresh token не был найден');

        if ($refreshToken->revoked)
            throw new RefreshTokenInvalid('Ваш refresh token уже не действителен');

        $dataToken = $this->JWTTokenManager->parse($request->refresh_token);

        $newRefreshToken = new RefreshToken();
        $newRefreshToken->setTokenHash($tokenHash);

        $this->refreshTokenRepository->save();

        // user_id для сброса токенов при смене пароля
        // device_fingerprint, ip_address(смотреть подсеть) - для безопастности и 2фа

//        $this->JWTTokenManager->create($user);
    }

    //todo limit
    public function createRefreshToken(Request $request, UserInterface $user)
    {
        $lifetime = $this->params->get('jwt')['refresh_token']['lifetime'];

        $token = Uuid::v7();

        $tokenHash = password_hash($token->hash(), PASSWORD_ARGON2ID);

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
    public function revoke()
    {

    }
}
