<?php

declare(strict_types=1);

namespace App\Events;

use Illuminate\Contracts\Queue\Job;

/**
 * @method static void dispatch(Job $job, float $duration)
 */
class JobHandled extends Event
{
    public function __construct(
        public readonly Job $job,
        public readonly float $duration,
    ) {
    }
}
