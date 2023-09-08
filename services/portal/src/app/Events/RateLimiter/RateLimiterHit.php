<?php

declare(strict_types=1);

namespace App\Events\RateLimiter;

use App\Events\Event;

/**
 * @method static void dispatch(int $hitCount, string $limiterName)
 */
final class RateLimiterHit extends Event
{
    public function __construct(
        public readonly int $hitCount,
        public readonly string $limiterName,
    ) {
    }
}
