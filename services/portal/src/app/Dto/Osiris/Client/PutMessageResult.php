<?php

declare(strict_types=1);

namespace App\Dto\Osiris\Client;

use App\ValueObjects\OsirisNumber;

final class PutMessageResult
{
    /**
     * @param array<string> $warnings
     */
    public function __construct(
        public readonly OsirisNumber $osirisNumber,
        public readonly array $warnings,
    ) {
    }
}
