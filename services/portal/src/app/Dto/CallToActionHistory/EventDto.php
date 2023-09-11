<?php

declare(strict_types=1);

namespace App\Dto\CallToActionHistory;

use App\Models\Eloquent\EloquentUser;
use DateTimeInterface;
use MinVWS\DBCO\Enum\Models\CallToActionEvent;

class EventDto
{
    public function __construct(
        public readonly CallToActionEvent $callToActionEvent,
        public readonly ?EloquentUser $user,
        public readonly ?DateTimeInterface $dateTime,
        public readonly ?string $note = null,
    ) {
    }
}
