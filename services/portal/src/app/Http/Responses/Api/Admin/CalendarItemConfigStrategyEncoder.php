<?php

declare(strict_types=1);

namespace App\Http\Responses\Api\Admin;

use App\Models\Policy\CalendarItemConfigStrategy;
use MinVWS\Codable\EncodableDecorator;
use MinVWS\Codable\EncodingContainer;

final class CalendarItemConfigStrategyEncoder implements EncodableDecorator
{
    public function encode(object $value, EncodingContainer $container): void
    {
        /** @var CalendarItemConfigStrategy $value */
        $container->uuid = $value->uuid;
        $container->strategyType = $value->strategy_type;
        $container->identifierType = $value->identifier_type;
        $container->dateOperations = $value->dateOperations;
    }
}
