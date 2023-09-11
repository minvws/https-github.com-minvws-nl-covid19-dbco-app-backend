<?php

declare(strict_types=1);

namespace App\Http\Responses\Api\Admin;

use App\Models\Policy\CalendarItemConfig;
use MinVWS\Codable\EncodableDecorator;
use MinVWS\Codable\EncodingContainer;

final class CalendarItemConfigEncoder implements EncodableDecorator
{
    public function encode(object $value, EncodingContainer $container): void
    {
        /** @var CalendarItemConfig $value */
        $container->uuid = $value->uuid;
        $container->label = $value->calendarItem->label;
        $container->isHidden = $value->is_hidden;
        $container->isHideable = $value->calendarItem->isHideable();
        $container->itemType = $value->calendarItem->calendar_item_enum->value;
        $container->strategies = $value->calendarItemConfigStrategies;
    }
}
