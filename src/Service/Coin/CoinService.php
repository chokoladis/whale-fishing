<?php

declare(strict_types=1);

namespace App\Service\Coin;

use App\Repository\CoinRepository;
use App\Request\Coin\ListRequest;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class CoinService
{
    public function __construct(
        #[Autowire(env: 'ALCHEMY_API_KEY')]
        private string $alchemyApiKey,
        private CoinRepository $coinRepository,
    )
    {
    }

    public function getCoins(?ListRequest $request): Paginator
    {
//        handle error
        return $this->coinRepository->getList($request);
    }
}
