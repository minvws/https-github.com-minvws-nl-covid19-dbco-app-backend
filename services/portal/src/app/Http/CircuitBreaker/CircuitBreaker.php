<?php

declare(strict_types=1);

namespace App\Http\CircuitBreaker;

interface CircuitBreaker
{
    public function isAvailable(string $service): bool;

    public function registerFailure(string $service): void;

    public function registerSuccess(string $service): void;
}
