<?php

declare(strict_types=1);

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;

final class ImportTestResultReport implements ShouldQueue
{
    public function __construct(
        public readonly string $messageId,
        public readonly string $payload,
    ) {
    }
}
