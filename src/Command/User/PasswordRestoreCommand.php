<?php

namespace App\Command\User;

use App\Repository\PasswordRestoreRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Scheduler\Attribute\AsCronTask;

#[AsCronTask('@weekly', method: 'clear')]
#[AsCommand(
    name: 'app:password-restore.clear',
    description: 'Clean up old rows in password restore'
)]
class PasswordRestoreCommand extends Command
{
    public function __construct(
        protected PasswordRestoreRepository $passwordRestoreRepository,
        #[Autowire(service: 'monolog.logger.commands')]
        protected LoggerInterface $logger,
    ){
        parent::__construct();
    }

    public function execute(InputInterface $input, OutputInterface $output) : int
    {
        $this->clear();

        return Command::SUCCESS;
    }

    public function clear() : void
    {
        $arIds = $this->passwordRestoreRepository->getOldRows();
        $this->logger->debug('count id for delete', [$arIds]);

        try {
            $result = $this->passwordRestoreRepository->deleteByIds($arIds);
            $this->logger->alert('success deleting', ['count deleting' => $result]);
        } catch (\Throwable $exception) {
            $this->logger->error($exception->getMessage());
        }
    }
}
