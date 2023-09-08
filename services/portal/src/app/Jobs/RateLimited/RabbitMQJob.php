<?php

declare(strict_types=1);

namespace App\Jobs\RateLimited;

use Illuminate\Support\Arr;
use PhpAmqpLib\Exception\AMQPProtocolChannelException;
use VladimirYuldashev\LaravelQueueRabbitMQ\Queue\Jobs\RabbitMQJob as BaseRabbitMQJob;

use function is_numeric;

class RabbitMQJob extends BaseRabbitMQJob implements RateLimitable
{
    private bool $isPostponed = false;

    /**
     * Replicates the parent method for the most part, but does not increment the attempt count.
     *
     * @see BaseRabbitMQJob::release()
     *
     * @throws AMQPProtocolChannelException
     */
    public function postpone(int $duration): void
    {
        $this->released = true;
        $this->rabbitmq->laterRaw($duration, $this->message->getBody(), $this->queue, $this->getCurrentAttempts());
        $this->rabbitmq->ack($this);
        $this->isPostponed = true;
    }

    private function getCurrentAttempts(): int
    {
        $attempts = Arr::get($this->getRabbitMQMessageHeaders() ?? [], 'laravel.attempts', 0);

        return is_numeric($attempts) ? (int) $attempts : 0;
    }

    public function isPostponed(): bool
    {
        return $this->isPostponed;
    }
}
