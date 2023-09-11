<?php

declare(strict_types=1);

namespace App\Observers;

use App\Jobs\EloquentTaskSearchHashJob;
use App\Models\Eloquent\EloquentTask;
use MelchiorKokernoot\LaravelAutowireConfig\Config\Config;

class EloquentTaskSearchHashObserver
{
    public function __construct(
        #[Config('searchhash.queue.connection')]
        private readonly string $connection,
        #[Config('searchhash.queue.queue_name')]
        private readonly string $queueName,
        #[Config('searchhash.queue.delayInSeconds')]
        private readonly int $delay,
    ) {
    }

    public function saved(EloquentTask $task): void
    {
        EloquentTaskSearchHashJob::dispatch($task->uuid)
            ->onConnection($this->connection)
            ->onQueue($this->queueName)
            ->delay($this->delay)
            ->afterCommit();
    }
}
