<?php

declare(strict_types=1);

namespace App\Observers;

use App\Jobs\ContactSearchHashJob;
use App\Jobs\IndexSearchHashJob;
use App\Models\CovidCase\Index;
use Illuminate\Bus\Dispatcher;
use MelchiorKokernoot\LaravelAutowireConfig\Config\Config;

class IndexSearchHashObserver
{
    public function __construct(
        #[Config('searchhash.queue.connection')]
        private readonly string $connection,
        #[Config('searchhash.queue.queue_name')]
        private readonly string $queueName,
        #[Config('searchhash.queue.delayInSeconds')]
        private readonly ?int $delay,
        private readonly Dispatcher $busDispatcher,
    ) {
    }

    public function saved(Index $index): void
    {
        $this->busDispatcher
            ->chain([
                (new IndexSearchHashJob($index->case_uuid))->afterCommit(),
                (new ContactSearchHashJob($index->case_uuid))->afterCommit(),
            ])
            ->onConnection($this->connection)
            ->onQueue($this->queueName)
            ->delay($this->delay)
            ->dispatch();
    }
}
