<?php

declare(strict_types=1);

namespace App\Models\CovidCase\Codables;

use App\Models\CovidCase\Trip;
use App\Models\Versions\CovidCase\Abroad\AbroadCommon;
use App\Models\Versions\CovidCase\Trip\TripV1;
use MinVWS\Codable\EncodingContainer;
use MinVWS\Codable\StaticEncodableDecorator;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;

use function assert;
use function collect;
use function is_array;
use function sprintf;

class AbroadEncoder implements StaticEncodableDecorator
{
    public static function encode(object $object, EncodingContainer $container): void
    {
        assert($object instanceof AbroadCommon);
        $container->wasAbroad = $object->wasAbroad;

        if ($object->wasAbroad !== YesNoUnknown::yes() || !is_array($object->trips)) {
            return;
        }

        $container->trips = self::tripToArray($object->trips);
    }

    /**
     * @param array<TripV1> $trips
     */
    private static function tripToArray(array $trips): array
    {
        return collect($trips)->map(static function (Trip $trip) {
            $info = collect($trip->countries)->pluck('label');

            if ($trip->transportation) {
                foreach ($trip->transportation as $transportation) {
                    $info->push($transportation->label);
                }
            }

            if ($trip->departureDate || $trip->returnDate) {
                $info->push(sprintf(
                    '%s - %s',
                    $trip->departureDate ? $trip->departureDate->format('Y-m-d') : '?',
                    $trip->returnDate ? $trip->returnDate->format('Y-m-d') : '?',
                ));
            }

            return $info->implode(', ');
        })->toArray();
    }
}
