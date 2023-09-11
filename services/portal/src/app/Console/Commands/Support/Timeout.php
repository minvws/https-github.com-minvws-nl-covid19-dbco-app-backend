<?php

declare(strict_types=1);

namespace App\Console\Commands\Support;

use Carbon\CarbonImmutable;

final class Timeout
{
    private ?CarbonImmutable $timeout = null;

    public function setTimeoutInMinutes(int $minutes): self
    {
        $this->timeout = $this->getNow()->addMinutes($minutes);

        return $this;
    }

    public function getNow(): CarbonImmutable
    {
        return CarbonImmutable::now();
    }

    /**
     * @phpstan-assert-if-true !null $this->timeout
     */
    public function isSet(): bool
    {
        return $this->timeout !== null;
    }

    public function timedOut(): bool
    {
        if ($this->isSet() === false) {
            return false;
        }

        return $this->getNow()->isAfter($this->timeout);
    }
}
