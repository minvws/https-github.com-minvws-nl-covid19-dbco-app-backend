<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use RuntimeException;
use Throwable;
use VladimirYuldashev\LaravelQueueRabbitMQ\Console\ExchangeDeleteCommand;
use VladimirYuldashev\LaravelQueueRabbitMQ\Console\QueueDeleteCommand;

use function sprintf;

final class MessageQueueSetupMigrateCommand extends Command
{
    private const LEGACY_DBCO_EXCHANGE = 'dbco';
    private const LEGACY_TEST_RESULT_QUEUE = 'test_result';
    private const LEGACY_TEST_RESULT_FAILED_QUEUE = 'test_result.failed';

    /**
     * @var string
     */
    protected $signature = 'message-queue:setup-migrate';

    /**
     * @var string
     */
    protected $description = 'Reconfigures the message queue for a smooth migration to the Laravel queue worker';

    public function handle(): int
    {
        if (!$this->confirmCommandRun()) {
            $this->line('Aborted command as you wish');
            return self::SUCCESS;
        }

        $this->info('Started migration of message queues...');

        try {
            $this->dropQueues();
            $this->dropExchange();
            $this->setupNewConfiguration();
        } catch (Throwable $throwable) {
            $this->error($throwable->getMessage());
            return self::FAILURE;
        }

        $this->info('Successful migration of message queues');

        return self::SUCCESS;
    }

    private function confirmCommandRun(): bool
    {
        $noInteraction = (bool) $this->option('no-interaction');
        $default = $noInteraction === true;

        return $this->confirm(
            '[CAUTION] this command will drop queues! Any messages in the queues will get lost. Do you want to proceed?',
            $default,
        );
    }

    private function dropQueues(): void
    {
        $queuesToDrop = [
            self::LEGACY_TEST_RESULT_QUEUE,
            self::LEGACY_TEST_RESULT_FAILED_QUEUE,
        ];

        foreach ($queuesToDrop as $queueToDrop) {
            if (self::SUCCESS !== $this->callQueueDeleteCommand($queueToDrop)) {
                throw new RuntimeException(sprintf('Failed to drop "%s" queue', $queueToDrop));
            }

            $this->line(sprintf('Successfully dropped "%s" queue', $queueToDrop));
        }
    }

    private function dropExchange(): void
    {
        if (self::SUCCESS !== $this->callExchangeDeleteCommand()) {
            throw new RuntimeException(sprintf('Failed to drop "%s" exchange', self::LEGACY_DBCO_EXCHANGE));
        }

        $this->line(sprintf('Successfully dropped "%s" exchange', self::LEGACY_DBCO_EXCHANGE));
    }

    private function setupNewConfiguration(): void
    {
        if (self::SUCCESS !== $this->callSilent(MessageQueueSetupCommand::class)) {
            throw new RuntimeException('Failed to configure queues');
        }

        $this->line('Successfully configured queues');
    }

    private function callQueueDeleteCommand(string $name): int
    {
        return $this->callSilent(QueueDeleteCommand::class, ['name' => $name]);
    }

    private function callExchangeDeleteCommand(): int
    {
        return $this->callSilent(ExchangeDeleteCommand::class, ['name' => self::LEGACY_DBCO_EXCHANGE]);
    }
}
