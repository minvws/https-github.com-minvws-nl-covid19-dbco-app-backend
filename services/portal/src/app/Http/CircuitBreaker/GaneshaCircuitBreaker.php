<?php

declare(strict_types=1);

namespace App\Http\CircuitBreaker;

use Ackintosh\Ganesha;

final class GaneshaCircuitBreaker implements CircuitBreaker
{
    public function __construct(
        private readonly Ganesha $ganesha,
    ) {
    }

    public function isAvailable(string $service): bool
    {
        return $this->ganesha->isAvailable($service);
    }

    public function registerFailure(string $service): void
    {
        $this->ganesha->failure($service);
    }

    public function registerSuccess(string $service): void
    {
        $this->ganesha->success($service);
    }
}
