<?php

namespace App\Command;

use App\DTO\Http\Response\Coin\CoinDetailResponse;
use App\Entity\Coin;
use App\Messages\LoadCoinBySymbolMessage;
use App\Repository\CoinRepository;
use App\Service\Coin\CoinContractService;
use App\Service\Coin\CoinDetailService;
use App\Service\External\CoinPrice\MobulaIOService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsCommand(
    name: 'app:coin.load',
    description: 'Load coin from external service by symbol',
)]
#[AsMessageHandler]
class LoadCoinCommand extends Command
{
    public function __construct(
        // for test
        private MobulaIOService     $priceService,
        private CoinRepository      $coinRepository,
        private CoinContractService $coinContractService,
        private CoinDetailService   $coinDetailService,
//        private LoggerInterface $logger,
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('symbol', InputArgument::OPTIONAL, 'Symbol of coin');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        if ($symbol = strtoupper($input->getArgument('symbol'))) {
            $coinDetailResponse = $this->priceService->getCoinDetailBySymbol($symbol);
            $this->updateCoin($coinDetailResponse);

            $io->success('Монета успешно обновлена');

            return Command::SUCCESS;
        }

        $io->error('Symbol не может быть пустым');

        return Command::INVALID;
    }

    public function __invoke(LoadCoinBySymbolMessage $message) : void
    {
        $coinDetailResponse = $this->priceService->getCoinDetailBySymbol($message->symbol);

        $this->updateCoin($coinDetailResponse);
    }

    private function updateCoin(CoinDetailResponse $coinDetailResponse): void
    {
        $coin = $this->coinRepository->findOneBy(['symbol' => $coinDetailResponse->symbol]);

        if (empty($coin)) {
            $coin = new Coin();
            $coin->setSymbol($coinDetailResponse->symbol);
            $coin->setName($coinDetailResponse->name);
        }

        $coin->setAvgPrice($coinDetailResponse->price);
//        $this->logger->info('full update coin', ['response' => $coinDetailResponse]);
        //        todo in one transaction
        $this->coinRepository->save($coin);

        $this->coinContractService->updateByCoinDetailResponse($coin, $coinDetailResponse);
        $this->coinDetailService->updateByCoinDetailResponse($coin, $coinDetailResponse->statistics);
    }
}
