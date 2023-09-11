<?php

declare(strict_types=1);

namespace App\Http\Responses\CallToAction;

use App\Dto\CallToActionHistory\CallToActionHistoryDto;
use App\Dto\CallToActionHistory\EventDto;
use MinVWS\Codable\EncodableDecorator;
use MinVWS\Codable\EncodingContainer;

use function explode;

class CallToActionHistoryDtoEncoder implements EncodableDecorator
{
    public function encode(object $value, EncodingContainer $container): void
    {
        if (!$value instanceof CallToActionHistoryDto) {
            return;
        }

        /** @phpstan-ignore-next-line */
        $container->events = $value->events->map(static fn(EventDto $event) => [
            'datetime' => $event->dateTime,
            'callToActionEvent' => $event->callToActionEvent->value,
            'note' => $event->note,
            'user' => [
                'name' => $event->user?->name,
                'roles' => explode(',', $event->user?->roles ?? ''),
                'uuid' => $event->user?->uuid,
            ],
        ])->values();
        $container->deletedAt = $value->deletedAt;
    }
}
