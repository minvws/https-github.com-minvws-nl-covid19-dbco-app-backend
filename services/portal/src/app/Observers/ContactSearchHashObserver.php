<?php

declare(strict_types=1);

namespace App\Observers;

use App\Jobs\ContactSearchHashJob;
use App\Models\CovidCase\Contact;
use MelchiorKokernoot\LaravelAutowireConfig\Config\Config;

class ContactSearchHashObserver
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

    public function saved(Contact $contact): void
    {
        ContactSearchHashJob::dispatch($contact->case_uuid)
            ->onConnection($this->connection)
            ->onQueue($this->queueName)
            ->delay($this->delay)
            ->afterCommit();
    }
}
