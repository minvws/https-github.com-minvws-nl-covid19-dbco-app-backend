<?php

declare(strict_types=1);

namespace App\Services\SecureMail;

final readonly class SecureMailStatusUpdateCollection
{
    /**
     * @param array<SecureMailStatusUpdate> $secureMailStatusUpdates
     */
    public function __construct(
        public int $total,
        public int $count,
        public array $secureMailStatusUpdates,
    ) {
    }
}
