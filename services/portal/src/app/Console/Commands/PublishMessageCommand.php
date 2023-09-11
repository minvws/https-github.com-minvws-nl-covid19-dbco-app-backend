<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\MessageQueue\Exceptions\MessageQueueException;
use App\Services\MessageQueue\MessageQueueService;
use App\Services\MessageQueue\OutgoingMessage;
use Illuminate\Console\Command;
use Ramsey\Uuid\Uuid;

use function json_decode;

/**
 * Publish message to message queue (mainly for testing purposes).
 */
class PublishMessageCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'message-queue:publish {queue} {data} {--id=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publish message to the given message queue.';

    /**
     * @throws MessageQueueException
     */
    public function handle(MessageQueueService $messageQueueService): int
    {
        /** @var string $id */
        $id = $this->option('id') ?? Uuid::uuid4()->toString();

        /** @var string $encodedData */
        $encodedData = $this->argument('data');

        /** @var array $data */
        $data = json_decode($encodedData, true);
        $message = new OutgoingMessage($id, $data);

        /** @var string $queueName */
        $queueName = $this->argument('queue');

        $queue = $messageQueueService->getQueue($queueName);
        $queue->publish($message);

        return self::SUCCESS;
    }
}
