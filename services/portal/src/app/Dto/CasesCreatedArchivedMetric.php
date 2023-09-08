<?php

declare(strict_types=1);

namespace App\Dto;

use DateTimeInterface;
use JsonSerializable;

class CasesCreatedArchivedMetric implements JsonSerializable
{
    public function __construct(
        public readonly DateTimeInterface $date,
        public readonly int $created,
        public readonly int $archived,
        public readonly ?DateTimeInterface $refreshedAt = null,
    ) {
    }

    /**
     * @codeCoverageIgnore
     */
    public function jsonSerialize(): array
    {
        return [
            'date' => $this->date->format('c'),
            'created' => $this->created,
            'archived' => $this->archived,
        ];
    }
}
