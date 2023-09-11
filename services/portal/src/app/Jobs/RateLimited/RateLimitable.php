<?php

declare(strict_types=1);

namespace App\Jobs\RateLimited;

interface RateLimitable
{
    public function postpone(int $duration): void;

    public function isPostponed(): bool;
}
