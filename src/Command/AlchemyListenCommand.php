<?php

namespace App\Command;

use App\Config\External\AlchemyConfig;
use App\Tool\Alchemy\TransactionParser;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(
    name: 'app:alchemy.listen',
    description: 'Listen to Alchemy WebSocket for pending transactions'

)]
class AlchemyListenCommand extends Command
{
    public function __construct(
        #[Autowire(env: 'ALCHEMY_API_KEY')]
        protected string $alchemyApiKey,
        #[Autowire(service: 'monolog.logger.alchemy')]
        protected LoggerInterface $logger,
        private MessageBusInterface $bus,
    )
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $url = sprintf('wss://%s/v2/%s', AlchemyConfig::ETH_MAINNET_DOMAIN, $this->alchemyApiKey);

        $output->writeln('Connect to eth mainnet alchemy...');

        $loop = \React\EventLoop\Loop::get();

        \Ratchet\Client\connect($url, [], [], $loop)->then(
            function (\Ratchet\Client\WebSocket $conn) use ($output) {
                $output->writeln('<info>Connected!</info>');

                $conn->send(json_encode([
                    'jsonrpc' => '2.0',
                    'id'      => 1,
                    'method'  => 'eth_subscribe',
                    'params'  => ['alchemy_minedTransactions'],
                ]));

                $conn->on('message', function ($msg) use ($output) {
                    $data = json_decode($msg, true);

                    if (isset($data['result']) && !isset($data['params'])) {
                        $output->writeln("<info>Subscribed: {$data['result']}</info>");
                        return;
                    }

                    $transferInfo = $data['params']['result']['transaction'] ?? null;
                    if (!$transferInfo) return;

                    $transactionDTO = TransactionParser::parse($transferInfo);
//                    $this->logger->info('parsed data ', ['dto' => $transactionDTO]);
                    if ($transactionDTO && $transactionDTO->amountRaw) {
                        $this->bus->dispatch($transactionDTO);
                    }
                });
            },
            function (\Exception $e) use ($output) {
                $output->writeln("<error>Could not connect: {$e->getMessage()}</error>");
                return null;
            }
        );

        $loop->run();

        return Command::SUCCESS;
    }
}
