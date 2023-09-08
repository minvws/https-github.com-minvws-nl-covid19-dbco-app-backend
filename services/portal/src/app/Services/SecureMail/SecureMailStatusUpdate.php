<?php

declare(strict_types=1);

namespace App\Services\SecureMail;

use Carbon\CarbonImmutable;
use MinVWS\DBCO\Enum\Models\MessageStatus;

final readonly class SecureMailStatusUpdate
{
    public function __construct(
        public string $id,
        public ?CarbonImmutable $notificationSentAt,
        public MessageStatus $status,
    ) {
    }
}
