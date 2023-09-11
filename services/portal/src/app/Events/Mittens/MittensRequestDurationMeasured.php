<?php

declare(strict_types=1);

namespace App\Events\Mittens;

use Illuminate\Foundation\Events\Dispatchable;
use MinVWS\Timer\Duration;

class MittensRequestDurationMeasured
{
    use Dispatchable;

    public function __construct(
        public string $uri,
        public Duration $duration,
    )
    {
    }
}
