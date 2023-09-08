<?php

declare(strict_types=1);

namespace App\Http\Responses\Api\Admin;

use App\Models\Policy\CalendarView;
use MinVWS\Codable\EncodableDecorator;
use MinVWS\Codable\EncodingContainer;

final class CalendarViewEncoder implements EncodableDecorator
{
    public function encode(object $value, EncodingContainer $container): void
    {
        /** @var CalendarView $value */
        $container->uuid = $value->uuid;
        $container->label = $value->label;
        $container->policyVersionUuid = $value->policy_version_uuid;
        $container->policyVersionStatus = $value->policyVersion?->status->value;
        $container->calendarViewEnum = $value->calendar_view_enum->value;
        $container->calendarItems = $value->calendarItems()->get()->all();
    }
}
