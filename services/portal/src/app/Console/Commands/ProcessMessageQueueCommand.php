<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\MessageQueue\Exceptions\MessageQueueException;
use App\Services\MessageQueue\Exceptions\UnrecoverableMessageExceptionInterface;
use App\Services\MessageQueue\IncomingMessage;
use App\Services\MessageQueue\MessageQueueService;
use App\Services\MessageQueue\Queue;
use Illuminate\Console\Command;
use Symfony\Component\Console\Command\SignalableCommandInterface;
use Throwable;

use function sprintf;

use const SIGINT;
use const SIGTERM;

class ProcessMessageQueueCommand extends Command implements SignalableCommandInterface
{
    /** @var string $signature */
    protected $signature = 'message-queue:process {queue}';

    /** @var string $description */
    protected $description = 'Process incoming messages of the given queue.';

    private MessageQueueService $messageQueueService;
    private ?Queue $queue = null;

    public function __construct(MessageQueueService $messageQueueService)
    {
        parent::__construct();

        $this->messageQueueService = $messageQueueService;
    }

    /**
     * @throws MessageQueueException
     */
    public function handle(): int
    {
        /** @var string $queue */
        $queue = $this->argument('queue');

        $this->queue = $this->messageQueueService->getQueue($queue);
        $this->queue->loop(fn (IncomingMessage $message) => $this->processIncomingMessage($queue, $message));

        return self::SUCCESS;
    }

    private function processIncomingMessage(string $queue, IncomingMessage $message): void
    {
        try {
            $this->output->writeln(sprintf('Processing message "%s"...', $message->getId()));
            $this->messageQueueService->processIncomingMessage($queue, $message);
            $this->output->writeln(sprintf('Message "%s" processed successfully!', $message->getId()));
            $message->ack();
        } catch (Throwable $e) {
            $this->output->error([sprintf('Error processing message "%s": %s', $message->getId(), $e->getMessage()), $e]);
            $requeue = !$e instanceof UnrecoverableMessageExceptionInterface;
            $message->nack($requeue);
        }
    }

    public function getSubscribedSignals(): array
    {
        return [SIGINT, SIGTERM];
    }

    public function handleSignal(int $signal): bool
    {
        if ($this->queue !== null) {
            $this->queue->stop();
        }

        $this->messageQueueService->stop();

        return false;
    }
}
