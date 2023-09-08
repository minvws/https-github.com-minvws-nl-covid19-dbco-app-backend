<?php

declare(strict_types=1);

namespace App\Http\Responses\Api\Admin;

use App\Models\Policy\CalendarItem;
use MinVWS\Codable\EncodableDecorator;
use MinVWS\Codable\EncodingContainer;

final class CalendarItemEncoder implements EncodableDecorator
{
    public function encode(object $value, EncodingContainer $container): void
    {
        /** @var CalendarItem $value */
        $container->uuid = $value->uuid;
        $container->policyVersionUuid = $value->policy_version_uuid;
        $container->policyVersionStatus = $value->policyVersion?->status->value;
        $container->label = $value->label;
        $container->fixedCalendarName = $value->fixed_calendar_item_enum?->value;
        $container->isDeletable = $value->isDeletable();
        $container->personType = $value->person_type_enum->value;
        $container->itemType = $value->calendar_item_enum->value;
        $container->color = $value->color_enum->value;
    }
}
