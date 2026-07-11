<?php

declare(strict_types=1);

namespace App\Command\Alchemy;

use App\Config\External\AlchemyConfig;
use App\Enum\External\Network;
use App\Messages\TransactionMessage;
use App\Tool\Alchemy\TransactionParser;
use Psr\Log\LoggerInterface;
use Ratchet\RFC6455\Messaging\Message;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(
    name: 'alchemy:wss.listen',
    description: 'Listen to Alchemy WebSocket for pending transactions',
)]
class AlchemyListenCommand extends Command
{
    private string $network;

    public function __construct(
        #[Autowire(env: 'ALCHEMY_API_KEY')]
        protected string              $alchemyApiKey,
        protected MessageBusInterface $bus,
        #[Autowire(service: 'monolog.logger.commands')]
        protected LoggerInterface     $logger,
    )
    {
        parent::__construct();
    }

    protected function configure() : void
    {
        $this->addArgument(
            'mainnet',
            InputArgument::REQUIRED,
            'Network name (e.g. eth, matic, opt, sol)'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if ($network = Network::tryFrom(
            strtoupper($input->getArgument('mainnet'))
        )) {
            $this->network = $network->value;

            $domain = self::getDomainByNetwork($network);

            $output->writeln(sprintf('Connect to %s alchemy...', $domain));

            $loop = \React\EventLoop\Loop::get();

            \Ratchet\Client\connect(
                sprintf('wss://%s/v2/%s', $domain, $this->alchemyApiKey), [], [], $loop
            )->then(
                function (\Ratchet\Client\WebSocket $conn) use ($output) {
                    $output->writeln('<info>Connected!</info>');

                    $conn->send(json_encode([
                        'jsonrpc' => '2.0',
                        'id' => 1,
                        'method' => 'eth_subscribe',
                        'params' => ['alchemy_minedTransactions'],
                    ]));

                    $conn->on('message', function ($msg) use ($output) {

                        if (gettype($msg) === 'string') {
                            $data = json_decode($msg, true);
                        } elseif (is_a($msg, Message::class)) {
                            /** @var Message $msg */
                            $data = json_decode($msg->getPayload(), true);
                        } else {
//                            $this->logger->debug('type msg', [gettype($msg)]);
                            return;
                        }

                        $this->logger->info('msg result', ['msg data' => $data]);

                        if (isset($data['result']) && !isset($data['params'])) {
                            $output->writeln("<info>Subscribed: {$data['result']}</info>");
                            return;
                        }

                        $transferInfo = $data['params']['result']['transaction'] ?? null;
                        if (!$transferInfo) return;

                        $transactionDTO = TransactionParser::parse($transferInfo, $this->network);

                        $this->logger->info('parsed data ', ['dto' => $transactionDTO]);

                        if ($transactionDTO && $transactionDTO->amountRaw) {
                            $this->bus->dispatch(
                                new TransactionMessage($transactionDTO)
                            );
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
        } else {
            $output->writeln(sprintf('<error>Unknown network listener "%s"</error>', $input->getArgument('mainnet')));
            return Command::FAILURE;
        }
    }

    private static function getDomainByNetwork(Network $network)
    {
        return match ($network) {
            Network::ETHEREUM => AlchemyConfig::ETH_MAINNET_DOMAIN,
            Network::POLYGON => AlchemyConfig::POLYGON_MAINNET_DOMAIN,
            Network::OPTIMISM => AlchemyConfig::OPTIMISM_MAINNET_DOMAIN,
            Network::ARBITRUM => AlchemyConfig::ARBITRUM_MAINNET_DOMAIN,
            Network::BASE => AlchemyConfig::BASE_MAINNET_DOMAIN,
            Network::SOLANA => AlchemyConfig::SOLANA_MAINNET_DOMAIN,
        };
    }
}
