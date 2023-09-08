<?php

declare(strict_types=1);

namespace App\Dto\CallToActionHistory;

use DateTimeInterface;
use Illuminate\Support\Collection;

class CallToActionHistoryDto
{
    public function __construct(
        public readonly Collection $events,
        public readonly ?DateTimeInterface $createdAt,
        public readonly ?DateTimeInterface $expiresAt,
        public readonly ?DateTimeInterface $deletedAt,
        public readonly ?string $subject,
        public readonly ?string $description,
        public readonly ?string $userRoles,
    ) {
    }
}
